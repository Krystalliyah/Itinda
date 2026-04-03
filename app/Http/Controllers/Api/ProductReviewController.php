<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductReviewController extends Controller
{
    /**
     * Initialize tenancy, run callback, then end tenancy.
     * IMPORTANT: Always return plain arrays/scalars from the callback,
     * never Eloquent collections or paginators — they hold live DB
     * connection references that break after tenancy()->end().
     */
    private function runForTenant(\App\Models\Tenant $tenant, callable $callback): mixed
    {
        tenancy()->initialize($tenant);
        try {
            return $callback();
        } finally {
            tenancy()->end();
        }
    }

    public function index(Request $request, $storeId, $productId)
{
    $perPage = $request->get('per_page', 10);
    $rating  = $request->get('rating');
    $sort    = $request->get('sort', 'newest');
    $user    = auth()->user();
    $userId  = $user?->id;

    $tenant = \App\Models\Tenant::find($storeId);
    if (!$tenant) {
        return response()->json(['error' => 'Store not found'], 404);
    }

    // Extract everything as plain arrays INSIDE the tenant context
    $result = $this->runForTenant($tenant, function () use ($productId, $perPage, $rating, $sort, $userId) {
        $query = \App\Models\ProductReview::where('product_id', $productId);

        $query->where(function ($q) use ($userId) {
            $q->where('is_approved', true);
            if ($userId) {
                $q->orWhere('user_id', $userId);
            }
        });

        if ($rating) {
            $query->where('rating', $rating);
        }

        switch ($sort) {
            case 'oldest':  $query->orderBy('created_at', 'asc'); break;
            case 'helpful': $query->orderBy('helpful_count', 'desc'); break;
            case 'highest': $query->orderBy('rating', 'desc'); break;
            case 'lowest':  $query->orderBy('rating', 'asc'); break;
            default:        $query->orderBy('created_at', 'desc');
        }

        $paginator = $query->paginate($perPage);

        // Convert to plain arrays NOW while tenant connection is still active
        return [
            'items'        => collect($paginator->items())->map(fn($r) => $r->toArray())->all(),
            'total'        => $paginator->total(),
            'per_page'     => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'from'         => $paginator->firstItem(),
            'to'           => $paginator->lastItem(),
        ];
    });

    // tenancy()->end() already called — safe to use central connection now
    $centralConnection = config('tenancy.database.central_connection', 'central');
    $userIds  = collect($result['items'])->pluck('user_id')->unique()->filter()->values();
    $rawUsers = DB::connection($centralConnection)
        ->table('users')
        ->whereIn('id', $userIds)
        ->get(['id', 'name', 'email'])
        ->keyBy('id');

    // Merge user data into review arrays (items are now plain arrays, not Eloquent models)
    $items = collect($result['items'])->map(function ($review) use ($rawUsers) {
        $user = $rawUsers->get($review['user_id']);
        $review['user'] = $user ? (array) $user : null;
        return $review;
    });

    return response()->json([
        'success' => true,
        'data' => [
            'data'         => $items,
            'total'        => $result['total'],
            'per_page'     => $result['per_page'],
            'current_page' => $result['current_page'],
            'last_page'    => $result['last_page'],
            'from'         => $result['from'],
            'to'           => $result['to'],
        ],
    ]);
}

    public function stats($storeId, $productId)
    {
        $tenant = \App\Models\Tenant::find($storeId);
        if (!$tenant) {
            return response()->json(['error' => 'Store not found'], 404);
        }

        $stats = $this->runForTenant($tenant, function () use ($productId) {
            $product = \App\Models\Product::find($productId);
            if (!$product) return null;

            $distribution = $product->rating_distribution ?? [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            $total        = $product->total_reviews;

            $percentages = [];
            for ($i = 5; $i >= 1; $i--) {
                $count           = $distribution[$i] ?? 0;
                $percentages[$i] = $total > 0 ? round(($count / $total) * 100) : 0;
            }

            // Return plain array — no Eloquent model references
            return [
                'average_rating'      => $product->formatted_rating,
                'total_reviews'       => $total,
                'rating_distribution' => $distribution,
                'rating_percentages'  => $percentages,
            ];
        });

        return response()->json(['success' => true, 'data' => $stats]);
    }

    public function store(Request $request, $storeId, $productId)
    {
        try {
            $validated = $request->validate([
                'rating'   => 'required|integer|min:1|max:5',
                'title'    => 'nullable|string|max:255',
                'comment'  => 'required|string|min:3|max:5000',
                'images'   => 'nullable|array|max:5',
                'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            $user   = auth()->user();
            $userId = $user?->id;

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Please login to leave a review'], 401);
            }

            $tenant = \App\Models\Tenant::find($storeId);
            if (!$tenant) {
                return response()->json(['success' => false, 'message' => 'Store not found'], 404);
            }

            // S3 uploads happen BEFORE tenant context — S3 is global, not tenant-specific
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path         = $image->store('product-reviews', 's3');
                    $imagePaths[] = Storage::disk('s3')->url($path);
                }
            }

            // Return plain array from callback, not Eloquent model
            $reviewData = $this->runForTenant($tenant, function () use ($productId, $userId, $validated, $imagePaths) {
                $product = \App\Models\Product::find($productId);
                if (!$product) throw new \Exception('Product not found');

                $exists = \App\Models\ProductReview::where('product_id', $productId)
                    ->where('user_id', $userId)
                    ->exists();
                if ($exists) throw new \Exception('You have already reviewed this product');

                $hasPurchased = \App\Models\OrderItem::where('product_id', $productId)
                    ->whereHas('order', fn($q) => $q->where('user_id', $userId)->where('status', 'completed'))
                    ->exists();

                $review = \App\Models\ProductReview::create([
                    'product_id'           => $productId,
                    'user_id'              => $userId,
                    'rating'               => $validated['rating'],
                    'title'                => $validated['title'] ?? null,
                    'comment'              => $validated['comment'],
                    'images'               => !empty($imagePaths) ? $imagePaths : null,
                    'is_verified_purchase' => $hasPurchased,
                    'is_approved'          => true,
                ]);

                $product->updateRatingStats();

                // Return plain array — safe to use after tenancy()->end()
                return $review->toArray();
            });

            // Attach user info as plain array
            $reviewData['user'] = [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully!',
                'data'    => $reviewData,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            if (tenancy()->initialized) tenancy()->end();
            Log::error('Review store error: ' . $e->getMessage(), [
                'storeId'   => $storeId,
                'productId' => $productId,
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
public function helpful(Request $request, $storeId, $reviewId)
{
    $user   = auth()->user();
    $userId = $user?->id;

    if (!$user) return response()->json(['message' => 'Please login'], 401);

    $tenant = \App\Models\Tenant::find($storeId);
    if (!$tenant) return response()->json(['message' => 'Store not found'], 404);

    try {
        // Return plain scalar from callback
        $result = $this->runForTenant($tenant, function () use ($reviewId, $userId) {
            $existingVote = \App\Models\ReviewHelpfulnessVote::where('review_id', $reviewId)
                ->where('user_id', $userId)
                ->first();

            $review = \App\Models\ProductReview::find($reviewId);
            
            if ($existingVote) {
                // User already voted - remove the vote (unlike)
                $existingVote->delete();
                $review->decrement('helpful_count');
                $action = 'unvoted';
            } else {
                // User hasn't voted - add the vote (like)
                \App\Models\ReviewHelpfulnessVote::create([
                    'review_id' => $reviewId,
                    'user_id'   => $userId,
                ]);
                $review->increment('helpful_count');
                $action = 'voted';
            }

            // Return plain array with both count and action
            return [
                'helpful_count' => $review->fresh()->helpful_count,
                'action' => $action
            ];
        });

        return response()->json([
            'success' => true, 
            'helpful_count' => $result['helpful_count'],
            'action' => $result['action']
        ]);

    } catch (\Exception $e) {
        if (tenancy()->initialized) tenancy()->end();
        return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }
}
}
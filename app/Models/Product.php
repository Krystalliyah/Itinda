<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    // Remove or comment out connection
    // protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category_id',
        'barcode',
        'sku',
        'image_path',
        'is_active',
        'average_rating',
        'total_reviews',
        'total_ratings',
        'rating_distribution',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'average_rating' => 'float',
        'rating_distribution' => 'array',
    ];

    // Add category relationship
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class)->approved();
    }

    public function allReviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function updateRatingStats()
    {
        $reviews = $this->reviews()->where('is_approved', true)->get();

        $totalReviews = $reviews->count();
        $totalRatings = $reviews->sum('rating');
        $averageRating = $totalReviews > 0 ? $totalRatings / $totalReviews : 0;

        $distribution = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];

        $this->update([
            'average_rating' => round($averageRating, 1),
            'total_reviews' => $totalReviews,
            'total_ratings' => $totalRatings,
            'rating_distribution' => $distribution,
        ]);

        return $this;
    }

    public function getFormattedRatingAttribute()
    {
        return number_format($this->average_rating, 1);
    }

    public function getRatingPercentage($stars)
    {
        if ($this->total_reviews === 0) {
            return 0;
        }

        $distribution = $this->rating_distribution ?? [];
        $count = $distribution[$stars] ?? 0;

        return round(($count / $this->total_reviews) * 100);
    }

    public function listings(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class)->withTimestamps();
    }
}

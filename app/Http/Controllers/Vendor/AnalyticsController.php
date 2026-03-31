<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $status = 'completed';

        // Resolve date range from query params, default to current week
        [$start, $end, $rangeLabel] = $this->resolveRange(
            $request->query('preset', 'this_week'),
            $request->query('from'),
            $request->query('to'),
        );

        // Previous period of equal length for growth comparison
        $length = $start->diffInDays($end);
        $previousStart = $start->copy()->subDays($length + 1)->startOfDay();
        $previousEnd   = $start->copy()->subDay()->endOfDay();

        $weeklySales  = $this->buildDailySales($status, $start, $end);
        $categoryMix  = $this->buildCategoryMix($status, $start, $end);
        $peakWindows  = $this->buildPeakWindows($status, $start, $end);
        $topProducts  = $this->buildTopProducts($status, $start, $end, $previousStart, $previousEnd);

        $currentRevenue  = collect($weeklySales)->sum('revenue');
        $previousRevenue = (float) Order::where('status', $status)
            ->whereBetween('placed_at', [$previousStart, $previousEnd])
            ->sum('total');

        $growthSignal = $this->formatGrowth($currentRevenue, $previousRevenue);

        $recentInsights = $this->buildInsights(
            $weeklySales, $categoryMix, $peakWindows, $topProducts, $growthSignal
        );

        return Inertia::render('vendor/Analytics', [
            'weeklySales'    => $weeklySales,
            'categoryMix'    => $categoryMix,
            'peakWindows'    => $peakWindows,
            'topProducts'    => $topProducts,
            'recentInsights' => $recentInsights,
            'growthSignal'   => $growthSignal,
            'rangeLabel'     => $rangeLabel,
            'activePreset'   => $request->query('preset', 'this_week'),
            'activeFrom'     => $request->query('from', ''),
            'activeTo'       => $request->query('to', ''),
        ]);
    }

    // -------------------------------------------------------------------------
    // Range resolver
    // -------------------------------------------------------------------------

    private function resolveRange(?string $preset, ?string $from, ?string $to): array
    {
        switch ($preset) {
            case 'this_week':
                $start = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
                $end   = now()->endOfWeek(Carbon::SUNDAY)->endOfDay();
                return [$start, $end, 'This week'];

            case 'last_7':
                $start = now()->subDays(6)->startOfDay();
                $end   = now()->endOfDay();
                return [$start, $end, 'Last 7 days'];

            case 'this_month':
                $start = now()->startOfMonth()->startOfDay();
                $end   = now()->endOfMonth()->endOfDay();
                return [$start, $end, 'This month'];

            case 'last_30':
                $start = now()->subDays(29)->startOfDay();
                $end   = now()->endOfDay();
                return [$start, $end, 'Last 30 days'];

            case 'custom':
                $start = $from ? Carbon::parse($from)->startOfDay() : now()->subDays(6)->startOfDay();
                $end   = $to   ? Carbon::parse($to)->endOfDay()     : now()->endOfDay();
                return [$start, $end, $start->format('M j') . ' – ' . $end->format('M j, Y')];

            default:
                $start = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
                $end   = now()->endOfWeek(Carbon::SUNDAY)->endOfDay();
                return [$start, $end, 'This week'];
        }
    }

    // -------------------------------------------------------------------------
    // Builders
    // -------------------------------------------------------------------------

    private function buildDailySales(string $status, CarbonInterface $start, CarbonInterface $end): array
    {
        $rows = Order::query()
            ->where('status', $status)
            ->whereBetween('placed_at', [$start, $end])
            ->selectRaw('DATE(placed_at) as order_date, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('order_date')
            ->get()
            ->keyBy('order_date');

        $days = (int) $start->diffInDays($end) + 1;

        return collect(range(0, $days - 1))
            ->map(function (int $offset) use ($start, $rows, $days) {
                $date = $start->copy()->addDays($offset);
                $key  = $date->toDateString();
                $row  = $rows->get($key);

                // For ranges > 14 days use short date label, otherwise day name
                $label = $days > 14
                    ? $date->format('M j')
                    : $date->format('D');

                return [
                    'day'     => $label,
                    'revenue' => round((float) ($row->revenue ?? 0), 2),
                    'orders'  => (int) ($row->orders ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    private function buildCategoryMix(string $status, CarbonInterface $start, CarbonInterface $end): array
    {
        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', $status)
            ->whereBetween('orders.placed_at', [$start, $end])
            ->selectRaw('products.category_id, SUM(order_items.total) as revenue')
            ->groupBy('products.category_id')
            ->get();

        $categoryNames = Category::whereIn('id', $rows->pluck('category_id')->filter()->unique())
            ->pluck('name', 'id');

        $totalRevenue = (float) $rows->sum('revenue');

        if ($totalRevenue <= 0) return [];

        return $rows
            ->map(fn ($row) => [
                'label' => $row->category_id
                    ? ($categoryNames[$row->category_id] ?? 'Unknown')
                    : 'Uncategorized',
                'value' => (int) round(((float) $row->revenue / $totalRevenue) * 100),
            ])
            ->sortByDesc('value')
            ->values()
            ->all();
    }

    private function buildPeakWindows(string $status, CarbonInterface $start, CarbonInterface $end): array
    {
        $rows = Order::query()
            ->where('status', $status)
            ->whereBetween('placed_at', [$start, $end])
            ->selectRaw("
                CASE
                    WHEN HOUR(placed_at) >= 8  AND HOUR(placed_at) < 10 THEN '8:00 AM - 10:00 AM'
                    WHEN HOUR(placed_at) >= 11 AND HOUR(placed_at) < 13 THEN '11:00 AM - 1:00 PM'
                    WHEN HOUR(placed_at) >= 14 AND HOUR(placed_at) < 16 THEN '2:00 PM - 4:00 PM'
                    WHEN HOUR(placed_at) >= 17 AND HOUR(placed_at) < 19 THEN '5:00 PM - 7:00 PM'
                    ELSE 'Other'
                END as label,
                COUNT(*) as orders
            ")
            ->groupBy('label')
            ->get()
            ->pluck('orders', 'label');

        return collect(['11:00 AM - 1:00 PM', '2:00 PM - 4:00 PM', '5:00 PM - 7:00 PM', '8:00 AM - 10:00 AM'])
            ->map(fn ($label) => ['label' => $label, 'orders' => (int) ($rows[$label] ?? 0)])
            ->sortByDesc('orders')
            ->values()
            ->all();
    }

    private function buildTopProducts(
        string $status,
        CarbonInterface $start,
        CarbonInterface $end,
        CarbonInterface $previousStart,
        CarbonInterface $previousEnd
    ): array {
        $current = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', $status)
            ->whereBetween('orders.placed_at', [$start, $end])
            ->selectRaw('products.id as product_id, products.name as product_name,
                         SUM(order_items.quantity) as orders_count, SUM(order_items.total) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('orders_count')
            ->limit(5)
            ->get();

        $previous = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', $status)
            ->whereBetween('orders.placed_at', [$previousStart, $previousEnd])
            ->selectRaw('products.id as product_id, SUM(order_items.quantity) as orders_count')
            ->groupBy('products.id')
            ->get()
            ->pluck('orders_count', 'product_id');

        return $current
            ->map(fn ($row) => [
                'name'    => $row->product_name,
                'orders'  => (int) $row->orders_count,
                'revenue' => round((float) $row->revenue, 2),
                'growth'  => $this->formatGrowth((int) $row->orders_count, (int) ($previous[$row->product_id] ?? 0)),
            ])
            ->values()
            ->all();
    }

    private function buildInsights(
        array $weeklySales,
        array $categoryMix,
        array $peakWindows,
        array $topProducts,
        string $growthSignal
    ): array {
        $bestDay    = collect($weeklySales)->sortByDesc('revenue')->first();
        $topCat     = collect($categoryMix)->sortByDesc('value')->first();
        $topWindow  = collect($peakWindows)->sortByDesc('orders')->first();
        $topProduct = collect($topProducts)->first();

        $insights = [];

        if (($bestDay['revenue'] ?? 0) > 0) {
            $insights[] = "{$bestDay['day']} is your strongest sales day in this period with {$bestDay['orders']} completed orders.";
        } else {
            $insights[] = 'No completed orders have been recorded yet for the selected period.';
        }

        if ($topCat) {
            $insights[] = "{$topCat['label']} leads your category mix at {$topCat['value']}% of revenue.";
        }

        if ($topWindow && $topWindow['orders'] > 0) {
            $insights[] = "{$topWindow['label']} is the busiest fulfillment window.";
        }

        if ($topProduct) {
            $insights[] = "{$topProduct['name']} is your top-selling product by order volume.";
        }

        if ($growthSignal !== '0%') {
            $insights[] = "Period-over-period revenue trend is {$growthSignal}.";
        }

        return array_slice($insights, 0, 3);
    }

    private function formatGrowth(float|int $current, float|int $previous): string
    {
        $current  = (float) $current;
        $previous = (float) $previous;

        if ($previous <= 0) return $current > 0 ? 'New' : '0%';

        $rounded = (int) round((($current - $previous) / $previous) * 100);
        return ($rounded > 0 ? '+' : '') . $rounded . '%';
    }
}

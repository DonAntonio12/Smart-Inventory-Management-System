<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $now = now();
        $thirtyDaysAgo = $now->copy()->subDays(30);
        $sixtyDaysAgo = $now->copy()->subDays(60);

        // Calculate 30-day sales stats per product
        $salesByProduct = Transaction::query()
            ->where('type', 'sale')
            ->where('occurred_at', '>=', $thirtyDaysAgo)
            ->select('product_id')
            ->selectRaw('SUM(ABS(quantity)) as units_sold, SUM(amount) as revenue')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $products = Product::query()->orderBy('name')->get();

        $analyticsProducts = $products->map(function (Product $product) use ($salesByProduct) {
            $sales = $salesByProduct->get($product->id);
            $unitsSold30 = (int) ($sales->units_sold ?? 0);
            $revenue30 = (float) ($sales->revenue ?? 0);
            $dailyVelocity = round($unitsSold30 / 30, 2);

            $daysRemaining = $dailyVelocity > 0 ? ceil($product->quantity / $dailyVelocity) : 999;
            $suggestedReorder = $dailyVelocity > 0
                ? max(0, ceil(($dailyVelocity * 14) + $product->low_stock_threshold - $product->quantity))
                : ($product->quantity <= $product->low_stock_threshold ? $product->low_stock_threshold * 2 : 0);

            $urgency = 'healthy';
            if ($product->quantity === 0) {
                $urgency = 'critical';
            } elseif ($daysRemaining <= 3 || $product->quantity <= $product->low_stock_threshold) {
                $urgency = 'critical';
            } elseif ($daysRemaining <= 7) {
                $urgency = 'warning';
            }

            return (object) [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category ?: 'Uncategorized',
                'brand' => $product->brand ?: 'Unbranded',
                'quantity' => $product->quantity,
                'low_stock_threshold' => $product->low_stock_threshold,
                'expiration_date' => $product->expiration_date,
                'units_sold_30' => $unitsSold30,
                'revenue_30' => $revenue30,
                'daily_velocity' => $dailyVelocity,
                'projected_30_demand' => ceil($dailyVelocity * 30),
                'days_remaining' => $daysRemaining,
                'suggested_reorder' => (int) $suggestedReorder,
                'urgency' => $urgency,
            ];
        });

        // Top 5 products needing reorder
        $reorderRecommendations = $analyticsProducts
            ->filter(fn ($p) => $p->urgency !== 'healthy' || $p->suggested_reorder > 0)
            ->sortBy(fn ($p) => $p->days_remaining)
            ->take(6)
            ->values();

        // 14-day sales trend chart
        $fourteenDaysAgo = $now->copy()->subDays(13)->startOfDay();
        $dailySalesQuery = Transaction::query()
            ->where('type', 'sale')
            ->where('occurred_at', '>=', $fourteenDaysAgo)
            ->selectRaw('DATE(occurred_at) as sale_date, SUM(amount) as daily_revenue, SUM(ABS(quantity)) as daily_units')
            ->groupBy('sale_date')
            ->pluck('daily_revenue', 'sale_date');

        $chartDays = collect(range(13, 0))->map(function (int $offset) use ($now, $dailySalesQuery) {
            $date = $now->copy()->subDays($offset)->toDateString();
            $label = $now->copy()->subDays($offset)->format('M j');

            return [
                'date' => $date,
                'label' => $label,
                'revenue' => (float) ($dailySalesQuery[$date] ?? 0),
            ];
        });

        $chartMax = max(1, (float) $chartDays->max('revenue'));
        $trendPoints = $chartDays->map(function ($day, $index) use ($chartMax) {
            $x = 20 + ($index * (560 / 13));
            $y = 130 - (($day['revenue'] / $chartMax) * 110);

            return sprintf('%.1f,%.1f', $x, $y);
        })->implode(' ');

        // KPI Summary
        $totalRevenue30 = $analyticsProducts->sum('revenue_30');
        $totalUnitsSold30 = $analyticsProducts->sum('units_sold_30');
        $criticalReorderCount = $analyticsProducts->where('urgency', 'critical')->count();
        $warningReorderCount = $analyticsProducts->where('urgency', 'warning')->count();

        // Dead stock (products in stock with 0 sales in last 60 days)
        $soldProductIds60 = Transaction::query()
            ->where('type', 'sale')
            ->where('occurred_at', '>=', $sixtyDaysAgo)
            ->pluck('product_id')
            ->unique();

        $deadStockCount = Product::query()
            ->where('quantity', '>', 0)
            ->whereNotIn('id', $soldProductIds60)
            ->count();

        return view('analytics.index', [
            'products' => $analyticsProducts,
            'reorderRecommendations' => $reorderRecommendations,
            'chartDays' => $chartDays,
            'chartMax' => $chartMax,
            'trendPoints' => $trendPoints,
            'totalRevenue30' => $totalRevenue30,
            'totalUnitsSold30' => $totalUnitsSold30,
            'criticalReorderCount' => $criticalReorderCount,
            'warningReorderCount' => $warningReorderCount,
            'deadStockCount' => $deadStockCount,
        ]);
    }

    public function forecast(): View
    {
        $thirtyDaysAgo = now()->subDays(30);

        $salesByProduct = Transaction::query()
            ->where('type', 'sale')
            ->where('occurred_at', '>=', $thirtyDaysAgo)
            ->select('product_id')
            ->selectRaw('SUM(ABS(quantity)) as units_sold, SUM(amount) as revenue')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $forecastProducts = Product::query()
            ->orderBy('name')
            ->paginate(15)
            ->through(function (Product $product) use ($salesByProduct) {
                $sales = $salesByProduct->get($product->id);
                $unitsSold30 = (int) ($sales->units_sold ?? 0);
                $revenue30 = (float) ($sales->revenue ?? 0);
                $dailyVelocity = round($unitsSold30 / 30, 2);

                return (object) [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category ?: 'Uncategorized',
                    'quantity' => $product->quantity,
                    'units_sold_30' => $unitsSold30,
                    'revenue_30' => $revenue30,
                    'daily_velocity' => $dailyVelocity,
                    'forecast_7_days' => ceil($dailyVelocity * 7),
                    'forecast_14_days' => ceil($dailyVelocity * 14),
                    'forecast_30_days' => ceil($dailyVelocity * 30),
                    'stockout_risk_days' => $dailyVelocity > 0 ? ceil($product->quantity / $dailyVelocity) : 999,
                ];
            });

        return view('analytics.forecast', [
            'forecastProducts' => $forecastProducts,
        ]);
    }

    public function reorder(): View
    {
        $thirtyDaysAgo = now()->subDays(30);

        $salesByProduct = Transaction::query()
            ->where('type', 'sale')
            ->where('occurred_at', '>=', $thirtyDaysAgo)
            ->select('product_id')
            ->selectRaw('SUM(ABS(quantity)) as units_sold')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $reorderItems = Product::query()
            ->orderBy('quantity')
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) use ($salesByProduct) {
                $sales = $salesByProduct->get($product->id);
                $unitsSold30 = (int) ($sales->units_sold ?? 0);
                $dailyVelocity = round($unitsSold30 / 30, 2);
                $daysRemaining = $dailyVelocity > 0 ? ceil($product->quantity / $dailyVelocity) : 999;
                $suggestedReorder = $dailyVelocity > 0
                    ? max(0, ceil(($dailyVelocity * 14) + $product->low_stock_threshold - $product->quantity))
                    : ($product->quantity <= $product->low_stock_threshold ? max(10, $product->low_stock_threshold * 2) : 0);

                $urgency = 'healthy';
                if ($product->quantity === 0) {
                    $urgency = 'out_of_stock';
                } elseif ($product->quantity <= $product->low_stock_threshold || $daysRemaining <= 3) {
                    $urgency = 'critical';
                } elseif ($daysRemaining <= 7) {
                    $urgency = 'warning';
                }

                return (object) [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category ?: 'Uncategorized',
                    'quantity' => $product->quantity,
                    'low_stock_threshold' => $product->low_stock_threshold,
                    'daily_velocity' => $dailyVelocity,
                    'days_remaining' => $daysRemaining,
                    'suggested_reorder' => (int) $suggestedReorder,
                    'urgency' => $urgency,
                ];
            })
            ->filter(fn ($item) => $item->urgency !== 'healthy' || $item->suggested_reorder > 0)
            ->values();

        return view('analytics.reorder', [
            'reorderItems' => $reorderItems,
            'outOfStockCount' => $reorderItems->where('urgency', 'out_of_stock')->count(),
            'criticalCount' => $reorderItems->where('urgency', 'critical')->count(),
            'warningCount' => $reorderItems->where('urgency', 'warning')->count(),
        ]);
    }

    public function abcAnalysis(): View
    {
        $sixtyDaysAgo = now()->subDays(60);

        $salesData = Transaction::query()
            ->where('type', 'sale')
            ->where('occurred_at', '>=', $sixtyDaysAgo)
            ->select('product_id')
            ->selectRaw('SUM(amount) as total_revenue, SUM(ABS(quantity)) as total_units')
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->get()
            ->keyBy('product_id');

        $totalCatalogRevenue = $salesData->sum('total_revenue');
        $runningRevenue = 0;

        $products = Product::query()->orderBy('name')->get()->map(function (Product $product) use ($salesData, $totalCatalogRevenue, &$runningRevenue) {
            $sales = $salesData->get($product->id);
            $revenue = (float) ($sales->total_revenue ?? 0);
            $units = (int) ($sales->total_units ?? 0);

            $runningRevenue += $revenue;
            $cumulativePercent = $totalCatalogRevenue > 0 ? ($runningRevenue / $totalCatalogRevenue) * 100 : 100;

            $class = 'C';
            if ($cumulativePercent <= 70) {
                $class = 'A';
            } elseif ($cumulativePercent <= 90) {
                $class = 'B';
            }

            return (object) [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category ?: 'Uncategorized',
                'quantity' => $product->quantity,
                'revenue_60' => $revenue,
                'units_60' => $units,
                'class' => $class,
                'is_dead_stock' => ($product->quantity > 0 && $units === 0),
            ];
        });

        $classA = $products->where('class', 'A')->values();
        $classB = $products->where('class', 'B')->values();
        $classC = $products->where('class', 'C')->values();
        $deadStock = $products->where('is_dead_stock', true)->values();

        return view('analytics.abc', [
            'classA' => $classA,
            'classB' => $classB,
            'classC' => $classC,
            'deadStock' => $deadStock,
            'totalProducts' => $products->count(),
            'deadStockCount' => $deadStock->count(),
        ]);
    }
}

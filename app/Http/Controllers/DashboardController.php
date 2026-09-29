<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->startOfDay();
        $tomorrow = $today->copy()->addDay();
        $chartStart = $today->copy()->subDays(6);
        $expiresBy = $today->copy()->addDays(30);

        $salesByDay = Transaction::query()
            ->where('type', 'sale')
            ->where('occurred_at', '>=', $chartStart)
            ->where('occurred_at', '<', $tomorrow)
            ->selectRaw('DATE(occurred_at) as sales_day, SUM(amount) as total_amount')
            ->groupBy('sales_day')
            ->pluck('total_amount', 'sales_day');

        $salesDays = collect(range(6, 0))->map(function (int $offset) use ($today, $salesByDay): array {
            $day = $today->copy()->subDays($offset);
            $date = $day->toDateString();

            return [
                'label' => $day->format('D'),
                'date' => $date,
                'amount' => (float) ($salesByDay[$date] ?? 0),
            ];
        });

        $chartMaximum = max(1, (float) $salesDays->max('amount'));
        $salesChart = $salesDays->values()->map(function (array $day, int $index) use ($chartMaximum): array {
            $day['x'] = 28 + ($index * 564 / 6);
            $day['y'] = 20 + ((1 - $day['amount'] / $chartMaximum) * 132);

            return $day;
        });
        $salesChartPoints = $salesChart
            ->map(fn (array $day): string => sprintf('%.1f,%.1f', $day['x'], $day['y']))
            ->implode(' ');

        $inventorySummary = Product::query()
            ->select('category')
            ->selectRaw('SUM(quantity) as quantity, COUNT(*) as products')
            ->groupBy('category')
            ->orderByDesc('quantity')
            ->get()
            ->groupBy(fn ($category) => filled($category->category) ? $category->category : 'Uncategorized')
            ->map(fn ($products, $category) => (object) [
                'category' => $category,
                'quantity' => $products->sum('quantity'),
                'products' => $products->sum('products'),
            ])
            ->sortByDesc('quantity')
            ->take(6)
            ->values();
        $inventoryMaximum = max(1, (int) $inventorySummary->max('quantity'));

        $expiringProducts = Product::query()
            ->where('quantity', '>', 0)
            ->whereBetween('expiration_date', [$today->toDateString(), $expiresBy->toDateString()])
            ->orderBy('expiration_date')
            ->limit(5)
            ->get();

        $notifications = collect();
        Product::query()->where('quantity', 0)->orderBy('name')->limit(3)->get()->each(
            fn (Product $product) => $notifications->push([
                'type' => 'critical',
                'title' => 'Out of stock',
                'message' => $product->name.' needs replenishment.',
                'product' => $product->sku,
            ])
        );
        Product::query()
            ->where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->orderBy('quantity')
            ->limit(3)
            ->get()
            ->each(fn (Product $product) => $notifications->push([
                'type' => 'warning',
                'title' => 'Low stock',
                'message' => $product->name.' is running low ('.$product->quantity.' left).',
                'product' => $product->sku,
            ]));
        $expiringProducts->take(3)->each(fn (Product $product) => $notifications->push([
            'type' => 'notice',
            'title' => 'Expiring soon',
            'message' => $product->name.' expires '.$product->expiration_date->format('M j').'.',
            'product' => $product->sku,
        ]));

        return view('dashboard', [
            'totalProducts' => Product::query()->count(),
            'totalQuantity' => Product::query()->sum('quantity'),
            'lowStockCount' => Product::query()
                ->where('quantity', '>', 0)
                ->whereColumn('quantity', '<=', 'low_stock_threshold')
                ->count(),
            'outOfStockCount' => Product::query()->where('quantity', 0)->count(),
            'expiringCount' => Product::query()
                ->where('quantity', '>', 0)
                ->whereBetween('expiration_date', [$today->toDateString(), $expiresBy->toDateString()])
                ->count(),
            'todaySales' => Transaction::query()
                ->where('type', 'sale')
                ->where('occurred_at', '>=', $today)
                ->where('occurred_at', '<', $tomorrow)
                ->sum('amount'),
            'recentTransactions' => Transaction::query()
                ->with('product:id,name,sku')
                ->latest('occurred_at')
                ->limit(7)
                ->get(),
            'salesChart' => $salesChart,
            'salesChartPoints' => $salesChartPoints,
            'inventorySummary' => $inventorySummary,
            'inventoryMaximum' => $inventoryMaximum,
            'expiringProducts' => $expiringProducts,
            'notifications' => $notifications->take(6),
        ]);
    }
}

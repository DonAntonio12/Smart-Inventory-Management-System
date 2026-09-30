<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        $monthStart = now()->startOfMonth();
        $today = now()->endOfDay();

        return view('reports.index', [
            'monthSales' => Transaction::query()
                ->where('type', 'sale')
                ->whereBetween('occurred_at', [$monthStart, $today])
                ->sum('amount'),
            'monthOrderCount' => Transaction::query()
                ->where('type', 'sale')
                ->whereBetween('occurred_at', [$monthStart, $today])
                ->whereNotNull('reference')
                ->distinct()
                ->count('reference'),
            'totalProducts' => Product::query()->count(),
            'totalQuantity' => Product::query()->sum('quantity'),
            'lowStockCount' => Product::query()
                ->where('quantity', '>', 0)
                ->whereColumn('quantity', '<=', 'low_stock_threshold')
                ->count(),
            'outOfStockCount' => Product::query()->where('quantity', 0)->count(),
        ]);
    }

    public function sales(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);
        $salesQuery = $this->salesQuery($from, $to);
        $summary = (clone $salesQuery)
            ->selectRaw('SUM(amount) as total_sales, COUNT(DISTINCT reference) as sale_count, SUM(ABS(quantity)) as units_sold')
            ->first();

        $dailyRows = (clone $salesQuery)
            ->selectRaw('DATE(occurred_at) as report_date, SUM(amount) as total_sales, COUNT(DISTINCT reference) as sale_count, SUM(ABS(quantity)) as units_sold')
            ->groupBy('report_date')
            ->orderBy('report_date')
            ->get()
            ->keyBy('report_date');

        $dailySales = collect();
        $day = $from->copy()->startOfDay();
        $lastDay = $to->copy()->startOfDay();

        while ($day->lte($lastDay)) {
            $date = $day->toDateString();
            $row = $dailyRows->get($date);
            $dailySales->push((object) [
                'date' => $date,
                'label' => $day->format('M j'),
                'total_sales' => (float) ($row->total_sales ?? 0),
                'sale_count' => (int) ($row->sale_count ?? 0),
                'units_sold' => (int) ($row->units_sold ?? 0),
            ]);
            $day->addDay();
        }

        $salesMaximum = max(1, (float) $dailySales->max('total_sales'));
        $topProducts = Transaction::query()
            ->where('type', 'sale')
            ->whereBetween('occurred_at', [$from, $to])
            ->select('product_id')
            ->selectRaw('SUM(ABS(quantity)) as units_sold, SUM(amount) as total_sales')
            ->with('product:id,name,sku')
            ->groupBy('product_id')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();

        return view('reports.sales', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'dailySales' => $dailySales,
            'salesMaximum' => $salesMaximum,
            'topProducts' => $topProducts,
            'totalSales' => (float) ($summary->total_sales ?? 0),
            'saleCount' => (int) ($summary->sale_count ?? 0),
            'unitsSold' => (int) ($summary->units_sold ?? 0),
            'averageSale' => (int) ($summary->sale_count ?? 0) > 0
                ? (float) $summary->total_sales / (int) $summary->sale_count
                : 0,
        ]);
    }

    public function inventory(): View
    {
        $categoryRows = Product::query()
            ->select('category')
            ->selectRaw('COUNT(*) as product_count, SUM(quantity) as unit_count')
            ->groupBy('category')
            ->orderByDesc('unit_count')
            ->get();
        $categories = $categoryRows
            ->groupBy(fn (Product $product): string => filled($product->category) ? $product->category : 'Uncategorized')
            ->map(fn ($products, string $name): object => (object) [
                'name' => $name,
                'product_count' => $products->sum('product_count'),
                'unit_count' => $products->sum('unit_count'),
            ])
            ->sortByDesc('unit_count')
            ->values();

        return view('reports.inventory', [
            'products' => Product::query()->orderBy('name')->paginate(20),
            'categories' => $categories,
            'totalProducts' => Product::query()->count(),
            'totalQuantity' => Product::query()->sum('quantity'),
            'lowStockCount' => Product::query()
                ->where('quantity', '>', 0)
                ->whereColumn('quantity', '<=', 'low_stock_threshold')
                ->count(),
            'outOfStockCount' => Product::query()->where('quantity', 0)->count(),
        ]);
    }

    public function exportSales(Request $request): StreamedResponse
    {
        [$from, $to] = $this->dateRange($request);
        $transactions = $this->salesQuery($from, $to)->with('product:id,name,sku')->orderBy('occurred_at')->lazy();
        $filename = 'sales-report-'.$from->toDateString().'-'.$to->toDateString().'.csv';

        return response()->streamDownload(function () use ($transactions): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['Sale reference', 'Date', 'Product', 'SKU', 'Units sold', 'Sales amount', 'Notes']);

            foreach ($transactions as $transaction) {
                fputcsv($stream, [
                    $this->csvCell($transaction->reference),
                    $transaction->occurred_at->format('Y-m-d H:i:s'),
                    $this->csvCell($transaction->product?->name ?? 'Deleted product'),
                    $this->csvCell($transaction->product?->sku ?? ''),
                    abs($transaction->quantity),
                    number_format((float) $transaction->amount, 2, '.', ''),
                    $this->csvCell($transaction->description ?? ''),
                ]);
            }

            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportInventory(): StreamedResponse
    {
        $products = Product::query()->orderBy('name')->lazy();

        return response()->streamDownload(function () use ($products): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['Product', 'SKU', 'Category', 'Brand', 'On hand', 'Low-stock threshold', 'Status', 'Expiration date']);

            foreach ($products as $product) {
                $status = $product->quantity === 0
                    ? 'Out of stock'
                    : ($product->quantity <= $product->low_stock_threshold ? 'Low stock' : 'In stock');

                fputcsv($stream, [
                    $this->csvCell($product->name),
                    $this->csvCell($product->sku),
                    $this->csvCell($product->category ?: 'Uncategorized'),
                    $this->csvCell($product->brand ?? ''),
                    $product->quantity,
                    $product->low_stock_threshold,
                    $status,
                    $product->expiration_date?->toDateString() ?? '',
                ]);
            }

            fclose($stream);
        }, 'inventory-report-'.now()->toDateString().'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function dateRange(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $from = isset($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : now()->startOfMonth()->startOfDay();
        $to = isset($validated['to'])
            ? Carbon::parse($validated['to'])->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            throw ValidationException::withMessages([
                'to' => 'The end date must be on or after the start date.',
            ]);
        }

        return [$from, $to];
    }

    private function salesQuery(Carbon $from, Carbon $to): Builder
    {
        return Transaction::query()
            ->where('type', 'sale')
            ->whereBetween('occurred_at', [$from, $to]);
    }

    private function csvCell(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesController extends Controller
{
    public function create(): View
    {
        return view('sales.create', [
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku', 'quantity']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);

        $reference = DB::transaction(function () use ($validated): string {
            $items = collect($validated['items']);
            $products = Product::query()
                ->whereIn('id', $items->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $items->count()) {
                throw ValidationException::withMessages([
                    'items' => 'One or more selected products are no longer available.',
                ]);
            }

            foreach ($items as $index => $item) {
                $product = $products->get($item['product_id']);

                if ($item['quantity'] > $product->quantity) {
                    throw ValidationException::withMessages([
                        'items.'.$index.'.quantity' => 'Only '.$product->quantity.' units of '.$product->name.' are available.',
                    ]);
                }
            }

            $saleReference = 'SALE-'.now()->format('Ymd').'-'.Str::upper((string) Str::ulid());
            $descriptionParts = array_filter([
                filled($validated['customer_name'] ?? null) ? 'Customer: '.trim($validated['customer_name']) : null,
                filled($validated['notes'] ?? null) ? trim($validated['notes']) : null,
            ]);
            $description = implode(' · ', $descriptionParts) ?: 'Point of sale';

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];

                $product->decrement('quantity', $quantity);
                $product->transactions()->create([
                    'reference' => $saleReference,
                    'type' => 'sale',
                    'description' => $description,
                    'quantity' => -$quantity,
                    'amount' => round($quantity * $unitPrice, 2),
                    'occurred_at' => now(),
                ]);
            }

            return $saleReference;
        });

        return redirect()->route('sales.show', $reference)->with('status', 'Sale completed and inventory updated.');
    }

    public function transactions(Request $request): View
    {
        $sales = Transaction::query()
            ->where('type', 'sale')
            ->whereNotNull('reference')
            ->select('reference')
            ->selectRaw('MAX(occurred_at) as sold_at, SUM(amount) as total_amount, SUM(ABS(quantity)) as units_sold, COUNT(*) as line_count')
            ->groupBy('reference')
            ->orderByDesc('sold_at')
            ->paginate(15)
            ->withQueryString();

        return view('sales.transactions', [
            'sales' => $sales,
            'saleCount' => Transaction::query()->where('type', 'sale')->whereNotNull('reference')->distinct()->count('reference'),
            'totalSales' => Transaction::query()->where('type', 'sale')->sum('amount'),
            'todaySales' => Transaction::query()
                ->where('type', 'sale')
                ->where('occurred_at', '>=', now()->startOfDay())
                ->where('occurred_at', '<', now()->startOfDay()->addDay())
                ->sum('amount'),
        ]);
    }

    public function show(string $reference): View
    {
        $saleItems = Transaction::query()
            ->with('product:id,name,sku')
            ->where('type', 'sale')
            ->where('reference', $reference)
            ->orderBy('id')
            ->get();

        abort_if($saleItems->isEmpty(), 404);

        return view('sales.show', [
            'reference' => $reference,
            'saleItems' => $saleItems,
            'description' => $saleItems->first()->description,
            'soldAt' => $saleItems->first()->occurred_at,
            'total' => $saleItems->sum('amount'),
            'units' => $saleItems->sum(fn (Transaction $transaction): int => abs($transaction->quantity)),
        ]);
    }
}

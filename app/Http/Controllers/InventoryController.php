<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function index(): View
    {
        return view('inventory.index', [
            'totalProducts' => Product::query()->count(),
            'totalQuantity' => Product::query()->sum('quantity'),
            'lowStockCount' => $this->lowStockQuery()->count(),
            'outOfStockCount' => Product::query()->where('quantity', 0)->count(),
            'expiringCount' => $this->expiringQuery()->count(),
            'products' => Product::query()->orderBy('name')->paginate(12),
            'recentTransactions' => Transaction::query()
                ->with('product:id,name,sku')
                ->latest('occurred_at')
                ->limit(8)
                ->get(),
        ]);
    }

    public function lowStock(): View
    {
        return view('inventory.products', [
            'title' => 'Low Stock',
            'description' => 'Products at or below their reorder threshold.',
            'products' => $this->lowStockQuery()->orderBy('quantity')->orderBy('name')->paginate(12),
            'kind' => 'low',
        ]);
    }

    public function expiringProducts(): View
    {
        return view('inventory.products', [
            'title' => 'Expiring Products',
            'description' => 'In-stock products expiring within the next 30 days.',
            'products' => $this->expiringQuery()->orderBy('expiration_date')->orderBy('name')->paginate(12),
            'kind' => 'expiring',
        ]);
    }

    public function stockIn(): View
    {
        return $this->movementView('Stock In', 'stock_in');
    }

    public function stockOut(): View
    {
        return $this->movementView('Stock Out', 'stock_out');
    }

    public function adjustment(): View
    {
        return $this->movementView('Stock Adjustment', 'adjustment');
    }

    public function storeStockIn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated): void {
            $product = Product::query()->lockForUpdate()->findOrFail($validated['product_id']);
            $product->increment('quantity', $validated['quantity']);

            $product->transactions()->create([
                'type' => 'stock_in',
                'description' => $validated['description'] ?? 'Stock received',
                'quantity' => $validated['quantity'],
                'amount' => 0,
                'occurred_at' => now(),
            ]);
        });

        return redirect()->route('inventory.stock-in')->with('status', 'Stock received and inventory updated.');
    }

    public function storeStockOut(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated): void {
            $product = Product::query()->lockForUpdate()->findOrFail($validated['product_id']);

            if ($validated['quantity'] > $product->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'The stock-out quantity exceeds the available stock of '.$product->quantity.'.',
                ]);
            }

            $product->decrement('quantity', $validated['quantity']);

            $product->transactions()->create([
                'type' => 'stock_out',
                'description' => $validated['description'] ?? 'Stock issued',
                'quantity' => -$validated['quantity'],
                'amount' => 0,
                'occurred_at' => now(),
            ]);
        });

        return redirect()->route('inventory.stock-out')->with('status', 'Stock issued and inventory updated.');
    }

    public function storeAdjustment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'new_quantity' => ['required', 'integer', 'min:0'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated): void {
            $product = Product::query()->lockForUpdate()->findOrFail($validated['product_id']);
            $difference = $validated['new_quantity'] - $product->quantity;

            if ($difference === 0) {
                throw ValidationException::withMessages([
                    'new_quantity' => 'The adjusted quantity must be different from the current stock.',
                ]);
            }

            $product->update(['quantity' => $validated['new_quantity']]);
            $product->transactions()->create([
                'type' => 'adjustment',
                'description' => $validated['description'],
                'quantity' => $difference,
                'amount' => 0,
                'occurred_at' => now(),
            ]);
        });

        return redirect()->route('inventory.adjustment')->with('status', 'Stock count adjusted and recorded.');
    }

    private function movementView(string $title, string $type): View
    {
        return view('inventory.movement', [
            'title' => $title,
            'type' => $type,
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku', 'quantity']),
            'recentTransactions' => Transaction::query()
                ->with('product:id,name,sku')
                ->where('type', $type)
                ->latest('occurred_at')
                ->limit(8)
                ->get(),
        ]);
    }

    private function lowStockQuery(): Builder
    {
        return Product::query()
            ->where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'low_stock_threshold');
    }

    private function expiringQuery(): Builder
    {
        return Product::query()
            ->where('quantity', '>', 0)
            ->whereBetween('expiration_date', [now()->toDateString(), now()->addDays(30)->toDateString()]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $purchaseOrders = PurchaseOrder::query()
            ->with('supplier:id,name')
            ->withCount('items')
            ->when(in_array($status, ['ordered', 'received'], true), fn (Builder $query) => $query->where('status', $status))
            ->latest('ordered_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('purchase-orders.index', [
            'purchaseOrders' => $purchaseOrders,
            'selectedStatus' => $status,
            'openCount' => PurchaseOrder::query()->where('status', 'ordered')->count(),
            'receivedCount' => PurchaseOrder::query()->where('status', 'received')->count(),
        ]);
    }

    public function create(): View
    {
        return view('purchase-orders.form', [
            'suppliers' => Supplier::query()->orderBy('name')->get(['id', 'name']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'expected_at' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);

        $purchaseOrder = DB::transaction(function () use ($validated): PurchaseOrder {
            $products = Product::query()
                ->whereIn('id', collect($validated['items'])->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($validated['items'])) {
                throw ValidationException::withMessages([
                    'items' => 'One or more selected products are no longer available.',
                ]);
            }

            $order = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'order_number' => 'PO-'.now()->format('Ymd').'-'.Str::upper((string) Str::ulid()),
                'status' => 'ordered',
                'ordered_at' => now()->toDateString(),
                'expected_at' => $validated['expected_at'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $items = collect($validated['items'])->map(function (array $item) use ($products): array {
                $product = $products->get($item['product_id']);

                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'quantity_received' => 0,
                    'unit_cost' => $item['unit_cost'],
                ];
            });

            $order->items()->createMany($items->all());

            return $order;
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('status', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'items.product']);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function receive(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        DB::transaction(function () use ($purchaseOrder): void {
            $order = PurchaseOrder::query()
                ->with('supplier:id,name')
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($purchaseOrder->id);

            if ($order->status === 'received') {
                throw ValidationException::withMessages([
                    'purchase_order' => 'This purchase order has already been received.',
                ]);
            }

            foreach ($order->items as $item) {
                $remainingQuantity = $item->quantity - $item->quantity_received;

                if ($remainingQuantity === 0) {
                    continue;
                }

                if ($item->product_id === null) {
                    throw ValidationException::withMessages([
                        'purchase_order' => $item->product_name.' is no longer in the product catalog.',
                    ]);
                }

                $product = Product::query()->lockForUpdate()->find($item->product_id);

                if ($product === null) {
                    throw ValidationException::withMessages([
                        'purchase_order' => $item->product_name.' is no longer in the product catalog.',
                    ]);
                }

                $product->increment('quantity', $remainingQuantity);
                $product->transactions()->create([
                    'reference' => $order->order_number,
                    'type' => 'stock_in',
                    'description' => 'Purchase order from '.$order->supplier->name,
                    'quantity' => $remainingQuantity,
                    'amount' => $remainingQuantity * (float) $item->unit_cost,
                    'occurred_at' => now(),
                ]);

                $item->update(['quantity_received' => $item->quantity]);
            }

            $order->update([
                'status' => 'received',
                'received_at' => now(),
            ]);
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('status', 'Purchase order received and stock updated.');
    }
}

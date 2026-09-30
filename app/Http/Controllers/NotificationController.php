<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('type', 'all');
        $filter = in_array($filter, ['all', 'stock', 'expiry', 'orders'], true) ? $filter : 'all';
        $today = today();
        $expiresBy = today()->addDays(30);

        $alerts = collect();

        Product::query()->where('quantity', 0)->orderBy('name')->limit(100)->get()->each(
            fn (Product $product) => $alerts->push($this->productAlert(
                type: 'stock',
                severity: 'critical',
                title: 'Out of stock',
                message: $product->name.' has no units available.',
                product: $product,
                action: route('products.edit', $product),
                actionLabel: 'Update product',
                sortDate: $product->updated_at,
            ))
        );

        Product::query()
            ->where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->orderBy('quantity')
            ->orderBy('name')
            ->limit(100)
            ->get()
            ->each(fn (Product $product) => $alerts->push($this->productAlert(
                type: 'stock',
                severity: 'warning',
                title: 'Low stock',
                message: $product->name.' is at or below its reorder threshold.',
                product: $product,
                action: route('products.edit', $product),
                actionLabel: 'Review stock',
                sortDate: $product->updated_at,
            )));

        Product::query()
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '<', $today->toDateString())
            ->orderBy('expiration_date')
            ->orderBy('name')
            ->limit(100)
            ->get()
            ->each(fn (Product $product) => $alerts->push($this->productAlert(
                type: 'expiry',
                severity: 'critical',
                title: 'Product expired',
                message: $product->name.' expired on '.$product->expiration_date->format('M j, Y').'.',
                product: $product,
                action: route('products.edit', $product),
                actionLabel: 'Review product',
                sortDate: $product->expiration_date,
            )));

        Product::query()
            ->where('quantity', '>', 0)
            ->whereBetween('expiration_date', [$today->toDateString(), $expiresBy->toDateString()])
            ->orderBy('expiration_date')
            ->orderBy('name')
            ->limit(100)
            ->get()
            ->each(fn (Product $product) => $alerts->push($this->productAlert(
                type: 'expiry',
                severity: 'warning',
                title: $product->expiration_date->isToday() ? 'Expires today' : 'Expiring soon',
                message: $product->name.' expires on '.$product->expiration_date->format('M j, Y').'.',
                product: $product,
                action: route('products.edit', $product),
                actionLabel: 'Review product',
                sortDate: $product->expiration_date,
            )));

        PurchaseOrder::query()
            ->with('supplier:id,name')
            ->where('status', 'ordered')
            ->whereNotNull('expected_at')
            ->whereDate('expected_at', '<', $today->toDateString())
            ->orderBy('expected_at')
            ->limit(100)
            ->get()
            ->each(fn (PurchaseOrder $purchaseOrder) => $alerts->push([
                'type' => 'orders',
                'severity' => 'warning',
                'title' => 'Purchase order overdue',
                'message' => $purchaseOrder->order_number.' from '.$purchaseOrder->supplier->name.' was expected '.$purchaseOrder->expected_at->format('M j, Y').'.',
                'detail' => $purchaseOrder->order_number,
                'date' => $purchaseOrder->expected_at,
                'action' => route('purchase-orders.show', $purchaseOrder),
                'action_label' => 'View order',
            ]));

        $alerts = $alerts
            ->sortBy(fn (array $alert): string => $alert['date']->format('Y-m-d').'|'.$alert['title'])
            ->values();

        return view('notifications.index', [
            'alerts' => $filter === 'all' ? $alerts : $alerts->where('type', $filter)->values(),
            'filter' => $filter,
            'counts' => [
                'all' => $alerts->count(),
                'stock' => $alerts->where('type', 'stock')->count(),
                'expiry' => $alerts->where('type', 'expiry')->count(),
                'orders' => $alerts->where('type', 'orders')->count(),
            ],
        ]);
    }

    private function productAlert(
        string $type,
        string $severity,
        string $title,
        string $message,
        Product $product,
        string $action,
        string $actionLabel,
        mixed $sortDate,
    ): array {
        return [
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'detail' => $product->sku,
            'date' => $sortDate,
            'action' => $action,
            'action_label' => $actionLabel,
        ];
    }
}

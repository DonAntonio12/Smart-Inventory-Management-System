<?php

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests must sign in before opening the dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('the dashboard summarizes current inventory and recent sales', function () {
    $user = User::factory()->create();

    $oliveOil = Product::create([
        'name' => 'Olive Oil',
        'sku' => 'PAN-001',
        'category' => 'Pantry',
        'quantity' => 4,
        'low_stock_threshold' => 5,
        'expiration_date' => now()->addDays(10)->toDateString(),
    ]);
    Product::create([
        'name' => 'Cold Brew',
        'sku' => 'DRK-001',
        'category' => 'Drinks',
        'quantity' => 0,
        'low_stock_threshold' => 4,
    ]);
    Product::create([
        'name' => 'Canvas Tote',
        'sku' => 'ACC-001',
        'category' => 'Accessories',
        'quantity' => 25,
        'low_stock_threshold' => 5,
    ]);

    Transaction::create([
        'product_id' => $oliveOil->id,
        'reference' => 'POS-001',
        'type' => 'sale',
        'quantity' => -2,
        'amount' => 1250.50,
        'occurred_at' => now(),
    ]);
    Transaction::create([
        'product_id' => $oliveOil->id,
        'reference' => 'RCV-001',
        'type' => 'restock',
        'quantity' => 10,
        'amount' => 0,
        'occurred_at' => now()->subDays(2),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard / Home / Overview')
        ->assertSee('Products')
        ->assertSee('All Products')
        ->assertSee('Categories')
        ->assertSee('Brands')
        ->assertSee('Stock Overview')
        ->assertSee('Stock In')
        ->assertSee('Stock Out')
        ->assertSee('Stock Adjustment')
        ->assertSee('Low Stock')
        ->assertSee('Expiring Products')
        ->assertSee('Suppliers')
        ->assertSee('Purchase Orders')
        ->assertSee('/suppliers')
        ->assertSee('/purchase-orders')
        ->assertSee('New Sale')
        ->assertSee('Transactions')
        ->assertSee('/sales/new')
        ->assertSee('/sales/transactions')
        ->assertSee('Smart Analytics')
        ->assertSee('Demand Forecast')
        ->assertSee(route('analytics.forecast'))
        ->assertSee('Smart Reorder')
        ->assertSee(route('analytics.reorder'))
        ->assertSee('Sales Trends')
        ->assertSee(route('analytics.index'))
        ->assertSee('Reports')
        ->assertSee('/reports')
        ->assertSee('Notifications')
        ->assertSee('/notifications')
        ->assertSee('Users & Roles')
        ->assertSee('Settings')
        ->assertSee('Total products')
        ->assertSee('class="metric-value">3</strong>', false)
        ->assertSee('class="metric-value">29</strong>', false)
        ->assertSee('₱1,250.50')
        ->assertSee('POS-001')
        ->assertSee('Olive Oil')
        ->assertSee('Pantry')
        ->assertSee('Sales performance')
        ->assertSee('Out of stock')
        ->assertSee('Expiring soon');
});

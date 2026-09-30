<?php

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected from notifications', function () {
    $this->get('/notifications')->assertRedirect('/login');
});

test('notifications combine stock expiry and overdue order alerts', function () {
    $user = User::factory()->create();
    $supplier = Supplier::create(['name' => 'Northline Supply']);

    Product::create([
        'name' => 'Empty Coffee',
        'sku' => 'NOT-OUT-001',
        'quantity' => 0,
        'low_stock_threshold' => 4,
    ]);
    Product::create([
        'name' => 'Low Tea',
        'sku' => 'NOT-LOW-001',
        'quantity' => 2,
        'low_stock_threshold' => 5,
    ]);
    Product::create([
        'name' => 'Expired Juice',
        'sku' => 'NOT-EXP-001',
        'quantity' => 0,
        'low_stock_threshold' => 2,
        'expiration_date' => now()->subDay()->toDateString(),
    ]);
    Product::create([
        'name' => 'Expiring Syrup',
        'sku' => 'NOT-EXP-002',
        'quantity' => 3,
        'low_stock_threshold' => 1,
        'expiration_date' => now()->addDays(5)->toDateString(),
    ]);
    PurchaseOrder::create([
        'supplier_id' => $supplier->id,
        'order_number' => 'PO-OVERDUE-001',
        'status' => 'ordered',
        'ordered_at' => now()->subDays(10)->toDateString(),
        'expected_at' => now()->subDays(3)->toDateString(),
    ]);

    $this->actingAs($user)
        ->get('/notifications')
        ->assertOk()
        ->assertSee('Notifications')
        ->assertSee('Empty Coffee')
        ->assertSee('Low Tea')
        ->assertSee('Expired Juice')
        ->assertSee('Expiring Syrup')
        ->assertSee('PO-OVERDUE-001')
        ->assertSee('Northline Supply');
});

test('notification filters narrow the list to their category', function () {
    $user = User::factory()->create();
    Product::create([
        'name' => 'Low Tea',
        'sku' => 'FILTER-LOW-001',
        'quantity' => 1,
        'low_stock_threshold' => 4,
    ]);
    Product::create([
        'name' => 'Expired Juice',
        'sku' => 'FILTER-EXP-001',
        'quantity' => 5,
        'low_stock_threshold' => 1,
        'expiration_date' => now()->subDay()->toDateString(),
    ]);

    $this->actingAs($user)
        ->get('/notifications?type=expiry')
        ->assertOk()
        ->assertSee('Expired Juice')
        ->assertDontSee('Low Tea');

    $this->get('/notifications?type=stock')
        ->assertOk()
        ->assertSee('Low Tea')
        ->assertDontSee('Expired Juice');
});

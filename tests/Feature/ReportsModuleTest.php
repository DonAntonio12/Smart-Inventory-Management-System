<?php

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated users can open the reports overview', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/reports')
        ->assertOk()
        ->assertSee('Sales Report')
        ->assertSee('Inventory Report')
        ->assertSee('/reports/sales')
        ->assertSee('/reports/inventory');
});

test('guests cannot access reports or download report exports', function () {
    $this->get('/reports')->assertRedirect('/login');
    $this->get('/reports/sales')->assertRedirect('/login');
    $this->get('/reports/inventory')->assertRedirect('/login');
    $this->get('/reports/sales/export')->assertRedirect('/login');
    $this->get('/reports/inventory/export')->assertRedirect('/login');
});

test('sales report filters by date and summarizes sales references and products', function () {
    $user = User::factory()->create();
    $coffee = Product::create([
        'name' => 'Coffee Beans',
        'sku' => 'RPT-COF-001',
        'quantity' => 12,
        'low_stock_threshold' => 3,
    ]);
    $tea = Product::create([
        'name' => 'Green Tea',
        'sku' => 'RPT-TEA-001',
        'quantity' => 9,
        'low_stock_threshold' => 2,
    ]);

    Transaction::create([
        'product_id' => $coffee->id,
        'reference' => 'SALE-REPORT-001',
        'type' => 'sale',
        'quantity' => -2,
        'amount' => 100,
        'occurred_at' => now()->subDays(2),
    ]);
    Transaction::create([
        'product_id' => $tea->id,
        'reference' => 'SALE-REPORT-001',
        'type' => 'sale',
        'quantity' => -1,
        'amount' => 20,
        'occurred_at' => now()->subDays(2),
    ]);
    Transaction::create([
        'product_id' => $coffee->id,
        'reference' => 'SALE-REPORT-002',
        'type' => 'sale',
        'quantity' => -4,
        'amount' => 200,
        'occurred_at' => now()->subDay(),
    ]);
    Transaction::create([
        'product_id' => $coffee->id,
        'reference' => 'SALE-OLD-001',
        'type' => 'sale',
        'quantity' => -5,
        'amount' => 999,
        'occurred_at' => now()->subDays(10),
    ]);

    $from = now()->subDays(3)->toDateString();
    $to = now()->toDateString();

    $this->actingAs($user)
        ->get('/reports/sales?from='.$from.'&to='.$to)
        ->assertOk()
        ->assertSee('Sales Report')
        ->assertSee('<strong>2</strong>', false)
        ->assertSee('Coffee Beans')
        ->assertSee('₱320.00')
        ->assertDontSee('₱1,319.00');

    $salesExport = $this->get('/reports/sales/export?from='.$from.'&to='.$to)
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($salesExport->streamedContent())->toContain('SALE-REPORT-001')->not->toContain('SALE-OLD-001');
});

test('inventory report summarizes current products and exports a stock snapshot', function () {
    $user = User::factory()->create();
    Product::create([
        'name' => 'Low Coffee Beans',
        'sku' => 'RPT-LOW-001',
        'category' => 'Pantry',
        'brand' => 'Northline',
        'quantity' => 2,
        'low_stock_threshold' => 5,
    ]);
    Product::create([
        'name' => 'Tea Cups',
        'sku' => 'RPT-CUP-001',
        'category' => 'Drinkware',
        'brand' => 'Northline',
        'quantity' => 14,
        'low_stock_threshold' => 4,
    ]);

    $this->actingAs($user)
        ->get('/reports/inventory')
        ->assertOk()
        ->assertSee('Inventory Report')
        ->assertSee('<strong>16</strong>', false)
        ->assertSee('Low Coffee Beans')
        ->assertSee('Tea Cups')
        ->assertSee('Northline');

    $inventoryExport = $this->get('/reports/inventory/export')
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($inventoryExport->streamedContent())->toContain('RPT-LOW-001')->toContain('RPT-CUP-001');
});

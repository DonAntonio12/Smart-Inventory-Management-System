<?php

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot access suppliers or purchase orders', function () {
    $this->get('/suppliers')->assertRedirect('/login');
    $this->get('/suppliers/create')->assertRedirect('/login');
    $this->get('/purchase-orders')->assertRedirect('/login');
    $this->get('/purchase-orders/create')->assertRedirect('/login');
});

test('an authenticated user can create and update a supplier', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/suppliers/create')->assertOk()->assertSee('Add supplier');

    $this->post('/suppliers', [
        'name' => 'Northline Supply',
        'contact_name' => 'Alex Rivera',
        'email' => 'alex@northline.example',
        'phone' => '555-0100',
        'address' => '12 Market Street',
        'notes' => 'Weekly deliveries',
    ])
        ->assertRedirect('/suppliers');

    $supplier = Supplier::where('email', 'alex@northline.example')->firstOrFail();
    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'name' => 'Northline Supply',
        'contact_name' => 'Alex Rivera',
    ]);

    $this->get('/suppliers')->assertOk()->assertSee('Northline Supply');
    $this->get('/suppliers/'.$supplier->id.'/edit')->assertOk()->assertSee('Edit supplier');

    $this->put('/suppliers/'.$supplier->id, [
        'name' => 'Northline Wholesale',
        'contact_name' => 'Alex Rivera',
        'email' => 'alex@northline.example',
        'phone' => '555-0100',
        'address' => '12 Market Street',
        'notes' => 'Weekly deliveries',
    ])->assertRedirect('/suppliers');

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'name' => 'Northline Wholesale',
    ]);
});

test('a supplier cannot be deleted while purchase orders reference it', function () {
    $user = User::factory()->create();
    $supplier = Supplier::create(['name' => 'Bound Supplier']);
    PurchaseOrder::create([
        'supplier_id' => $supplier->id,
        'order_number' => 'PO-KEEP-001',
        'status' => 'ordered',
        'ordered_at' => now()->toDateString(),
    ]);

    $this->actingAs($user)
        ->delete('/suppliers/'.$supplier->id)
        ->assertSessionHasErrors('supplier');

    $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
});

test('a purchase order can be created and received exactly once', function () {
    $user = User::factory()->create();
    $supplier = Supplier::create(['name' => 'Northline Supply']);
    $mug = Product::create([
        'name' => 'Travel Mug',
        'sku' => 'SUP-MUG-001',
        'quantity' => 5,
        'low_stock_threshold' => 2,
    ]);
    $tea = Product::create([
        'name' => 'Green Tea',
        'sku' => 'SUP-TEA-001',
        'quantity' => 8,
        'low_stock_threshold' => 3,
    ]);

    $this->actingAs($user)
        ->get('/purchase-orders/create')
        ->assertOk()
        ->assertSee('New Purchase Order')
        ->assertSee('Add item');

    $this->post('/purchase-orders', [
        'supplier_id' => $supplier->id,
        'expected_at' => now()->addDays(5)->toDateString(),
        'notes' => 'Restock before the weekend',
        'items' => [
            ['product_id' => $mug->id, 'quantity' => 4, 'unit_cost' => 12.50],
            ['product_id' => $tea->id, 'quantity' => 6, 'unit_cost' => 4.25],
        ],
    ])
        ->assertRedirect();

    $purchaseOrder = PurchaseOrder::with('items')->firstOrFail();
    expect($purchaseOrder->items)->toHaveCount(2);
    $this->assertDatabaseHas('purchase_orders', [
        'id' => $purchaseOrder->id,
        'supplier_id' => $supplier->id,
        'status' => 'ordered',
    ]);

    $this->get('/purchase-orders')->assertOk()->assertSee('Northline Supply');
    $this->get('/purchase-orders/'.$purchaseOrder->id)->assertOk()->assertSee('Travel Mug');

    $this->post('/purchase-orders/'.$purchaseOrder->id.'/receive')
        ->assertRedirect('/purchase-orders/'.$purchaseOrder->id)
        ->assertSessionHas('status');

    $this->assertDatabaseHas('products', ['id' => $mug->id, 'quantity' => 9]);
    $this->assertDatabaseHas('products', ['id' => $tea->id, 'quantity' => 14]);
    $this->assertDatabaseHas('purchase_orders', ['id' => $purchaseOrder->id, 'status' => 'received']);
    $this->assertDatabaseHas('transactions', [
        'product_id' => $mug->id,
        'reference' => $purchaseOrder->order_number,
        'type' => 'stock_in',
        'quantity' => 4,
        'amount' => 50,
    ]);
    $this->assertDatabaseHas('transactions', [
        'product_id' => $tea->id,
        'reference' => $purchaseOrder->order_number,
        'type' => 'stock_in',
        'quantity' => 6,
        'amount' => 25.5,
    ]);

    $this->from('/purchase-orders/'.$purchaseOrder->id)
        ->post('/purchase-orders/'.$purchaseOrder->id.'/receive')
        ->assertSessionHasErrors('purchase_order');

    $this->assertDatabaseHas('products', ['id' => $mug->id, 'quantity' => 9]);
    $this->assertDatabaseCount('transactions', 2);
});

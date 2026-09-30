<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot access inventory pages or record stock movements', function () {
    $this->get('/inventory')->assertRedirect('/login');
    $this->get('/inventory/stock-in')->assertRedirect('/login');
    $this->get('/inventory/stock-out')->assertRedirect('/login');
    $this->get('/inventory/adjustment')->assertRedirect('/login');
    $this->get('/inventory/low-stock')->assertRedirect('/login');
    $this->get('/inventory/expiring-products')->assertRedirect('/login');

    $this->post('/inventory/stock-in', [])->assertRedirect('/login');
});

test('stock overview and focused inventory lists show database products', function () {
    $user = User::factory()->create();
    Product::create([
        'name' => 'Low Coffee Beans',
        'sku' => 'INV-LOW',
        'quantity' => 2,
        'low_stock_threshold' => 5,
    ]);
    Product::create([
        'name' => 'Expiring Tea',
        'sku' => 'INV-EXP',
        'quantity' => 8,
        'low_stock_threshold' => 3,
        'expiration_date' => now()->addDays(12)->toDateString(),
    ]);
    Product::create([
        'name' => 'Expired Juice',
        'sku' => 'INV-EXPIRED',
        'quantity' => 4,
        'low_stock_threshold' => 2,
        'expiration_date' => now()->subDays(3)->toDateString(),
    ]);
    Product::create([
        'name' => 'Expired Empty Syrup',
        'sku' => 'INV-EXPIRED-EMPTY',
        'quantity' => 0,
        'low_stock_threshold' => 2,
        'expiration_date' => now()->subDays(20)->toDateString(),
    ]);
    Product::create([
        'name' => 'Healthy Cups',
        'sku' => 'INV-OK',
        'quantity' => 40,
        'low_stock_threshold' => 5,
    ]);

    $this->actingAs($user)
        ->get('/inventory')
        ->assertOk()
        ->assertSee('Stock Overview')
        ->assertSee('Total units')
        ->assertSee('<strong>54</strong>', false)
        ->assertSee('Expired');

    $this->get('/products')
        ->assertOk()
        ->assertSee('Expired Juice')
        ->assertSee('Expired');

    $this->get('/inventory/low-stock')
        ->assertOk()
        ->assertSee('Low Coffee Beans')
        ->assertDontSee('Healthy Cups');

    $this->get('/inventory/expiring-products')
        ->assertOk()
        ->assertSee('Expiring Tea')
        ->assertSee('Expired Juice')
        ->assertSee('Expired Empty Syrup')
        ->assertSee('Expired')
        ->assertDontSee('Healthy Cups');

    $this->get('/reports/inventory')->assertOk()->assertSee('Expired');
});

test('stock in increments quantity and writes a transaction', function () {
    $user = User::factory()->create();
    $product = Product::create([
        'name' => 'Coffee Beans',
        'sku' => 'BEAN-001',
        'quantity' => 10,
        'low_stock_threshold' => 3,
    ]);

    $this->actingAs($user)
        ->post('/inventory/stock-in', [
            'product_id' => $product->id,
            'quantity' => 7,
            'description' => 'Supplier delivery',
        ])
        ->assertRedirect('/inventory/stock-in')
        ->assertSessionHas('status');

    $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => 17]);
    $this->assertDatabaseHas('transactions', [
        'product_id' => $product->id,
        'type' => 'stock_in',
        'quantity' => 7,
        'description' => 'Supplier delivery',
    ]);
});

test('stock out decrements quantity and rejects quantities greater than available stock', function () {
    $user = User::factory()->create();
    $product = Product::create([
        'name' => 'Paper Cups',
        'sku' => 'CUP-001',
        'quantity' => 10,
        'low_stock_threshold' => 2,
    ]);

    $this->actingAs($user)
        ->post('/inventory/stock-out', [
            'product_id' => $product->id,
            'quantity' => 4,
            'description' => 'Counter sale',
        ])
        ->assertRedirect('/inventory/stock-out')
        ->assertSessionHas('status');

    $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => 6]);
    $this->assertDatabaseHas('transactions', [
        'product_id' => $product->id,
        'type' => 'stock_out',
        'quantity' => -4,
    ]);

    $this->from('/inventory/stock-out')
        ->post('/inventory/stock-out', [
            'product_id' => $product->id,
            'quantity' => 7,
        ])
        ->assertSessionHasErrors('quantity');

    $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => 6]);
    $this->assertDatabaseCount('transactions', 1);
});

test('stock adjustment records the difference and cannot set negative stock', function () {
    $user = User::factory()->create();
    $product = Product::create([
        'name' => 'Glass Jars',
        'sku' => 'JAR-001',
        'quantity' => 12,
        'low_stock_threshold' => 4,
    ]);

    $this->actingAs($user)
        ->post('/inventory/adjustment', [
            'product_id' => $product->id,
            'new_quantity' => 9,
            'description' => 'Cycle count correction',
        ])
        ->assertRedirect('/inventory/adjustment')
        ->assertSessionHas('status');

    $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => 9]);
    $this->assertDatabaseHas('transactions', [
        'product_id' => $product->id,
        'type' => 'adjustment',
        'quantity' => -3,
        'description' => 'Cycle count correction',
    ]);

    $this->from('/inventory/adjustment')
        ->post('/inventory/adjustment', [
            'product_id' => $product->id,
            'new_quantity' => -1,
        ])
        ->assertSessionHasErrors('new_quantity');

    $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => 9]);
    $this->assertDatabaseCount('transactions', 1);
});

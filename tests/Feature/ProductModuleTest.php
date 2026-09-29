<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected from the products module', function () {
    $this->get('/products')->assertRedirect('/login');
    $this->get('/products/categories')->assertRedirect('/login');
    $this->get('/products/brands')->assertRedirect('/login');
});

test('an authenticated user can create update and delete a product', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/products', [
            'name' => 'Travel Mug',
            'sku' => 'MUG-100',
            'category' => 'Drinkware',
            'brand' => 'Northline',
            'quantity' => 18,
            'low_stock_threshold' => 4,
            'expiration_date' => null,
        ])
        ->assertRedirect('/products');

    $product = Product::where('sku', 'MUG-100')->firstOrFail();
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'category' => 'Drinkware',
        'brand' => 'Northline',
        'quantity' => 18,
    ]);

    $this->get('/products')
        ->assertOk()
        ->assertSee('Travel Mug')
        ->assertSee('Northline')
        ->assertSee('Drinkware')
        ->assertSee('delete-product-dialog')
        ->assertSee('data-delete-open')
        ->assertDontSee('onsubmit="return confirm');
    $this->get('/products/create')->assertOk()->assertSee('Create product');
    $this->get('/products/'.$product->id.'/edit')->assertOk()->assertSee('Edit product');

    $this->put('/products/'.$product->id, [
        'name' => 'Insulated Travel Mug',
        'sku' => 'MUG-100',
        'category' => 'Drinkware',
        'brand' => 'Northline',
        'quantity' => 16,
        'low_stock_threshold' => 5,
        'expiration_date' => null,
    ])->assertRedirect('/products');

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Insulated Travel Mug',
        'quantity' => 16,
    ]);

    $this->delete('/products/'.$product->id)->assertRedirect('/products');
    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('duplicate product SKUs are rejected', function () {
    $user = User::factory()->create();
    Product::create(['name' => 'Existing item', 'sku' => 'DUP-001']);

    $this->actingAs($user)
        ->from('/products/create')
        ->post('/products', [
            'name' => 'Another item',
            'sku' => 'DUP-001',
            'quantity' => 0,
            'low_stock_threshold' => 5,
        ])
        ->assertSessionHasErrors('sku');

    $this->assertDatabaseCount('products', 1);
});

test('category and brand pages summarize saved products', function () {
    $user = User::factory()->create();
    Product::create([
        'name' => 'Pour Over Set',
        'sku' => 'KIT-001',
        'category' => 'Coffee Gear',
        'brand' => 'Northline',
        'quantity' => 7,
    ]);
    Product::create([
        'name' => 'Brew Scale',
        'sku' => 'KIT-002',
        'category' => 'Coffee Gear',
        'brand' => 'Northline',
        'quantity' => 3,
    ]);

    $this->actingAs($user)
        ->get('/products/categories')
        ->assertOk()
        ->assertSee('Coffee Gear')
        ->assertSee('2 products')
        ->assertSee('<strong>10</strong>', false)
        ->assertSee('<span>units</span>', false);

    $this->get('/products/brands')
        ->assertOk()
        ->assertSee('Northline')
        ->assertSee('2 products')
        ->assertSee('<strong>10</strong>', false)
        ->assertSee('<span>units</span>', false);
});

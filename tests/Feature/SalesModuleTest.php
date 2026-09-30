<?php

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot access sales pages or submit a sale', function () {
    $this->get('/sales/new')->assertRedirect('/login');
    $this->get('/sales/transactions')->assertRedirect('/login');

    $this->post('/sales', [])->assertRedirect('/login');
});

test('an authenticated user can create a sale and review its receipt', function () {
    $user = User::factory()->create();
    $mug = Product::create([
        'name' => 'Travel Mug',
        'sku' => 'SALE-MUG-001',
        'quantity' => 8,
        'low_stock_threshold' => 2,
    ]);
    $tea = Product::create([
        'name' => 'Green Tea',
        'sku' => 'SALE-TEA-001',
        'quantity' => 10,
        'low_stock_threshold' => 2,
    ]);

    $this->actingAs($user)
        ->get('/sales/new')
        ->assertOk()
        ->assertSee('New Sale')
        ->assertSee('Travel Mug');

    $this->post('/sales', [
        'customer_name' => 'Jamie Rivera',
        'notes' => 'Counter sale',
        'items' => [
            ['product_id' => $mug->id, 'quantity' => 2, 'unit_price' => 12.50],
            ['product_id' => $tea->id, 'quantity' => 3, 'unit_price' => 4.25],
        ],
    ])->assertRedirect();

    $saleTransactions = Transaction::query()->where('type', 'sale')->get();
    expect($saleTransactions)->toHaveCount(2);
    expect($saleTransactions->pluck('reference')->unique())->toHaveCount(1);
    $saleReference = $saleTransactions->first()->reference;

    $this->assertDatabaseHas('products', ['id' => $mug->id, 'quantity' => 6]);
    $this->assertDatabaseHas('products', ['id' => $tea->id, 'quantity' => 7]);
    $this->assertDatabaseHas('transactions', [
        'product_id' => $mug->id,
        'reference' => $saleReference,
        'type' => 'sale',
        'quantity' => -2,
        'amount' => 25,
    ]);
    $this->assertDatabaseHas('transactions', [
        'product_id' => $tea->id,
        'reference' => $saleReference,
        'type' => 'sale',
        'quantity' => -3,
        'amount' => 12.75,
    ]);

    $this->get('/sales/transactions')->assertOk()->assertSee($saleReference)->assertSee('37.75');
    $this->get('/sales/transactions/'.$saleReference)
        ->assertOk()
        ->assertSee('Jamie Rivera')
        ->assertSee('Travel Mug')
        ->assertSee('Green Tea')
        ->assertSee('37.75');
});

test('a sale that exceeds available stock changes neither inventory nor transactions', function () {
    $user = User::factory()->create();
    $product = Product::create([
        'name' => 'Limited Notebook',
        'sku' => 'SALE-NOTE-001',
        'quantity' => 2,
        'low_stock_threshold' => 1,
    ]);

    $this->actingAs($user)
        ->from('/sales/new')
        ->post('/sales', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 5],
            ],
        ])
        ->assertSessionHasErrors('items.0.quantity');

    $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => 2]);
    $this->assertDatabaseCount('transactions', 0);
});

test('expired products cannot be selected or sold', function () {
    $user = User::factory()->create();
    $product = Product::create([
        'name' => 'Expired Juice',
        'sku' => 'SALE-EXPIRED-001',
        'quantity' => 6,
        'low_stock_threshold' => 1,
        'expiration_date' => now()->subDay()->toDateString(),
    ]);

    $this->actingAs($user)
        ->get('/sales/new')
        ->assertOk()
        ->assertDontSee('Expired Juice');

    $this->from('/sales/new')
        ->post('/sales', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 4.50],
            ],
        ])
        ->assertSessionHasErrors('items.0.product_id');

    $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => 6]);
    $this->assertDatabaseCount('transactions', 0);
});

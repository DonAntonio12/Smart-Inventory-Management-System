<?php

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Helpers ───────────────────────────────────────────────────────────────

function makeProduct(array $overrides = []): Product
{
    return Product::create(array_merge([
        'name'                => 'Test Product',
        'sku'                 => 'SKU-' . uniqid(),
        'quantity'            => 50,
        'price'               => 100.00,
        'cost'                => 60.00,
        'low_stock_threshold' => 10,
        'category'            => 'General',
        'brand'               => 'BrandX',
    ], $overrides));
}

function makeSale(Product $product, int $quantity = 5, float $amount = 500.00, ?string $date = null): void
{
    Transaction::create([
        'product_id'  => $product->id,
        'type'        => 'sale',
        'quantity'    => -$quantity,
        'amount'      => $amount,
        'occurred_at' => $date ?? now()->toDateTimeString(),
        'reference'   => 'REF-' . uniqid(),
    ]);
}

// ─── Overview (index) ──────────────────────────────────────────────────────

test('authenticated user can view the smart analytics overview page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('analytics.index'));

    $response->assertStatus(200);
    $response->assertSee('Smart Analytics Overview');
});

test('analytics overview shows 30-day revenue kpi', function () {
    $user    = User::factory()->create();
    $product = makeProduct();
    makeSale($product, 10, 1500.00);

    $response = $this->actingAs($user)->get(route('analytics.index'));

    $response->assertStatus(200);
    $response->assertSee('30-Day Revenue');
    $response->assertSee('1,500.00');
});

test('analytics overview shows critical restock count for out-of-stock products', function () {
    $user = User::factory()->create();

    makeProduct(['quantity' => 0, 'low_stock_threshold' => 10]);

    $response = $this->actingAs($user)->get(route('analytics.index'));

    $response->assertStatus(200);
    $response->assertSee('Critical Restock Alerts');
});

test('analytics overview renders 14-day revenue velocity chart section', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('analytics.index'));

    $response->assertStatus(200);
    $response->assertSee('14-Day Revenue Velocity Trend');
});

test('analytics overview shows top restock recommendations panel', function () {
    $user = User::factory()->create();

    makeProduct(['quantity' => 2, 'low_stock_threshold' => 10]);

    $response = $this->actingAs($user)->get(route('analytics.index'));

    $response->assertStatus(200);
    $response->assertSee('Top Restock Recommendations');
});

test('unauthenticated user is redirected away from analytics overview', function () {
    $response = $this->get(route('analytics.index'));

    $response->assertRedirect(route('login'));
});

// ─── Demand Forecast ───────────────────────────────────────────────────────

test('authenticated user can view the demand forecast page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('analytics.forecast'));

    $response->assertStatus(200);
    $response->assertSee('Demand Forecast Analytics');
});

test('demand forecast shows velocity column headings', function () {
    $user    = User::factory()->create();
    $product = makeProduct(['name' => 'Forecast Product', 'sku' => 'FC-001']);
    makeSale($product, 30, 3000.00);

    $response = $this->actingAs($user)->get(route('analytics.forecast'));

    $response->assertStatus(200);
    $response->assertSee('Forecast Product');
    $response->assertSee('7-Day Forecast');
    $response->assertSee('14-Day Forecast');
    $response->assertSee('30-Day Forecast');
});

test('demand forecast shows no-sales pill for products with zero velocity', function () {
    $user = User::factory()->create();
    makeProduct(['name' => 'Dead Mover', 'sku' => 'DM-001', 'quantity' => 100]);

    $response = $this->actingAs($user)->get(route('analytics.forecast'));

    $response->assertStatus(200);
    $response->assertSee('No Sales');
});

// ─── Smart Reorder ─────────────────────────────────────────────────────────

test('authenticated user can view the smart reorder page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('analytics.reorder'));

    $response->assertStatus(200);
    $response->assertSee('Smart Reorder Engine');
});

test('smart reorder page shows out-of-stock items', function () {
    $user = User::factory()->create();
    makeProduct(['name' => 'Empty Stock Item', 'quantity' => 0]);

    $response = $this->actingAs($user)->get(route('analytics.reorder'));

    $response->assertStatus(200);
    $response->assertSee('Out of Stock');
});

test('smart reorder shows suggested order quantity for critical products', function () {
    $user    = User::factory()->create();
    $product = makeProduct(['name' => 'Critical Item', 'quantity' => 3, 'low_stock_threshold' => 20]);
    makeSale($product, 15, 1500.00);

    $response = $this->actingAs($user)->get(route('analytics.reorder'));

    $response->assertStatus(200);
    $response->assertSee('Suggested Order Qty');
    $response->assertSee('Critical Item');
});

test('smart reorder page renders without error when all stocks are at safe levels', function () {
    $user = User::factory()->create();

    makeProduct(['quantity' => 9999, 'low_stock_threshold' => 5]);

    $response = $this->actingAs($user)->get(route('analytics.reorder'));

    $response->assertStatus(200);
    $response->assertSee('Smart Reorder Engine');
});

// ─── ABC & Dead Stock Analysis ─────────────────────────────────────────────

test('authenticated user can view the abc and dead stock analysis page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('analytics.abc-analysis'));

    $response->assertStatus(200);
    $response->assertSee('ABC &amp; Dead Stock Analysis', false);
});

test('abc analysis shows class a high value drivers section', function () {
    $user    = User::factory()->create();
    $product = makeProduct(['name' => 'Premium Product A', 'sku' => 'A-001']);
    makeSale($product, 100, 99999.00);

    $response = $this->actingAs($user)->get(route('analytics.abc-analysis'));

    $response->assertStatus(200);
    $response->assertSee('Class A — High Value Drivers');
});

test('abc analysis identifies dead stock products with no recent sales', function () {
    $user = User::factory()->create();
    makeProduct(['name' => 'Stagnant Stock Item', 'quantity' => 50]);

    $response = $this->actingAs($user)->get(route('analytics.abc-analysis'));

    $response->assertStatus(200);
    $response->assertSee('Dead Stock Risk');
    $response->assertSee('Stagnant Stock Item');
});

test('abc analysis stat grid shows dead stock count correctly', function () {
    $user = User::factory()->create();

    makeProduct(['name' => 'Dead A', 'quantity' => 10]);
    makeProduct(['name' => 'Dead B', 'quantity' => 20]);

    $response = $this->actingAs($user)->get(route('analytics.abc-analysis'));

    $response->assertStatus(200);
    $response->assertSee('Dead Stock Risk (60 Days Unsold)');
    $response->assertSee('2 products');
});

test('abc analysis shows excellent turnover message when no dead stock exists', function () {
    $user    = User::factory()->create();
    $product = makeProduct(['name' => 'Fast Mover', 'quantity' => 100]);
    makeSale($product, 5, 500.00, now()->subDays(10)->toDateTimeString());

    $response = $this->actingAs($user)->get(route('analytics.abc-analysis'));

    $response->assertStatus(200);
    $response->assertSee('Dead Stock Risk (0 Sales in Last 60 Days)');
});

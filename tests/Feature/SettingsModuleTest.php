<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated user can view the settings page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('settings.index'));

    $response->assertStatus(200);
    $response->assertSee('Settings &amp; Preferences', false);
    $response->assertSee('General Store');
});

test('can update general store settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('settings.general.update'), [
        'store_name' => 'Metro Supermarket',
        'store_email' => 'contact@metro.com',
        'store_phone' => '+63 999 888 7777',
        'store_address' => 'Makati City',
        'currency_symbol' => '₱',
        'timezone' => 'Asia/Manila',
    ]);

    $response->assertRedirect(route('settings.index', ['tab' => 'general']));
    expect(Setting::get('store_name'))->toBe('Metro Supermarket');
    expect(Setting::get('store_email'))->toBe('contact@metro.com');
});

test('can update inventory threshold settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('settings.inventory.update'), [
        'default_low_stock_threshold' => 15,
        'expiry_warning_days' => 45,
    ]);

    $response->assertRedirect(route('settings.index', ['tab' => 'inventory']));
    expect(Setting::get('default_low_stock_threshold'))->toBe('15');
    expect(Setting::get('expiry_warning_days'))->toBe('45');
});

test('can update receipt customization settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('settings.receipt.update'), [
        'tax_number' => 'TIN: 999-888-777-000',
        'receipt_header' => 'Welcome to Metro Supermarket!',
        'receipt_footer' => 'Thank you for shopping!',
    ]);

    $response->assertRedirect(route('settings.index', ['tab' => 'receipt']));
    expect(Setting::get('tax_number'))->toBe('TIN: 999-888-777-000');
    expect(Setting::get('receipt_header'))->toBe('Welcome to Metro Supermarket!');
});

test('user can update their name and email under account security settings', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    $response = $this->actingAs($user)->post(route('settings.security.update'), [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);

    $response->assertRedirect(route('settings.index', ['tab' => 'security']));
    $user->refresh();
    expect($user->name)->toBe('Updated Name');
    expect($user->email)->toBe('updated@example.com');
});

test('user can change their password with valid current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password-123'),
    ]);

    $response = $this->actingAs($user)->post(route('settings.security.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'current_password' => 'old-password-123',
        'password' => 'new-password-456',
        'password_confirmation' => 'new-password-456',
    ]);

    $response->assertRedirect(route('settings.index', ['tab' => 'security']));
    $user->refresh();
    expect(Hash::check('new-password-456', $user->password))->toBeTrue();
});

test('password update fails when current password is invalid', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    $response = $this->actingAs($user)->post(route('settings.security.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'current_password' => 'wrong-password',
        'password' => 'new-password-456',
        'password_confirmation' => 'new-password-456',
    ]);

    $response->assertSessionHasErrors('current_password');
});

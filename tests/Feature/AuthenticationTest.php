<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

test('guests can view all authentication pages', function () {
    $this->get(route('login'))->assertOk()->assertSee('Sign in to Stockroom');
    $this->get(route('register'))->assertOk()->assertSee('Create your account');
    $this->get(route('password.request'))->assertOk()->assertSee('Reset your password');
    $this->get(route('password.reset', ['token' => 'example-token']))
        ->assertOk()
        ->assertSee('Choose a new password');
});

test('a visitor can register and is signed in', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Jamie Rivera',
        'email' => 'jamie@example.com',
        'password' => 'inventory-pass-123',
        'password_confirmation' => 'inventory-pass-123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'jamie@example.com']);

    $user = User::where('email', 'jamie@example.com')->firstOrFail();
    expect($user->email)->toBe('jamie@example.com')
        ->and($user->password)->not->toBe('inventory-pass-123')
        ->and(Hash::check('inventory-pass-123', $user->password))->toBeTrue();
});

test('a user can log in and log out', function () {
    $user = User::factory()->create(['password' => 'inventory-pass-123']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'inventory-pass-123',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);

    $this->post(route('logout'))->assertRedirect(route('home'));
    $this->assertGuest();
});

test('login rejects credentials that do not match a database account', function () {
    $this->post(route('login.store'), [
        'email' => 'missing@example.com',
        'password' => 'inventory-pass-123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('a visitor can request a password reset link', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertRedirect()
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

test('a reset link is not sent when the email has no database account', function () {
    Notification::fake();

    $this->post(route('password.email'), ['email' => 'missing@example.com'])
        ->assertRedirect()
        ->assertSessionHas('status');

    Notification::assertNothingSent();
});

test('a user can reset their password with a valid token', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-inventory-pass-123',
        'password_confirmation' => 'new-inventory-pass-123',
    ])->assertRedirect(route('login'));

    expect(Hash::check('new-inventory-pass-123', $user->fresh()->password))->toBeTrue();
});

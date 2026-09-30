<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can view the user accounts list', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('users.index'));

    $response->assertStatus(200);
    $response->assertSee('Users &amp; Accounts', false);
});

test('can create a new user account with role assignment', function () {
    $admin = User::factory()->create();
    $role = Role::create(['name' => 'Cashier', 'guard_name' => 'web']);

    $response = $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'John Cashier',
        'email' => 'cashier@example.com',
        'password' => 'password123',
        'roles' => [$role->id],
    ]);

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseHas('users', [
        'name' => 'John Cashier',
        'email' => 'cashier@example.com',
    ]);

    $createdUser = User::where('email', 'cashier@example.com')->first();
    expect($createdUser->roles->pluck('id'))->toContain($role->id);
});

test('can update an existing user account and role assignment', function () {
    $admin = User::factory()->create();
    $targetUser = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);
    $role = Role::create(['name' => 'Manager', 'guard_name' => 'web']);

    $response = $this->actingAs($admin)->put(route('users.update', $targetUser), [
        'name' => 'New Name',
        'email' => 'new@example.com',
        'password' => '', // leave blank
        'roles' => [$role->id],
    ]);

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);

    $targetUser->refresh();
    expect($targetUser->roles->pluck('id'))->toContain($role->id);
});

test('user cannot delete their own account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete(route('users.destroy', $user));

    $response->assertSessionHasErrors('user');
    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

test('admin can delete another user account', function () {
    $admin = User::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($admin)->delete(route('users.destroy', $otherUser));

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseMissing('users', ['id' => $otherUser->id]);
});

test('authenticated user can view roles and permissions list', function () {
    $user = User::factory()->create();
    Role::create(['name' => 'Auditor', 'description' => 'Audits transactions']);

    $response = $this->actingAs($user)->get(route('roles.index'));

    $response->assertStatus(200);
    $response->assertSee('Roles &amp; Permissions', false);
    $response->assertSee('Auditor');
});

test('can create a new security role', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('roles.store'), [
        'name' => 'Supervisor',
        'description' => 'Oversees daily warehouse operations',
    ]);

    $response->assertRedirect(route('roles.index'));
    $this->assertDatabaseHas('roles', [
        'name' => 'Supervisor',
        'description' => 'Oversees daily warehouse operations',
    ]);
});

test('cannot delete protected Admin role', function () {
    $user = User::factory()->create();
    $adminRole = Role::create(['name' => 'Admin']);

    $response = $this->actingAs($user)->delete(route('roles.destroy', $adminRole));

    $response->assertSessionHasErrors('role');
    $this->assertDatabaseHas('roles', ['id' => $adminRole->id]);
});

test('cannot delete role that is assigned to users', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Active Role']);
    $assignedUser = User::factory()->create();
    $assignedUser->roles()->attach($role);

    $response = $this->actingAs($user)->delete(route('roles.destroy', $role));

    $response->assertSessionHasErrors('role');
    $this->assertDatabaseHas('roles', ['id' => $role->id]);
});

test('can delete an unassigned non-system role', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Temporary Role']);

    $response = $this->actingAs($user)->delete(route('roles.destroy', $role));

    $response->assertRedirect(route('roles.index'));
    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

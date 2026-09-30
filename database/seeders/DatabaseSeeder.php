<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin'],
            ['guard_name' => 'web', 'description' => 'Full administrative access to all system features and user management.']
        );

        Role::firstOrCreate(
            ['name' => 'Inventory Manager'],
            ['guard_name' => 'web', 'description' => 'Manages product catalog, stock receiving, stock out, and purchase orders.']
        );

        Role::firstOrCreate(
            ['name' => 'Cashier'],
            ['guard_name' => 'web', 'description' => 'Processes point-of-sale transactions and views sales history.']
        );

        Role::firstOrCreate(
            ['name' => 'Staff'],
            ['guard_name' => 'web', 'description' => 'General team member access for stock lookup and notification monitoring.']
        );

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => bcrypt('password'),
            ]
        );

        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
    }
}

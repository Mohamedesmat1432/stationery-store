<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Identity\Enums\RoleName;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => $password,
                'phone' => '+212612345678',
                'is_active' => true,
            ]
        );
        $admin->assignRole(RoleName::ADMIN->value);

        // Create Manager User
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => $password,
                'phone' => '+212623456789',
                'is_active' => true,
            ]
        );
        $manager->assignRole(RoleName::MANAGER->value);

        // Create Default User
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Default User',
                'password' => $password,
                'phone' => '+212634567890',
                'is_active' => true,
            ]
        );
        $user->assignRole(RoleName::EDITOR->value);
    }
}

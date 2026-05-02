<?php

namespace Modules\Identity\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
        $admin->assignRole('admin');

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
        $manager->assignRole('manager');

        // Create Customer User
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'User',
                'password' => $password,
                'phone' => '+212634567890',
                'is_active' => true,
            ]
        );
        $user->assignRole('user');
    }
}

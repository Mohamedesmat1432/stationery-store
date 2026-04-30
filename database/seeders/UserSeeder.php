<?php

namespace Database\Seeders;

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
                'phone' => '+1234567890',
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
                'phone' => '+1234567891',
                'is_active' => true,
            ]
        );
        $manager->assignRole('manager');

        // Create Customer User
        $customer = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Customer User',
                'password' => $password,
                'phone' => '+1234567892',
                'is_active' => true,
            ]
        );
        $customer->assignRole('customer');
    }
}

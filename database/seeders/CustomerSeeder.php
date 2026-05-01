<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = CustomerGroup::all();

        User::factory()
            ->count(50)
            ->create()
            ->each(function (User $user) use ($groups) {
                Customer::factory()->create([
                    'user_id' => $user->id,
                    'customer_group_id' => $groups->random()->id,
                ]);

                $user->assignRole('customer');
            });
    }
}

<?php

namespace Database\Seeders;

use App\Models\CustomerGroup;
use Illuminate\Database\Seeder;

class CustomerGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'name' => 'General',
                'slug' => 'general',
                'description' => 'Default customer group',
                'discount_percentage' => 0,
                'sort_order' => 1,
            ],
            [
                'name' => 'VIP',
                'slug' => 'vip',
                'description' => 'High-value customers',
                'discount_percentage' => 10,
                'sort_order' => 2,
            ],
            [
                'name' => 'Wholesale',
                'slug' => 'wholesale',
                'description' => 'B2B customers',
                'discount_percentage' => 20,
                'sort_order' => 3,
            ],
        ];

        foreach ($groups as $group) {
            CustomerGroup::updateOrCreate(['slug' => $group['slug']], $group);
        }
    }
}

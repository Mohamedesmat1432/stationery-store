<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_group_id' => CustomerGroup::factory(),
            'phone' => fake()->phoneNumber(),
            'birth_date' => fake()->optional()->date('Y-m-d', '-18 years'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'company_name' => fake()->optional()->company(),
            'tax_number' => fake()->optional()->bothify('###-###-###'),
            'total_spent' => fake()->randomFloat(2, 0, 10000),
            'orders_count' => fake()->numberBetween(0, 50),
            'metadata' => [],
        ];
    }
}

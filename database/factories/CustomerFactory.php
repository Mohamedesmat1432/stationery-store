<?php

namespace Database\Factories;

use App\Models\Customer;
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
            'phone' => fake()->phoneNumber(),
            'company_name' => fake()->optional()->company(),
            'total_spent' => fake()->randomFloat(2, 0, 10000),
            'orders_count' => fake()->numberBetween(0, 50),
            'metadata' => [],
        ];
    }
}

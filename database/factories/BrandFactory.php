<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
            'website' => $this->faker->url(),
            'is_active' => $this->faker->boolean(90),
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(rand(1, 2), true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'parent_id' => null,
            'is_active' => true,
            'is_featured' => $this->faker->boolean(20),
            'sort_order' => 0,
            'meta_title' => Str::title($name),
            'meta_description' => $this->faker->sentence(),
        ];
    }
}

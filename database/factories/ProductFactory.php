<?php

namespace Database\Factories;

use App\Enums\InventoryPolicy;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'name' => ucfirst($name),
            'sku' => strtoupper(Str::random(10)),
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraphs(3, true),
            'short_description' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(90),
            'is_featured' => $this->faker->boolean(20),
            'is_digital' => false,
            'is_taxable' => true,
            'barcode' => $this->faker->ean13(),
            'weight' => $this->faker->randomFloat(2, 0.1, 10),
            'dimensions' => [
                'width' => $this->faker->randomFloat(2, 1, 50),
                'height' => $this->faker->randomFloat(2, 1, 50),
                'depth' => $this->faker->randomFloat(2, 1, 50),
            ],
            'inventory_policy' => $this->faker->randomElement(InventoryPolicy::cases()),
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'meta_title' => ucfirst($name),
            'meta_description' => $this->faker->sentence(),
            'meta_keywords' => implode(',', $this->faker->words(5)),
        ];
    }
}

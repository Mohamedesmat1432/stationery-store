<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Shared\Events\ResourceChanged;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $brands = Brand::all();
        $baseCurrency = Currency::where('code', 'USD')->first();

        if ($categories->isEmpty() || $brands->isEmpty() || ! $baseCurrency) {
            $this->command->warn('Skipping ProductSeeder: Categories, Brands, or Currencies table is empty.');

            return;
        }

        $stationeryProducts = [
            'Luxury Fountain Pen' => ['price' => 150, 'brand' => 'Montblanc', 'category' => 'Fountain Pens'],
            'Executive Ballpoint' => ['price' => 45, 'brand' => 'Parker', 'category' => 'Ballpoint Pens'],
            'Professional Sketchbook' => ['price' => 25, 'brand' => 'Moleskine', 'category' => 'Sketchbooks'],
            'Drawing Pencil Set' => ['price' => 35, 'brand' => 'Faber-Castell', 'category' => 'Graphite Pencils'],
            'Precision Mechanical Pencil' => ['price' => 20, 'brand' => 'Rotring', 'category' => 'Mechanical Pencils'],
            'Ergonomic Gel Pen' => ['price' => 5, 'brand' => 'Lamy', 'category' => 'Gel Pens'],
            'Vibrant Marker Set' => ['price' => 15, 'brand' => 'Staedtler', 'category' => 'Markers & Highlighters'],
            'Technical Drawing Pen' => ['price' => 12, 'brand' => 'Rotring', 'category' => 'Writing Instruments'],
        ];

        foreach ($stationeryProducts as $name => $details) {
            $category = Category::where('name', $details['category'])->first() ?? $categories->random();
            $brand = Brand::where('name', $details['brand'])->first() ?? $brands->random();

            $product = Product::factory()->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'category_id' => $category->id,
                'brand_id' => $brand->id,
            ]);

            $product->prices()->create([
                'amount' => $details['price'],
                'compare_at_price' => $details['price'] * 1.15,
                'cost_price' => $details['price'] * 0.5,
                'currency_id' => $baseCurrency->id,
                'type' => 'base',
            ]);
        }

        // Add 40 more random products
        Product::factory()->count(40)->create([
            'category_id' => fn () => $categories->random()->id,
            'brand_id' => fn () => $brands->random()->id,
        ])->each(function (Product $product) use ($baseCurrency) {
            $price = fake()->randomFloat(2, 5, 200);
            $product->prices()->create([
                'amount' => $price,
                'compare_at_price' => fake()->boolean(20) ? $price * 1.25 : null,
                'cost_price' => $price * 0.4,
                'currency_id' => $baseCurrency->id,
                'type' => 'base',
            ]);
        });

        ResourceChanged::dispatch(Product::class, 'seeded', []);
    }
}

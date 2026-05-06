<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Modules\Shared\Events\ResourceChanged;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Parker', 'website' => 'https://www.parkerpen.com'],
            ['name' => 'Montblanc', 'website' => 'https://www.montblanc.com'],
            ['name' => 'Faber-Castell', 'website' => 'https://www.faber-castell.com'],
            ['name' => 'Moleskine', 'website' => 'https://www.moleskine.com'],
            ['name' => 'Rotring', 'website' => 'https://www.rotring.com'],
            ['name' => 'Lamy', 'website' => 'https://www.lamy.com'],
            ['name' => 'Staedtler', 'website' => 'https://www.staedtler.com'],
            ['name' => 'Pelikan', 'website' => 'https://www.pelikan.com'],
        ];

        foreach ($brands as $index => $brandData) {
            Brand::factory()->create(array_merge($brandData, [
                'sort_order' => $index,
            ]));
        }

        ResourceChanged::dispatch(Brand::class, 'seeded', []);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Shared\Events\ResourceChanged;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Writing Instruments' => [
                'Fountain Pens',
                'Ballpoint Pens',
                'Gel Pens',
                'Pencils' => [
                    'Graphite Pencils',
                    'Colored Pencils',
                    'Mechanical Pencils',
                ],
                'Markers & Highlighters',
            ],
            'Paper & Notebooks' => [
                'Spiral Notebooks',
                'Hardcover Journals',
                'Notepads',
                'Copy Paper',
                'Sticky Notes',
            ],
            'Art Supplies' => [
                'Paints' => [
                    'Watercolors',
                    'Acrylic Paints',
                    'Oil Paints',
                ],
                'Sketchbooks',
                'Brushes',
                'Canvas',
            ],
            'Office Essentials' => [
                'Staplers & Staples',
                'Calculators',
                'Folders & Filing' => [
                    'Expanding Files',
                    'Ring Binders',
                    'Folder Dividers',
                ],
                'Desk Organizers',
            ],
            'School Supplies' => [
                'Backpacks',
                'Pencil Cases',
                'Lunch Boxes',
                'Geometry Sets',
            ],
        ];

        $this->createCategories($categories);

        ResourceChanged::dispatch(Category::class, 'seeded', []);
    }

    /**
     * Recursively create categories.
     */
    protected function createCategories(array $categories, ?string $parentId = null): void
    {
        $order = 0;
        foreach ($categories as $key => $value) {
            $name = is_array($value) ? $key : $value;

            $category = Category::factory()->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'parent_id' => $parentId,
                'sort_order' => $order++,
            ]);

            if (is_array($value)) {
                $this->createCategories($value, $category->id);
            }
        }
    }
}

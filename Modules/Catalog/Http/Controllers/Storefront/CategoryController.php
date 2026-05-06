<?php

namespace Modules\Catalog\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Catalog\Data\CategoryData;

class CategoryController extends Controller
{
    /**
     * Display the specified category and its products.
     */
    public function show(string $slug): Response
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Load recursive children for the sidebar/filters
        $category->load(['children' => fn ($q) => $q->where('is_active', true)]);

        return Inertia::render('Catalog/Category/Show', [
            'category' => CategoryData::fromCategory($category),
            // Products will be added here once the Product module is ready
            'products' => [],
        ]);
    }
}

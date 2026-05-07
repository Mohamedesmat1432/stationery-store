<?php

namespace Modules\Catalog\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Catalog\Data\ExportProductsData;
use Modules\Catalog\Data\ImportProductsData;
use Modules\Catalog\Data\ProductData;
use Modules\Catalog\Services\CatalogCacheService;
use Modules\Catalog\Services\CategoryService;
use Modules\Catalog\Services\ProductService;
use Modules\Shared\Http\Controllers\Traits\HandlesBulkActions;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends Controller
{
    use HandlesBulkActions;

    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Product::class);

        return Inertia::render('Admin/Catalog/Products/Index', [
            'products' => Inertia::defer(fn () => $this->productService->getProductsPaginated($request->all())),
            'filters' => $request->only(['filter']),
            'categories' => Inertia::defer(fn () => app(CategoryService::class)->getCategoryTree()),
            'brands' => Inertia::defer(fn () => CatalogCacheService::getAvailableBrands()),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Product::class);

        return Inertia::render('Admin/Catalog/Products/Create', [
            'categories' => Inertia::defer(fn () => app(CategoryService::class)->getCategoryTree()),
            'brands' => Inertia::defer(fn () => CatalogCacheService::getAvailableBrands()),
        ]);
    }

    public function store(ProductData $data): RedirectResponse
    {
        Gate::authorize('create', Product::class);

        $this->productService->createProduct($data);

        return to_route('admin.products.index')->with('success', __('Product created successfully.'));
    }

    public function edit(Product $product): Response
    {
        Gate::authorize('update', $product);

        return Inertia::render('Admin/Catalog/Products/Edit', [
            'product' => ProductData::fromProduct($product->loadMissing(['category', 'brand', 'media', 'prices'])),
            'categories' => Inertia::defer(fn () => app(CategoryService::class)->getCategoryTree()),
            'brands' => Inertia::defer(fn () => CatalogCacheService::getAvailableBrands()),
        ]);
    }

    public function update(Product $product, ProductData $data): RedirectResponse
    {
        Gate::authorize('update', $product);

        $this->productService->updateProduct($product, $data);

        return to_route('admin.products.index')->with('success', __('Product updated successfully.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        $this->productService->deleteProduct($product);

        return to_route('admin.products.index')->with('success', __('Product deleted successfully.'));
    }

    public function restore($id): RedirectResponse
    {
        return $this->performRestore($id, Product::class, 'productService', 'restoreProduct');
    }

    public function forceDelete($id): RedirectResponse
    {
        return $this->performForceDelete($id, Product::class, 'productService', 'forceDeleteProduct');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        return $this->performBulkAction($request, Product::class, 'productService');
    }

    /**
     * Export products.
     */
    public function export(Request $request, ExportProductsData $data): BinaryFileResponse
    {
        Gate::authorize('export', Product::class);

        return $this->productService->exportProducts($data->columns, $data->format, $request->all());
    }

    /**
     * Import products.
     */
    public function import(ImportProductsData $data): RedirectResponse
    {
        Gate::authorize('import', Product::class);

        $this->productService->importProducts($data->file);

        return to_route('admin.products.index')->with('success', __('Products imported successfully.'));
    }

    /**
     * Toggle the active status of a product.
     */
    public function toggleActive(Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        $this->productService->toggleActive($product);

        return back()->with('success', __('Product status updated successfully.'));
    }
}

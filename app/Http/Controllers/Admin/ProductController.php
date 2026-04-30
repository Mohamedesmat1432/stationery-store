<?php

namespace App\Http\Controllers\Admin;

use App\Data\ProductData;
use App\Http\Controllers\Admin\Traits\HandlesBulkActions;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    use HandlesBulkActions;

    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Product::class);

        return Inertia::render('Admin/Products/Index', [
            'products' => ProductData::collect($this->productService->getProductsPaginated()),
            'filters' => $request->only(['filter']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Product::class);

        return Inertia::render('Admin/Products/Create');
    }

    public function store(ProductData $data): RedirectResponse
    {
        Gate::authorize('create', Product::class);

        $this->productService->createProduct($data);

        return to_route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): Response
    {
        Gate::authorize('update', $product);

        return Inertia::render('Admin/Products/Edit', [
            'product' => ProductData::fromModel($product),
        ]);
    }

    public function update(Product $product, ProductData $data): RedirectResponse
    {
        Gate::authorize('update', $product);

        $this->productService->updateProduct($product, $data);

        return to_route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        $this->productService->deleteProduct($product);

        return to_route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    public function restore($id): RedirectResponse
    {
        return $this->performRestore($id, Product::class, 'productService', 'restoreProduct');
    }

    public function forceDelete($id): RedirectResponse
    {
        return $this->performForceDelete($id, Product::class, 'productService', 'forceDeleteProduct');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        return $this->performBulkAction($request, Product::class, 'productService', 'Products');
    }
}

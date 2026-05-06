<?php

namespace Modules\Catalog\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Catalog\Data\BrandData;
use Modules\Catalog\Data\ExportBrandsData;
use Modules\Catalog\Data\ImportBrandsData;
use Modules\Catalog\Services\BrandService;
use Modules\Shared\Http\Controllers\Traits\HandlesBulkActions;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BrandController extends Controller
{
    use HandlesBulkActions;

    public function __construct(
        protected BrandService $brandService
    ) {}

    /**
     * Display a listing of brands.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Brand::class);

        return Inertia::render('Admin/Catalog/Brands/Index', [
            'brands' => $this->brandService->getBrandsPaginated($request->all()),
            'filters' => $request->only(['filter']),
        ]);
    }

    /**
     * Show the form for creating a new brand.
     */
    public function create(): Response
    {
        Gate::authorize('create', Brand::class);

        return Inertia::render('Admin/Catalog/Brands/Create');
    }

    /**
     * Store a newly created brand in storage.
     */
    public function store(BrandData $data): RedirectResponse
    {
        Gate::authorize('create', Brand::class);

        $this->brandService->createBrand($data);

        return to_route('admin.brands.index')->with('success', __('Brand created successfully.'));
    }

    /**
     * Show the form for editing the specified brand.
     */
    public function edit(Brand $brand): Response
    {
        Gate::authorize('update', $brand);

        $brand->loadMissing('media')->loadCount('products');

        return Inertia::render('Admin/Catalog/Brands/Edit', [
            'brand' => BrandData::fromBrand($brand),
        ]);
    }

    /**
     * Update the specified brand in storage.
     */
    public function update(Brand $brand, BrandData $data): RedirectResponse
    {
        Gate::authorize('update', $brand);

        $this->brandService->updateBrand($brand, $data);

        return to_route('admin.brands.index')->with('success', __('Brand updated successfully.'));
    }

    /**
     * Remove the specified brand from storage.
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        Gate::authorize('delete', $brand);

        $this->brandService->deleteBrand($brand);

        return to_route('admin.brands.index')->with('success', __('Brand deleted successfully.'));
    }

    /**
     * Restore a soft-deleted brand.
     */
    public function restore(Brand $brand): RedirectResponse
    {
        $this->brandService->restoreBrand($brand);

        return back()->with('success', __('Brand restored successfully.'));
    }

    /**
     * Permanently delete a brand.
     */
    public function forceDelete(Brand $brand): RedirectResponse
    {
        $this->brandService->forceDeleteBrand($brand);

        return to_route('admin.brands.index')->with('success', __('Brand permanently deleted.'));
    }

    /**
     * Handle bulk actions for brands.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        return $this->performBulkAction($request, Brand::class, 'brandService');
    }

    /**
     * Export brands.
     */
    public function export(ExportBrandsData $data): BinaryFileResponse
    {
        Gate::authorize('export', Brand::class);

        return $this->brandService->exportBrands($data->columns, $data->format);
    }

    /**
     * Import brands.
     */
    public function import(ImportBrandsData $data): RedirectResponse
    {
        Gate::authorize('import', Brand::class);

        $this->brandService->importBrands($data->file);

        return to_route('admin.brands.index')->with('success', __('Brands imported successfully.'));
    }
}

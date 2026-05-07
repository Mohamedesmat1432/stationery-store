<?php

namespace Modules\Catalog\Services;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Catalog\Data\BrandData;
use Modules\Catalog\Exports\BrandsExport;
use Modules\Catalog\Imports\BrandsImport;
use Modules\Catalog\Repositories\Contracts\BrandRepositoryInterface;
use Modules\Shared\Events\ResourceChanged;
use Modules\Shared\Services\Concerns\HandlesBulkOperations;
use Modules\Shared\Services\Concerns\HandlesResourceOperations;
use Modules\Shared\Services\Concerns\ProtectsSystemResources;
use Modules\Shared\Services\Logging\ModuleLogger;
use Modules\Shared\Services\Media\HandlesResourceMedia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BrandService
{
    use HandlesBulkOperations, HandlesResourceMedia, HandlesResourceOperations, ModuleLogger, ProtectsSystemResources {
        ProtectsSystemResources::filterBulkIds insteadof HandlesBulkOperations;
    }

    public function __construct(
        protected BrandRepositoryInterface $brandRepository
    ) {}

    protected function getRepository(): BrandRepositoryInterface
    {
        return $this->brandRepository;
    }

    protected function getModelClass(): string
    {
        return Brand::class;
    }

    public function getBrandsPaginated(array $params = [], int $perPage = 15): array
    {
        return CatalogCacheService::rememberBrands(
            $params,
            $perPage,
            fn () => $this->brandRepository->paginate($perPage, $params)
        );
    }

    public function createBrand(BrandData $data): Brand
    {
        try {
            return DB::transaction(function () use ($data) {
                $brand = $this->brandRepository->create($data->except('logo')->toArray());

                $this->handleMedia($brand, $data);

                ResourceChanged::dispatch(Brand::class, 'created', [$brand->id]);

                return $brand;
            });
        } catch (\Throwable $e) {
            $this->logError('Failed to create brand', ['name' => $data->name], $e);
            throw $e;
        }
    }

    public function updateBrand(Brand $brand, BrandData $data): Brand
    {
        try {
            return DB::transaction(function () use ($brand, $data) {
                $brand = $this->brandRepository->update($brand, $data->except('logo')->toArray());

                $this->handleMedia($brand, $data);

                ResourceChanged::dispatch(Brand::class, 'updated', [$brand->id]);

                return $brand;
            });
        } catch (\Throwable $e) {
            $this->logError('Failed to update brand', ['id' => $brand->id], $e);
            throw $e;
        }
    }

    public function deleteBrand(Brand $brand): bool
    {
        return $this->performDelete($brand);
    }

    public function restoreBrand(Brand $brand): bool
    {
        return $this->performRestore($brand);
    }

    public function forceDeleteBrand(Brand $brand): bool
    {
        return $this->performForceDelete($brand);
    }

    /**
     * Check if a brand is protected from deletion/modification.
     */
    public function isProtected(Model|Brand $model): bool
    {
        return $model->isProtected(Auth::user());
    }

    protected function handleMedia(Brand $brand, BrandData $data): void
    {
        $this->syncMedia($brand, $data->logo, 'logo');
    }

    public function getAllActive(): array
    {
        return CatalogCacheService::rememberDirect(
            CatalogCacheService::TAG_BRANDS,
            'active_list',
            fn () => $this->brandRepository->allActive(),
            fn ($collection) => $collection->toArray()
        );
    }

    /**
     * Export brands.
     */
    public function exportBrands(array $columns, string $format, array $params = []): BinaryFileResponse
    {
        $query = $this->brandRepository->buildExportQuery($params);
        $filename = 'brands_'.now()->format('Y-m-d_H-i-s').'.'.$format;

        return Excel::download(
            new BrandsExport($query, $columns),
            $filename
        );
    }

    /**
     * Import brands.
     */
    public function importBrands(UploadedFile $file): void
    {
        Brand::withoutEvents(fn () => Excel::import(new BrandsImport, $file));

        ResourceChanged::dispatch($this->getModelClass(), 'imported');
    }

    /**
     * Toggle the active status of a brand.
     */
    public function toggleActive(Brand $brand): bool
    {
        $result = $this->brandRepository->toggleActive($brand);

        if ($result) {
            ResourceChanged::dispatch(Brand::class, 'updated', [$brand->id]);
        }

        return $result;
    }
}

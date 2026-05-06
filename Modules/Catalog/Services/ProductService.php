<?php

namespace Modules\Catalog\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Catalog\Data\ProductData;
use Modules\Catalog\Exports\ProductsExport;
use Modules\Catalog\Imports\ProductsImport;
use Modules\Catalog\Repositories\Contracts\ProductRepositoryInterface;
use Modules\Shared\Events\ResourceChanged;
use Modules\Shared\Services\Concerns\HandlesBulkOperations;
use Modules\Shared\Services\Concerns\HandlesResourceOperations;
use Modules\Shared\Services\Concerns\ProtectsSystemResources;
use Modules\Shared\Services\Logging\ModuleLogger;
use Modules\Shared\Services\Media\HandlesResourceMedia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductService
{
    use HandlesBulkOperations, HandlesResourceMedia, HandlesResourceOperations, ModuleLogger, ProtectsSystemResources {
        ProtectsSystemResources::filterBulkIds insteadof HandlesBulkOperations;
    }

    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    protected function getRepository(): ProductRepositoryInterface
    {
        return $this->productRepository;
    }

    protected function getModelClass(): string
    {
        return Product::class;
    }

    /**
     * Get paginated products.
     */
    public function getProductsPaginated(array $params = [], int $perPage = 15): array
    {
        return CatalogCacheService::rememberProducts(
            $params,
            $perPage,
            fn () => $this->productRepository->paginate($perPage, $params)
        );
    }

    public function createProduct(ProductData $data): Product
    {
        $product = $this->productRepository->create($data->except('price', 'compare_at_price', 'cost_price', 'featured_image')->toArray());

        $this->syncPrice($product, $data);
        $this->handleMedia($product, $data);

        ResourceChanged::dispatch(Product::class, 'created', [$product->id]);

        return $product;
    }

    public function updateProduct(Product $product, ProductData $data): Product
    {
        $product = $this->productRepository->update($product, $data->except('price', 'compare_at_price', 'cost_price', 'featured_image')->toArray());

        $this->syncPrice($product, $data);
        $this->handleMedia($product, $data);

        ResourceChanged::dispatch(Product::class, 'updated', [$product->id]);

        return $product;
    }

    protected function syncPrice(Product $product, ProductData $data): void
    {
        $product->prices()->updateOrCreate(
            [
                'currency_id' => config('app.currency_id'),
                'type' => 'base',
            ],
            [
                'amount' => $data->price,
                'compare_at_price' => $data->compare_at_price,
                'cost_price' => $data->cost_price,
            ]
        );
    }

    protected function handleMedia(Product $product, ProductData $data): void
    {
        $this->syncMedia($product, $data->featured_image, 'featured');
    }

    public function deleteProduct(Product $product): bool
    {
        return $this->performDelete($product);
    }

    public function restoreProduct(Product $product): bool
    {
        return $this->performRestore($product);
    }

    public function forceDeleteProduct(Product $product): bool
    {
        return $this->performForceDelete($product);
    }

    /**
     * Check if a product is protected from deletion/modification.
     */
    public function isProtected(Model|Product $model): bool
    {
        return $model->isProtected(Auth::user());
    }

    /**
     * Export products.
     */
    public function exportProducts(array $columns, string $format): BinaryFileResponse
    {
        $query = Product::query()->with(['category', 'brand', 'prices']);
        $filename = 'products_'.now()->format('Y-m-d_H-i-s').'.'.$format;

        return Excel::download(
            new ProductsExport($query, $columns),
            $filename
        );
    }

    /**
     * Import products.
     */
    public function importProducts(UploadedFile $file): void
    {
        Product::withoutEvents(fn () => Excel::import(new ProductsImport, $file));

        ResourceChanged::dispatch($this->getModelClass(), 'imported');
    }
}

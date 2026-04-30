<?php

namespace App\Services;

use App\Data\ProductData;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function getProductsPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(Product::class)
            ->with(['category', 'brand'])
            ->allowedFilters(...[
                AllowedFilter::partial('name'),
                AllowedFilter::exact('sku'),
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('brand_id'),
                AllowedFilter::callback('trash', function ($query, $value) {
                    if ($value === 'only') {
                        $query->onlyTrashed();
                    } elseif ($value === 'with') {
                        $query->withTrashed();
                    }
                }),
            ])
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createProduct(ProductData $data): Product
    {
        return $this->productRepository->create($data->toArray());
    }

    public function updateProduct(Product $product, ProductData $data): Product
    {
        return $this->productRepository->update($product, $data->toArray());
    }

    public function deleteProduct(Product $product): bool
    {
        return $this->productRepository->delete($product);
    }

    public function bulkDeleteProducts(array $ids): bool
    {
        return $this->productRepository->bulkDelete($ids);
    }

    public function restoreProduct(Product $product): bool
    {
        return $this->productRepository->restore($product);
    }

    public function forceDeleteProduct(Product $product): bool
    {
        return $this->productRepository->forceDelete($product);
    }

    public function bulkRestoreProducts(array $ids): bool
    {
        return $this->productRepository->bulkRestore($ids);
    }

    public function bulkForceDeleteProducts(array $ids): bool
    {
        return $this->productRepository->bulkForceDelete($ids);
    }
}

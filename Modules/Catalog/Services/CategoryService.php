<?php

namespace Modules\Catalog\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Catalog\Data\CategoryData;
use Modules\Catalog\Exports\CategoriesExport;
use Modules\Catalog\Imports\CategoriesImport;
use Modules\Catalog\Repositories\Contracts\CategoryRepositoryInterface;
use Modules\Shared\Events\ResourceChanged;
use Modules\Shared\Services\Concerns\HandlesBulkOperations;
use Modules\Shared\Services\Concerns\HandlesResourceOperations;
use Modules\Shared\Services\Concerns\ProtectsSystemResources;
use Modules\Shared\Services\Logging\ModuleLogger;
use Modules\Shared\Services\Media\HandlesResourceMedia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CategoryService
{
    use HandlesBulkOperations, HandlesResourceMedia, HandlesResourceOperations, ModuleLogger, ProtectsSystemResources {
        ProtectsSystemResources::filterBulkIds insteadof HandlesBulkOperations;
    }

    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {}

    protected function getRepository(): CategoryRepositoryInterface
    {
        return $this->categoryRepository;
    }

    protected function getModelClass(): string
    {
        return Category::class;
    }

    public function getCategoriesPaginated(array $filters = [], int $perPage = 5): array
    {
        return CatalogCacheService::rememberCategories(
            $filters,
            $perPage,
            fn () => $this->categoryRepository->paginate($perPage, $filters)
        );
    }

    /**
     * Get the non-paginated category tree.
     */
    public function getCategoryTree(array $filters = []): array
    {
        return CatalogCacheService::rememberDirect(
            CatalogCacheService::TAG_CATEGORIES,
            'tree_flat_'.md5(json_encode($filters)),
            fn () => $this->categoryRepository->getTree($filters),
            fn ($collection) => CategoryData::collect($collection)->toArray()
        );
    }

    /**
     * Create a new category.
     */
    public function createCategory(CategoryData $data): Category
    {
        $category = $this->categoryRepository->create($data->except('icon', 'banner_image')->toArray());

        $this->handleMedia($category, $data);

        ResourceChanged::dispatch($this->getModelClass(), 'created', [$category->id]);

        return $category;
    }

    /**
     * Update an existing category.
     */
    public function updateCategory(Category $category, CategoryData $data): Category
    {
        // Prevent setting itself or a descendant as parent
        if ($data->parent_id) {
            if ($data->parent_id === $category->id) {
                $data->parent_id = $category->parent_id;
            } else {
                $newParent = Category::find($data->parent_id);
                if ($newParent && $newParent->isDescendantOf($category)) {
                    throw new ValidationException(
                        validator(
                            ['parent_id' => $data->parent_id],
                            ['parent_id' => [fn ($attr, $val, $fail) => $fail(__('Cannot set a descendant as parent.'))]]
                        )
                    );
                }
            }
        }

        $category = $this->categoryRepository->update($category, $data->except('icon', 'banner_image')->toArray());

        $this->handleMedia($category, $data);

        ResourceChanged::dispatch($this->getModelClass(), 'updated', [$category->id]);

        return $category;
    }

    /**
     * Handle media uploads for category.
     */
    protected function handleMedia(Category $category, CategoryData $data): void
    {
        $this->syncMedia($category, $data->icon, 'icon');
        $this->syncMedia($category, $data->banner_image, 'banner');
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(Category $category): bool
    {
        if ($this->isProtected($category)) {
            throw ValidationException::withMessages([
                'category' => __('Cannot delete category because it or its subcategories contain active products.'),
            ]);
        }

        $descendantIds = $category->getDescendantIds();
        $subtreeIds = array_merge([$category->id], $descendantIds);

        // Perform bulk deletion
        return DB::transaction(function () use ($subtreeIds) {
            $result = Category::whereIn('id', $subtreeIds)->lazy()->each->delete();

            ResourceChanged::dispatch($this->getModelClass(), 'deleted', $subtreeIds);

            return true;
        });
    }

    /**
     * Restore a soft-deleted category.
     */
    public function restoreCategory(Category $category): bool
    {
        return $this->performRestore($category);
    }

    /**
     * Permanently delete a category.
     */
    public function forceDeleteCategory(Category $category): bool
    {
        if ($this->isProtected($category)) {
            throw ValidationException::withMessages([
                'category' => __('Cannot force delete category because it or its subcategories contain active products.'),
            ]);
        }

        $result = $this->categoryRepository->forceDelete($category);

        if ($result) {
            ResourceChanged::dispatch($this->getModelClass(), 'force_deleted', [$category->id]);
        }

        return $result;
    }

    /**
     * Check if a category is protected from deletion/modification.
     */
    public function isProtected(Model|Category $model): bool
    {
        return $model->isProtected(Auth::user());
    }

    /**
     * Reorder the category tree.
     */
    public function reorderTree(array $treeData): void
    {
        $this->categoryRepository->reorderTree($treeData);

        ResourceChanged::dispatch($this->getModelClass(), 'reordered');
    }

    /**
     * Toggle the active status of a category.
     */
    public function toggleActive(Category $category): bool
    {
        $result = $this->categoryRepository->toggleActive($category);

        if ($result) {
            ResourceChanged::dispatch(Category::class, 'updated', [$category->id]);
        }

        return $result;
    }

    /**
     * Export categories.
     */
    public function exportCategories(array $columns, string $format): BinaryFileResponse
    {
        $query = Category::query();
        $filename = 'categories_'.now()->format('Y-m-d_H-i-s').'.'.$format;

        return Excel::download(
            new CategoriesExport($query, $columns),
            $filename
        );
    }

    /**
     * Import categories.
     */
    public function importCategories(UploadedFile $file): void
    {
        Category::withoutEvents(fn () => Excel::import(new CategoriesImport, $file));

        ResourceChanged::dispatch($this->getModelClass(), 'imported');
    }
}

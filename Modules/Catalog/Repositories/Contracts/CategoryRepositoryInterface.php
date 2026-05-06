<?php

namespace Modules\Catalog\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Shared\Repositories\Contracts\RepositoryInterface;

interface CategoryRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated categories with filters and tree support.
     */
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator;

    /**
     * Get the category tree with filters (non-paginated).
     */
    public function getTree(array $filters = []): Collection;

    /**
     * Get root categories.
     */
    public function getRootCategories(): Collection;

    /**
     * Reorder the entire category tree.
     */
    public function reorderTree(array $treeData): void;

    /**
     * Toggle the active status of a category.
     */
    public function toggleActive(Category $category): bool;
}

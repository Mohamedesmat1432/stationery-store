<?php

namespace Modules\Catalog\Repositories\Eloquent;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Modules\Catalog\Repositories\Contracts\CategoryRepositoryInterface;
use Modules\Shared\Repositories\Concerns\HandlesQueryBuilder;
use Modules\Shared\Repositories\Contracts\ProtectsBulkResources;
use Modules\Shared\Repositories\Eloquent\BaseRepository;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface, ProtectsBulkResources
{
    use HandlesQueryBuilder;

    protected function getModelClass(): string
    {
        return Category::class;
    }

    public function paginate(int $perPage = 5, array $params = []): LengthAwarePaginator
    {
        $search = $params['filter']['search'] ?? request()->input('filter.search');
        $trash = $params['filter']['trash'] ?? request()->input('filter.trash');

        $query = $this->buildQueryBuilder(
            Category::query(),
            [
                AllowedFilter::scope('search'),
                AllowedFilter::trashed('trash'),
            ],
            [],
            [],
            ['sort_order'],
            [],
            [],
            $params
        );

        // If searching or viewing ONLY trashed items, return flat list for better UX
        if (! empty($search) || $trash === 'only') {
            return $query->with(['parent', 'media'])
                ->withCount(['products' => fn ($q) => $q->active()])
                ->paginate($perPage);
        }

        $this->applyTreeLoading($query, $trash);

        return $query->paginate($perPage);
    }

    /**
     * Get the category tree with filters (non-paginated).
     */
    public function getTree(array $filters = []): Collection
    {
        return $this->buildExportQuery($filters)->get();
    }

    /**
     * Build a filtered query for exports (no pagination).
     */
    public function buildExportQuery(array $params = []): Builder
    {
        $search = $params['filter']['search'] ?? request()->input('filter.search');
        $trash = $params['filter']['trash'] ?? request()->input('filter.trash');

        $query = $this->buildQueryBuilder(
            Category::query(),
            [
                AllowedFilter::scope('search'),
                AllowedFilter::trashed('trash'),
            ],
            [],
            [],
            ['sort_order'],
            [],
            [],
            $params
        );

        if (empty($search) && $trash !== 'only') {
            $this->applyTreeLoading($query, $trash);
        }

        return $query->getEloquentBuilder();
    }

    /**
     * Apply recursive tree loading with media and product counts.
     */
    protected function applyTreeLoading(QueryBuilder|Builder $query, ?string $trash = null): void
    {
        $query->root()->with([
            'allChildren' => function ($q) use ($trash) {
                $q->orderBy('sort_order')->with('media');
                if ($trash === 'with' || $trash === 'only') {
                    $q->withTrashed();
                }
            },
            'media',
        ])->withCount(['products' => fn ($q) => $q->active()]);
    }

    /**
     * Toggle the active status of a category.
     */
    public function toggleActive(Model $model): bool
    {
        /** @var Category $model */
        $model->is_active = ! $model->is_active;

        return $model->save();
    }

    /**
     * Get root categories.
     */
    public function getRootCategories(): Collection
    {
        return Category::root()->orderBy('sort_order')->get();
    }

    /**
     * Reorder the entire category tree.
     */
    public function reorderTree(array $treeData): void
    {
        $this->processTreeReorder($treeData);
    }

    /**
     * Recursively process tree reordering.
     */
    protected function processTreeReorder(array $nodes, ?string $parentId = null): void
    {
        foreach ($nodes as $index => $node) {
            Category::where('id', $node['id'])->update([
                'parent_id' => $parentId,
                'sort_order' => $index,
            ]);

            if (! empty($node['children'])) {
                $this->processTreeReorder($node['children'], $node['id']);
            }
        }
    }

    /**
     * Implementation for ProtectsBulkResources if needed.
     */
    public function getProtectedIds(array $ids): array
    {
        $user = Auth::user();

        return Category::withTrashed()
            ->withCount(['products' => fn ($q) => $q->active()])
            ->whereIn('id', $ids)
            ->cursor()
            ->filter(fn (Category $category) => $category->isProtected($user))
            ->pluck('id')
            ->toArray();
    }
}

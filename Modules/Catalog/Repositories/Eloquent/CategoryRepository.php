<?php

namespace Modules\Catalog\Repositories\Eloquent;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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
        $trash = $params['filter']['trash'] ?? null;
        $search = $params['filter']['search'] ?? null;

        $request = request();
        if (! empty($params)) {
            $request = clone $request;
            $request->merge($params);
        }

        $query = QueryBuilder::for(Category::class, $request)
            ->allowedFilters(...[
                AllowedFilter::scope('search'),
                AllowedFilter::trashed('trash'),
            ])
            ->defaultSort('sort_order');

        // If searching or viewing ONLY trashed items, return flat list for better UX
        if (! empty($search) || $trash === 'only') {
            return $query->with('parent')->paginate($perPage)->withQueryString();
        }

        // Tree mode: get root items with all levels of children
        $this->applyTreeLoading($query, $trash);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get the category tree with filters (non-paginated).
     */
    public function getTree(array $filters = []): Collection
    {
        $trash = $filters['filter']['trash'] ?? null;

        $query = QueryBuilder::for(Category::class)
            ->allowedFilters(...[
                AllowedFilter::scope('search'),
                AllowedFilter::trashed('trash'),
            ])
            ->defaultSort('sort_order');

        if (empty($filters['filter']['search']) && $trash !== 'only') {
            $this->applyTreeLoading($query, $trash);
        }

        return $query->get();
    }

    /**
     * Apply recursive tree loading with media and product counts.
     */
    protected function applyTreeLoading(QueryBuilder|Builder $query, ?string $trash = null): void
    {
        $query->root()->with([
            'allChildren' => function ($q) use ($trash) {
                $q->orderBy('sort_order')->with('media')->withCount(['products' => fn ($q) => $q->active()]);
                if ($trash === 'with' || $trash === 'only') {
                    $q->withTrashed();
                }
            },
        ])->with('media')->withCount(['products' => fn ($q) => $q->active()]);
    }

    /**
     * Toggle the active status of a category.
     */
    public function toggleActive(Category $category): bool
    {
        $category->is_active = ! $category->is_active;

        return $category->save();
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
            ->whereIn('id', $ids)
            ->get()
            ->filter(fn (Category $category) => $category->shouldBeProtected($user))
            ->pluck('id')
            ->toArray();
    }
}

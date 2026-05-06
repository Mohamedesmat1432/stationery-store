<?php

namespace Modules\Catalog\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Catalog\Data\CategoryData;
use Modules\Catalog\Data\ExportCategoriesData;
use Modules\Catalog\Data\ImportCategoriesData;
use Modules\Catalog\Services\CategoryService;
use Modules\Shared\Http\Controllers\Traits\HandlesBulkActions;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CategoryController extends Controller
{
    use HandlesBulkActions;

    public function __construct(
        protected CategoryService $categoryService
    ) {}

    /**
     * Display a listing of categories.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Category::class);

        return Inertia::render('Admin/Catalog/Categories/Index', [
            'categories' => $this->categoryService->getCategoriesPaginated($request->all(), (int) ($request->input('per_page', 5))),
            'filters' => $request->only(['filter']),
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): Response
    {
        Gate::authorize('create', Category::class);

        return Inertia::render('Admin/Catalog/Categories/Create', [
            'available_categories' => Inertia::defer(fn () => $this->categoryService->getCategoryTree()),
        ]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(CategoryData $data): RedirectResponse
    {
        Gate::authorize('create', Category::class);

        $this->categoryService->createCategory($data);

        return to_route('admin.categories.index')->with('success', __('Category created successfully.'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): Response
    {
        Gate::authorize('update', $category);

        $category->loadMissing(['media', 'parent', 'allChildren']);

        return Inertia::render('Admin/Catalog/Categories/Edit', [
            'category' => CategoryData::fromCategory($category),
            'available_categories' => Inertia::defer(fn () => $this->categoryService->getCategoryTree()),
        ]);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Category $category, CategoryData $data): RedirectResponse
    {
        Gate::authorize('update', $category);

        $this->categoryService->updateCategory($category, $data);

        return to_route('admin.categories.index')->with('success', __('Category updated successfully.'));
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        $this->categoryService->deleteCategory($category);

        return to_route('admin.categories.index')->with('success', __('Category deleted successfully.'));
    }

    /**
     * Restore a soft-deleted category.
     */
    public function restore(Category $category): RedirectResponse
    {
        $this->categoryService->restoreCategory($category);

        return back()->with('success', __('Category restored successfully.'));
    }

    /**
     * Permanently delete a category.
     */
    public function forceDelete(Category $category): RedirectResponse
    {
        $this->categoryService->forceDeleteCategory($category);

        return to_route('admin.categories.index')->with('success', __('Category permanently deleted.'));
    }

    /**
     * Handle bulk actions for categories.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        return $this->performBulkAction($request, Category::class, 'categoryService');
    }

    /**
     * Reorder the category tree.
     */
    public function reorder(Request $request): RedirectResponse
    {
        Gate::authorize('update', Category::class);

        $data = $request->validate([
            'tree' => ['required', 'array'],
        ]);

        $this->categoryService->reorderTree($data['tree']);

        return back()->with('success', __('Category tree reordered successfully.'));
    }

    /**
     * Toggle the active status of a category.
     */
    public function toggleActive(Category $category): RedirectResponse
    {
        Gate::authorize('update', $category);

        $this->categoryService->toggleActive($category);

        return back()->with('success', __('Category status updated successfully.'));
    }

    /**
     * Export categories.
     */
    public function export(ExportCategoriesData $data): BinaryFileResponse
    {
        Gate::authorize('export', Category::class);

        return $this->categoryService->exportCategories($data->columns, $data->format);
    }

    /**
     * Import categories.
     */
    public function import(ImportCategoriesData $data): RedirectResponse
    {
        Gate::authorize('import', Category::class);

        $this->categoryService->importCategories($data->file);

        return to_route('admin.categories.index')->with('success', __('Categories imported successfully.'));
    }
}

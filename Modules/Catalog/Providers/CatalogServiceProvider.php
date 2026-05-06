<?php

namespace Modules\Catalog\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Catalog\Listeners\SyncCatalogCache;
use Modules\Catalog\Policies\BrandPolicy;
use Modules\Catalog\Policies\CategoryPolicy;
use Modules\Catalog\Policies\ProductPolicy;
use Modules\Catalog\Repositories\Contracts\BrandRepositoryInterface;
use Modules\Catalog\Repositories\Contracts\CategoryRepositoryInterface;
use Modules\Catalog\Repositories\Contracts\ProductRepositoryInterface;
use Modules\Catalog\Repositories\Eloquent\BrandRepository;
use Modules\Catalog\Repositories\Eloquent\CategoryRepository;
use Modules\Catalog\Repositories\Eloquent\ProductRepository;
use Modules\Shared\Events\ResourceChanged;

class CatalogServiceProvider extends ServiceProvider
{
    /**
     * Register Catalog module bindings.
     */
    public function register(): void
    {
        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
        );

        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );

        $this->app->bind(
            BrandRepositoryInterface::class,
            BrandRepository::class
        );
    }

    /**
     * Bootstrap Catalog module services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerEvents();
        $this->registerRoutes();
    }

    /**
     * Register the module's policies.
     */
    protected function registerPolicies(): void
    {
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
    }

    /**
     * Register events and listeners.
     */
    protected function registerEvents(): void
    {
        Event::listen(ResourceChanged::class, SyncCatalogCache::class);
        // Add other domain listeners here as they are created
    }

    /**
     * Register the module's routes.
     */
    protected function registerRoutes(): void
    {
        // Main catalog routes
        if (file_exists(__DIR__.'/../Routes/web.php')) {
            Route::middleware(['web', 'auth', 'verified'])
                ->prefix('admin')
                ->as('admin.')
                ->group(__DIR__.'/../Routes/web.php');
        }

        // Public routes
        if (file_exists(__DIR__.'/../Routes/public.php')) {
            Route::middleware(['web'])
                ->group(__DIR__.'/../Routes/public.php');
        }
    }
}

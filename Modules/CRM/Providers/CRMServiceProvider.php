<?php

namespace Modules\CRM\Providers;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\CRM\Listeners\SyncCRMCache;
use Modules\CRM\Policies\CustomerGroupPolicy;
use Modules\CRM\Policies\CustomerPolicy;
use Modules\CRM\Repositories\Contracts\CustomerGroupRepositoryInterface;
use Modules\CRM\Repositories\Contracts\CustomerRepositoryInterface;
use Modules\CRM\Repositories\Eloquent\CustomerGroupRepository;
use Modules\CRM\Repositories\Eloquent\CustomerRepository;
use Modules\Shared\Events\ResourceChanged;

class CRMServiceProvider extends ServiceProvider
{
    /**
     * Register CRM module bindings.
     */
    public function register(): void
    {
        $this->app->bind(
            CustomerRepositoryInterface::class,
            CustomerRepository::class
        );

        $this->app->bind(
            CustomerGroupRepositoryInterface::class,
            CustomerGroupRepository::class
        );
    }

    /**
     * Bootstrap CRM module services.
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
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(CustomerGroup::class, CustomerGroupPolicy::class);
    }

    /**
     * Register events and listeners.
     */
    protected function registerEvents(): void
    {
        // Unify all resource changes (single, bulk, import) to trigger cache sync
        Event::listen(ResourceChanged::class, SyncCRMCache::class);
    }

    /**
     * Register the module's routes.
     */
    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'auth', 'verified'])
            ->prefix('admin')
            ->as('admin.')
            ->group(__DIR__.'/../Routes/web.php');
    }
}

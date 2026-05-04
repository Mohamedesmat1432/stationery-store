<?php

namespace Modules\Identity\Providers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Identity\Enums\RoleName;
use Modules\Identity\Listeners\SyncIdentityCache;
use Modules\Identity\Policies\RolePolicy;
use Modules\Identity\Policies\UserPolicy;
use Modules\Identity\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Identity\Repositories\Contracts\UserRepositoryInterface;
use Modules\Identity\Repositories\Eloquent\RoleRepository;
use Modules\Identity\Repositories\Eloquent\UserRepository;
use Modules\Shared\Events\ResourceChanged;
use Spatie\Permission\Events\PermissionAttachedEvent;
use Spatie\Permission\Events\PermissionDetachedEvent;
use Spatie\Permission\Events\RoleAttachedEvent;
use Spatie\Permission\Events\RoleDetachedEvent;

class IdentityServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            RoleRepositoryInterface::class,
            RoleRepository::class
        );

        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerEvents();
        $this->registerRoutes();

        // Grant 'admin' role full access to everything
        Gate::before(function ($user, $ability) {
            return $user->hasRole(RoleName::ADMIN->value) ? true : null;
        });
    }

    /**
     * Register the module's policies.
     */
    protected function registerPolicies(): void
    {
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }

    /**
     * Register events and listeners.
     */
    protected function registerEvents(): void
    {
        // Unify all resource changes (single, bulk, import) to trigger cache sync
        Event::listen(ResourceChanged::class, SyncIdentityCache::class);

        // Access Control → Flush Cache on Role/Permission attach/detach (Spatie events)
        Event::listen(RoleAttachedEvent::class, SyncIdentityCache::class);
        Event::listen(RoleDetachedEvent::class, SyncIdentityCache::class);
        Event::listen(PermissionAttachedEvent::class, SyncIdentityCache::class);
        Event::listen(PermissionDetachedEvent::class, SyncIdentityCache::class);
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

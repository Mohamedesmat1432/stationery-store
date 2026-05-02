<?php

namespace Modules\Identity\Providers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Identity\Listeners\FlushUserPermissionCache;
use Modules\Identity\Observers\PermissionObserver;
use Modules\Identity\Observers\RoleObserver;
use Modules\Identity\Observers\UserObserver;
use Modules\Identity\Policies\RolePolicy;
use Modules\Identity\Policies\UserPolicy;
use Modules\Identity\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Identity\Repositories\Contracts\UserRepositoryInterface;
use Modules\Identity\Repositories\Eloquent\RoleRepository;
use Modules\Identity\Repositories\Eloquent\UserRepository;
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
        $this->registerObservers();
        $this->registerEvents();
        $this->registerRoutes();

        // Grant 'admin' role full access to everything
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
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
     * Register model observers.
     */
    protected function registerObservers(): void
    {
        User::observe(UserObserver::class);
        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);
    }

    /**
     * Register events and listeners.
     */
    protected function registerEvents(): void
    {
        // Access Control → Flush Redis Cache on Role/Permission changes
        Event::listen(RoleAttachedEvent::class, FlushUserPermissionCache::class);
        Event::listen(RoleDetachedEvent::class, FlushUserPermissionCache::class);
        Event::listen(PermissionAttachedEvent::class, FlushUserPermissionCache::class);
        Event::listen(PermissionDetachedEvent::class, FlushUserPermissionCache::class);
    }

    /**
     * Register the module's routes.
     */
    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'auth', 'verified'])
            ->prefix('admin')
            ->as('admin.')
            ->group(__DIR__ . '/../Routes/web.php');
    }
}

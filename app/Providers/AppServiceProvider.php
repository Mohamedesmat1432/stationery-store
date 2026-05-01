<?php

namespace App\Providers;

use App\Events\OrderCancelled;
use App\Events\OrderCreated;
use App\Events\OrderDelivered;
use App\Events\OrderPaid;
use App\Events\OrderShipped;
use App\Events\StockLow;
use App\Listeners\DeductStockOnOrderPaid;
use App\Listeners\FlushUserPermissionCache;
use App\Listeners\NotifyAdminOfLowStock;
use App\Listeners\ReleaseStockOnOrderCancelled;
use App\Listeners\SendOrderConfirmation;
use App\Listeners\SendShippingUpdate;
use App\Listeners\UpdateCustomerStatsOnOrderDelivered;
use App\Listeners\UpdateStockOnOrder;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Observers\CustomerGroupObserver;
use App\Observers\CustomerObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\StockObserver;
use App\Observers\UserObserver;
use App\Repositories\Contracts\CustomerGroupRepositoryInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\CustomerGroupRepository;
use App\Repositories\Eloquent\CustomerRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\UserRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Events\PermissionAttachedEvent;
use Spatie\Permission\Events\PermissionDetachedEvent;
use Spatie\Permission\Events\RoleAttachedEvent;
use Spatie\Permission\Events\RoleDetachedEvent;

class AppServiceProvider extends ServiceProvider
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

        $this->app->bind(
            CustomerGroupRepositoryInterface::class,
            CustomerGroupRepository::class
        );

        $this->app->bind(
            CustomerRepositoryInterface::class,
            CustomerRepository::class
        );

        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerObservers();
        $this->registerEvents();

        // Grant 'admin' role full access to everything
        // Gate::before(function ($user, $ability) {
        //     return $user->hasRole('admin') ? true : null;
        // });
    }

    /**
     * Register events and listeners.
     */
    protected function registerEvents(): void
    {
        // Order Created → reserve stock + send confirmation email
        Event::listen(OrderCreated::class, UpdateStockOnOrder::class);
        Event::listen(OrderCreated::class, SendOrderConfirmation::class);

        // Order Paid (PROCESSING) → deduct stock from inventory
        Event::listen(OrderPaid::class, DeductStockOnOrderPaid::class);

        // Order Shipped → send shipping notification to customer
        Event::listen(OrderShipped::class, SendShippingUpdate::class);

        // Order Delivered → update customer lifetime stats
        Event::listen(OrderDelivered::class, UpdateCustomerStatsOnOrderDelivered::class);

        // Order Cancelled → release reserved stock
        Event::listen(OrderCancelled::class, ReleaseStockOnOrderCancelled::class);

        // Stock Low → notify admin
        Event::listen(StockLow::class, NotifyAdminOfLowStock::class);

        // Access Control → Flush Redis Cache on Role/Permission changes
        Event::listen(RoleAttachedEvent::class, FlushUserPermissionCache::class);
        Event::listen(RoleDetachedEvent::class, FlushUserPermissionCache::class);
        Event::listen(PermissionAttachedEvent::class, FlushUserPermissionCache::class);
        Event::listen(PermissionDetachedEvent::class, FlushUserPermissionCache::class);
    }

    /**
     * Register model observers.
     */
    protected function registerObservers(): void
    {
        Order::observe(OrderObserver::class);
        Product::observe(ProductObserver::class);
        Stock::observe(StockObserver::class);
        User::observe(UserObserver::class);
        CustomerGroup::observe(CustomerGroupObserver::class);
        Customer::observe(CustomerObserver::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

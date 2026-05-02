<?php

namespace App\Providers;

use App\Events\OrderCancelled;
use App\Events\OrderCreated;
use App\Events\OrderDelivered;
use App\Events\OrderPaid;
use App\Events\OrderShipped;
use App\Events\StockLow;
use App\Listeners\DeductStockOnOrderPaid;
use App\Listeners\NotifyAdminOfLowStock;
use App\Listeners\ReleaseStockOnOrderCancelled;
use App\Listeners\SendOrderConfirmation;
use App\Listeners\SendShippingUpdate;
use App\Listeners\UpdateCustomerStatsOnOrderDelivered;
use App\Listeners\UpdateStockOnOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\StockObserver;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Eloquent\ProductRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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
    }

    /**
     * Register model observers.
     */
    protected function registerObservers(): void
    {
        Order::observe(OrderObserver::class);
        Product::observe(ProductObserver::class);
        Stock::observe(StockObserver::class);
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

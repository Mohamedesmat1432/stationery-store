<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\StockService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateStockOnOrder implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(protected StockService $stockService) {}

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $this->stockService->reserveForOrder($event->order);
    }
}

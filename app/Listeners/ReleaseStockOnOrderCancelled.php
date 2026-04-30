<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Services\StockService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReleaseStockOnOrderCancelled implements ShouldQueue
{
    public function __construct(protected StockService $stockService) {}

    public function handle(OrderCancelled $event): void
    {
        $this->stockService->releaseForOrder($event->order);
    }
}

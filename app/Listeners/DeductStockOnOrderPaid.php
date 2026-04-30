<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Services\StockService;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeductStockOnOrderPaid implements ShouldQueue
{
    public function __construct(protected StockService $stockService) {}

    public function handle(OrderPaid $event): void
    {
        $this->stockService->deductForOrder($event->order);
    }
}

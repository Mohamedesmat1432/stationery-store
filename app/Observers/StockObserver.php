<?php

namespace App\Observers;

use App\Models\Stock;

class StockObserver
{
    public function updated(Stock $stock): void
    {
        $stock->updateRedisStock();

        if ($stock->isLowStock()) {
            // Dispatch low stock notification
            // LowStockNotification::dispatch($stock);
        }
    }
}

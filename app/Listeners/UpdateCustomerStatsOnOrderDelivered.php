<?php

namespace App\Listeners;

use App\Events\OrderDelivered;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateCustomerStatsOnOrderDelivered implements ShouldQueue
{
    /**
     * Update customer lifetime stats when an order is confirmed delivered.
     */
    public function handle(OrderDelivered $event): void
    {
        if ($event->order->customer) {
            $event->order->customer->updateTotalSpent();
        }
    }
}

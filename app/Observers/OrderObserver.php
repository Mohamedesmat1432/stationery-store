<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Events\OrderCancelled;
use App\Events\OrderDelivered;
use App\Events\OrderPaid;
use App\Events\OrderShipped;
use App\Models\Order;

class OrderObserver
{
    /**
     * Handle order status transitions by dispatching domain events.
     *
     * Business flow:
     * PENDING → PROCESSING (payment confirmed) → OrderPaid
     * PROCESSING → SHIPPED → OrderShipped
     * SHIPPED → DELIVERED → OrderDelivered
     * any → CANCELLED → OrderCancelled
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            match ($order->status) {
                OrderStatus::PROCESSING => OrderPaid::dispatch($order),
                OrderStatus::SHIPPED => OrderShipped::dispatch($order),
                OrderStatus::DELIVERED => OrderDelivered::dispatch($order),
                OrderStatus::CANCELLED => OrderCancelled::dispatch($order),
                default => null,
            };
        }
    }
}

<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use App\Notifications\OrderShippedNotification;

class SendShippingUpdate
{
    public function handle(OrderShipped $event): void
    {
        if ($event->order->user) {
            $event->order->user->notify(new OrderShippedNotification($event->order));
        }
    }
}

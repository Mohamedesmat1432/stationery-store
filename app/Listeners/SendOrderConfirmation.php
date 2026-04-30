<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Notifications\OrderConfirmationNotification;

class SendOrderConfirmation
{
    public function handle(OrderCreated $event): void
    {
        if ($event->order->user) {
            $event->order->user->notify(new OrderConfirmationNotification($event->order));
        }
    }
}

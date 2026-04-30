<?php

namespace App\Listeners;

use App\Events\StockLow;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;

class NotifyAdminOfLowStock
{
    public function handle(StockLow $event): void
    {
        $admins = User::role('admin')->get();
        Notification::send($admins, new LowStockNotification($event->stock));
    }
}

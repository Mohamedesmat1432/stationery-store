<?php

namespace App\Notifications;

use App\Models\Stock;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public Stock $stock) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('Low Stock Alert: '.$this->stock->product->name)
            ->line('Product: '.$this->stock->product->name)
            ->line('Current Stock: '.$this->stock->available_quantity)
            ->line('Reorder Level: '.$this->stock->reorder_level)
            ->action('Manage Inventory', url('/admin/inventory'))
            ->line('Please restock soon.');
    }

    public function toArray($notifiable): array
    {
        return [
            'product_id' => $this->stock->product_id,
            'product_name' => $this->stock->product->name,
            'current_quantity' => $this->stock->available_quantity,
            'message' => 'Low stock alert for '.$this->stock->product->name,
        ];
    }
}

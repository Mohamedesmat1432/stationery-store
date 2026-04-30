<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('Order Cancelled - '.$this->order->order_number)
            ->line('Your order '.$this->order->order_number.' has been cancelled.')
            ->line('If this was a mistake, please contact our support team.')
            ->action('View Order Details', url('/orders/'.$this->order->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'message' => 'Your order '.$this->order->order_number.' was cancelled.',
        ];
    }
}

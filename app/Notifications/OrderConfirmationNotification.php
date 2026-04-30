<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmationNotification extends Notification implements ShouldQueue
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
            ->subject('Order Confirmation - '.$this->order->order_number)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Thank you for your order! We have received it and are processing it.')
            ->line('Order Number: '.$this->order->order_number)
            ->line('Total: '.$this->order->grand_total.' '.$this->order->currency->code)
            ->action('View Order', url('/orders/'.$this->order->id))
            ->line('Thank you for shopping with us!');
    }

    public function toArray($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'amount' => $this->order->grand_total,
            'message' => 'Your order '.$this->order->order_number.' has been confirmed.',
        ];
    }
}

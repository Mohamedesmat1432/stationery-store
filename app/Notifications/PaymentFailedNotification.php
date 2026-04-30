<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
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
            ->subject('Payment Failed - '.$this->order->order_number)
            ->line('We were unable to process your payment for order '.$this->order->order_number.'.')
            ->line('Please update your payment method to complete the purchase.')
            ->action('Retry Payment', url('/orders/'.$this->order->id.'/payment'));
    }

    public function toArray($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'message' => 'Payment failed for order '.$this->order->order_number.'.',
        ];
    }
}

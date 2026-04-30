<?php

namespace App\Services;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Order;

class OrderService
{
    /**
     * Mark an order as paid and transition to PROCESSING.
     * Note: OrderPaid event is dispatched by the OrderObserver on status change.
     *
     * @throws InvalidOrderTransitionException
     */
    public function markAsPaid(Order $order): void
    {
        $this->guardTransition($order, OrderStatus::PROCESSING);

        $order->update([
            'payment_status' => PaymentStatus::PAID->value,
            'status' => OrderStatus::PROCESSING->value,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark an order as shipped.
     * Note: OrderShipped event is dispatched by the OrderObserver on status change.
     *
     * @throws InvalidOrderTransitionException
     */
    public function markAsShipped(Order $order, ?string $trackingNumber = null): void
    {
        $this->guardTransition($order, OrderStatus::SHIPPED);

        $order->update([
            'status' => OrderStatus::SHIPPED->value,
            'fulfillment_status' => FulfillmentStatus::FULFILLED->value,
            'shipped_at' => now(),
            'tracking_number' => $trackingNumber,
        ]);
    }

    /**
     * Mark an order as delivered.
     * Note: OrderDelivered event is dispatched by the OrderObserver on status change.
     *
     * @throws InvalidOrderTransitionException
     */
    public function markAsDelivered(Order $order): void
    {
        $this->guardTransition($order, OrderStatus::DELIVERED);

        $order->update([
            'status' => OrderStatus::DELIVERED->value,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Cancel an order and release stock.
     * Note: OrderCancelled event is dispatched by the OrderObserver on status change.
     *
     * @throws InvalidOrderTransitionException
     */
    public function cancel(Order $order, ?string $reason = null): void
    {
        $this->guardTransition($order, OrderStatus::CANCELLED);

        $data = [
            'status' => OrderStatus::CANCELLED->value,
            'cancelled_at' => now(),
        ];

        if ($reason) {
            $data['admin_notes'] = trim(($order->admin_notes ?? '')."\nCancellation reason: ".$reason);
        }

        $order->update($data);
    }

    /**
     * Validate that the order can transition to the target status.
     *
     * @throws InvalidOrderTransitionException
     */
    protected function guardTransition(Order $order, OrderStatus $target): void
    {
        if (! $order->status->canTransitionTo($target)) {
            throw new InvalidOrderTransitionException($order->status, $target);
        }
    }
}

<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class ProcessPaymentAction
{
    /**
     * Mark a payment as successful and update the order.
     * Idempotent — safe to call multiple times (e.g., webhook retries).
     * Note: OrderPaid event is dispatched by the OrderObserver on status change.
     */
    public function execute(Payment $payment, string $gatewayReference): void
    {
        // Idempotency guard — skip if already processed
        if ($payment->isSuccessful()) {
            return;
        }

        DB::transaction(function () use ($payment, $gatewayReference) {
            $payment->update([
                'status' => PaymentStatus::PAID->value,
                'gateway_reference' => $gatewayReference,
                'paid_at' => now(),
            ]);

            $order = $payment->order;

            // Only transition if the order is still in PENDING state
            if ($order->status === OrderStatus::PENDING) {
                $order->update([
                    'payment_status' => PaymentStatus::PAID->value,
                    'status' => OrderStatus::PROCESSING->value,
                    'paid_at' => now(),
                ]);
            }
        });
    }
}

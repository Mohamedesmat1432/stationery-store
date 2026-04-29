<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Enums\StockMovementType;
use App\Models\Order;
use App\Models\Stock;
use App\Models\StockMovement;

class OrderObserver
{
    public function created(Order $order): void
    {
        // Reserve stock when order is created
        foreach ($order->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('variant_id', $item->variant_id)
                ->first();

            if ($stock) {
                $stock->reserve($item->quantity);
            }
        }
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            match($order->status) {
                OrderStatus::DELIVERED => $this->handlePaid($order),
                OrderStatus::CANCELLED => $this->handleCancelled($order),
                OrderStatus::SHIPPED => $this->handleShipped($order),
                default => null,
            };
        }
    }

    protected function handlePaid(Order $order): void
    {
        // Convert reservation to actual stock deduction
        foreach ($order->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('variant_id', $item->variant_id)
                ->first();

            if ($stock) {
                $beforeQty = $stock->available_quantity;
                $stock->deduct($item->quantity);

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'warehouse_id' => $stock->warehouse_id,
                    'unit_id' => $item->unit_id,
                    'quantity' => -$item->quantity,
                    'before_quantity' => $beforeQty,
                    'after_quantity' => $stock->available_quantity,
                    'type' => StockMovementType::SALE->value,
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                ]);
            }
        }
    }

    protected function handleCancelled(Order $order): void
    {
        // Release reserved stock
        foreach ($order->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('variant_id', $item->variant_id)
                ->first();

            if ($stock) {
                $stock->release($item->quantity);
            }
        }
    }

    protected function handleShipped(Order $order): void
    {
        // Update customer stats
        if ($order->customer) {
            $order->customer->updateTotalSpent();
        }
    }
}

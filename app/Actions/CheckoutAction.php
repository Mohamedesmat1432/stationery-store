<?php

namespace App\Actions;

use App\Enums\CartStatus;
use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Exceptions\InsufficientStockException;
use App\Models\Cart;
use App\Models\CouponUsage;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CheckoutAction
{
    /**
     * Convert a cart into an order.
     *
     * @throws \Exception
     */
    public function execute(Cart $cart): Order
    {
        return DB::transaction(function () use ($cart) {
            // 1. Create the Order
            $order = Order::create([
                'user_id' => $cart->user_id,
                'customer_id' => $cart->user?->customer?->id,
                'currency_id' => $cart->currency_id,
                'subtotal' => $cart->subtotal,
                'tax_total' => $cart->tax_total,
                'discount_total' => $cart->discount_total,
                'shipping_total' => $cart->shipping_total,
                'grand_total' => $cart->grand_total,
                'status' => OrderStatus::PENDING->value,
                'shipping_address_id' => $cart->shipping_address_id,
                'billing_address_id' => $cart->billing_address_id,
                'discount_id' => $cart->discount_id,
                'coupon_code' => $cart->discount?->code,
                'ip_address' => $cart->ip_address,
                'user_agent' => $cart->user_agent,
            ]);

            // 2. Process Items & Validate Stock
            foreach ($cart->items as $item) {
                $this->validateStock($item);

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'unit_id' => $item->unit_id,
                    'name' => $item->product->name,
                    'sku' => $item->variant?->sku ?? $item->product->sku,
                    'image' => $item->product->getFeaturedImageUrl('thumb'),
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]);
            }

            // 3. Track Discount/Coupon Usage
            if ($cart->discount_id && $cart->discount) {
                $cart->discount->incrementUsage();

                CouponUsage::create([
                    'discount_id' => $cart->discount_id,
                    'user_id' => $cart->user_id,
                    'order_id' => $order->id,
                    'cart_id' => $cart->id,
                    'amount_saved' => $cart->discount_total,
                ]);
            }

            // 4. Update Cart Status
            $cart->update(['status' => CartStatus::CONVERTED->value]);

            // 5. Dispatch Event
            event(new OrderCreated($order));

            return $order;
        });
    }

    /**
     * Validate stock availability for a cart item.
     *
     * @throws InsufficientStockException
     */
    protected function validateStock($item): void
    {
        $variant = $item->variant;
        $product = $item->product;

        $available = $variant
            ? $variant->stock?->available_quantity ?? 0
            : $product->totalStock();

        if ($available < $item->quantity) {
            throw new InsufficientStockException(
                $product->name,
                $item->quantity,
                $available
            );
        }
    }
}

<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use Illuminate\Support\Facades\Redis;

class CartService
{
    /**
     * Add or update an item in the cart.
     */
    public function addItem(Cart $cart, Product $product, float $quantity, ?ProductVariant $variant = null, ?Unit $unit = null): CartItem
    {
        $unit = $unit ?? $product->productUnits()->where('is_default', true)->first()?->unit;
        $variant = $variant ?? $product->defaultVariant();

        $price = $variant
            ? $variant->prices()->active()->forCurrency($cart->currency_id)->first()
            : $product->prices()->active()->forCurrency($cart->currency_id)->first();

        $unitPrice = $price?->amount ?? 0;
        $subtotal = $unitPrice * $quantity;

        $item = $cart->items()->updateOrCreate(
            [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'unit_id' => $unit?->id,
            ],
            [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]
        );

        $this->recalculateTotals($cart);

        return $item;
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(Cart $cart, string $itemId): void
    {
        $cart->items()->where('id', $itemId)->delete();
        $this->recalculateTotals($cart);
    }

    /**
     * Clear the cart.
     */
    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
        $this->recalculateTotals($cart);
    }

    /**
     * Recalculate cart totals and sync with Redis.
     */
    public function recalculateTotals(Cart $cart): void
    {
        $subtotal = $cart->items()->sum('subtotal');
        $grandTotal = $this->computeGrandTotal($subtotal, $cart);

        $cart->update([
            'subtotal' => $subtotal,
            'grand_total' => $grandTotal,
        ]);

        $this->syncTotalsToRedis($cart, $subtotal, $grandTotal);
    }

    /**
     * Single source of truth for grand total calculation.
     */
    protected function computeGrandTotal(float $subtotal, Cart $cart): float
    {
        return $subtotal + $cart->tax_total + $cart->shipping_total - $cart->discount_total;
    }

    /**
     * Sync cart totals to Redis.
     */
    protected function syncTotalsToRedis(Cart $cart, float $subtotal, float $grandTotal): void
    {
        $totals = [
            'subtotal' => $subtotal,
            'grand_total' => $grandTotal,
            'items_count' => $cart->items()->count(),
        ];

        Redis::connection()->setex("cart:{$cart->id}:totals", 3600, json_encode($totals));
    }

    /**
     * Apply a discount code to the cart.
     */
    public function applyCoupon(Cart $cart, string $code): bool
    {
        // Implementation for coupon logic would go here
        // This usually involves checking discount availability, dates, etc.
        return true;
    }
}

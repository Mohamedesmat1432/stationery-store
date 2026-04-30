<?php

namespace App\Services;

use App\Enums\DiscountType;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\Tax;

class PricingService
{
    /**
     * Calculate tax for a given amount and tax rules.
     */
    public function calculateTax(float $amount, array $taxIds): float
    {
        $taxTotal = 0;
        $taxes = Tax::whereIn('id', $taxIds)->get();

        foreach ($taxes as $tax) {
            $taxTotal += $amount * ($tax->rate / 100);
        }

        return round($taxTotal, 4);
    }

    /**
     * Calculate discount for a given amount and discount rule.
     */
    public function calculateDiscount(float $amount, Discount $discount): float
    {
        return match ($discount->type) {
            DiscountType::PERCENTAGE => round($amount * ($discount->value / 100), 4),
            DiscountType::FIXED_AMOUNT => min($discount->value, $amount),
            DiscountType::FREE_SHIPPING => 0,
            default => 0,
        };
    }

    /**
     * Recalculate totals for a Cart or Order.
     */
    public function calculateTotals(Cart $cart): array
    {
        $subtotal = $cart->items->sum('subtotal');
        $discountTotal = $cart->discount
            ? $this->calculateDiscount($subtotal, $cart->discount)
            : 0;

        $shippingTotal = $cart->shipping_total ?? 0;
        $taxTotal = $cart->tax_total ?? 0;

        return [
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'shipping_total' => $shippingTotal,
            'tax_total' => $taxTotal,
            'grand_total' => $subtotal - $discountTotal + $shippingTotal + $taxTotal,
        ];
    }
}

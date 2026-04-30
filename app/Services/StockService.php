<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Order;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Reserve stock for an order.
     */
    public function reserveForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $stock = $this->findStock($item->product_id, $item->variant_id);

                if ($stock) {
                    $stock->reserve($item->quantity);
                }
            }
        });
    }

    /**
     * Release reserved stock for an order.
     */
    public function releaseForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $stock = $this->findStock($item->product_id, $item->variant_id);

                if ($stock) {
                    $stock->release($item->quantity);
                }
            }
        });
    }

    /**
     * Deduct stock for an order (convert reservation to actual sale).
     */
    public function deductForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $stock = $this->findStock($item->product_id, $item->variant_id);

                if ($stock) {
                    $beforeQty = $stock->available_quantity;
                    $stock->deduct($item->quantity);

                    $this->createMovement(
                        $stock,
                        $item->quantity,
                        $beforeQty,
                        StockMovementType::SALE,
                        Order::class,
                        $order->id,
                        $item->unit_id
                    );
                }
            }
        });
    }

    /**
     * Find stock record for a product/variant.
     */
    public function findStock(string $productId, ?string $variantId = null, ?string $warehouseId = null): ?Stock
    {
        $query = Stock::where('product_id', $productId);

        if ($variantId) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->first();
    }

    /**
     * Create a stock movement record.
     */
    public function createMovement(
        Stock $stock,
        float $quantity,
        $beforeQty,
        StockMovementType $type,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $unitId = null
    ): StockMovement {
        return StockMovement::create([
            'product_id' => $stock->product_id,
            'variant_id' => $stock->variant_id,
            'warehouse_id' => $stock->warehouse_id,
            'unit_id' => $unitId ?? $stock->unit_id,
            'quantity' => $quantity * $type->sign(),
            'before_quantity' => $beforeQty,
            'after_quantity' => $stock->available_quantity,
            'type' => $type->value,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }
}

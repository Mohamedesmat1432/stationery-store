<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function saved(Product $product): void
    {
        $product->forgetRedisCache();
    }

    public function deleted(Product $product): void
    {
        $product->forgetRedisCache();
        $product->forgetRedisCache('products:featured:*');
    }
}

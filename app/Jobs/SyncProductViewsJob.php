<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductView;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class SyncProductViewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $redis = Redis::connection();
        $cursor = '0';

        do {
            [$cursor, $keys] = $redis->scan($cursor, ['MATCH' => 'product:*:views', 'COUNT' => 100]);

            foreach ($keys as $key) {
                if (preg_match('/product:(.*):views/', $key, $matches)) {
                    $productId = $matches[1];
                    $views = $redis->getset($key, 0);

                    if ($views > 0) {
                        Product::where('id', $productId)->increment('views_count', $views);

                        ProductView::create([
                            'product_id' => $productId,
                            'views' => $views,
                            'period' => now()->format('Y-m-d H:00:00'),
                        ]);
                    }
                }
            }
        } while ($cursor !== '0');
    }
}

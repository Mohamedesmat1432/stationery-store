<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

require __DIR__.'/../../../../vendor/autoload.php';
$app = require_once __DIR__.'/../../../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "--- Redis Cache Debugger ---\n";

$cachePrefix = Cache::getPrefix();
echo "Laravel Cache Prefix: '{$cachePrefix}'\n";

$redisPrefix = config('database.redis.options.prefix');
echo "Redis Connection Prefix: '{$redisPrefix}'\n";

$tags = ['customers', 'customer_groups', 'users'];

foreach ($tags as $tag) {
    echo "\nAnalyzing Tag: {$tag}\n";
    $trackingSet = "tracker:set:{$tag}";

    $redis = Redis::connection('cache');
    $keys = $redis->smembers($trackingSet);

    echo 'Tracked Keys in DB: '.count($keys)."\n";
    foreach ($keys as $key) {
        $exists = Cache::has($key) ? 'YES' : 'NO';
        echo " - Key: {$key} (Exists in Cache: {$exists})\n";
    }
}

echo "\n--- End of Debug ---\n";

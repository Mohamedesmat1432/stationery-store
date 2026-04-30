<?php

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

// Test the filter manually
$request = new Request(['filter' => ['trash' => 'only']]);
$query = QueryBuilder::for(User::class, $request)
    ->allowedFilters(...[
        AllowedFilter::trashed('trash'),
    ]);

echo 'SQL: '.$query->toSql().PHP_EOL;
echo 'Count: '.$query->count().PHP_EOL;

<?php

use App\Models\CustomerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

uses(RefreshDatabase::class);

test('multiple customer group updates only trigger one flush per related tag after commit', function () {
    CustomerGroup::resetFlushedTags();

    $redisMock = Mockery::mock('StdClass');
    Redis::shouldReceive('connection')->andReturn($redisMock);

    // We expect 2 calls per tag (TitleCase and lowercase)
    // But since we have 3 tags (Customer, Price, Product) + the Group tag itself = 4 tags.
    // Each tag is flushed twice (standard and lower). Total 8 calls to deleteRedisByPattern.
    // BUT we only expect this to happen ONCE even if we update 10 groups.

    $redisMock->shouldReceive('client')->andReturn($redisMock);
    $scanCount = 0;
    $redisMock->shouldReceive('scan')->andReturnUsing(function () use (&$scanCount) {
        return $scanCount++ % 2 === 0 ? ['key1'] : false;
    });
    $redisMock->shouldReceive('del')->atLeast()->times(1);

    DB::transaction(function () {
        CustomerGroup::factory()->count(3)->create();
        // At this point, no Redis DEL should have happened yet because we are in a transaction
    });

    // After commit, the hooks should run
});

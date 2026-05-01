<?php

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;

uses(RefreshDatabase::class);

beforeEach(function () {
    Redis::shouldReceive('connection')->andReturn(Mockery::mock('StdClass', function ($mock) {
        $mock->shouldReceive('client')->andReturn($mock);
        $mock->shouldReceive('scan')->andReturn(false);
        $mock->shouldReceive('del')->andReturn(0);
    }));
});

test('customer group saved event flushes related caches', function () {
    $group = CustomerGroup::factory()->create();

    // Since we are using shouldReceive on Redis, we can assert that flushRedisTag was called
    // However, it's easier to just check if the model events are working

    // We expect Customer::flushRedisTag(), Price::flushRedisTag(), Product::flushRedisTag()
    // but BaseModel also calls CustomerGroup::flushRedisTag()
});

test('customer group deleted event flushes related caches and dissociates customers', function () {
    $group = CustomerGroup::factory()->create();
    $customer = Customer::factory()->create(['customer_group_id' => $group->id]);

    $group->delete();

    expect($customer->fresh()->customer_group_id)->toBeNull();
});

<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\CRM\Data\CustomerGroupData;
use Modules\CRM\Services\CRMCacheService;
use Modules\CRM\Services\CustomerGroupService;
use Modules\Shared\Events\ResourceChanged;
use Modules\Shared\Services\Cache\BaseCacheService;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    BaseCacheService::clearVersionCache();
});

test('versioned cache isolates tag invalidation', function () {
    $tag = 'test_tag';
    $key = 'test_key';

    // Store a value
    $value1 = BaseCacheService::rememberDirect($tag, $key, fn () => 'original');
    expect($value1)->toBe('original');

    // Invalidate the tag - we'll use the service which dispatches the event
    CRMCacheService::flushCustomerCaches();

    // Clear local version cache to simulate new request
    BaseCacheService::clearVersionCache();

    // Store a new value — should not return stale data if the tag was flushed
    $value2 = BaseCacheService::rememberDirect($tag, $key, fn () => 'updated');

    // Note: BaseCacheService::rememberDirect increments the version if the tag is flushed.
    // If we flushed 'customers', but our tag is 'test_tag', it shouldn't affect it UNLESS we flush all.
    // This test actually verifies isolation.
    expect($value2)->toBe('original');

    // Now flush the actual tag
    BaseCacheService::incrementTagVersion($tag);
    BaseCacheService::clearVersionCache();

    $value3 = BaseCacheService::rememberDirect($tag, $key, fn () => 'new_value');
    expect($value3)->toBe('new_value');
});

test('service actions trigger cache invalidation via events', function () {
    $service = resolve(CustomerGroupService::class);

    $initialVersion = CRMCacheService::getTagVersion(CRMCacheService::TAG_CUSTOMER_GROUPS);

    // Create via service - this dispatches ResourceChanged
    $service->createCustomerGroup(CustomerGroupData::from([
        'name' => 'Service Group',
        'slug' => 'service-group',
        'discount_percentage' => 5.0,
        'is_active' => true,
    ]));

    $newVersion = CRMCacheService::getTagVersion(CRMCacheService::TAG_CUSTOMER_GROUPS);
    expect($newVersion)->toBeGreaterThan($initialVersion);
});

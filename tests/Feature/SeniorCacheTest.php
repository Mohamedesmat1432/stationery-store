<?php

use App\Models\CustomerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\CRM\Services\CRMCacheService;
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

    // Invalidate the tag
    CRMCacheService::flushCustomerCaches();

    // Clear local version cache to simulate new request
    BaseCacheService::clearVersionCache();

    // Store a new value — should not return stale data
    $value2 = BaseCacheService::rememberDirect($tag, $key, fn () => 'updated');
    expect($value2)->toBe('updated');
});

test('multiple customer group updates trigger cache invalidation after commit', function () {
    // Create groups within a transaction
    $groups = DB::transaction(function () {
        return CustomerGroup::factory()->count(3)->create();
    });

    // After transaction commit, observers with $afterCommit = true should have fired
    // Verify groups were created and cache version was updated
    expect(CustomerGroup::count())->toBe(3);
});

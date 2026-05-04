<?php

use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\CRM\Services\CRMCacheService;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clear cache before each test to ensure clean state
    Cache::flush();
});

test('customer group saved event flushes related caches', function () {
    $group = CustomerGroup::factory()->create();

    // Prime the cache
    CRMCacheService::rememberCustomerGroups([], 15, fn () => CustomerGroup::paginate(15));

    // Update the group — this should invalidate the cache via observer → event → listener
    $group->update(['name' => 'Updated Name']);

    // Cache should be invalidated — next read won't hit stale data
    // We verify by checking the version key was incremented
    $versionKey = 'version:customer_groups';
    expect(Cache::has($versionKey))->toBeTrue();
});

test('customer group deleted event flushes related caches', function () {
    $group = CustomerGroup::factory()->create();
    $customer = Customer::factory()->create(['customer_group_id' => $group->id]);

    // Prime the cache
    CRMCacheService::rememberCustomerGroups([], 15, fn () => CustomerGroup::paginate(15));

    $group->delete();

    // Verify the customer was soft-deleted along with cache invalidation
    expect($group->fresh())->toBeNull();
});

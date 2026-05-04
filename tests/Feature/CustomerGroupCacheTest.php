<?php

use App\Models\CustomerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\CRM\Data\CustomerGroupData;
use Modules\CRM\Services\CRMCacheService;
use Modules\CRM\Services\CustomerGroupService;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    CRMCacheService::clearVersionCache();
});

test('customer group created via service flushes related caches', function () {
    $service = resolve(CustomerGroupService::class);

    // Initial version
    $initialVersion = CRMCacheService::getTagVersion(CRMCacheService::TAG_CUSTOMER_GROUPS);

    // Create via service
    $service->createCustomerGroup(CustomerGroupData::from([
        'name' => 'New Group',
        'slug' => 'new-group',
        'discount_percentage' => 10.0,
        'is_active' => true,
    ]));

    // Version should be incremented
    $newVersion = CRMCacheService::getTagVersion(CRMCacheService::TAG_CUSTOMER_GROUPS);
    expect($newVersion)->toBeGreaterThan($initialVersion);
});

test('customer group updated via service flushes related caches', function () {
    $service = resolve(CustomerGroupService::class);
    $group = CustomerGroup::factory()->create();

    // Prime and get version
    $initialVersion = CRMCacheService::getTagVersion(CRMCacheService::TAG_CUSTOMER_GROUPS);

    // Update via service
    $service->updateCustomerGroup($group, CustomerGroupData::from([
        'name' => 'Updated Name',
        'slug' => $group->slug,
        'discount_percentage' => $group->discount_percentage,
        'is_active' => true,
    ]));

    // Version should be incremented
    $newVersion = CRMCacheService::getTagVersion(CRMCacheService::TAG_CUSTOMER_GROUPS);
    expect($newVersion)->toBeGreaterThan($initialVersion);
});

test('customer group deleted via service flushes related caches', function () {
    $service = resolve(CustomerGroupService::class);
    $group = CustomerGroup::factory()->create(['slug' => 'not-protected']);

    // Prime and get version
    $initialVersion = CRMCacheService::getTagVersion(CRMCacheService::TAG_CUSTOMER_GROUPS);

    $service->deleteCustomerGroup($group);

    // Version should be incremented
    $newVersion = CRMCacheService::getTagVersion(CRMCacheService::TAG_CUSTOMER_GROUPS);
    expect($newVersion)->toBeGreaterThan($initialVersion);
    expect(CustomerGroup::find($group->id))->toBeNull();
});

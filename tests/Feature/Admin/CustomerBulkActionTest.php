<?php

use App\Models\Customer;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware();

    $permissions = [
        'view_customers',
        'delete_customers',
        'restore_customers',
        'force_delete_customers',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }
});

it('bulk deletes customers', function () {
    $customers = Customer::factory()->count(3)->create();
    $ids = $customers->pluck('id')->toArray();

    actingAsAdmin(['delete_customers'])
        ->post(route('admin.customers.bulk-action'), [
            'ids' => $ids,
            'action' => 'delete',
        ])
        ->assertRedirect();

    foreach ($ids as $id) {
        $this->assertSoftDeleted('customers', ['id' => $id]);
    }
});

it('bulk restores trashed customers', function () {
    $customers = Customer::factory()->count(2)->create();
    $ids = $customers->pluck('id')->toArray();
    Customer::whereIn('id', $ids)->delete();

    actingAsAdmin(['restore_customers'])
        ->post(route('admin.customers.bulk-action'), [
            'ids' => $ids,
            'action' => 'restore',
        ])
        ->assertRedirect();

    foreach ($ids as $id) {
        $this->assertDatabaseHas('customers', ['id' => $id, 'deleted_at' => null]);
    }
});

it('bulk force deletes trashed customers', function () {
    $customers = Customer::factory()->count(2)->create();
    $ids = $customers->pluck('id')->toArray();
    Customer::whereIn('id', $ids)->delete();

    actingAsAdmin(['force_delete_customers'])
        ->post(route('admin.customers.bulk-action'), [
            'ids' => $ids,
            'action' => 'forceDelete',
        ])
        ->assertRedirect();

    foreach ($ids as $id) {
        $this->assertDatabaseMissing('customers', ['id' => $id]);
    }
});

it('denies bulk delete without permission', function () {
    $noPermUser = User::factory()->create();
    $customer = Customer::factory()->create();

    $this->actingAs($noPermUser)
        ->post(route('admin.customers.bulk-action'), [
            'ids' => [$customer->id],
            'action' => 'delete',
        ])
        ->assertForbidden();
});

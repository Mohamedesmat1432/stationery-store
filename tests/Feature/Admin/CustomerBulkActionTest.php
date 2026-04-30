<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([
        'view_customers',
        'delete_customers',
        'restore_customers',
        'force_delete_customers',
    ]);
});

it('bulk deletes customers', function () {
    $customers = Customer::factory()->count(3)->create();
    $ids = $customers->pluck('id')->toArray();

    $this->actingAs($this->admin)
        ->post(route('admin.customers.bulk-action'), [
            'ids' => $ids,
            'action' => 'delete',
        ])
        ->assertRedirect();

    foreach ($ids as $id) {
        $this->assertSoftDeleted('customers', ['id' => $id]);
    }
})->skip(fn () => ! extension_loaded('pdo_sqlite'), 'SQLite not available in this environment');

it('bulk restores trashed customers', function () {
    $customers = Customer::factory()->count(2)->create();
    $ids = $customers->pluck('id')->toArray();
    Customer::whereIn('id', $ids)->delete();

    $this->actingAs($this->admin)
        ->post(route('admin.customers.bulk-action'), [
            'ids' => $ids,
            'action' => 'restore',
        ])
        ->assertRedirect();

    foreach ($ids as $id) {
        $this->assertDatabaseHas('customers', ['id' => $id, 'deleted_at' => null]);
    }
})->skip(fn () => ! extension_loaded('pdo_sqlite'), 'SQLite not available in this environment');

it('bulk force deletes trashed customers', function () {
    $customers = Customer::factory()->count(2)->create();
    $ids = $customers->pluck('id')->toArray();
    Customer::whereIn('id', $ids)->delete();

    $this->actingAs($this->admin)
        ->post(route('admin.customers.bulk-action'), [
            'ids' => $ids,
            'action' => 'forceDelete',
        ])
        ->assertRedirect();

    foreach ($ids as $id) {
        $this->assertDatabaseMissing('customers', ['id' => $id]);
    }
})->skip(fn () => ! extension_loaded('pdo_sqlite'), 'SQLite not available in this environment');

it('denies bulk delete without permission', function () {
    $noPermUser = User::factory()->create();
    $customer = Customer::factory()->create();

    $this->actingAs($noPermUser)
        ->post(route('admin.customers.bulk-action'), [
            'ids' => [$customer->id],
            'action' => 'delete',
        ])
        ->assertForbidden();
})->skip(fn () => ! extension_loaded('pdo_sqlite'), 'SQLite not available in this environment');

<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Modules\Identity\Data\UserData;
use Modules\Identity\Services\UserService;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => Role::ROLE_ADMIN, 'guard_name' => 'web']);
});

test('admin user is protected from deletion', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::ROLE_ADMIN);

    $user = User::factory()->create();
    $this->actingAs($user);

    $userService = app(UserService::class);

    // Should throw ValidationException
    $userService->deleteUser($admin);
})->throws(ValidationException::class);

test('user cannot delete themselves', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $userService = app(UserService::class);

    // Should throw ValidationException
    $userService->deleteUser($user);
})->throws(ValidationException::class);

test('user data dto correctly identifies protected status', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::ROLE_ADMIN);

    $user = User::factory()->create();

    $this->actingAs($admin);

    $adminData = UserData::fromUser($admin);
    $userData = UserData::fromUser($user);

    expect($adminData->is_protected)->toBeTrue();
    expect($userData->is_protected)->toBeFalse();

    // Switch to non-admin user
    $this->actingAs($user);

    $adminData = UserData::fromUser($admin);
    expect($adminData->is_protected)->toBeTrue();
});

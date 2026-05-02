<?php

namespace Modules\Identity\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Modules\Identity\Enums\PermissionName;
use Modules\Shared\Services\Cache\BaseCacheService;

class IdentityCacheService extends BaseCacheService
{
    private const PREFIX_USER = 'user';

    private const PREFIX_ROLE = 'role';

    private const PREFIX_PERMISSION = 'permission';

    private const TAG_USERS = 'users';

    private const TAG_ROLES = 'roles';

    private const TAG_PERMISSIONS = 'permissions';

    // ========== USER CACHE METHODS ==========

    public static function getUserPermissions(string $userId): array
    {
        return Cache::tags([self::TAG_USERS, self::TAG_PERMISSIONS])
            ->remember(
                self::key(self::PREFIX_USER, 'permissions', $userId),
                self::TTL_LONG,
                fn () => User::find($userId)?->getAllPermissions()->pluck('name')->toArray() ?? []
            );
    }

    public static function getUserRoles(string $userId): array
    {
        return Cache::tags([self::TAG_USERS, self::TAG_ROLES])
            ->remember(
                self::key(self::PREFIX_USER, 'roles', $userId),
                self::TTL_LONG,
                fn () => User::find($userId)?->getRoleNames()->toArray() ?? []
            );
    }

    public static function userHasPermission(string $userId, string $permission): bool
    {
        return in_array($permission, self::getUserPermissions($userId));
    }

    public static function userIsAdmin(string $userId): bool
    {
        return in_array(Role::ROLE_ADMIN, self::getUserRoles($userId));
    }

    public static function flushUserCaches(): void
    {
        Cache::tags([self::TAG_USERS])->flush();
    }

    public static function flushUserCache(string $userId): void
    {
        Cache::forget(self::key(self::PREFIX_USER, 'permissions', $userId));
        Cache::forget(self::key(self::PREFIX_USER, 'roles', $userId));
    }

    // ========== ROLE CACHE METHODS ==========

    public static function getAvailableRoles(): array
    {
        return Cache::tags([self::TAG_ROLES])
            ->remember(
                self::key(self::PREFIX_ROLE, 'available_list'),
                self::TTL_MEDIUM,
                fn () => Role::pluck('name')->toArray()
            );
    }

    public static function getRolePermissions(string $roleId): array
    {
        return Cache::tags([self::TAG_ROLES, self::TAG_PERMISSIONS])
            ->remember(
                self::key(self::PREFIX_ROLE, 'permissions', $roleId),
                self::TTL_LONG,
                fn () => Role::with('permissions')->find($roleId)?->permissions->pluck('name')->toArray() ?? []
            );
    }

    public static function getAllRolesWithPermissions(): array
    {
        return Cache::tags([self::TAG_ROLES, self::TAG_PERMISSIONS])
            ->remember(
                self::key(self::PREFIX_ROLE, 'all_with_permissions'),
                self::TTL_MEDIUM,
                function () {
                    return Role::with('permissions')
                        ->orderBy('name')
                        ->get()
                        ->map(fn ($role) => [
                            'id' => $role->id,
                            'name' => $role->name,
                            'permissions' => $role->permissions->pluck('name')->toArray(),
                        ])
                        ->toArray();
                }
            );
    }

    public static function flushRoleCaches(): void
    {
        Cache::tags([self::TAG_ROLES])->flush();
    }

    // ========== PERMISSION CACHE METHODS ==========

    public static function getAvailablePermissions(): array
    {
        return Cache::tags([self::TAG_PERMISSIONS])
            ->remember(
                self::key(self::PREFIX_PERMISSION, 'available_list'),
                self::TTL_MEDIUM,
                fn () => PermissionName::values()
            );
    }

    public static function flushPermissionCaches(): void
    {
        Cache::tags([self::TAG_PERMISSIONS])->flush();
    }
}

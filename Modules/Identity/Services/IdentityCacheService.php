<?php

namespace Modules\Identity\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Modules\Identity\Data\RoleData;
use Modules\Identity\Data\UserData;
use Modules\Identity\Enums\PermissionName;
use Modules\Identity\Enums\RoleName;
use Modules\Identity\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Identity\Repositories\Contracts\UserRepositoryInterface;
use Modules\Shared\Services\Cache\BaseCacheService;
use Spatie\Permission\PermissionRegistrar;

class IdentityCacheService extends BaseCacheService
{
    public const TAG_USERS = 'users';

    public const TAG_ROLES = 'roles';

    public const TAG_PERMISSIONS = 'permissions';

    // ========== USER CACHE METHODS ==========

    public static function getUserPermissions(string $userId): array
    {
        return self::rememberDirect(
            self::TAG_USERS,
            "user_permissions:{$userId}",
            fn () => resolve(UserRepositoryInterface::class)->getPermissions($userId)
        );
    }

    public static function getUserRoles(string $userId): array
    {
        return self::rememberDirect(
            self::TAG_USERS,
            "user_roles:{$userId}",
            fn () => resolve(UserRepositoryInterface::class)->getRoles($userId)
        );
    }

    public static function userHasPermission(string $userId, string $permission): bool
    {
        return in_array($permission, self::getUserPermissions($userId));
    }

    public static function userIsAdmin(string $userId): bool
    {
        return in_array(RoleName::ADMIN->value, self::getUserRoles($userId));
    }

    public static function rememberUsers(array $params, int $perPage, callable $callback, ?callable $transform = null): array
    {
        return self::rememberPaginated(
            self::TAG_USERS,
            $params,
            $perPage,
            $callback,
            $transform ?? fn ($collection) => UserData::collect($collection)
        );
    }

    /**
     * Flush all user-related caches.
     */
    public static function flushUserCaches(): void
    {
        self::flushTagsWithFallbacks([self::TAG_USERS]);
    }

    /**
     * Flush cache for a specific user.
     * Also flushes the paginated user list since the user data changed.
     */
    public static function flushUserCache(string $userId): void
    {
        // Flush specific user keys
        Cache::forget(self::getVersionedKey(self::TAG_USERS, "direct:user_permissions:{$userId}"));
        Cache::forget(self::getVersionedKey(self::TAG_USERS, "direct:user_roles:{$userId}"));
        Cache::forget(self::getVersionedKey(self::TAG_USERS, "direct:available_for_customer:{$userId}"));

        // Also flush paginated lists since user data may affect filters
        self::flushUserCaches();
    }

    public static function getAvailableForCustomer(?string $includeUserId = null): array
    {
        $key = 'available_for_customer:'.($includeUserId ?? 'none');

        return self::rememberDirect(
            self::TAG_USERS,
            $key,
            fn () => resolve(UserRepositoryInterface::class)->getAvailableForCustomer($includeUserId),
            fn ($collection) => UserData::collect($collection)->toArray()
        );
    }

    // ========== ROLE CACHE METHODS ==========

    public static function getAvailableRoles(): array
    {
        return self::rememberDirect(
            self::TAG_ROLES,
            'available_roles',
            fn () => resolve(RoleRepositoryInterface::class)->getAvailableNames()
        );
    }

    public static function getRolePermissions(string $roleId): array
    {
        return self::rememberDirect(
            self::TAG_ROLES,
            "role_permissions:{$roleId}",
            fn () => resolve(RoleRepositoryInterface::class)->getPermissions($roleId)
        );
    }

    /**
     * Flush all role-related caches and Spatie internal cache.
     */
    public static function flushRoleCaches(): void
    {
        self::flushTagsWithFallbacks([self::TAG_ROLES, self::TAG_USERS]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public static function rememberRoles(array $params, int $perPage, callable $callback, ?callable $transform = null): array
    {
        return self::rememberPaginated(
            self::TAG_ROLES,
            $params,
            $perPage,
            $callback,
            $transform ?? fn ($collection) => RoleData::collect($collection)
        );
    }

    // ========== PERMISSION CACHE METHODS ==========

    public static function getAvailablePermissions(): array
    {
        return self::rememberDirect(
            self::TAG_PERMISSIONS,
            'available_permissions_grouped',
            function (): array {
                $permissions = PermissionName::values();
                $grouped = [];

                foreach ($permissions as $permission) {
                    $parts = explode('_', $permission);
                    $action = $parts[0];
                    $module = count($parts) > 2 && $action === 'force' && $parts[1] === 'delete'
                        ? implode('_', array_slice($parts, 2))
                        : implode('_', array_slice($parts, 1));

                    $grouped[$module] ??= [];
                    $grouped[$module][] = $permission;
                }

                ksort($grouped);

                return $grouped;
            }
        );
    }

    /**
     * Flush all permission-related caches.
     */
    public static function flushPermissionCaches(): void
    {
        self::flushTagsWithFallbacks([self::TAG_PERMISSIONS, self::TAG_ROLES, self::TAG_USERS]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

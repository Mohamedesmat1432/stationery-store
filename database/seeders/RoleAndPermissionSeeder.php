<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Modules\Identity\Enums\PermissionName;
use Modules\Identity\Enums\RoleName;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions from Enum
        foreach (PermissionName::values() as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles and assign created permissions
        $adminRole = Role::firstOrCreate(['name' => RoleName::ADMIN->value]);
        $adminRole->givePermissionTo(Permission::all());

        $managerRole = Role::firstOrCreate(['name' => RoleName::MANAGER->value]);
        $managerRole->givePermissionTo([
            // CRM
            PermissionName::VIEW_CUSTOMERS->value,

            // Catalog
            PermissionName::VIEW_PRODUCTS->value,
            PermissionName::CREATE_PRODUCTS->value,
            PermissionName::UPDATE_PRODUCTS->value,
            PermissionName::VIEW_CATEGORIES->value,
            PermissionName::CREATE_CATEGORIES->value,
            PermissionName::UPDATE_CATEGORIES->value,
            PermissionName::DELETE_CATEGORIES->value,
            PermissionName::RESTORE_CATEGORIES->value,
            PermissionName::FORCE_DELETE_CATEGORIES->value,
            PermissionName::EXPORT_CATEGORIES->value,
            PermissionName::IMPORT_CATEGORIES->value,
            PermissionName::VIEW_BRANDS->value,
            PermissionName::VIEW_ATTRIBUTES->value,
            PermissionName::VIEW_TAGS->value,

            // Inventory
            PermissionName::VIEW_INVENTORY->value,
            PermissionName::UPDATE_INVENTORY->value,
            PermissionName::VIEW_WAREHOUSES->value,
            PermissionName::VIEW_UNITS->value,

            // Orders
            PermissionName::VIEW_ORDERS->value,
            PermissionName::UPDATE_ORDERS->value,
            PermissionName::VIEW_SHIPMENTS->value,
            PermissionName::CREATE_SHIPMENTS->value,
            PermissionName::UPDATE_SHIPMENTS->value,
            PermissionName::VIEW_REFUNDS->value,

            // Marketing
            PermissionName::VIEW_DISCOUNTS->value,

            // Engagement
            PermissionName::VIEW_REVIEWS->value,
            PermissionName::UPDATE_REVIEWS->value,

            // Analytics
            PermissionName::VIEW_REPORTS->value,
        ]);

        Role::firstOrCreate(['name' => RoleName::EDITOR->value]);

        Role::firstOrCreate(['name' => RoleName::CUSTOMER->value]);
    }
}

<?php

namespace App\Enums;

enum PermissionName: string
{
    // ============================================
    // 1. Access Control (Users, Roles, Permissions)
    // ============================================
    case VIEW_USERS = 'view_users';
    case CREATE_USERS = 'create_users';
    case UPDATE_USERS = 'update_users';
    case DELETE_USERS = 'delete_users';
    case RESTORE_USERS = 'restore_users';
    case FORCE_DELETE_USERS = 'force_delete_users';
    case EXPORT_USERS = 'export_users';
    case IMPORT_USERS = 'import_users';

    case VIEW_ROLES = 'view_roles';
    case CREATE_ROLES = 'create_roles';
    case UPDATE_ROLES = 'update_roles';
    case DELETE_ROLES = 'delete_roles';

    case VIEW_PERMISSIONS = 'view_permissions';
    case CREATE_PERMISSIONS = 'create_permissions';
    case UPDATE_PERMISSIONS = 'update_permissions';
    case DELETE_PERMISSIONS = 'delete_permissions';

    // ============================================
    // 2. CRM (Customers, Customer Groups)
    // ============================================
    case VIEW_CUSTOMERS = 'view_customers';
    case CREATE_CUSTOMERS = 'create_customers';
    case UPDATE_CUSTOMERS = 'update_customers';
    case DELETE_CUSTOMERS = 'delete_customers';
    case RESTORE_CUSTOMERS = 'restore_customers';
    case FORCE_DELETE_CUSTOMERS = 'force_delete_customers';
    case EXPORT_CUSTOMERS = 'export_customers';
    case IMPORT_CUSTOMERS = 'import_customers';

    case VIEW_CUSTOMER_GROUPS = 'view_customer_groups';
    case CREATE_CUSTOMER_GROUPS = 'create_customer_groups';
    case UPDATE_CUSTOMER_GROUPS = 'update_customer_groups';
    case DELETE_CUSTOMER_GROUPS = 'delete_customer_groups';
    case RESTORE_CUSTOMER_GROUPS = 'restore_customer_groups';
    case FORCE_DELETE_CUSTOMER_GROUPS = 'force_delete_customer_groups';

    // ============================================
    // 3. Catalog (Products, Categories, Brands, Attributes, Tags)
    // ============================================
    case VIEW_PRODUCTS = 'view_products';
    case CREATE_PRODUCTS = 'create_products';
    case UPDATE_PRODUCTS = 'update_products';
    case DELETE_PRODUCTS = 'delete_products';

    case VIEW_CATEGORIES = 'view_categories';
    case CREATE_CATEGORIES = 'create_categories';
    case UPDATE_CATEGORIES = 'update_categories';
    case DELETE_CATEGORIES = 'delete_categories';

    case VIEW_BRANDS = 'view_brands';
    case CREATE_BRANDS = 'create_brands';
    case UPDATE_BRANDS = 'update_brands';
    case DELETE_BRANDS = 'delete_brands';

    case VIEW_ATTRIBUTES = 'view_attributes';
    case CREATE_ATTRIBUTES = 'create_attributes';
    case UPDATE_ATTRIBUTES = 'update_attributes';
    case DELETE_ATTRIBUTES = 'delete_attributes';

    case VIEW_TAGS = 'view_tags';
    case CREATE_TAGS = 'create_tags';
    case UPDATE_TAGS = 'update_tags';
    case DELETE_TAGS = 'delete_tags';

    // ============================================
    // 4. Inventory & Logistics (Warehouses, Stock, Units)
    // ============================================
    case VIEW_WAREHOUSES = 'view_warehouses';
    case CREATE_WAREHOUSES = 'create_warehouses';
    case UPDATE_WAREHOUSES = 'update_warehouses';
    case DELETE_WAREHOUSES = 'delete_warehouses';

    case VIEW_INVENTORY = 'view_inventory';
    case UPDATE_INVENTORY = 'update_inventory'; // Covers stock movements

    case VIEW_UNITS = 'view_units';
    case CREATE_UNITS = 'create_units';
    case UPDATE_UNITS = 'update_units';
    case DELETE_UNITS = 'delete_units';

    // ============================================
    // 5. Sales & Orders (Orders, Carts)
    // ============================================
    case VIEW_ORDERS = 'view_orders';
    case CREATE_ORDERS = 'create_orders'; // E.g., manual order creation from admin
    case UPDATE_ORDERS = 'update_orders';
    case CANCEL_ORDERS = 'cancel_orders';
    case DELETE_ORDERS = 'delete_orders';

    case VIEW_CARTS = 'view_carts'; // Support agents viewing active customer carts

    // ============================================
    // 6. Fulfillment (Shipments, Shipping Methods, Zones)
    // ============================================
    case VIEW_SHIPMENTS = 'view_shipments';
    case CREATE_SHIPMENTS = 'create_shipments';
    case UPDATE_SHIPMENTS = 'update_shipments';
    case DELETE_SHIPMENTS = 'delete_shipments';

    case VIEW_SHIPPING_METHODS = 'view_shipping_methods';
    case CREATE_SHIPPING_METHODS = 'create_shipping_methods';
    case UPDATE_SHIPPING_METHODS = 'update_shipping_methods';
    case DELETE_SHIPPING_METHODS = 'delete_shipping_methods';

    case VIEW_SHIPPING_ZONES = 'view_shipping_zones';
    case CREATE_SHIPPING_ZONES = 'create_shipping_zones';
    case UPDATE_SHIPPING_ZONES = 'update_shipping_zones';
    case DELETE_SHIPPING_ZONES = 'delete_shipping_zones';

    // ============================================
    // 7. Payments & Finance (Payments, Refunds, Taxes, Currencies)
    // ============================================
    case VIEW_PAYMENTS = 'view_payments';
    case UPDATE_PAYMENTS = 'update_payments';

    case VIEW_PAYMENT_METHODS = 'view_payment_methods';
    case CREATE_PAYMENT_METHODS = 'create_payment_methods';
    case UPDATE_PAYMENT_METHODS = 'update_payment_methods';
    case DELETE_PAYMENT_METHODS = 'delete_payment_methods';

    case VIEW_REFUNDS = 'view_refunds';
    case CREATE_REFUNDS = 'create_refunds';
    case UPDATE_REFUNDS = 'update_refunds';

    case VIEW_TAXES = 'view_taxes';
    case CREATE_TAXES = 'create_taxes';
    case UPDATE_TAXES = 'update_taxes';
    case DELETE_TAXES = 'delete_taxes';

    case VIEW_CURRENCIES = 'view_currencies';
    case CREATE_CURRENCIES = 'create_currencies';
    case UPDATE_CURRENCIES = 'update_currencies';
    case DELETE_CURRENCIES = 'delete_currencies';

    // ============================================
    // 8. Marketing & Promotions (Discounts)
    // ============================================
    case VIEW_DISCOUNTS = 'view_discounts';
    case CREATE_DISCOUNTS = 'create_discounts';
    case UPDATE_DISCOUNTS = 'update_discounts';
    case DELETE_DISCOUNTS = 'delete_discounts';

    // ============================================
    // 9. User Engagement (Reviews, Wishlists)
    // ============================================
    case VIEW_REVIEWS = 'view_reviews';
    case UPDATE_REVIEWS = 'update_reviews'; // Approve/reject
    case DELETE_REVIEWS = 'delete_reviews';

    case VIEW_WISHLISTS = 'view_wishlists'; // Analytics purposes

    // ============================================
    // 10. System, Data & Reports (Settings, Imports, Logs)
    // ============================================
    case VIEW_SETTINGS = 'view_settings';
    case UPDATE_SETTINGS = 'update_settings';

    case VIEW_IMPORTS = 'view_imports';
    case CREATE_IMPORTS = 'create_imports'; // Triggering bulk CSV imports

    case VIEW_REPORTS = 'view_reports';
    case VIEW_AUDIT_LOGS = 'view_audit_logs';

    /**
     * Get all permission names as an array of strings.
     * Useful for seeders.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

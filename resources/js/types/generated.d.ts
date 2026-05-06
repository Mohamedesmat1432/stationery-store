declare namespace App {
    namespace Data {
        export type CartData = {
            id: string;
            subtotal: number;
            grand_total: number;
            items_count: number;
        };
        export type OrderData = {
            id: string;
            order_number: string;
            status: string;
            grand_total: number;
            created_at: string;
        };
    }
    namespace Enums {
        export type CartStatus =
            | 'active'
            | 'converted'
            | 'abandoned'
            | 'expired';
        export type DiscountType =
            | 'percentage'
            | 'fixed_amount'
            | 'buy_x_get_y'
            | 'free_shipping';
        export type FulfillmentStatus = 'unfulfilled' | 'partial' | 'fulfilled';
        export type ImportStatus =
            | 'pending'
            | 'processing'
            | 'completed'
            | 'failed'
            | 'partial';
        export type InventoryPolicy = 'deny' | 'continue' | 'backorder';
        export type OrderStatus =
            | 'pending'
            | 'processing'
            | 'shipped'
            | 'delivered'
            | 'cancelled'
            | 'refunded';
        export type PaymentStatus =
            | 'pending'
            | 'paid'
            | 'failed'
            | 'refunded'
            | 'partially_refunded';
        export type PriceType = 'base' | 'sale' | 'wholesale' | 'b2b';
        export type RefundStatus =
            | 'pending'
            | 'completed'
            | 'failed'
            | 'cancelled';
        export type ShipmentStatus =
            | 'pending'
            | 'shipped'
            | 'delivered'
            | 'returned'
            | 'cancelled';
        export type StockMovementType =
            | 'purchase'
            | 'sale'
            | 'adjustment'
            | 'transfer_in'
            | 'transfer_out'
            | 'return_from_customer'
            | 'return_to_vendor'
            | 'waste'
            | 'damage';
    }
}
declare namespace Modules {
    namespace CRM {
        namespace Data {
            export type CustomerData = {
                is_protected: boolean;
                id: string | null;
                user_id: string | null;
                name: string | null;
                email: string | null;
                phone: string | null;
                birth_date: string | null;
                gender: Modules.CRM.Enums.Gender | null;
                tax_number: string | null;
                company_name: string | null;
                customer_group_id: string | null;
                group_name: string | null;
                metadata: Record<string, any> | any;
                deleted_at: string | null;
                total_spent: number;
                orders_count: number;
                age: number | null;
            };
            export type CustomerGroupData = {
                is_protected: boolean;
                id: string | null;
                name: string;
                slug: string;
                description: string | null;
                discount_percentage: number;
                is_active: boolean;
                sort_order: number;
                customers_count: number;
                deleted_at: string | null;
            };
            export type ExportCustomerGroupsData = {
                columns: string[];
                format: string;
            };
            export type ExportCustomersData = {
                columns: string[];
                format: string;
            };
            export type ImportCustomerGroupsData = {
                file: string;
            };
            export type ImportCustomersData = {
                file: string;
            };
        }
        namespace Enums {
            export type Gender = 'male' | 'female' | 'other';
        }
    }
    namespace Catalog {
        namespace Data {
            export type BrandData = {
                id: string | null;
                name: string;
                slug: string;
                description: string | null;
                website: string | null;
                is_active: boolean;
                sort_order: number;
                logo: any;
                logo_url: string | null;
                deleted_at: string | null;
                products_count: number | null;
            };
            export type CategoryData = {
                full_path: string | null;
                parent_name: string | null;
                breadcrumbs: Array<any> | null;
                total_product_count: number;
                children: Modules.Catalog.Data.CategoryData[] | null;
                id: string | null;
                name: string;
                slug: string;
                description: string | null;
                parent_id: string | null;
                is_active: boolean;
                is_featured: boolean;
                sort_order: number;
                icon: any;
                banner_image: any;
                meta_title: string | null;
                meta_description: string | null;
                meta_keywords: string | null;
                deleted_at: string | null;
            };
            export type ExportBrandsData = {
                columns: string[];
                format: string;
            };
            export type ExportCategoriesData = {
                columns: string[];
                format: string;
            };
            export type ExportProductsData = {
                columns: string[];
                format: string;
            };
            export type ImportBrandsData = {
                file: string;
            };
            export type ImportCategoriesData = {
                file: string;
            };
            export type ImportProductsData = {
                file: string;
            };
            export type ProductData = {
                id: string | null;
                name: string;
                sku: string;
                slug: string | null;
                description: string | null;
                short_description: string | null;
                price: number;
                compare_at_price: number | null;
                cost_price: number | null;
                is_active: boolean;
                is_featured: boolean;
                is_digital: boolean;
                is_taxable: boolean;
                barcode: string | null;
                weight: number | null;
                dimensions: Array<any> | null;
                inventory_policy: string;
                category_id: string | null;
                brand_id: string | null;
                meta_title: string | null;
                meta_description: string | null;
                meta_keywords: string | null;
                featured_image: string | null;
                deleted_at: string | null;
                category: Modules.Catalog.Data.CategoryData | null;
                brand: Modules.Catalog.Data.BrandData | null;
            };
        }
    }
    namespace Identity {
        namespace Data {
            export type ExportUsersData = {
                columns: string[];
                format: string;
            };
            export type ImportUsersData = {
                file: string;
            };
            export type RoleData = {
                is_protected: boolean;
                id: string | null;
                name: string;
                permissions: string[];
            };
            export type UserData = {
                is_protected: boolean;
                id: string | null;
                name: string;
                email: string;
                password: string | null;
                roles: string[];
                deleted_at: string | null;
            };
        }
        namespace Enums {
            export type PermissionName =
                | 'view_users'
                | 'create_users'
                | 'update_users'
                | 'delete_users'
                | 'restore_users'
                | 'force_delete_users'
                | 'export_users'
                | 'import_users'
                | 'view_roles'
                | 'create_roles'
                | 'update_roles'
                | 'delete_roles'
                | 'view_permissions'
                | 'create_permissions'
                | 'update_permissions'
                | 'delete_permissions'
                | 'view_customers'
                | 'create_customers'
                | 'update_customers'
                | 'delete_customers'
                | 'restore_customers'
                | 'force_delete_customers'
                | 'export_customers'
                | 'import_customers'
                | 'view_customer_groups'
                | 'create_customer_groups'
                | 'update_customer_groups'
                | 'delete_customer_groups'
                | 'restore_customer_groups'
                | 'force_delete_customer_groups'
                | 'export_customer_groups'
                | 'import_customer_groups'
                | 'view_products'
                | 'create_products'
                | 'update_products'
                | 'delete_products'
                | 'restore_products'
                | 'force_delete_products'
                | 'view_categories'
                | 'create_categories'
                | 'update_categories'
                | 'delete_categories'
                | 'restore_categories'
                | 'force_delete_categories'
                | 'export_categories'
                | 'import_categories'
                | 'view_brands'
                | 'create_brands'
                | 'update_brands'
                | 'delete_brands'
                | 'restore_brands'
                | 'force_delete_brands'
                | 'view_attributes'
                | 'create_attributes'
                | 'update_attributes'
                | 'delete_attributes'
                | 'view_tags'
                | 'create_tags'
                | 'update_tags'
                | 'delete_tags'
                | 'view_warehouses'
                | 'create_warehouses'
                | 'update_warehouses'
                | 'delete_warehouses'
                | 'view_inventory'
                | 'update_inventory'
                | 'view_units'
                | 'create_units'
                | 'update_units'
                | 'delete_units'
                | 'view_orders'
                | 'create_orders'
                | 'update_orders'
                | 'cancel_orders'
                | 'delete_orders'
                | 'view_carts'
                | 'view_shipments'
                | 'create_shipments'
                | 'update_shipments'
                | 'delete_shipments'
                | 'view_shipping_methods'
                | 'create_shipping_methods'
                | 'update_shipping_methods'
                | 'delete_shipping_methods'
                | 'view_shipping_zones'
                | 'create_shipping_zones'
                | 'update_shipping_zones'
                | 'delete_shipping_zones'
                | 'view_payments'
                | 'update_payments'
                | 'view_payment_methods'
                | 'create_payment_methods'
                | 'update_payment_methods'
                | 'delete_payment_methods'
                | 'view_refunds'
                | 'create_refunds'
                | 'update_refunds'
                | 'view_taxes'
                | 'create_taxes'
                | 'update_taxes'
                | 'delete_taxes'
                | 'view_currencies'
                | 'create_currencies'
                | 'update_currencies'
                | 'delete_currencies'
                | 'view_discounts'
                | 'create_discounts'
                | 'update_discounts'
                | 'delete_discounts'
                | 'view_reviews'
                | 'update_reviews'
                | 'delete_reviews'
                | 'view_wishlists'
                | 'view_settings'
                | 'update_settings'
                | 'view_imports'
                | 'create_imports'
                | 'view_reports'
                | 'view_audit_logs';
            export type RoleName = 'admin' | 'customer' | 'manager' | 'editor';
        }
    }
    namespace Shared {
        namespace Enums {
            export type BulkActionType = 'delete' | 'restore' | 'forceDelete';
        }
    }
}

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
        export type ProductData = {
            id: string | null;
            name: string;
            sku: string;
            slug: string | null;
            description: string | null;
            price: number;
            is_active: boolean;
            category_id: string | null;
            brand_id: string | null;
            featured_image: string | null;
            deleted_at: string | null;
        };
        export type UserData = {
            id: string;
            name: string;
            email: string;
            phone: string | null;
            avatar_url: string | null;
            locale: string;
            is_active: boolean;
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
    }
}

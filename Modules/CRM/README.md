# CRM Module

The CRM (Customer Relationship Management) module manages customers and customer groups.

## Architecture

### Services
- `CustomerService`: Handles customer profiles and integration with the Identity module (User).
- `CustomerGroupService`: Manages segmentation of customers for marketing and pricing.
- `CRMCacheService`: Manages caching for customer data and lists.

### Repositories
- `CustomerRepository`: Implements `ProtectsBulkResources` and handles filtering by groups and spending.
- `CustomerGroupRepository`: Standard repository for group management.

### Data (DTOs)
- `CustomerData`: Strongly typed DTO for customer profiles.
- `CustomerGroupData`: DTO for customer group information.

## Protection Logic
- **Customers**: Protected from deletion if they have active orders or significant lifetime value history.
- **Customer Groups**: Protected if they contain active customers.

## Cache Synchronization
The `SyncCRMCache` listener handles invalidation of customer-related caches upon resource modification.

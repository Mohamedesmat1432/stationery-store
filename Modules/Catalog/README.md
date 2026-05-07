# Catalog Module

The Catalog module handles products, categories, brands, attributes, and tags. It follows the Service-Repository-Data pattern for all core entities.

## Architecture

### Services
- `ProductService`: Manages product lifecycle, media, and variants.
- `CategoryService`: Handles recursive category trees and product counts.
- `BrandService`: Manages brand information and logos.
- `CatalogCacheService`: Centralizes cache keys and TTLs for catalog data.

### Repositories
- `ProductRepository`: Implements `ProtectsBulkResources` and handles complex eager loading for index views.
- `CategoryRepository`: Handles tree-based loading and recursive counting.
- `BrandRepository`: Standard repository for brand data.

### Data (DTOs)
- `ProductData`: Synchronizes backend products with frontend TypeScript contracts.
- `CategoryData`: Handles recursive tree hydration.
- `BrandData`: Standard DTO for brand entities.

## Protection Logic
Critical resources are protected from deletion if they have active dependencies:
- **Products**: Protected if they appear in non-cancelled orders or wishlists.
- **Categories**: Protected if they or their subcategories contain active products.
- **Brands**: Protected if they have active products assigned.

## Cache Synchronization
The `SyncCatalogCache` listener ensures that catalog caches are invalidated whenever a relevant resource is created, updated, or deleted. It listens for the `ResourceChanged` event.

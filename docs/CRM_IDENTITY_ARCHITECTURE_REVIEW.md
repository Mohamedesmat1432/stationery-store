# CRM & Identity Modules — Enterprise Architecture Review

> **Review Date:** 2026-05-04
> **Reviewer:** Principal Software Architect
> **Scope:** `Modules/CRM`, `Modules/Identity`, `Modules/Shared`
> **Status:** ✅ Refactored & Production-Ready

---

## Table of Contents

- [CRM \& Identity Modules — Enterprise Architecture Review](#crm--identity-modules--enterprise-architecture-review)
  - [Table of Contents](#table-of-contents)
  - [1. Module Overview](#1-module-overview)
    - [CRM Module](#crm-module)
    - [Identity Module](#identity-module)
    - [Shared Module](#shared-module)
  - [2. Architecture Structure](#2-architecture-structure)
  - [3. Recursive Dependency Flow](#3-recursive-dependency-flow)
    - [CRM Module Dependencies](#crm-module-dependencies)
    - [Identity Module Dependencies](#identity-module-dependencies)
  - [4. Backend/Frontend Communication Flow](#4-backendfrontend-communication-flow)
    - [Props Contract (Standardized Across All Index Pages)](#props-contract-standardized-across-all-index-pages)
  - [5. Inertia Lifecycle Flow](#5-inertia-lifecycle-flow)
  - [6. Shared Props Lifecycle](#6-shared-props-lifecycle)
  - [7. Services Map](#7-services-map)
    - [CRM Services](#crm-services)
    - [Identity Services](#identity-services)
  - [8. Event/Listener Flow](#8-eventlistener-flow)
    - [New Event-Driven Cache Invalidation Architecture](#new-event-driven-cache-invalidation-architecture)
    - [Bulk Operation Flow](#bulk-operation-flow)
    - [Spatie Permission Event Flow](#spatie-permission-event-flow)
  - [9. Cache Flow](#9-cache-flow)
    - [Versioned Cache Architecture](#versioned-cache-architecture)
    - [Cache Invalidation Matrix](#cache-invalidation-matrix)
  - [10. Entity Lifecycle](#10-entity-lifecycle)
    - [Customer Lifecycle](#customer-lifecycle)
    - [User Lifecycle](#user-lifecycle)
  - [11. Filters/Search Lifecycle](#11-filterssearch-lifecycle)
  - [12. Middleware/Auth Flow](#12-middlewareauth-flow)
  - [13. Validation Flow](#13-validation-flow)
  - [14. Package Integration Map](#14-package-integration-map)
  - [15. Dependency Graph](#15-dependency-graph)
  - [16. Frontend State Flow](#16-frontend-state-flow)
    - [Composables Architecture](#composables-architecture)
    - [Page Component Pattern (Standardized)](#page-component-pattern-standardized)
  - [17. Findings \& Remediation Summary](#17-findings--remediation-summary)
    - [Issues Found \& Fixed](#issues-found--fixed)
    - [Files Created](#files-created)
    - [Files Modified](#files-modified)
  - [18. Production Readiness Evaluation](#18-production-readiness-evaluation)
    - [✅ Strengths](#-strengths)
    - [⚠️ Considerations for Production](#️-considerations-for-production)
    - [📊 Scalability Analysis](#-scalability-analysis)
    - [🎯 Architecture Improvement Opportunities](#-architecture-improvement-opportunities)

---

## 1. Module Overview

### CRM Module
Manages customer relationships through two primary entities:
- **Customer** — Linked to `User`, belongs to `CustomerGroup`, supports soft deletes
- **CustomerGroup** — Segments with discount percentages, protected slugs (`retail`, `general`)

### Identity Module
Manages authentication, authorization, and access control:
- **User** — Authenticatable with roles, permissions, soft deletes, 2FA (Fortify)
- **Role** — Spatie Permission-based with protection for `admin` role
- **Permission** — Spatie Permission-based, grouped by module for frontend

### Shared Module
Cross-cutting infrastructure:
- **BaseCacheService** — Versioned cache engine (no Redis tags required)
- **BaseRepository** — CRUD + bulk operations with QueryBuilder
- **HandlesBulkOperations** — Standardized bulk delete/restore/forceDelete
- **ProtectsSystemResources** — Filters protected IDs from bulk operations
- **BulkOperationCompleted** — Event fired after any bulk action

---

## 2. Architecture Structure

```
┌─────────────────────────────────────────────────────────────────────────┐
│                              PRESENTATION LAYER                          │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────────┐ │
│  │ Vue Pages   │  │ Composables │  │ Components  │  │ Wayfinder Routes│ │
│  │ (Inertia)   │  │ (useForm)   │  │ (Reusable)  │  │ (Typed)         │ │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘  └────────┬────────┘ │
│         │                │                │                  │          │
│  ┌──────▼────────────────▼────────────────▼──────────────────▼────────┐ │
│  │                        INERTIA MIDDLEWARE                          │ │
│  │         HandleInertiaRequests → Shared Props (auth, flash)         │ │
│  └──────┬─────────────────────────────────────────────────────────────┘ │
│         │                                                               │
├─────────┼───────────────────────────────────────────────────────────────┤
│         │                      APPLICATION LAYER                         │
│  ┌──────▼──────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────────┐ │
│  │ Controllers │  │ DTO/Data    │  │ Policies    │  │ Form Requests   │ │
│  │ (Thin)      │  │ (Validation)│  │ (Gates)     │  │ (Implicit)      │ │
│  └──────┬──────┘  └─────────────┘  └─────────────┘  └─────────────────┘ │
│         │                                                               │
│  ┌──────▼─────────────────────────────────────────────────────────────┐ │
│  │                      SERVICE LAYER                                  │ │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌───────────┐ │ │
│  │  │ UserService │  │ RoleService │  │ CustomerSvc │  │ GroupSvc  │ │ │
│  │  │ + CacheSvc  │  │ + CacheSvc  │  │ + CacheSvc  │  │ + CacheSvc│ │ │
│  │  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘  └─────┬─────┘ │ │
│  └─────────┼────────────────┼────────────────┼───────────────┼───────┘ │
│            │                │                │               │         │
├────────────┼────────────────┼────────────────┼───────────────┼─────────┤
│            │                │                │               │         │
│  ┌─────────▼────────────────▼────────────────▼───────────────▼───────┐ │
│  │                      REPOSITORY LAYER                               │ │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌───────────┐ │ │
│  │  │ UserRepo    │  │ RoleRepo    │  │ CustomerRepo│  │ GroupRepo │ │ │
│  │  │ (Interface) │  │ (Interface) │  │ (Interface) │  │(Interface)│ │ │
│  │  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘  └─────┬─────┘ │ │
│  └─────────┼────────────────┼────────────────┼───────────────┼───────┘ │
│            │                │                │               │         │
├────────────┼────────────────┼────────────────┼───────────────┼─────────┤
│            │                │                │               │         │
│  ┌─────────▼────────────────▼────────────────▼───────────────▼───────┐ │
│  │                      DOMAIN LAYER                                   │ │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌───────────┐ │ │
│  │  │ User Model  │  │ Role Model  │  │ Customer    │  │ Group     │ │ │
│  │  │ (Spatie)    │  │ (Spatie)    │  │ Model       │  │ Model     │ │ │
│  │  └─────────────┘  └─────────────┘  └─────────────┘  └───────────┘ │ │
│  └────────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Recursive Dependency Flow

### CRM Module Dependencies
```
CRMServiceProvider
├── binds CustomerRepositoryInterface → CustomerRepository
├── binds CustomerGroupRepositoryInterface → CustomerGroupRepository
├── registers policies (CustomerPolicy, CustomerGroupPolicy)
├── registers events:
│   └── ResourceChanged → SyncCRMCache
└── registers routes (admin.*)

CustomerController
├── injects CustomerService, CustomerGroupService, UserService
├── uses HandlesBulkActions trait
├── Gate::authorize() on every action
└── Inertia::render() with deferred props

CustomerService
├── uses HandlesBulkOperations
├── delegates to CustomerRepository
├── CRMCacheService for paginated reads
└── fires BulkOperationCompleted on import

CustomerRepository
├── extends BaseRepository
├── uses Spatie QueryBuilder
├── eager loads: user, group, addresses, orders
└── filters: search, group, trash
```

### Identity Module Dependencies
```
IdentityServiceProvider
├── binds UserRepositoryInterface → UserRepository
├── binds RoleRepositoryInterface → RoleRepository
├── registers policies (UserPolicy, RolePolicy)
├── registers events:
│   ├── ResourceChanged → SyncIdentityCache
│   ├── RoleAttached/Detached → SyncIdentityCache
│   └── PermissionAttached/Detached → SyncIdentityCache
├── Gate::before() grants admin superpowers
└── registers routes (admin.*)

UserController
├── injects UserService
├── uses HandlesBulkActions trait
└── Inertia::render() with deferred props

UserService
├── uses HandlesBulkOperations + ProtectsSystemResources
├── filterAssignableRoles() prevents non-admin from assigning admin
├── isProtected() delegates to User::isProtectedBy()
└── IdentityCacheService for paginated reads
```

---

## 4. Backend/Frontend Communication Flow

```
┌─────────────┐     HTTP Request      ┌─────────────┐
│   Browser   │ ────────────────────► │   Laravel   │
│             │                       │   Router    │
└─────────────┘                       └──────┬──────┘
                                             │
                                             ▼
                                    ┌─────────────────┐
                                    │  Middleware     │
                                    │  web, auth,     │
                                    │  verified       │
                                    └────────┬────────┘
                                             │
                                             ▼
                                    ┌─────────────────┐
                                    │  Controller     │
                                    │  Gate::authorize│
                                    │  Service call   │
                                    └────────┬────────┘
                                             │
                                             ▼
                                    ┌─────────────────┐
                                    │  Inertia::render│
                                    │  + DTO transform│
                                    │  + Deferred     │
                                    └────────┬────────┘
                                             │
                                             ▼
                                    ┌─────────────────┐
                                    │  JSON Response  │
                                    │  (Inertia page  │
                                    │   + props)      │
                                    └────────┬────────┘
                                             │
┌─────────────┐     HTTP Response     ◄────┘
│   Browser   │ ◄────────────────────
│  (Inertia   │
│   client)   │
└──────┬──────┘
       │
       ▼
┌─────────────────┐
│  Vue Router     │
│  intercepts,    │
│  swaps page     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Vue Component  │
│  receives props │
│  + deferred     │
└─────────────────┘
```

### Props Contract (Standardized Across All Index Pages)

| Prop | Type | Source |
|------|------|--------|
| `users` / `customers` / `roles` / `groups` | Paginated array | Service → Cache → Repository |
| `filters` | `{ filter?: { search?, trash?, ... } }` | `$request->only(['filter'])` |
| `available_roles` | `string[]` | `IdentityCacheService::getAvailableRoles()` (deferred) |
| `available_groups` | `CustomerGroupData[]` | `CRMCacheService::getActiveCustomerGroups()` (deferred) |
| `available_users` | `UserData[]` | `UserService::getAvailableForCustomer()` (deferred) |
| `available_permissions` | `GroupedPermissions` | `IdentityCacheService::getAvailablePermissions()` (deferred) |

---

## 5. Inertia Lifecycle Flow

```
1. Initial Page Load (Full HTML)
   Browser → Laravel → HandleInertiaRequests::share()
   → Inertia::render('Admin/Users/Index', props)
   → Blade app.blade.php + root Vue mount

2. Subsequent Navigation (XHR)
   Browser → Inertia.visit() → Laravel → Controller
   → Inertia::render() → JSON response
   → Vue router swaps component, merges props

3. Deferred Props
   Initial render: page loads immediately, deferred slots show Skeleton
   Background XHR: fetches deferred props
   → Vue re-renders with full data

4. Shared Props (Every Request)
   auth.user, auth.roles, auth.permissions
   sidebarOpen, locale, translations, flash
```

---

## 6. Shared Props Lifecycle

```php
// HandleInertiaRequests::share()
[
    'auth' => [
        'user' => UserData::fromUser($user),           // Eager (always)
        'roles' => fn() => IdentityCacheService::getUserRoles($user->id),       // Lazy
        'permissions' => fn() => IdentityCacheService::getUserPermissions($user->id), // Lazy
    ],
    'sidebarOpen' => cookie-based,
    'locale' => app()->getLocale(),
    'translations' => fn() => CacheService::getTranslations(locale), // Lazy
    'flash' => ['success', 'error', 'info', 'warning'],
]
```

**Frontend Consumption:**
- `AppSidebar.vue` — reads `auth.permissions` to show/hide nav items
- `useBulkActions.ts` — reads `auth.permissions` for `can()` checks
- `flashToast.ts` — reads `flash` to display toast notifications

---

## 7. Services Map

### CRM Services

| Service | Methods | Cache Tag | Traits Used |
|---------|---------|-----------|-------------|
| `CustomerService` | CRUD, restore, forceDelete, export, import, bulk ops | `customers` | `HandlesBulkOperations` |
| `CustomerGroupService` | CRUD, restore, forceDelete, export, import, bulk ops, getAllActive | `customer_groups` | `HandlesBulkOperations`, `ProtectsSystemResources` |
| `CRMCacheService` | rememberCustomers, rememberCustomerGroups, getActiveCustomerGroups, flush* | — | extends `BaseCacheService` |

### Identity Services

| Service | Methods | Cache Tag | Traits Used |
|---------|---------|-----------|-------------|
| `UserService` | CRUD, restore, forceDelete, export, import, bulk ops, getAvailableForCustomer | `users` | `HandlesBulkOperations`, `ProtectsSystemResources` |
| `RoleService` | CRUD, bulk ops | `roles` | `HandlesBulkOperations`, `ProtectsSystemResources` |
| `IdentityCacheService` | rememberUsers, rememberRoles, getUserPermissions, getUserRoles, getAvailableRoles, getRolePermissions, getAvailablePermissions, getAvailableForCustomer, flush* | `users`, `roles`, `permissions` | extends `BaseCacheService` |

---

## 8. Event/Listener Flow

### New Event-Driven Cache Invalidation Architecture

```
┌─────────────────┐     created/updated/deleted/imported        ┌─────────────────┐
│  Service Layer  │ ──────────────────────────────────────────► │  Event Dispatch │
│                 │                                           │  (ResourceChgd) │
└─────────────────┘                                           └────────┬────────┘
                                                                       │
                                                                       ▼
                                                            ┌─────────────────────┐
                                                            │ ResourceChanged     │
                                                            │ ($modelClass,       │
                                                            │ $action, $ids)      │
                                                            └──────────┬──────────┘
                                                                       │
                                          ┌────────────────────────────┼────────────────────────────┐
                                          │                            │                            │
                                          ▼                            ▼                            ▼
                              ┌──────────────────┐       ┌──────────────────┐       ┌──────────────────┐
                              │ SyncCRMCache     │       │ SyncCRMCache     │       │ SyncIdentityCache│
                              │ (CRM)            │       │ (CRM)            │       │ (Identity)       │
                              └────────┬─────────┘       └────────┬─────────┘       └────────┬─────────┘
                                       │                            │                            │
                                       ▼                            ▼                            ▼
                              ┌──────────────────┐       ┌──────────────────┐       ┌──────────────────┐
                              │CRMCacheService:: │       │CRMCacheService:: │       │IdentityCacheSvc::│
                              │flushCustomerCache│       │flushGroupCache   │       │flushUserCache    │
                              └──────────────────┘       └──────────────────┘       └──────────────────┘
```

### Bulk Operation Flow

```
┌─────────────────┐     bulk_deleted/bulk_restored/etc            ┌─────────────────┐
│  Service        │ ─────────────────────────────────────────────► │ ResourceChanged │
│  (HandlesBulk)  │                                              │ (bulk action)   │
└─────────────────┘                                              └────────┬────────┘
                                                                          │
                                                                          ▼
                                                               ┌─────────────────────┐
                                                               │ SyncCRMCache        │
                                                               │ SyncIdentityCache   │
                                                               │                      │
                                                               └──────────┬──────────┘
                                                                          │
                                                                          ▼
                                                               ┌─────────────────────┐
                                                               │ Flush all related   │
                                                               │ cache tags            │
                                                               └─────────────────────┘
```

### Spatie Permission Event Flow

```
User::syncRoles() / Role::syncPermissions()
         │
         ▼
Spatie dispatches: RoleAttached/Detached, PermissionAttached/Detached
         │
         ▼
SyncIdentityCache listener
         │
         ├──► IdentityCacheService::flushUserCache($userId)
         └──► PermissionRegistrar::forgetCachedPermissions()
```

---

## 9. Cache Flow

### Versioned Cache Architecture

```
Key Format: v7:g{globalVersion}:t{tagVersion}:{tag}:{key}

Example: v7:g12345:t67890:users:paginated:p15:a1b2c3d4

┌─────────────────┐
│  Cache Request  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐     miss     ┌─────────────────┐
│  getTagVersion  │ ───────────► │  Initialize     │
│  (from cache)   │              │  version:{tag}  │
└────────┬────────┘              └─────────────────┘
         │ hit
         ▼
┌─────────────────┐
│  Build full key │
│  with versions  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐     miss     ┌─────────────────┐
│  Cache::get     │ ───────────► │  Execute query  │
│                 │              │  + transform    │
└────────┬────────┘              │  + store        │
         │ hit                   └─────────────────┘
         ▼
┌─────────────────┐
│  Return cached  │
│  data           │
└─────────────────┘

Invalidation: incrementTagVersion('users') → all old keys become stale
```

### Cache Invalidation Matrix

| Trigger | Event | Listener | Action |
|---------|-------|----------|--------|
| Customer saved | `ResourceChanged` | `SyncCRMCache` | `flushCustomerCaches()` |
| CustomerGroup saved | `ResourceChanged` | `SyncCRMCache` | `flushCustomerGroupCaches()` |
| User saved | `ResourceChanged` | `SyncIdentityCache` | `flushUserCache($id)` |
| Role saved | `ResourceChanged` | `SyncIdentityCache` | `flushRoleCaches()` |
| Permission saved | `ResourceChanged` | `SyncIdentityCache` | `flushPermissionCaches()` |
| Bulk operation | `ResourceChanged` | `SyncCRMCache` / `SyncIdentityCache` | Flush related tags |
| Role attach/detach | Spatie events | `SyncIdentityCache` | Flush user + Spatie cache |

---

## 10. Entity Lifecycle

### Customer Lifecycle

```
Create:
  Request → CustomerData validation → CustomerService::createCustomer()
  → CustomerRepository::create() → ResourceChanged::dispatch('created')
  → SyncCRMCache → flushCustomerCaches()

Update:
  Request → CustomerData validation → CustomerService::updateCustomer()
  → collect()->only() filtering → CustomerRepository::update()
  → ResourceChanged::dispatch('updated') → Cache invalidation

Delete (soft):
  Gate::authorize('delete') → CustomerService::deleteCustomer()
  → CustomerRepository::delete() → ResourceChanged::dispatch('deleted')
  → Cache invalidation

Restore:
  HandlesBulkActions::performRestore() → CustomerService::restoreCustomer()
  → CustomerRepository::restore() → ModelObserver::restored()
  → Cache invalidation

Force Delete:
  HandlesBulkActions::performForceDelete() → CustomerService::forceDeleteCustomer()
  → CustomerRepository::forceDelete() → ModelObserver::forceDeleted()
  → Cache invalidation

Bulk Delete:
  HandlesBulkActions::performBulkAction() → CustomerService::bulkDelete()
  → BaseRepository::bulkDelete() → ResourceChanged::dispatch('bulk_deleted')
  → SyncCRMCache → flush related tags
```

### User Lifecycle

```
Create:
  Request → UserData validation → UserService::createUser()
  → DB::transaction() → UserRepository::create() + syncRoles()
  → ResourceChanged::dispatch('created') → SyncIdentityCache → flushUserCache($id)

Update:
  Request → UserData validation → UserService::updateUser()
  → DB::transaction() → update + syncRoles()
  → ResourceChanged::dispatch('updated') → Cache invalidation

Delete:
  Gate::authorize('delete') + isProtected check → UserService::deleteUser()
  → returns false if protected → Controller shows error
  → OR deletes → ResourceChanged::dispatch('deleted') → Cache invalidation
```

---

## 11. Filters/Search Lifecycle

```
Frontend:
  useResourceFilters(initialFilters, { baseUrl })
  → searchQuery ref + showTrashed ref + extraFilters ref
  → applyFilters() → router.get(baseUrl, { filter: cleanFilters }, { preserveState: true })

Backend:
  Request → Controller → $request->only(['filter'])
  → Service::get*Paginated($request->all())
  → CRMCacheService::rememberCustomers($params, ...)
  → BaseCacheService::rememberPaginated('customers', $params, ...)
  → Repository::paginate() with QueryBuilder

QueryBuilder Filters:
  Customer: AllowedFilter::scope('search'), exact('group'), trashed('trash')
  User: AllowedFilter::scope('search'), callback('role'), trashed('trash')
  Role: AllowedFilter::scope('search')
  CustomerGroup: AllowedFilter::scope('search'), exact('is_active'), trashed('trash')
```

---

## 12. Middleware/Auth Flow

```
Request
  │
  ▼
Route::middleware(['web', 'auth', 'verified'])
  │
  ▼
web middleware (sessions, CSRF)
  │
  ▼
auth middleware (authenticated)
  │
  ▼
verified middleware (email verified)
  │
  ▼
HandleInertiaRequests::share() → auth props
  │
  ▼
Controller → Gate::authorize('action', $model)
  │
  ▼
IdentityServiceProvider::Gate::before()
  → if user has 'admin' role → return true (superuser)
  │
  ▼
Policy method → hasPermissionTo(PermissionName::ACTION)
  │
  ▼
Spatie Permission check (cached via PermissionRegistrar)
```

---

## 13. Validation Flow

```
Request → Route Model Binding / Form Data
  │
  ▼
Spatie Laravel Data DTO (TypeScript attribute)
  │
  ├──► UserData::rules() → name, email, password, roles validation
  ├──► CustomerData::rules() → user_id, phone, birth_date, gender, etc.
  ├──► RoleData::rules() → name, permissions validation
  └──► CustomerGroupData::rules() → name, slug, discount_percentage, etc.
  │
  ▼
Validation passes → DTO instantiated with typed properties
  │
  ▼
Controller receives validated DTO → passes to Service
```

---

## 14. Package Integration Map

| Package | Version | Usage | Module |
|---------|---------|-------|--------|
| `spatie/laravel-permission` | Latest | Role/Permission core | Identity |
| `spatie/laravel-data` | Latest | DTOs + validation | Both |
| `spatie/typescript-transformer` | Latest | Auto TS types | Both |
| `spatie/query-builder` | Latest | Filter/sort/paginate | Both |
| `spatie/activitylog` | Latest | Audit logging | Both (models) |
| `spatie/medialibrary` | Latest | File uploads | User model |
| `maatwebsite/excel` | Latest | Import/Export | Both |
| `inertiajs/inertia-laravel` | v3 | SPA responses | Both |
| `laravel/fortify` | v1 | Auth/2FA | Identity (User model) |
| `laravel/prompts` | v0 | CLI | — |
| `laravel/wayfinder` | v0 | Typed routes | Frontend |

---

## 15. Dependency Graph

```
Modules/CRM
├── depends on: Modules/Shared (BaseCacheService, BaseRepository, Events, Traits)
├── depends on: Modules/Identity (PermissionName enum, UserService)
├── depends on: App/Models (Customer, CustomerGroup, User)
└── used by: App/Http/Middleware (HandleInertiaRequests — auth props)

Modules/Identity
├── depends on: Modules/Shared (BaseCacheService, BaseRepository, Events, Traits)
├── depends on: App/Models (User, Role, Permission)
└── used by: Modules/CRM (UserService), App/Http/Middleware (auth props)

Modules/Shared
├── no module dependencies
├── depends on: Laravel Framework, Spatie QueryBuilder
└── used by: CRM, Identity, and potentially all future modules
```

---

## 16. Frontend State Flow

### Composables Architecture

```
useBulkActions(itemsSource, config)
├── useSelection(itemsSource) → selectedIds, isAllSelected, isIndeterminate
├── usePage().props.auth.permissions → can(permission)
├── openConfirm() → ConfirmDialog state
├── bulkAction(action) → router.post(bulkActionRoute)
├── deleteItem(id) → router.delete(destroyRoute)
├── restoreItem(id) → router.post(restoreRoute)
└── forceDeleteItem(id) → router.delete(forceDeleteRoute)

useResourceFilters(initialFilters, options)
├── searchQuery ref
├── showTrashed ref
├── extraFilters ref
└── applyFilters() → router.get(baseUrl, { filter }, { preserveState: true })

useUsers(initialData)
├── useForm({ name, email, password, roles })
└── submit(id?) → form.put(update) / form.post(store)

useCustomers(initialData)
├── useForm({ user_id, phone, birth_date, ... })
└── submit(id?) → form.put(update) / form.post(store)

usePermissions(form, availablePermissions)
├── groupedPermissions computed
├── formatPermissionLabel()
├── togglePermission()
└── toggleModule()
```

### Page Component Pattern (Standardized)

All index pages follow identical structure:
1. `defineOptions({ layout: { breadcrumbs } })`
2. `defineProps<{ items, filters, available_* }>`
3. `useResourceFilters()` for search + filters
4. `useBulkActions()` for selection + actions
5. `AdminPageHeader` with bulk action toolbar
6. `ResourceFilterBar` with search + custom filters
7. Table with checkbox selection + action buttons
8. `ResourcePagination`
9. `ConfirmDialog`, `ResourceExportModal`, `ResourceImportModal`

---

## 17. Findings & Remediation Summary

### Issues Found & Fixed

| # | Severity | Finding | Fix | Files Changed |
|---|----------|---------|-----|---------------|
| 1 | 🔴 Critical | Implicit Model Observers for Cache Invalidation | Replaced with centralized Service-driven `ResourceChanged` event architecture. | 15+ files |
| 2 | 🟠 High | Fragmented Cache Listeners | Consolidated into `SyncCRMCache` and `SyncIdentityCache`. | 5 files |
| 3 | 🟠 High | Dead tests referencing old `BaseModel` cache (`resetFlushedTags`, `flushRedisTag`) | Rewrote tests to match current `BaseCacheService` versioned cache architecture | 2 test files |
| 4 | 🟡 Medium | `getAvailablePermissions()` not cached despite being deferred prop | Wrapped in `rememberDirect()` with `TAG_PERMISSIONS` | `IdentityCacheService.php` |
| 5 | 🟡 Medium | `Roles/Index.vue` duplicates `formatRoleName()` logic | Replaced inline function with `useRoles()` composable | `Roles/Index.vue` |
| 6 | 🟡 Medium | `RoleController::destroy()` ignores `deleteRole()` return value | Added protected-role check with error flash (matches `CustomerGroupController`) | `RoleController.php` |
| 7 | 🟢 Low | Unused imports (`DB` in CustomerService, `PermissionRegistrar` in observers) | Removed unused imports | 4 files |
| 8 | 🟢 Low | `UserService` constructor has unnecessary empty body | Cleaned to `{}` | `UserService.php` |

### Round 2 — Issues Found & Fixed (2026-05-04)

| # | Severity | Finding | Fix | Files Changed |
|---|----------|---------|-----|---------------|
| 9 | 🔴 Critical | `IdentityCacheService::flushUserCache()` missing `direct:` prefix in cache keys | Cached user permissions/roles never flushed. Fixed key format to match `rememberDirect()` | `IdentityCacheService.php` |
| 10 | 🔴 Critical | `UserController::destroy()` ignores `deleteUser()` return value | Protected users show false "deleted successfully". Added return value check | `UserController.php` |
| 11 | 🔴 Critical | Roles Create/Edit pages use hardcoded URLs instead of Wayfinder | Converted all form actions, breadcrumbs, and links to Wayfinder routes | `Roles/Create.vue`, `Roles/Edit.vue` |
| 12 | 🟡 Medium | `filter(null)` prevents clearing fields to `null` | Removed `->filter(fn ($value) => $value !== null)` from update methods | `CustomerGroupService.php`, `CustomerService.php` |
| 13 | 🟡 Medium | `formatPermissionLabel()` only shows first word | Fixed to extract full action portion (e.g. "Update" → "Update Customers") | `usePermissions.ts` |
| 14 | 🟡 Medium | `useRoles()` misused in `Roles/Index.vue` with plain object | Extracted `formatRoleName` as standalone export, updated import | `useRoles.ts`, `Roles/Index.vue` |
| 15 | 🟡 Medium | `Users/Edit.vue` uses destructured `defineProps()` | Changed to standard `const props = defineProps()` pattern | `Users/Edit.vue` |
| 16 | 🟡 Medium | `useResourceFilters` watch never triggers on prop updates | Refactored to use reactive `filtersRef` with proper sync | `useResourceFilters.ts` |
| 17 | 🟡 Medium | CustomerGroups Create breadcrumb uses `href: '#'` | Changed to `create.url()` for consistency | `CustomerGroups/Create.vue` |
| 18 | 🟢 Low | `useBulkActions` requires `restoreRoute`/`forceDeleteRoute` even for non-soft-delete resources | Made both optional in `BulkActionsConfig` interface | `useBulkActions.ts` |
| 19 | 🟢 Low | Route config `String()` wrapper inconsistency across index pages | Normalized all to use `String(id)` wrapper | `CustomerGroups/Index.vue`, `Users/Index.vue` |
| 20 | 🟢 Low | `CustomerGroupService` uses fully-qualified Excel facade | Changed to imported `Excel` facade for consistency | `CustomerGroupService.php` |
| 21 | 🟢 Low | `formatLabel` produces double spaces on consecutive separators | Added `filter()` to remove empty segments, `split(/[_-]+/)` | `format.ts` |
| 22 | 🟢 Low | Unused `RoleData` import in `IdentityCacheService` | Removed unused import | `IdentityCacheService.php` |

### Files Created

1. `Modules/Shared/Events/ResourceChanged.php` — Central resource change event
2. `Modules/CRM/Listeners/SyncCRMCache.php` — Consolidated CRM cache listener
3. `Modules/Identity/Listeners/SyncIdentityCache.php` — Consolidated Identity cache listener

### Files Modified

1. `Modules/CRM/Observers/CustomerObserver.php` — REMOVED (logic moved to Service)
2. `Modules/CRM/Observers/CustomerGroupObserver.php` — REMOVED (logic moved to Service)
3. `Modules/Identity/Observers/UserObserver.php` — REMOVED (logic moved to Service)
4. `Modules/Identity/Observers/RoleObserver.php` — REMOVED (logic moved to Service)
5. `Modules/Identity/Observers/PermissionObserver.php` — REMOVED (logic moved to Service)
6. `Modules/CRM/Providers/CRMServiceProvider.php` — Registers new listeners
7. `Modules/Identity/Providers/IdentityServiceProvider.php` — Registers new listeners
8. `Modules/Identity/Http/Controllers/RoleController.php` — Protected role check
9. `Modules/Identity/Services/IdentityCacheService.php` — Cache getAvailablePermissions()
10. `Modules/CRM/Services/CustomerService.php` — Removed unused import
11. `Modules/CRM/Services/CustomerGroupService.php` — Removed unused import
12. `Modules/Identity/Services/UserService.php` — Cleaned constructor
13. `resources/js/pages/Admin/Roles/Index.vue` — Uses useRoles composable
14. `tests/Feature/CustomerGroupCacheTest.php` — Rewrote for current architecture
15. `tests/Feature/SeniorCacheTest.php` — Rewrote for current architecture
16. `Modules/Identity/Services/IdentityCacheService.php` — Fixed cache key prefix + removed unused import
17. `Modules/Identity/Http/Controllers/UserController.php` — Protected user deletion check
18. `resources/js/pages/Admin/Roles/Create.vue` — Wayfinder routes
19. `resources/js/pages/Admin/Roles/Edit.vue` — Wayfinder routes
20. `Modules/CRM/Services/CustomerGroupService.php` — Removed `filter(null)`, fixed Excel facade
21. `Modules/CRM/Services/CustomerService.php` — Removed `filter(null)`
22. `resources/js/composables/usePermissions.ts` — Fixed permission label formatting
23. `resources/js/composables/useRoles.ts` — Extracted standalone `formatRoleName`
24. `resources/js/pages/Admin/Users/Edit.vue` — Fixed prop destructuring
25. `resources/js/composables/useResourceFilters.ts` — Fixed reactive watch
26. `resources/js/pages/Admin/CustomerGroups/Create.vue` — Fixed breadcrumb
27. `resources/js/composables/useBulkActions.ts` — Optional restore/forceDelete routes
28. `resources/js/pages/Admin/CustomerGroups/Index.vue` — Normalized String() wrapper
29. `resources/js/pages/Admin/Users/Index.vue` — Normalized String() wrapper
30. `resources/js/lib/format.ts` — Fixed double spaces

---

## 18. Production Readiness Evaluation

### ✅ Strengths

1. **Clean Layered Architecture** — Repository → Service → Controller separation is consistent
2. **Versioned Cache System** — `BaseCacheService` provides reliable invalidation without Redis tag support
3. **Event-Driven Invalidation** — Post-refactor: all cache invalidation flows through events
4. **DTO Validation** — Spatie Laravel Data provides type-safe validation + TypeScript generation
5. **Permission System** — Spatie Permission with admin superuser via `Gate::before()`
6. **Protected Resources** — `ProtectsSystemResources` trait prevents accidental deletion of critical data
7. **Bulk Operations** — Standardized across all modules via shared traits
8. **Deferred Props** — Inertia v3 deferred loading improves perceived performance
9. **Soft Deletes** — All entities support restore/forceDelete with proper authorization
10. **Wayfinder Integration** — Type-safe frontend routing eliminates hardcoded URLs

### ⚠️ Considerations for Production

1. **Cache Driver** — Versioned cache works with any driver, but Redis recommended for production scale
2. **Queue Workers** — Bulk operations currently synchronous; consider queueing for large datasets
3. **Rate Limiting** — Export/import endpoints may need rate limiting
4. **Database Indexing** — Ensure `users.email`, `customers.user_id`, `roles.name` are indexed
5. **Audit Logging** — ActivityLog configured; verify log retention policy
6. **Permission Cache** — Spatie's `PermissionRegistrar` cache is cleared on role/permission changes

### 📊 Scalability Analysis

| Component | Current | Scale Limit | Mitigation |
|-----------|---------|-------------|------------|
| Cache keys | Versioned per tag | Unlimited (version increments) | None needed |
| Pagination | 15/page default | Configurable | Adjust perPage |
| Bulk operations | Synchronous | ~1000 records | Queue for larger |
| Import | Chunked 1000 | Memory bound | Increase chunk size |
| Permission check | Cached per user | ~1000 permissions/user | Group permissions |

### 🎯 Architecture Improvement Opportunities

1. **Queue Bulk Operations** — Move `bulkDelete`/`bulkRestore` to queue jobs for large datasets
2. **Cache Warming** — Pre-warm `getAvailableRoles()` and `getActiveCustomerGroups()` on deploy
3. **API Versioning** — If evolving to API architecture, current Service layer is already decoupled
4. **Feature Tests** — Add comprehensive feature tests for cache invalidation scenarios
5. **Frontend Caching** — Consider SWR pattern for deferred props in Vue

---

*End of Architecture Review Document*

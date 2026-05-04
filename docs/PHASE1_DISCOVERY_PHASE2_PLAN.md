# Phase 1: Discovery Findings + Phase 2: Remediation Plan

## Executive Summary

Full recursive audit of CRM (Customers, CustomerGroups) and Identity (Users, Roles, Permissions) modules completed. **24 new issues found** across backend PHP and frontend Vue/TS. 6 issues were already fixed in a prior refactoring pass.

---

## Already Fixed (Prior Refactoring)

| # | Issue | Fix |
|---|-------|-----|
| 1 | Direct cache flushing in observers | Event-driven: observers dispatch `CacheInvalidationRequested` |
| 2 | `RoleController::destroy()` ignored return value | Now checks `$deleted`, returns error if false |
| 3 | `getAvailablePermissions()` uncached | Now cached via `rememberDirect()` |
| 4 | Inline `formatRoleName` in Roles/Index.vue | Uses shared `useRoles` composable |
| 5 | Unused imports | Cleaned up |
| 6 | Dead tests | Rewrote with proper mock expectations |

---

## New Findings (24 Issues)

### 🔴 HIGH SEVERITY (5 issues)

| ID | File | Issue | Impact |
|----|------|-------|--------|
| **H1** | `IdentityCacheService.php:81-83` | **Missing `direct:` prefix in cache key invalidation** — `flushUserCache()` forgets keys without the `direct:` prefix that `rememberDirect()` adds. Cached user permissions/roles are NEVER flushed on user save. | Stale permissions/roles persist after user updates |
| **H2** | `UserController.php:108` | **Ignores `deleteUser()` return value** — If user is protected, still shows "deleted successfully". Inconsistent with `RoleController` which checks return value. | False success message on protected user deletion |
| **H3** | `Roles/Create.vue:48` | **Hardcoded form POST URL** — `form.post('/admin/roles')` instead of Wayfinder `store.url()` | Breaks if route changes; loses type safety |
| **H4** | `Roles/Edit.vue:56` | **Hardcoded form PUT URL** — `form.put(\`/admin/roles/${props.role.id}\`)` instead of Wayfinder | Same as H3 |
| **H5** | `Roles/Edit.vue:74` | **Hardcoded router.delete URL** — `router.delete(\`/admin/roles/${props.role.id}\`)` instead of Wayfinder | Same as H3 |

### 🟡 MEDIUM SEVERITY (10 issues)

| ID | File | Issue | Impact |
|----|------|-------|--------|
| **M1** | `CustomerGroupService.php:76` | `filter(fn ($value) => $value !== null)` prevents clearing fields to `null` (e.g. description) | Cannot clear optional fields |
| **M2** | `CustomerService.php:59` | Same `filter(null)` issue as M1 | Cannot clear optional fields |
| **M3** | `usePermissions.ts:22-30` | `formatPermissionLabel()` only uses `parts[0]` — shows "Update" instead of "Update Customers" | Permission labels are misleading |
| **M4** | `Roles/Index.vue:49` | `useRoles({ roles: [] })` passes plain object instead of `InertiaForm` — type mismatch | Runtime works but type-unsafe |
| **M5** | `Users/Edit.vue:19-23` | Destructured `defineProps()` with `[key: string]: unknown` — loses reactivity, unusual pattern | Potential reactivity bugs |
| **M6** | `useResourceFilters.ts:46-55` | `watch(() => initialFilters, ...)` watches parameter reference, never triggers on prop updates | Filter state may desync |
| **M7** | `Roles/Create.vue:24-25` | Breadcrumbs use hardcoded URLs | Inconsistent with other pages |
| **M8** | `Roles/Edit.vue:26` | Breadcrumb uses hardcoded URL | Inconsistent |
| **M9** | `CustomerGroups/Create.vue` | Create breadcrumb uses `href: '#'` instead of `create.url()` | Inconsistent with Users/Customers |
| **M10** | `IdentityCacheService.php:8` | Unused import `RoleData` | Dead code |

### 🟢 LOW SEVERITY (9 issues)

| ID | File | Issue | Impact |
|----|------|-------|--------|
| **L1** | `Roles/Create.vue:2` | `Deferred` imported but unused | Dead import |
| **L2** | `Roles/Edit.vue:2` | `Deferred` imported but unused | Dead import |
| **L3** | `CustomerGroups/Index.vue` | `restoreRoute`/`forceDeleteRoute` missing `String()` wrapper (inconsistent with Customers) | Minor inconsistency |
| **L4** | `Users/Index.vue` | Same `String()` wrapper inconsistency | Minor |
| **L5** | `Roles/Index.vue:68-70` | `destroyRoute` uses different pattern `{ url: ... }` vs direct route function | Minor inconsistency |
| **L6** | `All Index.vue` | `/dashboard` breadcrumb hardcoded | Low — unlikely to change |
| **L7** | `CustomerGroupService.php:124` | Excel facade uses `\Maatwebsite\Excel\Facades\Excel` instead of imported `Excel` | Inconsistent style |
| **L8** | `format.ts` | `formatLabel('user__admin')` produces double spaces | Minor formatting |
| **L9** | `RoleController.php` | No PHPDoc blocks (inconsistent with `UserController`) | Documentation gap |

---

## Phase 2: Remediation Plan

### Batch 1: Critical Bug Fixes (H1-H5)

**H1: Fix cache key prefix in `IdentityCacheService::flushUserCache()`**
- Change `"user_permissions:{$userId}"` → `"direct:user_permissions:{$userId}"`
- Change `"user_roles:{$userId}"` → `"direct:user_roles:{$userId}"`
- Change `"available_for_customer:{$userId}"` → `"direct:available_for_customer:{$userId}"`
- Files: `Modules/Identity/Services/IdentityCacheService.php`

**H2: Check `deleteUser()` return value in `UserController::destroy()`**
- Same pattern as `RoleController::destroy()` (already fixed there)
- Files: `Modules/Identity/Http/Controllers/UserController.php`

**H3-H5: Convert Roles Create/Edit to Wayfinder routes**
- Import `* as rolesRoutes from '@/routes/admin/roles/index'`
- Replace all hardcoded URLs with `rolesRoutes.*.url()` calls
- Files: `resources/js/pages/Admin/Roles/Create.vue`, `resources/js/pages/Admin/Roles/Edit.vue`

### Batch 2: Medium Fixes (M1-M10)

**M1-M2: Remove `filter(null)` from update methods**
- Remove `->filter(fn ($value) => $value !== null)` from both services
- Files: `Modules/CRM/Services/CustomerGroupService.php`, `Modules/CRM/Services/CustomerService.php`

**M3: Fix `formatPermissionLabel()` to show full action name**
- Change `parts[0]` to extract action portion properly (drop entity suffix)
- File: `resources/js/composables/usePermissions.ts`

**M4: Extract `formatRoleName` to standalone utility**
- Create `formatRoleName()` in `format.ts` or make `useRoles` accept optional form
- File: `resources/js/composables/useRoles.ts` + `resources/js/lib/format.ts`

**M5: Fix `Users/Edit.vue` prop destructuring**
- Change to `const props = defineProps<...>()` pattern
- File: `resources/js/pages/Admin/Users/Edit.vue`

**M6: Fix `useResourceFilters` watch**
- Watch `props.filters` reactive reference instead of parameter
- File: `resources/js/composables/useResourceFilters.ts`

**M7-M9: Fix breadcrumb inconsistencies**
- Roles Create/Edit: use Wayfinder routes
- CustomerGroups Create: use `create.url()`
- Files: `Roles/Create.vue`, `Roles/Edit.vue`, `CustomerGroups/Create.vue`

**M10: Remove unused import**
- Remove `use Modules\Identity\Data\RoleData;`
- File: `Modules/Identity/Services/IdentityCacheService.php`

### Batch 3: Low Fixes (L1-L9)

- Remove unused `Deferred` imports (L1, L2)
- Normalize `String()` wrappers in route configs (L3, L4, L5)
- Fix Excel facade import in `CustomerGroupService` (L7)
- Fix `formatLabel` double spaces (L8)
- Add PHPDoc to `RoleController` (L9)

---

## Test Plan

1. Run `vendor/bin/pint --dirty --format agent` after PHP changes
2. Run `php artisan test --compact` for affected modules
3. Verify TypeScript compiles: `npx vue-tsc --noEmit`
4. Manual verification: Role create/edit forms submit correctly with Wayfinder routes

---

## Estimated Effort

| Batch | Files | Estimated Time |
|-------|-------|----------------|
| Batch 1 (Critical) | 4 | 15 min |
| Batch 2 (Medium) | 8 | 30 min |
| Batch 3 (Low) | 6 | 15 min |
| Testing | — | 15 min |
| **Total** | **~18 files** | **~75 min** |

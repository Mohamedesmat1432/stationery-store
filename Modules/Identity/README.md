# Identity Module

The Identity module handles authentication, authorization (RBAC), and user management.

## Architecture

### Services
- `UserService`: Manages administrative users and their security profiles.
- `RoleService`: Handles Spatie Role management and permission assignments.
- `IdentityCacheService`: Manages granular caching for user profiles and access control states.

### Repositories
- `UserRepository`: Handles complex user filtering and role-based access.
- `RoleRepository`: Manages role persistence and permission relationships.

### Data (DTOs)
- `UserData`: Standardizes user information across the administrative interface.
- `RoleData`: Manages role and permission structures for the frontend.

## Security & Protection
- **Users**: Protected from deletion if they are system administrators or have active sessions.
- **Roles**: System roles (e.g., 'admin') are protected from modification or deletion.

## Cache Synchronization
The `SyncIdentityCache` listener is a critical component that flushes user-specific caches when roles or permissions are modified, ensuring immediate enforcement of access control changes.

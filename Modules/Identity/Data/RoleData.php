<?php

namespace Modules\Identity\Data;

use App\Models\Role;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class RoleData extends Data
{
    public function __construct(
        public ?string $id,
        public string $name,
        /** @var array<string> */
        public array $permissions,
    ) {}

    public static function fromModel(Role $role): self
    {
        return new self(
            $role->id,
            $role->name,
            $role->permissions->pluck('name')->toArray(),
        );
    }

    public static function rules(?ValidationContext $context = null): array
    {
        // Extract ID for uniqueness check if it exists in the payload (during updates)
        // Laravel Data handles merging route parameters if mapped correctly, but let's be safe.
        $roleId = request()->route('role')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($roleId)],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];
    }
}

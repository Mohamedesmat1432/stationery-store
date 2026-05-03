<?php

namespace Modules\Identity\Data;

use App\Models\Role;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class RoleData extends Data
{
    #[Computed]
    public bool $is_protected;

    public function __construct(
        /** @var string|null */
        public ?string $id,

        /** @var string */
        public string $name,

        /** @var array<string> */
        public array $permissions,
    ) {}

    public static function fromRole(Role $role): self
    {
        $data = new self(
            id: $role->id,
            name: $role->name,
            permissions: $role->permissions->pluck('name')->toArray(),
        );

        $data->is_protected = $role->isProtected();

        return $data;
    }

    public static function rules(?ValidationContext $context = null): array
    {
        // Extract ID for uniqueness check if it exists in the payload or route
        $role = request()->route('role');
        $roleId = $role instanceof Role ? $role->id : $role;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($roleId)],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];
    }
}

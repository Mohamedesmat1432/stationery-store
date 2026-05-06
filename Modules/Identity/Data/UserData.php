<?php

namespace Modules\Identity\Data;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class UserData extends Data
{
    #[Computed]
    public bool $is_protected;

    public function __construct(
        /** @var string|null */
        public ?string $id,

        /** @var string */
        public string $name,

        /** @var string */
        public string $email,

        /** @var string|null */
        public ?string $password,

        /** @var array<string> */
        public array $roles,

        #[Computed]
        public ?string $deleted_at = null,
    ) {}

    /**
     * Create a DTO instance from a User model.
     */
    public static function fromUser(User $user): self
    {
        $data = new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            password: null, // Do not expose password
            roles: $user->roles->pluck('name')->toArray(),
            deleted_at: $user->deleted_at?->toIso8601String(),
        );

        $data->is_protected = $user->isProtected(Auth::user());

        return $data;
    }

    /**
     * Validation rules for User data.
     */
    public static function rules(?ValidationContext $context = null): array
    {
        $user = request()->route('user');
        $userId = ($user instanceof User ? $user->id : $user) ?? request()->input('id');
        $isUpdate = $userId !== null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                Password::defaults(),
            ],
            'roles' => ['present', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ];
    }
}

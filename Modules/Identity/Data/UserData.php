<?php

namespace Modules\Identity\Data;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UserData extends Data
{
    public function __construct(
        public ?string $id,
        public string $name,
        public string $email,
        public ?string $password,
        /** @var array<string> */
        public array $roles,
        public ?string $deleted_at,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            $user->id,
            $user->name,
            $user->email,
            null, // Do not expose password
            $user->roles->pluck('name')->toArray(),
            $user->deleted_at?->toDateTimeString(),
        );
    }

    public static function rules(?ValidationContext $context = null): array
    {
        $userId = request()->route('user')?->id;
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

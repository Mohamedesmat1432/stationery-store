<?php

namespace Modules\CRM\Data;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\CRM\Enums\Gender;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class CustomerData extends Data
{
    #[Computed]
    public bool $is_protected;

    public function __construct(
        /** @var string|null */
        public ?string $id,

        /** @var string|null */
        public ?string $user_id,

        /** @var string|null */
        public ?string $name = null,

        /** @var string|null */
        public ?string $email = null,

        /** @var string|null */
        public ?string $phone = null,

        /** @var string|null */
        public ?string $birth_date = null,

        /** @var Gender|null */
        public ?Gender $gender = null,

        /** @var string|null */
        public ?string $tax_number = null,

        /** @var string|null */
        public ?string $company_name = null,

        /** @var string|null */
        public ?string $customer_group_id = null,

        /** @var string|null */
        public ?string $group_name = null,

        /** @var array<string, mixed>|Optional */
        public array|Optional $metadata = [],

        #[Computed]
        public ?string $deleted_at = null,
    ) {
        $this->total_spent = 0.0;
        $this->orders_count = 0;
        $this->age = null;
    }

    #[Computed]
    public float $total_spent;

    #[Computed]
    public int $orders_count;

    #[Computed]
    public ?int $age;

    public static function fromCustomer(Customer $customer): self
    {
        $data = new self(
            id: $customer->id,
            user_id: $customer->user_id,
            name: $customer->user?->name,
            email: $customer->user?->email,
            phone: $customer->phone,
            birth_date: $customer->birth_date instanceof \DateTimeInterface ? $customer->birth_date->format('Y-m-d') : null,
            gender: $customer->gender instanceof Gender ? $customer->gender : Gender::tryFrom((string) $customer->gender),
            tax_number: $customer->tax_number,
            company_name: $customer->company_name,
            customer_group_id: $customer->customer_group_id,
            group_name: $customer->group?->name,
            metadata: $customer->metadata ?? [],
            deleted_at: $customer->deleted_at?->toIso8601String(),
        );

        $data->total_spent = (float) $customer->total_spent;
        $data->orders_count = (int) $customer->orders_count;
        $data->age = $customer->age;
        $data->is_protected = $customer->isProtected(Auth::user());

        return $data;
    }

    public static function rules(?ValidationContext $context = null): array
    {
        $customer = request()->route('customer');
        $customerId = ($customer instanceof Customer ? $customer->id : $customer) ?? request()->input('id');

        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id'),
                Rule::unique('customers', 'user_id')->ignore($customerId),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'customer_group_id' => ['nullable', Rule::exists('customer_groups', 'id')],
            'metadata' => ['nullable', 'array'],
        ];
    }
}

<?php

namespace App\Data\CRM;

use App\Models\Customer;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class CustomerData extends Data
{
    public function __construct(
        public ?string $id,
        public ?string $user_id,
        public ?string $name = null, // From User
        public ?string $email = null, // From User
        public ?string $phone = null,
        public ?string $birth_date = null,
        public ?string $gender = null,
        public ?string $tax_number = null,
        public ?string $company_name = null,
        public float $total_spent = 0.0,
        public int $orders_count = 0,
        public ?string $customer_group_id = null,
        public ?string $group_name = null, // From CustomerGroup
        /** @var array<string, mixed>|Optional */
        public array|Optional $metadata = [],
        public ?string $deleted_at = null,
    ) {}

    public static function fromModel(Customer $customer): self
    {
        return new self(
            $customer->id,
            $customer->user_id,
            $customer->user?->name,
            $customer->user?->email,
            $customer->phone,
            $customer->birth_date ? Carbon::parse($customer->birth_date)->toDateString() : null,
            $customer->gender,
            $customer->tax_number,
            $customer->company_name,
            (float) $customer->total_spent,
            (int) $customer->orders_count,
            $customer->customer_group_id,
            $customer->group?->name,
            $customer->metadata ?? [],
            $customer->deleted_at?->toDateTimeString(),
        );
    }

    public static function rules(?ValidationContext $context = null): array
    {
        $customerId = request()->route('customer')?->id;

        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id'),
                Rule::unique('customers', 'user_id')->ignore($customerId),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'customer_group_id' => ['nullable', Rule::exists('customer_groups', 'id')],
            'metadata' => ['nullable', 'array'],
        ];
    }
}

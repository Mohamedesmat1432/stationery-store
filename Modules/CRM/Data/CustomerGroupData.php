<?php

namespace Modules\CRM\Data;

use App\Models\CustomerGroup;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class CustomerGroupData extends Data
{
    #[Computed]
    public bool $is_protected;

    public function __construct(
        /** @var string|null */
        public ?string $id,

        /** @var string */
        public string $name,

        /** @var string */
        public string $slug,

        /** @var string|null */
        public ?string $description,

        /** @var float */
        public float $discount_percentage,

        /** @var bool */
        public bool $is_active = true,

        /** @var int */
        public int $sort_order = 0,

        /** @var int */
        public int $customers_count = 0,

        /** @var string|null */
        public ?string $deleted_at = null,
    ) {}

    public static function fromCustomerGroup(CustomerGroup $group): self
    {
        $data = new self(
            id: $group->id,
            name: $group->name,
            slug: $group->slug,
            description: $group->description,
            discount_percentage: (float) $group->discount_percentage,
            is_active: (bool) $group->is_active,
            sort_order: (int) $group->sort_order,
            customers_count: (int) ($group->customers_count ?? 0),
            deleted_at: $group->deleted_at?->toDateTimeString(),
        );

        $data->is_protected = $group->isProtected();

        return $data;
    }

    public static function rules(?ValidationContext $context = null): array
    {
        $groupId = request()->route('customer_group')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('customer_groups', 'slug')->ignore($groupId)],
            'description' => ['nullable', 'string'],
            'discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }
}

<?php

namespace Modules\CRM\Data;

use App\Models\CustomerGroup;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class CustomerGroupData extends Data
{
    public function __construct(
        public ?string $id,
        public string $name,
        public string $slug,
        public ?string $description,
        public float $discount_percentage,
        public bool $is_active = true,
        public int $sort_order = 0,
        public int $customers_count = 0,
        public ?string $deleted_at = null,
    ) {}

    public static function fromModel(CustomerGroup $group): self
    {
        return new self(
            $group->id,
            $group->name,
            $group->slug,
            $group->description,
            (float) $group->discount_percentage,
            $group->is_active,
            $group->sort_order,
            (int) ($group->customers_count ?? 0),
            $group->deleted_at?->toDateTimeString(),
        );
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

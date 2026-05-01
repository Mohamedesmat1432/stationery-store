<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'addressable_type',
        'addressable_id',
        'type',
        'first_name',
        'last_name',
        'company',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'is_default',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_default' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForType(Builder $query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeBilling(Builder $query)
    {
        return $query->where('type', 'billing');
    }

    public function scopeShipping(Builder $query)
    {
        return $query->where('type', 'shipping');
    }

    public function scopeDefault(Builder $query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForCountry(Builder $query, string $countryCode)
    {
        return $query->where('country', $countryCode);
    }

    public function getFullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getFullAddress(): string
    {
        $parts = [
            $this->address_line_1,
            $this->address_line_2,
            "{$this->city}, {$this->state} {$this->postal_code}",
            $this->country,
        ];

        return implode("\n", array_filter($parts));
    }

    public function setAsDefault(): void
    {
        // Remove default from other addresses of same type for same owner
        static::where('addressable_type', $this->addressable_type)
            ->where('addressable_id', $this->addressable_id)
            ->where('type', $this->type)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    public function cloneForOrder(): Address
    {
        $clone = $this->replicate();
        $clone->addressable_type = Order::class;
        $clone->is_default = false;
        $clone->save();

        return $clone;
    }
}

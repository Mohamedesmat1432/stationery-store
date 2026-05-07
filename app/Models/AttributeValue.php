<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AttributeValue extends BaseModel
{
    use HasFactory, \Modules\Shared\Concerns\HasProtection;

    /**
     * Determine if the attribute value is protected from deletion or modification.
     */
    public function shouldBeProtected(?User $user = null): bool
    {
        // Prevent deletion of values used in variants
        return $this->variants()->exists();
    }

    protected $fillable = [
        'attribute_id',
        'value',
        'color_code',
        'image',
        'sort_order',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'variant_attribute_values');
    }
}

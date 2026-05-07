<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Brand extends BaseModel implements HasMedia
{
    use HasFactory, InteractsWithMedia, \Modules\Shared\Concerns\HasProtection;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'website',
        'logo',
        'is_active',
        'sort_order',
    ];

    /**
     * Determine if the brand is protected from deletion or modification.
     */
    public function shouldBeProtected(?User $user = null): bool
    {
        // Prevent deletion of brands with products
        if ($this->products_count !== null) {
            return $this->products_count > 0;
        }

        return $this->products()->exists();
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeWithProductsCount(Builder $query): Builder
    {
        return $query->withCount(['products' => fn (Builder $q) => $q->active()]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']);
    }
}

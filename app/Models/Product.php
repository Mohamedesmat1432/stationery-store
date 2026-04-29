<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\InventoryPolicy;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Redis;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends BaseModel implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'category_id',
        'brand_id',
        'vendor_id',
        'is_active',
        'is_featured',
        'is_digital',
        'is_taxable',
        'sku',
        'barcode',
        'mpn',
        'gtin',
        'weight',
        'dimensions',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'inventory_policy',
        'view_count',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_digital' => 'boolean',
            'is_taxable' => 'boolean',
            'weight' => 'decimal:4',
            'dimensions' => 'array',
            'inventory_policy' => InventoryPolicy::class,
            'view_count' => 'integer',
            'avg_rating' => 'decimal:1',
            'reviews_count' => 'integer',
            'sold_count' => 'integer',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new ActiveScope);

        static::saved(function ($product) {
            $product->forgetRedisCache();
            $product->forgetRedisCache('products:featured:*');
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'category_id', 'brand_id', 'is_active', 'is_featured'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant(): ?ProductVariant
    {
        return $this->variants()->where('is_default', true)->first()
            ?? $this->variants()->first();
    }

    public function prices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    public function activePrices(): MorphMany
    {
        return $this->prices()
            ->where('start_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', now());
            });
    }

    public function currentPrice(): ?Price
    {
        return $this->activePrices()
            ->where('type', 'base')
            ->where('currency_id', config('app.currency_id'))
            ->first();
    }

    public function stock(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function totalStock(): int
    {
        return (int) $this->stock()->sum('available_quantity');
    }

    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'product_tax');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('is_approved', true);
    }

    public function relatedProducts(): HasMany
    {
        return $this->hasMany(ProductRelation::class)->where('type', 'related');
    }

    public function crossSells(): HasMany
    {
        return $this->hasMany(ProductRelation::class)->where('type', 'cross_sell');
    }

    public function upSells(): HasMany
    {
        return $this->hasMany(ProductRelation::class)->where('type', 'up_sell');
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function productUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeDigital(Builder $query): Builder
    {
        return $query->where('is_digital', true);
    }

    public function scopePhysical(Builder $query): Builder
    {
        return $query->where('is_digital', false);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->whereHas('stock', fn($q) => $q->where('available_quantity', '>', 0));
    }

    public function scopeWithPrice(Builder $query, ?string $currencyCode = null)
    {
        $currencyId = $currencyCode
            ? Currency::where('code', $currencyCode)->value('id')
            : config('app.currency_id');

        return $query->with([
            'prices' => fn($q) => $q
                ->where('currency_id', $currencyId)
                ->where('type', 'base')
                ->where(function ($sq) {
                    $sq->whereNull('start_at')->orWhere('start_at', '<=', now());
                })
                ->where(function ($sq) {
                    $sq->whereNull('end_at')->orWhere('end_at', '>=', now());
                })
        ]);
    }

    public function scopeSearch(Builder $query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('barcode', 'like', "%{$term}%");
        });
    }

    public function scopeFilterByCategory(Builder $query, string $categorySlug)
    {
        return $query->whereHas('category', fn($q) => $q->where('slug', $categorySlug));
    }

    public function scopeFilterByBrand(Builder $query, string $brandSlug): Builder
    {
        return $query->whereHas('brand', fn($q) => $q->where('slug', $brandSlug));
    }

    public function scopeFilterByPriceRange(Builder $query, float $min, float $max)
    {
        return $query->whereHas(
            'prices',
            fn($q) => $q
                ->whereBetween('amount', [$min, $max])
                ->where('type', 'base')
        );
    }

    public function scopeFilterByAttributes(Builder $query, array $attributes)
    {
        foreach ($attributes as $attributeCode => $values) {
            $query->whereHas(
                'variants.attributeValues',
                fn($q) => $q
                    ->whereHas('attribute', fn($aq) => $aq->where('code', $attributeCode))
                    ->whereIn('value', (array) $values)
            );
        }
        return $query;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('downloads')
            ->acceptsMimeTypes(['application/pdf', 'application/zip']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)->height(300)->sharpen(10)->nonQueued();

        $this->addMediaConversion('medium')
            ->width(800)->height(800)->nonQueued();

        $this->addMediaConversion('large')
            ->width(1200)->height(1200)->nonQueued();
    }

    public function getFeaturedImageUrl(string $conversion = 'medium'): ?string
    {
        $media = $this->getFirstMedia('featured');
        return $media ? $media->getUrl($conversion) : null;
    }

    public function getGalleryUrls(string $conversion = 'thumb'): array
    {
        return $this->getMedia('gallery')->map(fn($m) => $m->getUrl($conversion))->toArray();
    }

    public function incrementViewCount(): void
    {
        Redis::connection()->incr("product:{$this->id}:views");
    }

    public function getViewCount(): int
    {
        $redisCount = (int) Redis::connection()->get("product:{$this->id}:views");
        return $redisCount + $this->view_count;
    }

    public function syncViewCountToDatabase(): void
    {
        $redisCount = (int) Redis::connection()->getdel("product:{$this->id}:views");
        if ($redisCount > 0) {
            $this->increment('view_count', $redisCount);
        }
    }

    public function isInStock(int $quantity = 1): bool
    {
        return $this->totalStock() >= $quantity;
    }

    public function canBackorder(): bool
    {
        return $this->inventory_policy === InventoryPolicy::BACKORDER;
    }

    public function recalculateRating(): void
    {
        $avg = $this->approvedReviews()->avg('rating') ?? 0;
        $count = $this->approvedReviews()->count();
        $this->update(['avg_rating' => $avg, 'reviews_count' => $count]);
    }
}

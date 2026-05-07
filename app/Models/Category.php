<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Shared\Concerns\HasProtection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class Category extends BaseModel implements HasMedia
{
    use HasFactory, HasProtection, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    /**
     * Determine if the category is protected from deletion or modification.
     */
    public function shouldBeProtected(?User $user = null): bool
    {
        // Prevent deletion of categories with active products
        // Check loaded count first to avoid N+1
        if ($this->products_count !== null && $this->products_count > 0) {
            return true;
        }

        // Check total product count (includes descendants)
        if ($this->total_product_count > 0) {
            return true;
        }

        return false;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('icon')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/svg+xml', 'image/webp']);

        $this->addMediaCollection('banner')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?SpatieMedia $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->sharpen(10)
            ->nonQueued();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function allChildren(): HasMany
    {
        return $this->children()
            ->with(['allChildren', 'media'])
            ->withCount(['products' => fn ($q) => $q->active()]);
    }

    public function ancestors(): array
    {
        $ancestors = [];
        $current = $this;
        $visited = [$this->id];

        while (true) {
            $parent = $current->relationLoaded('parent')
                ? $current->parent
                : $current->parent()->first();

            if (! $parent || in_array($parent->id, $visited)) {
                break;
            }

            $ancestors[] = $parent;
            $current = $parent;
            $visited[] = $current->id;
        }

        return array_reverse($ancestors);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeWithProductsCount(Builder $query): Builder
    {
        return $query->withCount(['products' => fn (Builder $q) => $q->active()]);
    }

    /**
     * Scope a query to search categories by name or slug.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Get the full breadcrumb path as a string.
     */
    public function getFullPathAttribute(): string
    {
        return collect($this->ancestors())
            ->push($this)
            ->pluck('name')
            ->implode(' > ');
    }

    /**
     * Get breadcrumbs as an array of objects.
     */
    public function getBreadcrumbs(): array
    {
        return collect($this->ancestors())
            ->push($this)
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->toArray();
    }

    /**
     * Determine if the category is a descendant of the given category.
     */
    public function isDescendantOf(Category $category): bool
    {
        $ancestors = $this->ancestors();
        foreach ($ancestors as $ancestor) {
            if ($ancestor->id === $category->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all descendant IDs recursively.
     * Uses eager loaded relations if available to minimize queries.
     *
     * @return array<string>
     */
    public function getDescendantIds(): array
    {
        $ids = [];

        $children = $this->relationLoaded('allChildren')
            ? $this->allChildren
            : ($this->relationLoaded('children') ? $this->children : $this->children()->get());

        foreach ($children as $child) {
            $ids[] = $child->id;
            if ($child->relationLoaded('allChildren') || $child->relationLoaded('children')) {
                $ids = array_merge($ids, $child->getDescendantIds());
            } else {
                // If not loaded, we might want to avoid deep recursion here if it's many levels
                // but for our tree it's usually fine.
                $ids = array_merge($ids, $child->getDescendantIds());
            }
        }

        return $ids;
    }

    /**
     * Get total product count including sub-categories.
     */
    public function getTotalProductCountAttribute(): int
    {
        // If we have products count eager loaded, use it as base
        $count = (int) ($this->products_count ?? (
            $this->relationLoaded('products')
            ? $this->products->where('is_active', true)->count()
            : $this->products()->active()->count()
        ));

        // Avoid recursion if children aren't loaded and we don't want to trigger queries
        if (! $this->relationLoaded('allChildren') && ! $this->relationLoaded('children')) {
            return $count;
        }

        $children = $this->allChildren ?? $this->children;

        foreach ($children as $child) {
            $count += $child->total_product_count;
        }

        return $count;
    }
}

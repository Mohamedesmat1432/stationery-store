<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends BaseModel
{
    use HasFactory;

    protected $table = 'seo_metas';

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'title',
        'description',
        'keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'schema_markup',
        'robots',
    ];

    protected function casts(): array
    {
        return [
            'schema_markup' => 'array',
        ];
    }  

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForEntity(Builder $query, string $type, string $id)
    {
        return $query->where('seoable_type', $type)->where('seoable_id', $id);
    }
}

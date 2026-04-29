<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Discountable extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'discount_id',
        'discountable_type',
        'discountable_id',
    ];

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function discountable(): MorphTo
    {
        return $this->morphTo();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseModel extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Scope a query to only include active records.
     */
    public function scopeActive(Builder $query)
    {
        return $query->where($this->qualifyColumn('is_active'), true);
    }
}

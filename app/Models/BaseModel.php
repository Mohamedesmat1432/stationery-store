<?php

namespace App\Models;

use App\Concerns\HasRedisCache;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseModel extends Model
{
    use HasUlids, SoftDeletes, HasFactory, HasRedisCache;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::saved(function ($model) {
            $model->forgetRedisCache();
        });

        static::deleted(function ($model) {
            $model->forgetRedisCache();
        });
    }
}

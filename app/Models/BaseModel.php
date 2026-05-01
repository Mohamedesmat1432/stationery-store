<?php

namespace App\Models;

use App\Concerns\HasRedisCache;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseModel extends Model
{
    use HasFactory, HasRedisCache, HasUlids, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::saved(function (self $model) {
            $model->forgetRedisCache(class_basename($model).":{$model->id}:*");
        });

        static::deleted(function (self $model) {
            $model->forgetRedisCache(class_basename($model).":{$model->id}:*");
        });
    }
}

<?php

namespace Modules\Shared\Services\Media;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

trait HandlesResourceMedia
{
    /**
     * Handle media upload for a model.
     */
    protected function syncMedia(Model $model, mixed $file, string $collection): void
    {
        if ($file instanceof UploadedFile) {
            $model->addMedia($file)->toMediaCollection($collection);
        } elseif (is_null($file)) {
            $model->clearMediaCollection($collection);
        }
    }

    /**
     * Handle media from request.
     */
    protected function syncMediaFromRequest(Model $model, string $key, string $collection): void
    {
        if (request()->hasFile($key)) {
            $model->addMediaFromRequest($key)->toMediaCollection($collection);
        }
    }
}

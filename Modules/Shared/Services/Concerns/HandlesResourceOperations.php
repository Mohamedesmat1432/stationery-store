<?php

namespace Modules\Shared\Services\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Modules\Shared\Events\ResourceChanged;

/**
 * Trait HandlesResourceOperations
 *
 * Provides standardized single-resource operation methods (delete, restore, forceDelete)
 * for services, ensuring consistent event dispatching and protection checks.
 */
trait HandlesResourceOperations
{
    /**
     * Standardized delete operation for a single resource.
     *
     * @throws ValidationException If the resource is protected.
     */
    public function performDelete(Model $model): bool
    {
        if ($this->isProtected($model)) {
            $this->logInfo('Prevented deletion of protected resource', [
                'model' => get_class($model),
                'id' => $model->id,
            ]);

            throw ValidationException::withMessages([
                'resource' => __('This :resource is protected and cannot be deleted.', ['resource' => strtolower(class_basename($model))]),
            ]);
        }

        try {
            $result = $this->getRepository()->delete($model);

            if ($result) {
                ResourceChanged::dispatch($this->getModelClass(), 'deleted', [$model->id]);
            }

            return $result;
        } catch (\Throwable $e) {
            $this->logError('Failed to delete resource', [
                'model' => get_class($model),
                'id' => $model->id,
            ], $e);

            throw $e;
        }
    }

    /**
     * Standardized restore operation for a single resource.
     */
    public function performRestore(Model $model): bool
    {
        try {
            $result = $this->getRepository()->restore($model);

            if ($result) {
                ResourceChanged::dispatch($this->getModelClass(), 'restored', [$model->id]);
            }

            return $result;
        } catch (\Throwable $e) {
            $this->logError('Failed to restore resource', [
                'model' => get_class($model),
                'id' => $model->id,
            ], $e);

            throw $e;
        }
    }

    /**
     * Standardized force delete operation for a single resource.
     *
     * @throws ValidationException If the resource is protected.
     */
    public function performForceDelete(Model $model): bool
    {
        if ($this->isProtected($model)) {
            $this->logInfo('Prevented force deletion of protected resource', [
                'model' => get_class($model),
                'id' => $model->id,
            ]);

            throw ValidationException::withMessages([
                'resource' => __('This :resource is protected and cannot be permanently deleted.', ['resource' => strtolower(class_basename($model))]),
            ]);
        }

        try {
            $result = $this->getRepository()->forceDelete($model);

            if ($result) {
                ResourceChanged::dispatch($this->getModelClass(), 'force_deleted', [$model->id]);
            }

            return $result;
        } catch (\Throwable $e) {
            $this->logError('Failed to force delete resource', [
                'model' => get_class($model),
                'id' => $model->id,
            ], $e);

            throw $e;
        }
    }

    /**
     * Requirement: Services using this trait must provide the repository.
     */
    abstract protected function getRepository();

    /**
     * Requirement: Services using this trait must provide the model class.
     */
    abstract protected function getModelClass(): string;

    /**
     * Requirement: Services using this trait must provide a logging method.
     */
    abstract protected function logInfo(string $message, array $context = []): void;

    /**
     * Requirement: Services using this trait must provide a logging method for errors.
     */
    abstract protected function logError(string $message, array $context = [], ?\Throwable $exception = null): void;

    /**
     * Requirement: Services using this trait must provide protection logic.
     */
    abstract public function isProtected(Model $model): bool;
}

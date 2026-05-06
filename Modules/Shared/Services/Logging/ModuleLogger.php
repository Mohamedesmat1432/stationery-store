<?php

/**
 * Created At: 2026-05-06T04:23:53Z
 */

namespace Modules\Shared\Services\Logging;

use Illuminate\Support\Facades\Log;

trait ModuleLogger
{
    /**
     * Log a module-specific error.
     */
    protected function logError(string $message, array $context = [], ?\Throwable $exception = null): void
    {
        $module = $this->getModuleName();
        $context = array_merge([
            'module' => $module,
            'service' => static::class,
        ], $context);

        if ($exception) {
            $context['exception'] = $exception->getMessage();
            $context['trace'] = $exception->getTraceAsString();
        }

        Log::error("[{$module}] {$message}", $context);
    }

    /**
     * Log a module-specific info message.
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $module = $this->getModuleName();
        Log::info("[{$module}] {$message}", array_merge([
            'module' => $module,
            'service' => static::class,
        ], $context));
    }

    /**
     * Determine the module name from the namespace.
     */
    protected function getModuleName(): string
    {
        $namespace = static::class;
        if (preg_match('/^Modules\\\\([^\\\\]+)/', $namespace, $matches)) {
            return $matches[1];
        }

        return 'Shared';
    }
}

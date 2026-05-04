<?php

namespace Modules\Shared\Providers;

use Illuminate\Support\ServiceProvider;

class ModulesServiceProvider extends ServiceProvider
{
    /**
     * Register all module service providers.
     */
    public function register(): void
    {
        $modules = $this->getModules();

        foreach ($modules as $module) {
            $this->registerModuleProviders($module);
        }
    }

    /**
     * Get all directories in the Modules folder.
     *
     * @return array<string>
     */
    protected function getModules(): array
    {
        $modulesPath = base_path('Modules');

        if (! is_dir($modulesPath)) {
            return [];
        }

        return array_filter(glob($modulesPath.'/*'), 'is_dir');
    }

    /**
     * Register all providers for a specific module.
     */
    protected function registerModuleProviders(string $modulePath): void
    {
        $moduleName = basename($modulePath);
        $providersPath = $modulePath.'/Providers';

        if (! is_dir($providersPath)) {
            return;
        }

        $providers = glob($providersPath.'/*.php');

        foreach ($providers as $providerFile) {
            $providerClass = 'Modules\\'.$moduleName.'\\Providers\\'.basename($providerFile, '.php');

            if (class_exists($providerClass) && $providerClass !== static::class) {
                $this->app->register($providerClass);
            }
        }
    }
}

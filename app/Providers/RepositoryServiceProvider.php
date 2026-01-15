<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->autoBindRepositories();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    protected function autoBindRepositories()
    {
        $contractPath = app_path('Repositories/Contracts');
        $contractNamespace = 'App\\Repositories\\Contracts\\';
        $implementationNamespace = 'App\\Repositories\\Eloquent\\';

        foreach (scandir($contractPath) as $folder) {
            if ($folder === '.' || $folder === '..') continue;

            $interfaceDir = $contractPath . '/' . $folder;
            $interfaceNamespace = $contractNamespace . $folder . '\\';
            $implNamespace = $implementationNamespace . $folder . '\\';

            if (!is_dir($interfaceDir)) continue;

            foreach (scandir($interfaceDir) as $file) {
                if (!Str::endsWith($file, 'Interface.php')) continue;

                $interfaceName = pathinfo($file, PATHINFO_FILENAME);
                $baseName = Str::before($interfaceName, 'RepositoryInterface');

                $interface = $interfaceNamespace . $interfaceName;
                $implementation = $implNamespace . $baseName . 'Repository';

                if (interface_exists($interface) && class_exists($implementation)) {
                    $this->app->bind($interface, $implementation);
                }
            }
        }
    }
}

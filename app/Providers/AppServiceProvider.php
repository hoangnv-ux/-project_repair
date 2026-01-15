<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('testing')) {
            $dbName = config('database.connections.mysql.database');
            $expectedDbName = env('DB_DATABASE');

            if ($dbName !== $expectedDbName) {
                throw new \Exception('Wrong database for testing! Config: ' . $dbName . ' | Expected: ' . $expectedDbName);
            }
        }
    }
}

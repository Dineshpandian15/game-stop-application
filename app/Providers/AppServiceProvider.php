<?php

namespace App\Providers;

use App\Services\DatabaseBootstrapper;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->app->make(DatabaseBootstrapper::class)->bootstrapWebApp();
    }
}

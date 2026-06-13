<?php

namespace App\Providers;

use App\Services\DatabaseBootstrapper;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    public function boot(): void
    {
        app(DatabaseBootstrapper::class)->bootstrapNativeApp();

        Window::open()
            ->width(1280)
            ->height(800)
            ->minWidth(1024)
            ->minHeight(700)
            ->title('Game Stop — Play Station Shop')
            ->route('dashboard');
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}

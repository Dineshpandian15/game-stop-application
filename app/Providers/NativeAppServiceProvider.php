<?php

namespace App\Providers;

use App\Services\DatabaseBootstrapper;
use Illuminate\Support\Facades\Log;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Window;
use Throwable;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    public function boot(): void
    {
        try {
            app(DatabaseBootstrapper::class)->bootstrapNativeApp();
        } catch (Throwable $exception) {
            Log::error('Native app database bootstrap failed', [
                'message' => $exception->getMessage(),
            ]);
        }

        Window::open()
            ->width(1280)
            ->height(800)
            ->minWidth(1024)
            ->minHeight(700)
            ->title('Game Stop - Play Station Shop')
            ->route('login');
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

<?php

namespace App\Services;

use App\Models\Station;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class DatabaseBootstrapper
{
    public function bootstrapWebApp(): void
    {
        if (config('nativephp-internal.running')) {
            return;
        }

        if (config('database.default') !== 'sqlite') {
            return;
        }

        $path = config('database.connections.sqlite.database');

        if (! file_exists($path)) {
            $this->createSqliteFile($path);
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);

            return;
        }

        $this->seedIfNeeded();
    }

    public function bootstrapNativeApp(): void
    {
        if (! config('nativephp-internal.running')) {
            return;
        }

        $this->seedIfNeeded();
    }

    private function seedIfNeeded(): void
    {
        if (! Schema::hasTable('stations')) {
            return;
        }

        if (Station::query()->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--force' => true]);
    }

    private function createSqliteFile(string $path): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        touch($path);
    }
}

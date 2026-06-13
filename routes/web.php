<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlaySessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/sessions', [PlaySessionController::class, 'store'])->name('sessions.store');
    Route::post('/sessions/{playSession}/pause', [PlaySessionController::class, 'pause'])->name('sessions.pause');
    Route::post('/sessions/{playSession}/resume', [PlaySessionController::class, 'resume'])->name('sessions.resume');
    Route::post('/sessions/{playSession}/stop', [PlaySessionController::class, 'stop'])->name('sessions.stop');
    Route::post('/sessions/{playSession}/reset', [PlaySessionController::class, 'reset'])->name('sessions.reset');
    Route::post('/sessions/{playSession}/restart', [PlaySessionController::class, 'restart'])->name('sessions.restart');

    Route::get('/api/packages', [PlaySessionController::class, 'packages'])->name('api.packages');
    Route::get('/api/stations/state', [PlaySessionController::class, 'stationsState'])->name('api.stations.state');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/daily', [ReportController::class, 'exportDaily'])->name('reports.export.daily');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

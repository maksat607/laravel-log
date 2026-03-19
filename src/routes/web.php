<?php

use Illuminate\Support\Facades\Route;
use Maksatsaparbekov\LaravelLog\Http\Controllers\LogController;

Route::middleware(['web'])->prefix('laravel-logs')->name('laravel-log.')->group(function () {
    Route::get('/', [LogController::class, 'index'])->name('index');
    Route::get('/view', [LogController::class, 'view'])->name('view');
    Route::delete('/delete', [LogController::class, 'delete'])->name('delete');
});

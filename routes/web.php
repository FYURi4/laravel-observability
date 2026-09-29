<?php

use Furushka\Observability\Http\Controllers\TraceController;
use Illuminate\Support\Facades\Route;

if (! config('observability.ui.enabled', true)) {
    return;
}

Route::prefix(config('observability.ui.path', 'observability'))
    ->middleware(config('observability.ui.middleware', ['web']))
    ->name('observability.')
    ->group(function () {
        Route::get('/', [TraceController::class, 'index'])->name('index');
        Route::get('/{traceId}', [TraceController::class, 'show'])->name('show');
    });

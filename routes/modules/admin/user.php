<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;

Route::prefix('admin')->middleware('auth:admin')->group(function () {
    Route::apiResource('user', UserController::class);
});

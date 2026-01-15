<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController;

Route::prefix('user/auth')->group(function () {
    Route::get('/login', function () {
        return view('user.auth.login');
    })->name('user.login');
    Route::post('login', [AuthController::class,'login'])->name('user.login');
    Route::middleware(['auth:user'])->group(function () {

    });
});

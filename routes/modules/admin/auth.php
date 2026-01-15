<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;

Route::prefix('admin/auth')->group(function () {
    Route::get('/login', function () {
        return view('admin.auth.login');
    })->name('admin.login');
    // b1 khoi tao router admin
    Route::post('login', [AuthController::class,'login'])->name('admin.login');
    Route::middleware(['auth:admin'])->group(function () {

    });
});

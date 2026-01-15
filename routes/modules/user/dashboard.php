<?php

use Illuminate\Support\Facades\Route;

Route::prefix('user')->middleware(['auth:user'])->group(function () {
    Route::get('/dashboard', function () {
        return view('user.dashboard');
    })->name('user.dashboard');
});

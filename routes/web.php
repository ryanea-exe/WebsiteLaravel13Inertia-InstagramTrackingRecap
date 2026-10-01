<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::resource('users', \App\Http\Controllers\UserController::class)->except(['show', 'destroy']);
    
    Route::resource('employees', \App\Http\Controllers\EmployeeController::class)->except(['show']);
    Route::patch('employees/{employee}/restore', [\App\Http\Controllers\EmployeeController::class, 'restore'])->name('employees.restore');

    // Instagram OAuth
    Route::get('/admin/instagram/connect', [\App\Http\Controllers\MetaOAuthController::class, 'connect'])->name('meta.connect');
    Route::get('/oauth/meta/callback', [\App\Http\Controllers\MetaOAuthController::class, 'callback'])->name('meta.callback');
});

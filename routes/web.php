<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PageManagementController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebsiteController::class, 'home'])->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/pages', [PageManagementController::class, 'index'])->name('pages.index');
        Route::get('/pages/{page}/edit', [PageManagementController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{page}', [PageManagementController::class, 'update'])->name('pages.update');
        Route::post('/pages/{page}/sections', [PageManagementController::class, 'storeSection'])->name('pages.sections.store');
        Route::put('/sections/{section}', [PageManagementController::class, 'updateSection'])->name('sections.update');
        Route::post('/sections/{section}/cards', [PageManagementController::class, 'storeCard'])->name('sections.cards.store');
        Route::put('/cards/{card}', [PageManagementController::class, 'updateCard'])->name('cards.update');
        Route::delete('/cards/{card}', [PageManagementController::class, 'destroyCard'])->name('cards.destroy');
    });

Route::middleware(['auth', 'role:'.UserRole::USER])
    ->prefix('app')
    ->name('user.')
    ->group(function (): void {
        Route::get('/dashboard', UserDashboardController::class)->name('dashboard');
    });
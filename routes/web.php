<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PageManagementController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

$adminRoutes = static function (string $prefix, string $namePrefix, array $middleware): void {
    Route::middleware($middleware)->prefix($prefix)->name($namePrefix)->group(function (): void {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/pages', [PageManagementController::class, 'index'])->name('pages.index');
        Route::put('/pages/builder-state', [PageManagementController::class, 'saveSiteBuilder'])->name('pages.builder.site-save');
        Route::get('/pages/{page}/edit', [PageManagementController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{page}/builder-state', [PageManagementController::class, 'saveBuilder'])->name('pages.builder.save');
        Route::put('/pages/{page}', [PageManagementController::class, 'update'])->name('pages.update');
        Route::post('/pages/{page}/sections', [PageManagementController::class, 'storeSection'])->name('pages.sections.store');
        Route::put('/sections/{section}', [PageManagementController::class, 'updateSection'])->name('sections.update');
        Route::post('/sections/{section}/delete', [PageManagementController::class, 'destroySection'])->name('sections.delete');
        Route::post('/sections/{section}/cards', [PageManagementController::class, 'storeCard'])->name('sections.cards.store');
        Route::put('/cards/{card}', [PageManagementController::class, 'updateCard'])->name('cards.update');
        Route::post('/cards/{card}/delete', [PageManagementController::class, 'destroyCard'])->name('cards.delete');
        Route::delete('/cards/{card}', [PageManagementController::class, 'destroyCard'])->name('cards.destroy');
    });
};

Route::get('/', [WebsiteController::class, 'home'])->middleware('resolve.tenant')->name('home');

Route::middleware(['guest', 'resolve.tenant'])->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware(['guest', 'resolve.tenant'])->prefix('{tenant}')->name('tenant.')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware(['auth', 'resolve.tenant'])
    ->name('logout');
Route::post('/{tenant}/logout', [LoginController::class, 'destroy'])
    ->middleware(['auth', 'resolve.tenant'])
    ->name('tenant.logout');

$adminRoutes('admin', 'admin.', ['resolve.tenant', 'auth', 'tenant.member', 'role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN]);
$adminRoutes('{tenant}/admin', 'tenant.admin.', ['resolve.tenant', 'auth', 'tenant.member', 'role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN]);

$roleRoutes = static function (string $prefix, string $namePrefix, array $middleware): void {
    Route::middleware($middleware)->prefix($prefix)->name($namePrefix)->group(function (): void {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
};

$roleRoutes('admin', 'admin.', ['resolve.tenant', 'auth', 'tenant.member', 'role:'.UserRole::SUPER_ADMIN]);
$roleRoutes('{tenant}/admin', 'tenant.admin.', ['resolve.tenant', 'auth', 'tenant.member', 'role:'.UserRole::SUPER_ADMIN]);

Route::middleware(['resolve.tenant', 'auth', 'tenant.member', 'role:'.UserRole::USER])
    ->prefix('app')->name('user.')
    ->group(fn (): mixed => Route::get('/dashboard', UserDashboardController::class)->name('dashboard'));
Route::middleware(['resolve.tenant', 'auth', 'tenant.member', 'role:'.UserRole::USER])
    ->prefix('{tenant}/app')->name('tenant.user.')
    ->group(fn (): mixed => Route::get('/dashboard', UserDashboardController::class)->name('dashboard'));

if (config('novabase.tenancy.enabled')) {
    Route::get('/{tenant:slug}', [WebsiteController::class, 'home'])->middleware('resolve.tenant')->name('tenant.home');
    Route::get('/{tenant:slug}/{page:slug}', [WebsiteController::class, 'page'])->middleware('resolve.tenant')->name('tenant.page');
}

Route::get('/{page:slug}', [WebsiteController::class, 'page'])
    ->where('page', '[A-Za-z0-9-]+')
    ->name('website.page');

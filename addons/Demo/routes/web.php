<?php

use Addons\Demo\Http\Controllers\DemoController;
use Addons\Demo\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:super_admin,admin', 'permission:demo.view'])
    ->prefix('admin/addons/demo')
    ->name('admin.addons.demo.')
    ->group(function (): void {
        Route::get('/', DemoController::class)->name('index');
    });

Route::middleware(['auth', 'role:super_admin', 'permission:demo.view'])
    ->prefix('admin/addons/demo/tenants')
    ->name('admin.addons.demo.tenants.')
    ->group(function (): void {
        Route::get('/', [TenantController::class, 'index'])->name('index');
        Route::post('/', [TenantController::class, 'store'])->name('store');
    });

Route::post('/addons/demo/tenant-accounts', [TenantController::class, 'storePublic'])
    ->name('demo.tenant-accounts.store');

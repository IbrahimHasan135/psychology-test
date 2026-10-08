<?php

use Addons\Demo\Http\Controllers\DemoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['role:super_admin,admin', 'permission:demo.view'])
    ->prefix('admin/addons/demo')
    ->name('admin.addons.demo.')
    ->group(function (): void {
        Route::get('/', DemoController::class)->name('index');
    });

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'roleCounts' => collect(UserRole::all())
                ->mapWithKeys(fn (string $role) => [$role => User::where('role', $role)->count()]),
        ]);
    }
}
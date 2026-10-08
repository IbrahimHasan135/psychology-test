<?php

namespace App\Http\Controllers\Admin;

use App\Core\Addons\AddonRegistry;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AddonRegistry $addons): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'roleCounts' => Role::query()
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn (Role $role) => [$role->slug => User::where('role', $role->slug)->count()]),
            'addonCards' => $addons->dashboardCardsFor(auth()->user())->groupBy(fn (array $card) => $card['addon']->slug),
            'addons' => $addons->enabled(),
        ]);
    }
}

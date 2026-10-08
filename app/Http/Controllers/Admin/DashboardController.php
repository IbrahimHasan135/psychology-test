<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Core\Addons\AddonRegistry;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AddonRegistry $addons): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'roleCounts' => collect(UserRole::all())
                ->mapWithKeys(fn (string $role) => [$role => User::where('role', $role)->count()]),
            'addonCards' => $addons->dashboardCardsFor(auth()->user())->groupBy(fn (array $card) => $card['addon']->slug),
            'addons' => $addons->enabled(),
        ]);
    }
}

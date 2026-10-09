<?php

namespace App\Http\Controllers\Admin;

use App\Core\Addons\AddonRegistry;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TenantMembership;
use App\Core\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AddonRegistry $addons): View
    {
        $context = app(TenantContext::class);
        if ($context->isScopedRequest()) {
            $totalUsers = TenantMembership::query()
                ->where('tenant_id', $context->id())
                ->where('status', 'active')
                ->distinct('user_id')
                ->count('user_id');
            $roleCounts = TenantMembership::query()
                ->where('tenant_id', $context->id())
                ->where('status', 'active')
                ->select('role', DB::raw('COUNT(DISTINCT user_id) as aggregate'))
                ->groupBy('role')
                ->pluck('aggregate', 'role');
        } else {
            $totalUsers = User::query()->where('status', 'active')->count();
            $roleCounts = User::query()
                ->where('status', 'active')
                ->select('role', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('role')
                ->pluck('aggregate', 'role');
        }

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'roleCounts' => $roleCounts,
            'addonCards' => $addons->dashboardCardsFor(auth()->user())->groupBy(fn (array $card) => $card['addon']->slug),
            'addons' => $addons->enabled(),
        ]);
    }
}

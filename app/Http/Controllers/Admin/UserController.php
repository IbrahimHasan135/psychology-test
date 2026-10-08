<?php

namespace App\Http\Controllers\Admin;

use App\Core\Addons\AddonRegistry;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(AddonRegistry $addons): View
    {
        $rolePermissions = DB::table('role_addon_permissions')
            ->get()
            ->groupBy('role');

        return view('admin.users.index', [
            'users' => User::query()->latest()->paginate(10),
            'roles' => UserRole::all(),
            'addonPermissions' => $addons->permissions()->groupBy('addon_name'),
            'rolePermissions' => $rolePermissions,
        ]);
    }
}

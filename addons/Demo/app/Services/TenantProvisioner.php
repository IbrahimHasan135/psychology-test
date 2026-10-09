<?php

namespace Addons\Demo\Services;

use App\Models\SitePage;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use App\Support\Audit;
use Illuminate\Support\Facades\DB;

class TenantProvisioner
{
    public function create(array $data): Tenant
    {
        return DB::transaction(function () use ($data): Tenant {
            $owner = User::query()->create([
                'name' => $data['owner_name'],
                'username' => $data['owner_username'],
                'email' => $data['owner_email'],
                // The account is a normal global user. Its tenant role lives on the membership.
                'role' => 'user',
                'password' => $data['owner_password'],
            ]);

            $tenant = Tenant::query()->create([
                'name' => $data['tenant_name'],
                'slug' => strtolower($data['tenant_slug']),
                'status' => 'active',
                'owner_user_id' => $owner->id,
            ]);

            $tenant->memberships()->create([
                'user_id' => $owner->id,
                'role' => 'super_admin',
                'role_id' => Role::query()->where('slug', 'super_admin')->value('id'),
                'status' => 'active',
                'display_name' => $owner->name,
                'joined_at' => now(),
            ]);

            SitePage::query()->withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => 'Home',
                'slug' => 'home',
                'display_mode' => 'sections',
                'is_published' => true,
            ]);

            Audit::record('tenant.created', $tenant, ['owner_user_id' => $owner->id]);

            return $tenant;
        });
    }
}

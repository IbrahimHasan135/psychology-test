<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('status')->default('active')->after('role')->index();
            $table->timestamp('last_login_at')->nullable()->after('email_verified_at')->index();
            $table->softDeletes();
            $table->index(['status', 'id'], 'users_status_id_index');
        });

        Schema::table('tenant_memberships', function (Blueprint $table): void {
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
            $table->index(['user_id', 'status'], 'tenant_memberships_user_status_index');
            $table->index(['tenant_id', 'status'], 'tenant_memberships_tenant_status_index');
            $table->index(['tenant_id', 'role_id', 'status'], 'tenant_memberships_tenant_role_status_index');
        });

        Schema::table('role_addon_permissions', function (Blueprint $table): void {
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
            $table->index(['tenant_id', 'role_id', 'permission'], 'role_permissions_scope_index');
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('action', 120);
            $table->string('target_type', 180)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('metadata_json')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['tenant_id', 'created_at'], 'audit_logs_tenant_created_index');
            $table->index(['actor_user_id', 'created_at'], 'audit_logs_actor_created_index');
            $table->index(['target_type', 'target_id'], 'audit_logs_target_index');
        });

        $this->backfillRoleIds();
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');

        Schema::table('role_addon_permissions', function (Blueprint $table): void {
            $table->dropIndex('role_permissions_scope_index');
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::table('tenant_memberships', function (Blueprint $table): void {
            $table->dropIndex('tenant_memberships_user_status_index');
            $table->dropIndex('tenant_memberships_tenant_status_index');
            $table->dropIndex('tenant_memberships_tenant_role_status_index');
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_status_id_index');
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'last_login_at', 'deleted_at']);
        });
    }

    private function backfillRoleIds(): void
    {
        DB::table('tenant_memberships')->orderBy('id')->eachById(function (object $membership): void {
            $roleId = DB::table('roles')
                ->where('slug', $membership->role)
                ->where(function ($query) use ($membership): void {
                    $query->where('tenant_id', $membership->tenant_id)->orWhereNull('tenant_id');
                })
                ->orderByRaw('tenant_id IS NULL')
                ->value('id');

            if ($roleId) {
                DB::table('tenant_memberships')->where('id', $membership->id)->update(['role_id' => $roleId]);
            }
        });

        DB::table('role_addon_permissions')->orderBy('id')->eachById(function (object $permission): void {
            $roleId = DB::table('roles')
                ->where('slug', $permission->role)
                ->where(function ($query) use ($permission): void {
                    $query->where('tenant_id', $permission->tenant_id)->orWhereNull('tenant_id');
                })
                ->orderByRaw('tenant_id IS NULL')
                ->value('id');

            if ($roleId) {
                DB::table('role_addon_permissions')->where('id', $permission->id)->update(['role_id' => $roleId]);
            }
        });
    }
};

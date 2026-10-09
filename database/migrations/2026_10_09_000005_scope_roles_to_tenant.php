<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->dropUnique(['slug']);
            $table->unique(['tenant_id', 'slug'], 'roles_tenant_slug_unique');
        });

        Schema::table('role_addon_permissions', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->dropUnique('role_addon_permission_unique');
            $table->unique(['tenant_id', 'role', 'addon_slug', 'permission'], 'tenant_role_addon_permission_unique');
        });

        Schema::table('role_creatable_roles', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->dropUnique('role_creatable_unique');
            $table->unique(['tenant_id', 'role_slug', 'creatable_role_slug'], 'tenant_role_creatable_unique');
        });
    }

    public function down(): void
    {
        Schema::table('role_creatable_roles', function (Blueprint $table): void {
            $table->dropUnique('tenant_role_creatable_unique');
            $table->unique(['role_slug', 'creatable_role_slug'], 'role_creatable_unique');
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('role_addon_permissions', function (Blueprint $table): void {
            $table->dropUnique('tenant_role_addon_permission_unique');
            $table->unique(['role', 'addon_slug', 'permission'], 'role_addon_permission_unique');
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('roles', function (Blueprint $table): void {
            $table->dropUnique('roles_tenant_slug_unique');
            $table->unique('slug');
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_addons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('addon_slug');
            $table->string('status')->default('active');
            $table->json('settings_json')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'addon_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_addons');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_blocks')) {
            return;
        }

        Schema::create('site_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_page_id')->constrained('site_pages')->cascadeOnDelete();
            $table->string('block_uid', 80);
            $table->string('type', 80);
            $table->boolean('nav_enabled')->default(false);
            $table->string('nav_label', 120)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('data_json');
            $table->timestamps();
            $table->unique(['site_page_id', 'block_uid']);
            $table->index(['site_page_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_blocks');
    }
};

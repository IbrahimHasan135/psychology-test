<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_pages')) {
            Schema::create('site_pages', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('display_mode')->default('sections');
                $table->boolean('is_published')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('site_sections')) {
            Schema::create('site_sections', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('site_page_id')->constrained('site_pages')->cascadeOnDelete();
                $table->string('title');
                $table->string('anchor')->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('site_cards')) {
            Schema::create('site_cards', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('site_section_id')->constrained('site_sections')->cascadeOnDelete();
                $table->string('template')->default('feature');
                $table->string('title');
                $table->text('body')->nullable();
                $table->string('image_url')->nullable();
                $table->string('image_position')->default('left');
                $table->string('button_label')->nullable();
                $table->string('button_url')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_cards');
        Schema::dropIfExists('site_sections');
        Schema::dropIfExists('site_pages');
    }
};
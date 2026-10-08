<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SitePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'display_mode',
        'is_published',
        'builder_initialized',
        'template_id',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'builder_initialized' => 'boolean',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(SiteSection::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeSections(): HasMany
    {
        return $this->sections()->where('is_active', true);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(SiteBlock::class, 'site_page_id')->orderBy('sort_order')->orderBy('id');
    }
}

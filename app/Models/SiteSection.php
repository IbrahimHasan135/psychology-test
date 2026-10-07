<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_page_id',
        'title',
        'anchor',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(SitePage::class, 'site_page_id');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(SiteCard::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeCards(): HasMany
    {
        return $this->cards()->where('is_active', true);
    }
}
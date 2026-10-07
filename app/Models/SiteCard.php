<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteCard extends Model
{
    use HasFactory;

    public const TEMPLATES = [
        'hero' => 'Hero Banner',
        'feature' => 'Feature Card',
        'media' => 'Media Split',
        'compact' => 'Compact Info',
        'stat' => 'Stat Highlight',
        'quote' => 'Quote Card',
        'cta' => 'Call To Action',
    ];

    public const IMAGE_POSITIONS = [
        'left' => 'Gambar kiri',
        'right' => 'Gambar kanan',
        'top' => 'Gambar atas',
    ];

    protected $fillable = [
        'site_section_id',
        'template',
        'title',
        'body',
        'image_url',
        'image_position',
        'button_label',
        'button_url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(SiteSection::class, 'site_section_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteBlock extends Model
{
    protected $fillable = ['site_page_id', 'block_uid', 'type', 'nav_enabled', 'nav_label', 'sort_order', 'data_json'];

    protected function casts(): array
    {
        return ['nav_enabled' => 'boolean', 'data_json' => 'array'];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(SitePage::class, 'site_page_id');
    }
}

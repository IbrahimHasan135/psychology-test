<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Core\Tenancy\TenantContext;

class SitePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'display_mode',
        'is_published',
        'builder_initialized',
        'builder_version',
        'template_id',
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'builder_initialized' => 'boolean',
            'builder_version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $context = app(TenantContext::class);
            if ($context->id()) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', $context->id());
            }
        });

        static::creating(function (self $page): void {
            $page->tenant_id ??= app(TenantContext::class)->id();
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
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

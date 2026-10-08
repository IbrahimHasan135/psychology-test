<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_system',
        'is_admin',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    public function creatableRoles(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'role_creatable_roles', 'role_slug', 'creatable_role_slug', 'slug', 'slug');
    }
}

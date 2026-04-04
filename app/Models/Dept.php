<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dept extends Model
{
    protected $fillable = ['name', 'parent_id'];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Dept::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Dept::class, 'parent_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeFiltered(Builder $query, string $search): Builder
    {
        return $query->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"));
    }
}

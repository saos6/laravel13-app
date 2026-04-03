<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dept extends Model
{
    protected $fillable = ['name', 'parent_id', 'is_deleted'];

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

    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
}

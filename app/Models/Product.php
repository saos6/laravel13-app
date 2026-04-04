<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'code', 'name', 'name_kana', 'category', 'spec', 'maker',
        'jan_code', 'unit', 'price', 'cost', 'tax_rate',
        'has_stock', 'status', 'remarks',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'has_stock' => 'boolean',
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    public const CATEGORIES = [
        'electronics' => '電子機器',
        'food' => '食品・飲料',
        'clothing' => '衣類・アパレル',
        'furniture' => '家具・インテリア',
        'stationery' => '文具・事務用品',
        'tools' => '工具・機械',
        'materials' => '化学・素材',
        'other' => 'その他',
    ];

    public const TAX_RATES = [
        '0' => '0%',
        '8' => '8%（軽減）',
        '10' => '10%',
    ];

    public const STATUSES = [
        'active' => '有効',
        'discontinued' => '廃盤',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeFiltered(Builder $query, string $search): Builder
    {
        return $query->when($search !== '', function ($q) use ($search) {
            $q->where(function ($q2) use ($search) {
                $q2->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('name_kana', 'like', "%{$search}%")
                    ->orWhere('maker', 'like', "%{$search}%")
                    ->orWhere('jan_code', 'like', "%{$search}%");
            });
        });
    }
}

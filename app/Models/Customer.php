<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $fillable = [
        'code', 'name', 'name_kana', 'postal_code', 'address',
        'phone', 'fax', 'email', 'employee_id',
        'closing_day', 'payment_cycle', 'payment_day', 'remarks',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'closing_day' => 'integer',
        'payment_day' => 'integer',
    ];

    public const PAYMENT_CYCLES = [
        'monthly'   => '月払い',
        'bimonthly' => '隔月払い',
        'quarterly' => '四半期払い',
        'annually'  => '年払い',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

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
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        });
    }

    public function getPaymentCycleLabelAttribute(): string
    {
        return self::PAYMENT_CYCLES[$this->payment_cycle] ?? '';
    }

    public function getClosingDayLabelAttribute(): string
    {
        if ($this->closing_day === null) return '';
        return $this->closing_day === 31 ? '末日' : "{$this->closing_day}日";
    }

    public function getPaymentDayLabelAttribute(): string
    {
        if ($this->payment_day === null) return '';
        return $this->payment_day === 31 ? '末日' : "{$this->payment_day}日";
    }
}

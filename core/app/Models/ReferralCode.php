<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralCode extends Model
{
    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'user_id',
        'referral_code',
        'discount_percent',
        'cashback_percent',
        'status',
        'total_usage',
        'total_earned_cashback',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'cashback_percent' => 'decimal:2',
        'status' => 'integer',
        'total_usage' => 'integer',
        'total_earned_cashback' => 'decimal:2',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ReferralTransaction::class);
    }

    public function isActive(): bool
    {
        return (int) $this->status === self::STATUS_ACTIVE;
    }
}

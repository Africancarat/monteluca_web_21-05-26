<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventorySoftLock extends Model
{
    public const STATUS_RESERVED = 'reserved';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_RELEASED = 'released';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'item_id',
        'variant_key',
        'user_id',
        'session_id',
        'qty',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'qty' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActiveReserved($query)
    {
        return $query->where('status', self::STATUS_RESERVED)
            ->where('expires_at', '>', now());
    }

    public static function variantKeyForItem(): string
    {
        return (string) config('inventory.variant_key_item', 'item');
    }

    public static function variantKeyForOption(int $optionId): string
    {
        return 'option:' . $optionId;
    }
}

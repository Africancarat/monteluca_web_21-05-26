<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    const TYPE_VIRTUAL     = 'virtual';
    const TYPE_STORE_VISIT = 'store_visit';

    protected $fillable = [
        'slot_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'event_type',
        'booking_type',
        'store_location',
        'meeting_url',
        'google_event_id',
        'starts_at',
        'status',
        'notes',
        'preparation_notes',
        'outcome_notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
    ];

    public function slot()
    {
        return $this->belongsTo(ConsultantSlot::class, 'slot_id');
    }

    public function isVirtual(): bool
    {
        return $this->booking_type === self::TYPE_VIRTUAL;
    }

    public function isStoreVisit(): bool
    {
        return $this->booking_type === self::TYPE_STORE_VISIT;
    }
}

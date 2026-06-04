<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultantSlot extends Model
{
    protected $fillable = [
        'slot_date',
        'slot_time',
        'is_booked',
    ];

    protected $casts = [
        'slot_date' => 'date',
        'is_booked' => 'boolean',
    ];

    public function appointment()
    {
        return $this->hasOne(Appointment::class, 'slot_id');
    }
}

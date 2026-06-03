<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoldAttribute extends Model
{
    protected $fillable = [
        'item_id',
        'metal_type',
        'gold_karat',
        'certificate_number',
        'certificate_pdf',
        'certificate_image',
        'status'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
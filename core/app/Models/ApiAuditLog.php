<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiAuditLog extends Model
{
    protected $fillable = [
        'endpoint',
        'method',
        'actor_id',
        'ip',
        'status_code',
        'latency_ms',
        'request_hash',
        'at',
    ];

    protected $casts = [
        'at' => 'datetime',
    ];
}

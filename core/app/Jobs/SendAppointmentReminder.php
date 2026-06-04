<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAppointmentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param string $type '24h' or '1h'
     */
    public function __construct(
        public readonly Appointment $appointment,
        public readonly string $type
    ) {}

    public function handle(WhatsAppService $whatsApp): void
    {
        // Skip if appointment was cancelled before the reminder fired.
        if ($this->appointment->fresh()?->status === 'cancelled') {
            return;
        }

        // WhatsAppService::sendReminder routes internally based on booking_type.
        $whatsApp->sendReminder($this->appointment, $this->type);
    }
}

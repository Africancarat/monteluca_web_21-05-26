<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment) {}

    public function envelope(): Envelope
    {
        $type = $this->appointment->isVirtual() ? 'Virtual' : 'Store Visit';

        return new Envelope(
            subject: "New {$type} Booking — {$this->appointment->guest_name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-alert',
        );
    }
}

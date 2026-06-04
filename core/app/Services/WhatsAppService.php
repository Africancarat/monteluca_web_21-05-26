<?php

namespace App\Services;

use App\Models\Appointment;
use Exception;
use Twilio\Rest\Client as TwilioClient;

class WhatsAppService
{
    private ?TwilioClient $twilio;
    private string $from;
    private string $consultantNumber;
    private bool $configured;

    public function __construct()
    {
        $sid   = config('services.twilio.sid', '');
        $token = config('services.twilio.token', '');

        $this->configured       = $sid !== '' && $token !== '';
        $this->from             = config('services.twilio.from', '');
        $this->consultantNumber = config('services.store.consultant_phone', '');
        $this->twilio           = $this->configured ? new TwilioClient($sid, $token) : null;
    }

    // ────────────────────────────────────────────────────────────────────────
    // Virtual booking
    // ────────────────────────────────────────────────────────────────────────

    public function sendConfirmation(Appointment $appointment): void
    {
        try {
            $to   = 'whatsapp:' . ltrim($appointment->guest_phone, 'whatsapp:');
            $date = $appointment->starts_at->format('D d M Y');
            $time = $appointment->starts_at->format('H:i');

            $body = "Hi {$appointment->guest_name}! Your {$appointment->event_type} consultation with African Carat is confirmed.\n\n"
                  . "Date: {$date}\n"
                  . "Time: {$time}\n"
                  . "Join via Google Meet: {$appointment->meeting_url}\n\n"
                  . "We look forward to speaking with you — African Carat";

            $this->send($to, $body);
        } catch (Exception $e) {
            logger()->error('WhatsAppService::sendConfirmation failed', ['error' => $e->getMessage()]);
        }
    }

    public function sendConsultantAlert(Appointment $appointment): void
    {
        try {
            if (! $this->consultantNumber) {
                return;
            }

            $date = $appointment->starts_at->format('D d M Y');
            $time = $appointment->starts_at->format('H:i');
            $type = $appointment->isStoreVisit() ? 'Store visit' : 'Virtual';

            $body = "New {$type} booking [{$appointment->event_type}]\n\n"
                  . "Guest: {$appointment->guest_name}\n"
                  . "Phone: {$appointment->guest_phone}\n"
                  . "Email: {$appointment->guest_email}\n"
                  . "Date: {$date} at {$time}\n";

            if ($appointment->isVirtual() && $appointment->meeting_url) {
                $body .= "Meet: {$appointment->meeting_url}\n";
            } else {
                $body .= "Location: " . $this->storeAddress() . "\n";
            }

            $this->send('whatsapp:' . ltrim($this->consultantNumber, 'whatsapp:'), $body);
        } catch (Exception $e) {
            logger()->error('WhatsAppService::sendConsultantAlert failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Routes reminder to the correct method based on booking type.
     *
     * @param string $type '24h' or '1h'
     */
    public function sendReminder(Appointment $appointment, string $type): void
    {
        if ($appointment->isStoreVisit()) {
            $this->sendStoreVisitReminder($appointment, $type);
        } else {
            $this->sendVirtualReminder($appointment, $type);
        }
    }

    private function sendVirtualReminder(Appointment $appointment, string $type): void
    {
        try {
            $to   = 'whatsapp:' . ltrim($appointment->guest_phone, 'whatsapp:');
            $time = $appointment->starts_at->format('H:i');

            if ($type === '24h') {
                $body = "Hi {$appointment->guest_name}, reminder that your African Carat consultation is tomorrow at {$time}.\n"
                      . "Join via Google Meet: {$appointment->meeting_url}\n"
                      . "See you soon — African Carat";
            } else {
                $body = "Hi {$appointment->guest_name}, your African Carat consultation is in 1 hour at {$time}.\n"
                      . "Join via Google Meet: {$appointment->meeting_url}\n"
                      . "We look forward to speaking with you — African Carat";
            }

            $this->send($to, $body);
        } catch (Exception $e) {
            logger()->error('WhatsAppService::sendVirtualReminder failed', ['error' => $e->getMessage()]);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Store visit booking
    // ────────────────────────────────────────────────────────────────────────

    public function sendStoreVisitConfirmation(Appointment $appointment): void
    {
        try {
            $to      = 'whatsapp:' . ltrim($appointment->guest_phone, 'whatsapp:');
            $date    = $appointment->starts_at->format('D d M Y');
            $time    = $appointment->starts_at->format('H:i');
            $address = $this->storeAddress();

            $body = "Hi {$appointment->guest_name}! Your {$appointment->event_type} store visit is confirmed.\n\n"
                  . "Date: {$date}\n"
                  . "Time: {$time}\n"
                  . "Location: {$address}\n\n"
                  . "Please bring any reference images, inspiration photos, or existing jewellery pieces you would like us to look at.\n\n"
                  . "We look forward to meeting you — African Carat";

            $this->send($to, $body);
        } catch (Exception $e) {
            logger()->error('WhatsAppService::sendStoreVisitConfirmation failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @param string $type '24h' or '1h'
     */
    public function sendStoreVisitReminder(Appointment $appointment, string $type): void
    {
        try {
            $to      = 'whatsapp:' . ltrim($appointment->guest_phone, 'whatsapp:');
            $time    = $appointment->starts_at->format('H:i');
            $address = $this->storeAddress();

            if ($type === '24h') {
                $body = "Hi {$appointment->guest_name}, reminder that your store visit is tomorrow at {$time}.\n"
                      . "Location: {$address}\n"
                      . "See you soon — African Carat";
            } else {
                $body = "Hi {$appointment->guest_name}, your store visit is in 1 hour at {$time}.\n"
                      . "Location: {$address}\n"
                      . "We look forward to seeing you — African Carat";
            }

            $this->send($to, $body);
        } catch (Exception $e) {
            logger()->error('WhatsAppService::sendStoreVisitReminder failed', ['error' => $e->getMessage()]);
        }
    }

    public function sendCancellation(Appointment $appointment): void
    {
        try {
            $to   = 'whatsapp:' . ltrim($appointment->guest_phone, 'whatsapp:');
            $date = $appointment->starts_at->format('D d M Y');
            $time = $appointment->starts_at->format('H:i');

            $body = "Hi {$appointment->guest_name}, your African Carat consultation on {$date} at {$time} has been cancelled.\n"
                  . "Please contact us to rebook — African Carat";

            $this->send($to, $body);
        } catch (Exception $e) {
            logger()->error('WhatsAppService::sendCancellation failed', ['error' => $e->getMessage()]);
        }
    }

    // ────────────────────────────────────────────────────────────────────────

    private function send(string $to, string $body): void
    {
        if (! $this->configured) {
            logger()->info('WhatsApp skipped — no Twilio credentials', ['to' => $to]);
            return;
        }
        $this->twilio->messages->create($to, ['from' => $this->from, 'body' => $body]);
    }

    private function storeAddress(): string
    {
        $parts = array_filter([
            config('services.store.address'),
            config('services.store.city'),
        ]);

        return implode(', ', $parts);
    }
}

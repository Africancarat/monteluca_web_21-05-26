<?php

namespace App\Services;

use App\Models\Appointment;
use Exception;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\ConferenceSolutionKey;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Str;

class GoogleMeetService
{
    private Client $client;

    public function __construct()
    {
        $credentialsPath = config('services.google.service_account_json');

        if (! file_exists($credentialsPath)) {
            throw new Exception(
                "Google service account credentials not found at {$credentialsPath}"
            );
        }

        $this->client = new Client();
        $this->client->setAuthConfig($credentialsPath);
        $this->client->addScope(Calendar::CALENDAR_EVENTS);
        $this->client->setSubject(config('services.store.email', ''));
    }

    /**
     * Creates a Google Calendar event with a Meet link.
     *
     * Returns ['meet_url' => string, 'event_id' => string].
     */
    public function createMeeting(Appointment $appointment): array
    {
        $service = new Calendar($this->client);

        $event = new Event([
            'summary'         => "Consultation: {$appointment->guest_name} — {$appointment->event_type}",
            'description'     => "African Carat virtual consultation.\nGuest: {$appointment->guest_name}\nPhone: {$appointment->guest_phone}",
            'start'           => new EventDateTime([
                'dateTime' => $appointment->starts_at->toRfc3339String(),
                'timeZone' => config('app.timezone', 'UTC'),
            ]),
            'end'             => new EventDateTime([
                'dateTime' => $appointment->starts_at->addHour()->toRfc3339String(),
                'timeZone' => config('app.timezone', 'UTC'),
            ]),
            'conferenceData'  => new ConferenceData([
                'createRequest' => new CreateConferenceRequest([
                    'requestId'             => Str::uuid()->toString(),
                    'conferenceSolutionKey' => new ConferenceSolutionKey(['type' => 'hangoutsMeet']),
                ]),
            ]),
            'attendees' => [
                ['email' => $appointment->guest_email],
            ],
        ]);

        $calendarId = config('services.google.calendar_id', 'primary');

        $createdEvent = $service->events->insert($calendarId, $event, [
            'conferenceDataVersion' => 1,
            'sendUpdates'           => 'all',
        ]);

        $meetUrl = $createdEvent->getConferenceData()?->getEntryPoints()[0]?->getUri() ?? '';

        return [
            'meet_url' => $meetUrl,
            'event_id' => $createdEvent->getId(),
        ];
    }

    /**
     * Cancels (deletes) a Google Calendar event.
     */
    public function cancelMeeting(Appointment $appointment): void
    {
        if (! $appointment->google_event_id) {
            return;
        }

        $service    = new Calendar($this->client);
        $calendarId = config('services.google.calendar_id', 'primary');

        try {
            $service->events->delete($calendarId, $appointment->google_event_id, [
                'sendUpdates' => 'all',
            ]);
        } catch (Exception $e) {
            // Event may already be deleted; log and continue.
            logger()->warning('GoogleMeetService: failed to cancel event', [
                'event_id' => $appointment->google_event_id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}

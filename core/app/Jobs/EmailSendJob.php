<?php

namespace App\Jobs;

use App\Helpers\EmailHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EmailSendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $emailData;
    public $type;
    public function __construct(array $emailData, string $type = null)
    {
        $this->emailData = $emailData;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $email = new EmailHelper();
        $ok = $this->type === 'template'
            ? $email->sendTemplateMail($this->emailData)
            : $email->sendCustomMail($this->emailData);

        if ($ok) {
            Log::info('Queued email sent', [
                'to' => $this->emailData['to'] ?? null,
                'type' => $this->emailData['type'] ?? $this->type,
                'order_id' => $this->emailData['order_id'] ?? null,
            ]);

            return;
        }

        Log::warning('Queued email failed', [
            'to' => $this->emailData['to'] ?? null,
            'type' => $this->emailData['type'] ?? $this->type,
            'order_id' => $this->emailData['order_id'] ?? null,
        ]);
    }
}

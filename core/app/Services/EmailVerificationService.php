<?php

namespace App\Services;

use App\Helpers\EmailHelper;
use App\Jobs\EmailSendJob;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class EmailVerificationService
{
    public function signedVerificationUrl(User $user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }

    public function send(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return true;
        }

        $setting = Setting::first();
        $siteTitle = $setting?->title ?? config('app.name', 'Store');
        $url = $this->signedVerificationUrl($user);
        $minutes = config('verification.expire', 60);

        $emailData = [
            'to' => $user->email,
            'subject' => __('Welcome to Monte Luca – Confirm Your Account'),
            'body' => view('emails.verify-email', [
                'user' => $user,
                'url' => $url,
                'siteTitle' => $siteTitle,
                'expireMinutes' => $minutes,
            ])->render(),
        ];

        try {
            if ($setting && (int) $setting->is_queue_enabled === 1) {
                dispatch(new EmailSendJob($emailData));
            } else {
                (new EmailHelper())->sendCustomMail($emailData);
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to queue/send verification email', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sendWelcomeTemplate(User $user): void
    {
        $setting = Setting::first();

        if (! $setting) {
            return;
        }

        $emailData = [
            'to' => $user->email,
            'type' => 'Registration',
            'user_name' => $user->displayName(),
            'order_cost' => '',
            'transaction_number' => '',
            'site_title' => $setting->title,
        ];

        try {
            if ((int) $setting->is_queue_enabled === 1) {
                dispatch(new EmailSendJob($emailData, 'template'));
            } else {
                (new EmailHelper())->sendTemplateMail($emailData);
            }
        } catch (\Throwable $e) {
            Log::warning('Welcome email after verification failed', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}

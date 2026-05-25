<?php

namespace App\Support;

use App\Models\Setting;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Applies database SMTP settings to PHPMailer (Gmail, Hostinger, Zoho, etc.).
 */
final class MailConfigurator
{
    public static function applyTo(PHPMailer $mail, ?Setting $setting = null): bool
    {
        $setting ??= Setting::first();

        if (! $setting || (int) $setting->smtp_check !== 1) {
            return false;
        }

        $host = trim((string) $setting->email_host);
        $user = trim((string) $setting->email_user);
        $pass = (string) $setting->email_pass;
        $port = (int) ($setting->email_port ?: 587);

        if ($host === '' || $user === '') {
            return false;
        }

        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $user;
        $mail->Password = $pass;
        $mail->Port = $port > 0 ? $port : 587;
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 30;
        $mail->SMTPKeepAlive = false;

        $encryption = strtolower(trim((string) $setting->email_encryption));

        if ($encryption === 'ssl' || $port === 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($encryption === 'tls' || $encryption === 'starttls' || $port === 587) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        }

        return true;
    }
}

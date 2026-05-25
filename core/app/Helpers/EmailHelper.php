<?php



namespace App\Helpers;



use App\Models\EmailTemplate;

use App\Models\Setting;

use App\Support\MailConfigurator;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Session;

use PHPMailer\PHPMailer\Exception;

use PHPMailer\PHPMailer\PHPMailer;



class EmailHelper

{

    public PHPMailer $mail;



    public ?Setting $setting;



    public function __construct()

    {

        $this->setting = Setting::first();

        $this->mail = new PHPMailer(true);

        MailConfigurator::applyTo($this->mail, $this->setting);

    }



    /**

     * @return bool True when the message was accepted for delivery.

     */

    public function sendTemplateMail(array $emailData): bool

    {

        $template = EmailTemplate::whereType($emailData['type'])->first();



        if (! $template || ! $this->setting) {

            Log::warning('Email template or settings missing', ['type' => $emailData['type'] ?? null]);



            return false;

        }



        try {

            $email_body = preg_replace('/{user_name}/', $emailData['user_name'], $template->body);

            $email_body = preg_replace('/{order_cost}/', $emailData['order_cost'], $email_body);

            $email_body = preg_replace('/{transaction_number}/', $emailData['transaction_number'], $email_body);

            $email_body = preg_replace('/{site_title}/', $this->setting->title, $email_body);



            $this->prepareMessage(

                $emailData['to'],

                $template->subject,

                $email_body

            );



            $this->mail->send();



            if ($this->setting->order_mail == 1) {

                $this->adminMail($emailData);

            }



            return true;

        } catch (Exception $e) {

            Log::error('Template mail failed', [

                'type' => $emailData['type'] ?? null,

                'to' => $this->maskEmail($emailData['to'] ?? ''),

                'message' => $e->getMessage(),

            ]);



            return false;

        } finally {

            $this->resetMailer();

        }

    }



    /**

     * @return bool True when the message was accepted for delivery.

     */

    public function sendCustomMail(array $emailData): bool

    {

        if (empty($emailData['to']) || empty($emailData['subject'])) {

            return false;

        }



        try {

            $this->prepareMessage(

                $emailData['to'],

                $emailData['subject'],

                $emailData['body'] ?? ''

            );



            $this->mail->send();



            return true;

        } catch (Exception $e) {

            Log::error('Custom mail failed', [

                'to' => $this->maskEmail($emailData['to']),

                'message' => $e->getMessage(),

            ]);



            return false;

        } finally {

            $this->resetMailer();

        }

    }



    public static function getEmail()

    {

        $user = Auth::user();

        if (isset($user)) {

            return $user->email;

        }



        return Session::get('billing_address')['bill_email'] ?? null;

    }



    public function adminMail(array $emailData): bool

    {

        $template = EmailTemplate::whereType('New Order Admin')->first();



        if (! $template || ! $this->setting || empty($this->setting->contact_email)) {

            return false;

        }



        try {

            $email_body = preg_replace('/{user_name}/', $emailData['user_name'], $template->body);

            $email_body = preg_replace('/{order_cost}/', $emailData['order_cost'], $email_body);

            $email_body = preg_replace('/{transaction_number}/', $emailData['transaction_number'], $email_body);

            $email_body = preg_replace('/{site_title}/', $this->setting->title, $email_body);



            $this->mail->clearAddresses();

            $this->prepareMessage(

                $this->setting->contact_email,

                $template->subject,

                $email_body

            );



            $this->mail->send();



            return true;

        } catch (\Throwable $e) {

            Log::error('Admin mail failed', ['message' => $e->getMessage()]);



            return false;

        } finally {

            $this->resetMailer();

        }

    }



    protected function prepareMessage(string $to, string $subject, string $body): void

    {

        if (! $this->setting) {

            throw new Exception('Mail settings are not configured.');

        }



        $this->mail->setFrom(

            $this->setting->email_from,

            $this->setting->email_from_name

        );

        $this->mail->addAddress($to);

        $this->mail->isHTML(true);

        $this->mail->Subject = $subject;

        $this->mail->Body = $body;

    }



    protected function resetMailer(): void

    {

        $this->mail->clearAddresses();

        $this->mail->clearAttachments();

        $this->mail = new PHPMailer(true);

        MailConfigurator::applyTo($this->mail, $this->setting);

    }



    protected function maskEmail(string $email): string

    {

        if (! str_contains($email, '@')) {

            return '***';

        }



        [$local, $domain] = explode('@', $email, 2);



        return substr($local, 0, 1).'***@'.$domain;

    }

}



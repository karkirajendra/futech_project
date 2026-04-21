<?php
// app/Services/MailService.php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class MailService
{
    public static function send($to, $subject, $body)
    {
        Mail::raw($body, function ($message) use ($to, $subject) {
            $message->to($to)
                    ->subject($subject);
        });
    }
}

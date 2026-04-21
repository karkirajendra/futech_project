<?php
// app/Services/OtpService.php
namespace App\Services;

use App\Models\Otp;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class OtpService
{
    public function send(string $email, string $type): void
    {
        // invalidate old OTPs
        Otp::where('email', $email)
            ->where('type', $type)
            ->where('used', false)
            ->update(['used' => true]);

        $otp = rand(100000, 999999);

        Otp::create([
            'email' => $email,
            'code' => $otp,
            'type' => $type,
            'expires_at' => now()->addMinutes(5),
        ]);

        Mail::to($email)->send(
            new OtpMail($otp, str_replace('_', ' ', $type))
        );
    }

    public function verify(string $email, string $code, string $type): bool
    {
        $otp = Otp::where([
            'email' => $email,
            'code' => $code,
            'type' => $type,
            'used' => false,
        ])->first();

        if (!$otp || $otp->expires_at->isPast()) {
            return false;
        }

        $otp->update(['used' => true]);

        return true;
    }
}

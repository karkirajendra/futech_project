<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\OtpService;



class AuthService
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    //register a new user
    public function register(array $data): User
    {
        try {

            $user = User::create([
                'firstname' => $data['firstname'] ,
                'middlename' => $data['middlename'] ?? null,
                'lastname' => $data['lastname'],
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);



            Log::info('New user registered', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return $user;

        } catch (\Exception $e) {


            Log::error('User registration failed', [
                'email' => $data['email'],
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    //Email verification
    public function verifyEmail(EmailVerificationRequest $request)
    {
        $request->fulfill();
        Log::info('Email verified', [
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
        ]);

        return [
            'message' => 'Email verified successfully',
        ];
    }

    //email resend verification
    public function resendVerification(Request $request): array
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return [
                'message' => 'Email already verified',
            ];
        }

        $user->sendEmailVerificationNotification();

        Log::info('Verification email resent', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return [
            'message' => 'Verification email resent',
        ];
    }



    //Login
    public function login(array $data): array
    {
        $credentials = $data;
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            Log::warning('Failed login attempt', [
                'email' => $credentials['email'],
            ]);

            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            return [
                'success' => false,
                'message' => 'Please verify your email address before logging in.',
                'requires_email_verification' => true,
            ];
        }

        Log::info('User logged in', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return ['success' => true, 'user' => $user];
    }
    //Logout
    public function logout(User $user): void
    {
        // Revoke all tokens
        $user->tokens()->delete();

        Log::info('User logged out', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }
    //check email
    public function sendTestEmail(string $email): void
    {
        \App\Services\MailService::send(
            $email,
            'SMTP Test Email',
            'This is a test email to confirm SMTP is working.'
        );
    }

    // Forgot password - send OTP
    public function forgotPassword(string $email): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Don't reveal if user exists for security
            return [
                'message' => 'If the email exists, a password reset OTP has been sent.',
            ];
        }

        $this->otpService->send($email, 'forgot_password');

        Log::info('Password reset OTP sent', [
            'user_id' => $user->id,
            'email' => $email,
        ]);

        return [
            'message' => 'If the email exists, a password reset OTP has been sent.',
        ];
    }

    // Reset password - verify OTP and reset
    public function resetPassword(string $email, string $otp, string $newPassword): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid email address.',
            ];
        }

        if (!$this->otpService->verify($email, $otp, 'forgot_password')) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ];
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Invalidate all tokens (force re-login)
        $user->tokens()->delete();

        Log::info('Password reset successful', [
            'user_id' => $user->id,
            'email' => $email,
        ]);

        return [
            'success' => true,
            'message' => 'Password has been reset successfully.',
        ];
    }

    // Update profile
    public function updateProfile(User $user, array $data): array
    {
        $updateData = [];
        $requiresOtp = false;

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        // Handle email update with OTP verification
        if (isset($data['email']) && $data['email'] !== $user->email) {
            // Check if email is already taken
            if (User::where('email', $data['email'])->where('id', '!=', $user->id)->exists()) {
                return [
                    'success' => false,
                    'message' => 'Email is already taken.',
                ];
            }

            // If OTP is provided, verify and update
            if (isset($data['email_otp'])) {
                if (!$this->otpService->verify($data['email'], $data['email_otp'], 'email_change')) {
                    return [
                        'success' => false,
                        'message' => 'Invalid or expired OTP for email change.',
                    ];
                }

                // OTP verified, update email
                $updateData['email'] = $data['email'];
                $updateData['email_verified_at'] = null; // Require re-verification
                $updateData['pending_email'] = null;
                $updateData['pending_email_otp_verified'] = false;

                // Send new verification email
                $user->sendEmailVerificationNotification();
            } else {
                // Store pending email and send OTP (but don't return early - update other fields)
                $updateData['pending_email'] = $data['email'];
                $updateData['pending_email_otp_verified'] = false;

                $this->otpService->send($data['email'], 'email_change');
                $requiresOtp = true;
            }
        }

        // Update user with all changes (name, password, and email if OTP provided)
        $user->update($updateData);

        Log::info('Profile updated', [
            'user_id' => $user->id,
        ]);

        $response = [
            'success' => true,
            'message' => $requiresOtp
                ? 'Profile updated. OTP sent to new email address. Please verify to complete email change.'
                : 'Profile updated successfully.',
            'user' => $user->fresh(),
        ];

        if ($requiresOtp) {
            $response['requires_otp'] = true;
        }

        return $response;
    }

}


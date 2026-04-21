<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\OtpService;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class AuthController extends Controller
{
    protected AuthService $authService;
    protected OtpService $otpService;

    public function __construct(
        AuthService $authService,
        OtpService $otpService
    ) {
        $this->authService = $authService;
        $this->otpService = $otpService;
    }


    // REGISTER

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());
            $this->otpService->send($user->email, 'email_verification');

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully. An OTP has been sent to your email for verification.',
                'data' => [
                    'user' => new UserResource($user),
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }


    // LOGIN

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => $result['data'] ?? [], // Pass any extra data like 'requires_email_verification'
            ], 401);
        }

        $user = $result['user'];
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ]);
    }


    // EMAIL VERIFICATION (OTP)

    public function sendEmailVerificationOtp(Request $request): JsonResponse
    {
        $email = $request->user() ? $request->user()->email : $request->email;

        if (!$email) {
             return response()->json([
                'success' => false,
                'message' => 'Email is required.',
            ], 422);
        }

        $this->otpService->send($email, 'email_verification');

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your email',
        ]);
    }

    public function verifyEmailWithOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string'
        ]);

        if (! $this->otpService->verify(
            $request->email,
            $request->otp,
            'email_verification'
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP',
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->markEmailAsVerified();
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully',
        ]);
    }

    // FORGOT PASSWORD (OTP)

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $this->otpService->send($request->email, 'forgot_password');

        return response()->json([
            'success' => true,
            'message' => 'Password reset OTP sent',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        if (! $this->otpService->verify(
            $request->email,
            $request->otp,
            'forgot_password'
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP',
            ], 422);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ]);
    }

    // UPDATE EMAIL (OTP)

    public function sendUpdateEmailOtp(Request $request): JsonResponse
    {
        $request->validate([
            'new_email' => 'required|email|unique:users,email',
        ]);

        $this->otpService->send($request->new_email, 'update_email');

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to new email',
        ]);
    }

    public function updateEmail(Request $request): JsonResponse
    {
        $request->validate([
            'new_email' => 'required|email',
            'otp' => 'required|string',
        ]);

        if (! $this->otpService->verify(
            $request->new_email,
            $request->otp,
            'update_email'
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP',
            ], 422);
        }

        $user = $request->user();
        $user->email = $request->new_email;
        $user->email_verified_at = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Email updated successfully',
        ]);
    }


    // LOGOUT

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
    }


    // CURRENT USER

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($request->user()),
            ],
        ]);
    }
    // PROFILE

    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|confirmed|min:8',
            'email_otp' => 'nullable|string',
        ]);

        $result = $this->authService->updateProfile($request->user(), $request->all());

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        $response = [
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'user' => new UserResource($result['user']),
            ],
        ];

        if (isset($result['requires_otp'])) {
            $response['requires_otp'] = true;
        }

        return response()->json($response);
    }
}

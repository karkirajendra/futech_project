<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\EnsureTokenIsValid;



Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now()->toIso8601String(),
    ]);
});


Route::prefix('auth')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register'])
        ->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])
        ->name('auth.login');

    // Password reset routes (public)
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:5,1')
        ->name('auth.forgot-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:5,1')
        ->name('auth.reset-password');

    // Email verification routes
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/resend', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');



    // OTP-based email verification
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('/email/send-otp', [AuthController::class, 'sendEmailVerificationOtp'])
            ->name('verification.send-otp');

        Route::post('/email/verify-otp', [AuthController::class, 'verifyEmailWithOtp'])
            ->name('verification.verify-otp');
    });

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('auth.logout');
        Route::get('/user', [AuthController::class, 'user'])
            ->name('auth.user');
        Route::put('/profile', [AuthController::class, 'updateProfile'])
            ->name('auth.update-profile');


        // Helper routes (for testing)
        Route::get('/status', [AuthController::class, 'checkStatus'])
            ->name('auth.status');
    });
});

// Public blog routes (viewable by anyone)
Route::prefix('blogs')->group(function () {
    // Public routes
    Route::get('/', [BlogController::class, 'index'])->name('blogs.index');
    Route::get('/{id}', [BlogController::class, 'show'])->name('blogs.show')->where('id', '[0-9]+');
    Route::get('/user/{userId}', [BlogController::class, 'userBlogs'])->name('blogs.user')->where('userId', '[0-9]+');

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [BlogController::class, 'store'])->name('blogs.store');
        Route::put('/{id}', [BlogController::class, 'update'])->name('blogs.update');
        Route::delete('/{id}', [BlogController::class, 'destroy'])->name('blogs.destroy');
    });
});

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint not found',
    ], 404);
});
Route::post('/test', function () {
    return response()->json(['success' => true, 'message' => 'POST works']);
});



Route::get('/test-smtp', [AuthController::class, 'testSmtp']);



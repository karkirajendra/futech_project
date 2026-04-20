<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'pending_email')) {
                $table->string('pending_email')->nullable();
            }

            if (!Schema::hasColumn('users', 'pending_email_otp_verified')) {
                $table->boolean('pending_email_otp_verified')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'pending_email')) {
                $table->dropColumn('pending_email');
            }
            if (Schema::hasColumn('users', 'pending_email_otp_verified')) {
                $table->dropColumn('pending_email_otp_verified');
            }
        });
    }
};

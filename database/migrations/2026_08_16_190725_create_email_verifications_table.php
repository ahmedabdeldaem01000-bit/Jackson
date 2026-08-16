<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_verifications', function (Blueprint $table) {
               $table->id();

            $table->string('email')->index();

            $table->string('name');

            // Password is already hashed before storing.
            $table->string('password');

            // Hashed OTP, not plain text.
            $table->string('otp');

            $table->unsignedTinyInteger('attempts')->default(0);

            $table->timestamp('expires_at');

            $table->timestamp('last_sent_at')->nullable();

            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->index(['email', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_verifications');
    }
};

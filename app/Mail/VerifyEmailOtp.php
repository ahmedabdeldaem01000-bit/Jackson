<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmailOtp extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp
    ) {
    }

    public function build()
    {
        return $this
            ->subject('رمز التحقق من البريد الإلكتروني')
            ->view('emails.verify-email-otp');
    }
}
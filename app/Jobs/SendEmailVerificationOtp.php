<?php

namespace App\Jobs;

use App\Mail\VerifyEmailOtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailVerificationOtp implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        public string $email,
        public string $otp
    ) {
    }

    public function handle(): void
    {
        Mail::to($this->email)
            ->send(
                new VerifyEmailOtp($this->otp)
            );
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Jobs\SendEmailVerificationOtp;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Form
    |--------------------------------------------------------------------------
    */

    public function showLoginForm()
    {
        return view('web.auth.login');
    }


    /*
    |--------------------------------------------------------------------------
    | Register Form
    |--------------------------------------------------------------------------
    */

    public function showRegisterForm()
    {
        return view('web.auth.signup');
    }


    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */

    public function register(RegisterRequest $request): RedirectResponse
    {
        // Don't allow creating another account for an existing email.
        if (User::where('email', $request->email)->exists()) {
            return back()
                ->withErrors([
                    'email' => 'هذا البريد الإلكتروني مستخدم بالفعل.',
                ])
                ->withInput();
        }

        // Remove old pending verification for this email.
        EmailVerification::where('email', $request->email)->delete();

        // Generate 6-digit OTP.
        $otp = (string) random_int(100000, 999999);

        // Store hashed OTP.
        $verification = EmailVerification::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'otp' => Hash::make($otp),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(5),
            'last_sent_at' => now(),
        ]);

        // Send email through queue.
        SendEmailVerificationOtp::dispatch(
            $verification->email,
            $otp
        );

        return redirect()
            ->route('verification.notice')
            ->with([
                'email' => $request->email,
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | OTP Page
    |--------------------------------------------------------------------------
    */

    public function showVerificationForm(Request $request)
    {
        $email = session('email');

        if (!$email) {
            return redirect()
                ->route('register')
                ->withErrors([
                    'email' => 'ابدأ التسجيل أولاً.',
                ]);
        }

        return view(
            'web.auth.verify-otp',
            compact('email')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verify OTP
    |--------------------------------------------------------------------------
    */

    public function verifyOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $verification = EmailVerification::where(
            'email',
            $validated['email']
        )->first();

        if (!$verification) {
            return back()
                ->withErrors([
                    'otp' => 'لا يوجد طلب تحقق لهذا البريد.',
                ])
                ->withInput();
        }

        // OTP expired.
        if ($verification->expires_at->isPast()) {
            $verification->delete();

            return back()
                ->withErrors([
                    'otp' => 'رمز التحقق انتهت صلاحيته. اطلب رمزًا جديدًا.',
                ])
                ->withInput();
        }

        // Maximum attempts.
        if ($verification->attempts >= 5) {
            $verification->delete();

            return back()
                ->withErrors([
                    'otp' => 'تم تجاوز عدد المحاولات المسموح بها. اطلب رمزًا جديدًا.',
                ]);
        }

        // Count attempt.
        $verification->increment('attempts');

        // Check hashed OTP.
        if (!Hash::check(
            $validated['otp'],
            $verification->otp
        )) {
            return back()
                ->withErrors([
                    'otp' => 'رمز التحقق غير صحيح.',
                ])
                ->withInput();
        }

        // Create user after successful verification.
        $user = DB::transaction(function () use ($verification) {

            $user = User::create([
                'name' => $verification->name,
                'email' => $verification->email,
                'password' => $verification->password,
            ]);

            $user->assignRole('customer');

            return $user;
        });

        $verification->update([
            'verified_at' => now(),
        ]);

        $verification->delete();

        // Login customer.
        Auth::guard('customer')->login($user);

        $request->session()->regenerate();

        $request->session()->forget('email');

        return redirect()
            ->intended(route('home'))
            ->with(
                'success',
                'تم إنشاء الحساب وتأكيد البريد الإلكتروني بنجاح.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Resend OTP
    |--------------------------------------------------------------------------
    */

    public function resendOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $verification = EmailVerification::where(
            'email',
            $validated['email']
        )->first();

        if (!$verification) {
            return redirect()
                ->route('register')
                ->withErrors([
                    'email' => 'لا يوجد طلب تسجيل لهذا البريد.',
                ]);
        }

        // 60-second cooldown.
        if (
            $verification->last_sent_at &&
            $verification->last_sent_at->addSeconds(60)->isFuture()
        ) {
            $seconds = now()->diffInSeconds(
                $verification->last_sent_at->addSeconds(60)
            );

            return back()->withErrors([
                'otp' => "يمكنك طلب رمز جديد بعد {$seconds} ثانية.",
            ]);
        }

        // Generate new OTP.
        $otp = (string) random_int(100000, 999999);

        // Update verification.
        $verification->update([
            'otp' => Hash::make($otp),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(5),
            'last_sent_at' => now(),
        ]);

        // Send new OTP through queue.
        SendEmailVerificationOtp::dispatch(
            $verification->email,
            $otp
        );

        return redirect()
            ->route('verification.notice')
            ->with([
                'email' => $verification->email,
                'success' => 'تم إرسال رمز تحقق جديد إلى بريدك الإلكتروني.',
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(LoginRequest $request)
    {
        if (
            !Auth::guard('customer')->attempt(
                $request->only('email', 'password'),
                $request->boolean('remember')
            )
        ) {
            return back()
                ->withErrors([
                    'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route('home'));
    }


    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(): RedirectResponse
    {
        Auth::guard('customer')->logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
    // public function show()
    // {
    //     return view('web.auth.profile');
    // }
}
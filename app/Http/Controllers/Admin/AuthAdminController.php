<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthAdminController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::guard('employee')->attempt(
            $credentials,
            $request->boolean('remember')
        )) {
            return back()
                ->withErrors([
                    'email' => 'بيانات تسجيل الدخول غير صحيحة.',
                ])
                ->withInput($request->only('email', 'remember'));
        }

        $request->session()->regenerate();

        $employee = Auth::guard('employee')->user();

        /*
        |--------------------------------------------------------------------------
        | Redirect حسب الـ Role
        |--------------------------------------------------------------------------
        */

        if ($employee->hasRole('admin')) {
            return redirect()->intended(
                route('admin.dashboard')
            );
        }

        if ($employee->hasAnyRole(['employee', 'barber'])) {
            return redirect()->intended(
                route('employee.bookings.index')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | لو مفيش Role
        |--------------------------------------------------------------------------
        */

        Auth::guard('employee')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('employee.login')
            ->withErrors([
                'email' => 'الحساب غير مصرح له بالدخول.',
            ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('employee')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('employee.login');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class EmployeeAuthController extends Controller
{
    public function showLoginForm()
    {
        // Reuse the existing login view for now. If there's a dedicated admin login view, update this.
        return view('web.auth.login');
    }

    public function login(LoginRequest $request)
    {
        if (!Auth::guard('employee')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::guard('employee')->user();

        if ($user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        }

        // employee or barber go to employee dashboard
        if ($user->hasRole('employee') || $user->hasRole('barber')) {
            return redirect()->intended(route('employee.dashboard'));
        }

        Auth::guard('employee')->logout();

        return redirect()->route('employee.login')->withErrors([
            'email' => 'لا يوجد دور مخصص لهذا الحساب.',
        ]);
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('employee')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('employee.login');
    }
}

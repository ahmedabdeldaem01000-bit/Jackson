<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Customer login form
    public function showLoginForm()
    {
        return view('web.auth.login');
    }

    // Customer register form
    public function showRegisterForm()
    {
        return view('web.auth.signup');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('customer');

        // Log in using the customer guard
        Auth::guard('customer')->login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('success', 'Account created successfully.');
    }

    public function login(LoginRequest $request)
    {
        // Authenticate explicitly using the customer guard
        if (!Auth::guard('customer')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()
                ->withErrors([
                    'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        // Redirect customers to frontend home
        return redirect()->intended(route('home'));
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('customer')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}

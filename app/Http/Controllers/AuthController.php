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

    //=======================Start Login Form====================================   
    public function showLoginForm()
    {
        return view('web.auth.login');
    }


    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('customer');
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('success', 'Account created successfully.');
    }


public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (!Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
        return back()
            ->withErrors([
                'email' => 'بيانات تسجيل الدخول غير صحيحة.',
            ])
            ->withInput();
    }

    $request->session()->regenerate();

    return redirect()->intended(route('home'));
}

public function logout(Request $request)
{
    Auth::guard('customer')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
}




    //=======================End Login Form====================================   




    //=======================Start Register Form====================================   

    public function showRegisterForm()
    {
        return view('web.auth.signup');
    }
    //=======================End Register Form====================================   

}

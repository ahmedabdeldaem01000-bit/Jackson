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

    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()
                ->withErrors([
                    'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();


        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->hasRole('barber')) {
            return redirect()->route('admin.barber');
        }
        if ($user->hasRole('customer')) {
            return redirect()->route('home');
        }

        Auth::logout();

        return redirect()->route('login')
            ->withErrors([
                'email' => 'لا يوجد دور مخصص لهذا الحساب.',
            ]);
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }




    //=======================End Login Form====================================   




    //=======================Start Register Form====================================   

    public function showRegisterForm()
    {
        return view('web.auth.signup');
    }
    //=======================End Register Form====================================   

}

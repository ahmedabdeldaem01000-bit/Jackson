<?php

namespace App\Http\Controllers\Admin;
    use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
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
            ->withInput();
    }

    $request->session()->regenerate();

    return redirect()->intended(route('admin.dashboard'));
}
public function logout(Request $request)
{
    Auth::guard('employee')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('employee.login');
}
}

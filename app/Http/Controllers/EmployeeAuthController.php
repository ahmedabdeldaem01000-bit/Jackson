<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (! Auth::guard('employee')->attempt(
            $credentials,
            $request->boolean('remember')
        )) {

            return back()->withErrors([
                'email' => 'Email or Password incorrect.',
            ]);
        }

        $request->session()->regenerate();

        $employee = Auth::guard('employee')->user();

        if ($employee->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($employee->hasRole('barber')) {
            return redirect()->route('admin.barber');
        }

        Auth::guard('employee')->logout();

        return back()->withErrors([
            'email' => 'You don\'t have permission.',
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
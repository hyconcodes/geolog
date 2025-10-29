<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class SupervisorController extends Controller
{
    /**
     * Show the supervisor registration form.
     */
    public function showRegisterForm()
    {
        // Redirect authenticated users to their dashboard
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->hasRole('superadmin')) {
                return redirect()->route('superadmin.dashboard');
            } elseif ($user->hasRole('supervisor')) {
                return redirect()->route('supervisor.dashboard');
            } else {
                return redirect()->route('student.dashboard');
            }
        }

        return view('auth.supervisor-register');
    }

    /**
     * Handle supervisor registration.
     */
    public function register(Request $request)
    {
        // Redirect authenticated users to their dashboard
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->hasRole('superadmin')) {
                return redirect()->route('superadmin.dashboard');
            } elseif ($user->hasRole('supervisor')) {
                return redirect()->route('supervisor.dashboard');
            } else {
                return redirect()->route('student.dashboard');
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                'unique:users',
                'regex:/^[a-zA-Z]+\.[a-zA-Z]+@bouesti\.edu\.ng$/'
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign supervisor role
        $user->assignRole('supervisor');

        // Log the user in
        auth()->login($user);

        return redirect()->route('supervisor.dashboard');
    }
}
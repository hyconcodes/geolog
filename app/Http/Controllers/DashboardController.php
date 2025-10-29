<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle dashboard access and redirect based on user role.
     */
    public function index()
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Redirect based on user role
        if ($user->hasRole('superadmin')) {
            return redirect()->route('superadmin.dashboard');
        } elseif ($user->hasRole('supervisor')) {
            return redirect()->route('supervisor.dashboard');
        } elseif ($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }

        // Default fallback - redirect to login if no role is assigned
        return redirect()->route('login')->with('error', 'No role assigned to your account. Please contact administrator.');
    }
}
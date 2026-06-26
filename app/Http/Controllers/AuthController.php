<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Show the login screen
    public function show()
    {
        return view('login');
    }

    // Process the login attempt
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/'); 
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    // Process logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    // One-time setup to create accounts
    public function setupAdmin()
    {
        if (User::count() == 0) {
            // 1. Master Admin Account
            User::create([
                'name' => 'Tusar Ahmmed',
                'email' => 'admin@atikrice.com',
                'password' => Hash::make('password123'),
                'role' => 'admin' // Full clearance
            ]);

            // 2. Standard Staff Account (For testing)
            User::create([
                'name' => 'Mill Staff',
                'email' => 'staff@atikrice.com',
                'password' => Hash::make('password123'),
                'role' => 'data_entry' // Restricted clearance
            ]);

            return "Master Admin and Staff accounts created! Go to /login to sign in.";
        }
        return "Accounts already exist!";
    }

    // Quick fix: Run this URL once to upgrade your existing account to Admin
    public function upgradeMe()
    {
        User::where('email', 'admin@atikrice.com')->update(['role' => 'admin']);
        return "Security Clearance Upgraded to Admin!";
    }
}
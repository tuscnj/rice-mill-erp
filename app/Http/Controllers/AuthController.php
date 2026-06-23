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
            return redirect()->intended('/'); // Send to dashboard on success
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

    // One-time setup to create your Master Admin account
    public function setupAdmin()
    {
        if (User::count() == 0) {
            User::create([
                'name' => 'Tusar Ahmmed',
                'email' => 'admin@atikrice.com',
                'password' => Hash::make('password123') // You can change this later
            ]);
            return "Master Admin created! Go to /login to sign in.";
        }
        return "Admin account already exists!";
    }
}
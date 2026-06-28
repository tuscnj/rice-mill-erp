<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('login');
    }

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

        return back()->with('error', 'The provided credentials do not match our records.')->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }

    public function setupAdmin()
    {
        if (User::where('role', 'admin')->exists()) {
            return 'An admin already exists in the database.';
        }

        User::create([
            'name' => 'System Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('123456'),
            'role' => 'admin'
        ]);

        return 'Admin created successfully! Email: admin@admin.com | Password: 123456';
    }

    public function upgradeMe()
    {
        if (Auth::check()) {
            $user = User::find(Auth::id());
            $user->role = 'admin';
            $user->save();
            return 'Your account has been upgraded to Admin!';
        }
        return 'Please login first.';
    }
}
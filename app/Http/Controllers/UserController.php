<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('users', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,data_entry'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        return redirect('/users')->with('success', 'Staff member added successfully!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:admin,data_entry'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        // Only update the password if the admin typed a new one
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        return redirect('/users')->with('success', 'Staff member updated!');
    }

    public function destroy($id)
    {
        if (auth()->id() == $id) {
            return redirect('/users')->with('error', 'Security Lock: You cannot delete your own active account.');
        }
        
        User::findOrFail($id)->delete();
        return redirect('/users')->with('success', 'Staff member removed.');
    }
}
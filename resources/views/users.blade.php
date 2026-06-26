@extends('layouts.app')
@section('title', 'Staff & Permissions')
@section('content')

    <div class="max-w-6xl mx-auto space-y-6 mt-6">
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-6 rounded-2xl text-white shadow-md flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight">Staff & Permissions</h2>
                <p class="text-slate-300 text-sm mt-1">Manage employee access and security roles</p>
            </div>
        </div>

        @if(session('error'))
            <div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200 font-bold">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                    <h3 class="font-bold text-gray-800 uppercase tracking-wider mb-4 border-b pb-2">Add New Staff</h3>
                    
                    <form action="/run-user" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Full Name</label>
                            <input type="text" name="name" required class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email / Username</label>
                            <input type="email" name="email" required class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Password</label>
                            <input type="text" name="password" required class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Security Clearance</label>
                            <select name="role" required class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="data_entry">Data Entry (Restricted)</option>
                                <option value="admin">Administrator (Full Access)</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition mt-2">Create Account</button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-gray-500">
                            <tr>
                                <th class="p-4 font-bold uppercase tracking-wider">Employee</th>
                                <th class="p-4 font-bold uppercase tracking-wider">Clearance Role</th>
                                <th class="p-4 font-bold text-right uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="p-4">
                                    <div class="font-bold text-gray-800">{{ $user->name }}</div>
                                    <div class="text-gray-500 text-xs">{{ $user->email }}</div>
                                </td>
                                <td class="p-4">
                                    @if($user->role === 'admin')
                                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider">Admin</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider">Data Entry</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <form action="/delete-user/{{ $user->id }}" method="GET" onsubmit="return confirm('Are you sure you want to permanently delete this user?');">
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">Revoke & Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

@endsection
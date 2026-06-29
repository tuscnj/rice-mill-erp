@extends('layouts.app')
@section('title', 'Staff & Permissions')
@section('content')

    <div class="max-w-6xl mx-auto space-y-6 mt-4 sm:mt-6 px-4 sm:px-0">
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-6 sm:p-8 rounded-2xl text-white shadow-lg flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sm:gap-0">
            <div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Staff & Permissions</h2>
                <p class="text-slate-300 text-sm mt-1">Manage employee access and security roles</p>
            </div>
            <div class="bg-slate-800/50 px-4 py-3 rounded-xl border border-slate-700 w-full sm:w-auto flex justify-between sm:block items-center">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider sm:mb-1">Active Accounts</p>
                <p class="text-xl font-bold text-white">{{ $users->count() }}</p>
            </div>
        </div>

        @if(session('error'))
            <div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200 font-bold shadow-sm">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200 font-bold shadow-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            
            {{-- ADD NEW STAFF FORM --}}
            <div class="lg:col-span-1">
                <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex items-center gap-2 border-b border-gray-100 pb-4 mb-6">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        <h3 class="font-bold text-gray-800 tracking-wide">Add New Staff</h3>
                    </div>
                    
                    <form action="/run-user" method="POST" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Full Name</label>
                            <input type="text" name="name" required class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Email / Username</label>
                            <input type="email" name="email" required autocomplete="new-email" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Password <span class="text-gray-400 lowercase normal-case">(Min 6 chars)</span></label>
                            <input type="password" name="password" required autocomplete="new-password" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Security Clearance</label>
                            <select name="role" required class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 outline-none cursor-pointer appearance-none">
                                <option value="data_entry">Data Entry (Restricted)</option>
                                <option value="admin">Administrator (Full Access)</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-sm transition active:scale-[0.98] mt-4">Create Account</button>
                    </form>
                </div>
            </div>

            {{-- EMPLOYEE DIRECTORY --}}
            <div class="lg:col-span-2">
                
                {{-- 🚨 MOBILE CARD LAYOUT (Hidden on Desktop) --}}
                <div class="md:hidden flex flex-col gap-4">
                    @foreach($users as $user)
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm relative overflow-hidden">
                        {{-- Color Strip Indicator (Admin = Blue, Data Entry = Slate) --}}
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $user->role === 'admin' ? 'bg-blue-500' : 'bg-slate-400' }}"></div>
                        
                        <div class="flex justify-between items-start pl-3 mb-4">
                            <div>
                                <h4 class="font-extrabold text-gray-800 text-lg">{{ $user->name }}</h4>
                                <p class="text-gray-500 text-xs font-medium mt-0.5">{{ $user->email }}</p>
                            </div>
                            <div class="shrink-0">
                                @if($user->role === 'admin')
                                    <span class="bg-blue-50 text-blue-700 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Admin
                                    </span>
                                @else
                                    <span class="bg-slate-50 text-slate-600 border border-slate-200 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Data Entry
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="pl-3 border-t border-slate-50 pt-4">
                            <form action="/delete-user/{{ $user->id }}" method="GET" class="w-full" onsubmit="return confirm('Are you sure you want to permanently delete this user?');">
                                <button type="submit" class="w-full flex justify-center items-center gap-2 text-red-600 bg-red-50 py-3 rounded-xl font-bold border border-red-100 text-sm transition active:bg-red-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Revoke Access & Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- DESKTOP TABLE (Hidden on Mobile) --}}
                <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-slate-50 border-b border-gray-200">
                            <tr>
                                <th class="p-5 font-bold text-gray-500 uppercase tracking-wider text-xs">Employee</th>
                                <th class="p-5 font-bold text-gray-500 uppercase tracking-wider text-xs">Clearance Role</th>
                                <th class="p-5 font-bold text-gray-500 uppercase tracking-wider text-xs text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($users as $user)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-5">
                                    <div class="font-extrabold text-gray-800 text-base">{{ $user->name }}</div>
                                    <div class="text-gray-500 text-xs font-medium mt-0.5">{{ $user->email }}</div>
                                </td>
                                <td class="p-5">
                                    @if($user->role === 'admin')
                                        <span class="bg-blue-50 text-blue-700 border border-blue-100 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1.5 shadow-sm">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Admin
                                        </span>
                                    @else
                                        <span class="bg-slate-50 text-slate-600 border border-slate-200 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1.5 shadow-sm">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Data Entry
                                        </span>
                                    @endif
                                </td>
                                <td class="p-5 text-right">
                                    <form action="/delete-user/{{ $user->id }}" method="GET" class="inline-block" onsubmit="return confirm('Are you sure you want to permanently delete this user?');">
                                        <button type="submit" class="text-red-600 hover:text-white font-bold text-xs bg-red-50 hover:bg-red-600 border border-red-100 hover:border-red-600 px-4 py-2 rounded-xl transition shadow-sm flex items-center gap-1.5 ml-auto">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Revoke
                                        </button>
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
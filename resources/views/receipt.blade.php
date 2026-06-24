@extends('layouts.app')
@section('title', 'Receive Money')
@section('content')

    <div class="bg-white w-full max-w-md mx-auto rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
        <div class="bg-gradient-to-r from-teal-500 to-teal-600 p-6 text-center sm:text-left sm:px-8">
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Receive Money</h2>
            <p class="text-teal-100 text-sm mt-1">Collect from customers or suppliers</p>
        </div>

        <form action="/run-receipt" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Receive Into (Bank/Cash)</label>
                <select name="cash_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 focus:bg-white transition-all duration-200 cursor-pointer appearance-none">
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Receive From (Party)</label>
                <select name="party_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 focus:bg-white transition-all duration-200 cursor-pointer appearance-none">
                    <option value="" disabled selected>Select Party...</option>
                    @foreach($parties as $party)
                        <option value="{{ $party->id }}">{{ $party->name }} (Balance: ৳{{ number_format($party->balance, 2) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="bg-teal-50/50 p-5 rounded-2xl border-2 border-teal-100">
                <label class="block text-xs font-bold text-teal-800 uppercase tracking-wider mb-3 text-center">Amount Received (৳)</label>
                <input type="number" step="0.01" name="amount" required class="w-full text-3xl p-4 border-2 border-teal-200 rounded-xl focus:ring-4 focus:ring-teal-500/20 focus:border-teal-500 text-center font-bold text-teal-600 bg-white placeholder-teal-200 transition-all duration-200" placeholder="0.00">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Reference / Cheque Number</label>
                <input type="text" name="receipt_number" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 focus:bg-white transition-all duration-200" placeholder="Optional">
            </div>

            <hr class="border-gray-200 mt-2 mb-2">

            <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold text-lg py-4 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 active:scale-[0.99] flex justify-center items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Confirm Receipt
            </button>
        </form>
    </div>
@endsection
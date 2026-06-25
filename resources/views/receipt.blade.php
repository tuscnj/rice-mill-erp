@extends('layouts.app')
@section('title', 'Receive Money')
@section('content')

    <div class="max-w-4xl mx-auto mt-8 bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Header Panel -->
        <div class="bg-teal-600 p-6">
            <h2 class="text-2xl font-bold text-white tracking-tight">Receive Money</h2>
            <p class="text-teal-100 text-sm mt-1">Collect from customers or suppliers</p>
        </div>

        <form action="/run-receipt" method="POST" class="p-6 sm:p-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Transaction Date (Spans Full Width) -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Transaction Date</label>
                    <input type="date" name="voucher_date" value="{{ date('Y-m-d') }}" 
                           class="w-full p-2.5 border border-gray-300 rounded-md focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                </div>

                <!-- Receive Into -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Receive Into (Bank/Cash)</label>
                    <select name="cash_id" required class="w-full p-2.5 bg-white border border-gray-300 rounded-md focus:border-teal-500 focus:ring-1 focus:ring-teal-500 cursor-pointer">
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Receive From -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Receive From (Party)</label>
                    <select name="party_id" required class="w-full p-2.5 bg-white border border-gray-300 rounded-md focus:border-teal-500 focus:ring-1 focus:ring-teal-500 cursor-pointer">
                        <option value="" disabled selected>Select Party...</option>
                        @foreach($parties as $party)
                            <option value="{{ $party->id }}">{{ $party->name }} (Balance: ৳{{ number_format($party->balance, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Amount Received -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Amount Received (৳)</label>
                    <input type="number" step="0.01" name="amount" required 
                           class="w-full p-2.5 border border-gray-300 rounded-md focus:border-teal-500 focus:ring-1 focus:ring-teal-500" placeholder="0.00">
                </div>

                <!-- Reference / Cheque Number -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Reference / Cheque Number</label>
                    <input type="text" name="receipt_number" 
                           class="w-full p-2.5 border border-gray-300 rounded-md focus:border-teal-500 focus:ring-1 focus:ring-teal-500" placeholder="Optional">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 px-6 rounded-md shadow-sm transition-colors active:scale-[0.99] flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Confirm Receipt
                </button>
            </div>
        </form>
    </div>

@endsection
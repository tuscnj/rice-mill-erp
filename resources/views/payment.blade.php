@extends('layouts.app')
@section('title', 'Payment')
@section('content')

    <div class="max-w-4xl mx-auto mt-8 bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Header Panel -->
        <div class="bg-orange-600 p-6">
            <h2 class="text-2xl font-bold text-white tracking-tight">Issue Payment</h2>
            <p class="text-orange-100 text-sm mt-1">Pay suppliers or customers</p>
        </div>

        <form action="/run-payment" method="POST" class="p-6 sm:p-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Transaction Date (Spans Full Width) -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Transaction Date</label>
                    <input type="date" name="voucher_date" value="{{ date('Y-m-d') }}" 
                           class="w-full p-2.5 border border-gray-300 rounded-md focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                </div>

                <!-- Pay From -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Pay From (Bank/Cash)</label>
                    <select name="cash_id" required class="w-full p-2.5 bg-white border border-gray-300 rounded-md focus:border-orange-500 focus:ring-1 focus:ring-orange-500 cursor-pointer">
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pay To -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Pay To (Party)</label>
                    <select name="party_id" required class="w-full p-2.5 bg-white border border-gray-300 rounded-md focus:border-orange-500 focus:ring-1 focus:ring-orange-500 cursor-pointer">
                        <option value="" disabled selected>Select Party...</option>
                        @foreach($parties as $party)
                            <option value="{{ $party->id }}">{{ $party->name }} (Balance: ৳{{ number_format($party->balance, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Amount -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Payment Amount (৳)</label>
                    <input type="number" step="0.01" name="amount" required 
                           class="w-full p-2.5 border border-gray-300 rounded-md focus:border-orange-500 focus:ring-1 focus:ring-orange-500" placeholder="0.00">
                </div>

                <!-- Receipt / Cheque Number -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Receipt / Cheque Number</label>
                    <input type="text" name="receipt_number" 
                           class="w-full p-2.5 border border-gray-300 rounded-md focus:border-orange-500 focus:ring-1 focus:ring-orange-500" placeholder="Optional">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2.5 px-6 rounded-md shadow-sm transition-colors active:scale-[0.99]">
                    Send Payment
                </button>
            </div>
        </form>
    </div>

@endsection
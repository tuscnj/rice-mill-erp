@extends('layouts.app')
@section('title', 'Edit Payment')
@section('content')

    <div class="max-w-4xl mx-auto mt-8 bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
        <div class="bg-orange-600 p-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Edit Payment</h2>
                <p class="text-orange-100 text-sm mt-1">Update payment transaction #VCH-{{ $voucher->id }}</p>
            </div>
            <a href="/transactions" class="bg-orange-800 hover:bg-orange-900 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                ← Back
            </a>
        </div>

        <form action="/update-payment/{{ $voucher->id }}" method="POST" class="p-6 sm:p-8">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Transaction Date</label>
                    <input type="date" name="voucher_date" value="{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('Y-m-d') }}" 
                           class="w-full p-2.5 border border-gray-300 rounded-md focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Pay From (Bank/Cash)</label>
                    <select name="cash_id" required class="w-full p-2.5 bg-white border border-gray-300 rounded-md focus:border-orange-500 focus:ring-1 focus:ring-orange-500 cursor-pointer">
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}" {{ $cashId == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Pay To (Party)</label>
                    <select name="party_id" required class="w-full p-2.5 bg-white border border-gray-300 rounded-md focus:border-orange-500 focus:ring-1 focus:ring-orange-500 cursor-pointer">
                        <option value="" disabled>Select Party...</option>
                        @foreach($parties as $party)
                            <option value="{{ $party->id }}" {{ $partyId == $party->id ? 'selected' : '' }}>{{ $party->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Payment Amount (৳)</label>
                    <input type="number" step="0.01" name="amount" value="{{ $amount }}" required 
                           class="w-full p-2.5 border border-gray-300 rounded-md focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Receipt / Cheque Number</label>
                    <input type="text" name="receipt_number" value="{{ $voucher->reference_number }}" 
                           class="w-full p-2.5 border border-gray-300 rounded-md focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2.5 px-6 rounded-md shadow-sm transition-colors">
                    Update Payment
                </button>
            </div>
        </form>
    </div>

@endsection
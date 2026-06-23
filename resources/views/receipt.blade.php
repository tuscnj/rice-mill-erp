@extends('layouts.app')
@section('title', 'Receive Money')
@section('content')

    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-teal-600 p-4 text-center">
            <h2 class="text-2xl font-bold text-white">Receive Money</h2>
            <p class="text-teal-100 text-sm">Collect customer payments</p>
        </div>

        <form action="/run-receipt" method="POST" class="p-6 space-y-5">
            @csrf

            <!-- Receive TO (Cash/Bank) -->
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                <label class="block text-sm font-bold text-gray-700 mb-1">Receive Into (Bank/Cash)</label>
                <select name="cash_id" required class="w-full p-2 border border-gray-300 rounded focus:border-teal-500 bg-white">
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Receive FROM (Customer) -->
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                <label class="block text-sm font-bold text-gray-700 mb-1">Receive From (Customer)</label>
                <select name="customer_id" required class="w-full p-2 border border-gray-300 rounded focus:border-teal-500 bg-white">
                    <option value="" disabled selected>Select Customer...</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} (Owes: ৳{{ number_format($customer->balance, 2) }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Amount -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Amount Received (৳)</label>
                <input type="number" name="amount" required class="w-full text-2xl p-3 border-2 border-teal-200 rounded-lg focus:border-teal-500 text-center font-bold text-teal-600" placeholder="0.00">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Reference / Cheque Number</label>
                <input type="text" name="receipt_number" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-teal-500" placeholder="Optional">
            </div>

            <button type="submit" class="w-full bg-teal-600 text-white font-bold text-lg py-3 rounded-lg shadow-lg hover:bg-teal-700 transition mt-4">
                Confirm Receipt
            </button>
        </form>
    </div>
@endsection
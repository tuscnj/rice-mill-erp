@extends('layouts.app')
@section('title', 'Payment')
@section('content')

    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-orange-600 p-4 text-center">
            <h2 class="text-2xl font-bold text-white">Issue Payment</h2>
            <p class="text-orange-200 text-sm">Pay supplier invoices</p>
        </div>

        <form action="/run-payment" method="POST" class="p-6 space-y-5">
            @csrf

            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                <label class="block text-sm font-bold text-gray-700 mb-1">Pay From (Bank/Cash)</label>
                <select name="cash_id" required class="w-full p-2 border border-gray-300 rounded focus:border-orange-500 bg-white">
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                <label class="block text-sm font-bold text-gray-700 mb-1">Pay To (Supplier)</label>
                <select name="supplier_id" required class="w-full p-2 border border-gray-300 rounded focus:border-orange-500 bg-white">
                    <option value="" disabled selected>Select Supplier...</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }} (Owe: ৳{{ $supplier->balance }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Payment Amount (৳)</label>
                <input type="number" name="amount" required class="w-full text-2xl p-3 border-2 border-orange-200 rounded-lg focus:border-orange-500 text-center font-bold text-orange-600" placeholder="0.00">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Receipt / Cheque Number</label>
                <input type="text" name="receipt_number" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-orange-500" placeholder="Optional">
            </div>

            <button type="submit" class="w-full bg-orange-600 text-white font-bold text-lg py-3 rounded-lg shadow-lg hover:bg-orange-700 transition mt-4">
                Send Payment
            </button>
        </form>
    </div>
@endsection
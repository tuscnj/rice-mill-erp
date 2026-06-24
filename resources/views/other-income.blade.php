@extends('layouts.app')

@section('title', 'Log Other Income')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl border-t-4 border-green-500 overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-6 text-white">
                <h2 class="text-2xl font-bold">Log Other Income</h2>
                <p class="text-green-100">Record extra income like rent, service charges, interest, etc.</p>
            </div>

            <form action="/run-other-income" method="POST" class="p-6 space-y-5">
                @csrf
                
                <div class="mb-4">
    <label class="block text-sm font-bold text-gray-700 mb-1">Transaction Date</label>
    <input type="date" name="voucher_date" value="{{ date('Y-m-d') }}" 
           class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-blue-500">
</div>

                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Income Category</label>
                        <select name="income_id" required class="w-full p-3 border border-gray-300 rounded focus:border-green-500 bg-white">
                            @foreach($incomes as $income)
                                <option value="{{ $income->id }}">{{ $income->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Cash / Bank</label>
                        <select name="cash_id" required class="w-full p-3 border border-gray-300 rounded focus:border-green-500 bg-white">
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Amount (৳)</label>
                        <input type="number" step="0.01" name="amount" required class="w-full p-3 border border-gray-300 rounded focus:border-green-500" placeholder="0.00">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Reference / Receipt No.</label>
                        <input type="text" name="reference_number" class="w-full p-3 border border-gray-300 rounded focus:border-green-500" placeholder="Optional">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="3" class="w-full p-3 border border-gray-300 rounded focus:border-green-500" placeholder="Brief note about the income"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-green-600 text-white font-bold py-3 px-6 rounded-lg shadow hover:bg-green-700 transition">
                        Save Other Income
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

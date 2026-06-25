@extends('layouts.app')
@section('title', 'Log Expense')
@section('content')

    <div class="max-w-4xl mx-auto mt-8 bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Header Panel -->
        <div class="bg-rose-600 p-6">
            <h2 class="text-2xl font-bold text-white tracking-tight">Log Expense</h2>
            <p class="text-rose-100 text-sm mt-1">Record daily mill costs</p>
        </div>

        <form action="/run-expense" method="POST" class="p-6 sm:p-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Transaction Date (Spans Full Width) -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Transaction Date</label>
                    <input type="date" name="voucher_date" value="{{ date('Y-m-d') }}" 
                           class="w-full p-2.5 border border-gray-300 rounded-md focus:border-rose-500 focus:ring-1 focus:ring-rose-500">
                </div>

                <!-- Pay From -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Pay From (Bank/Cash)</label>
                    <select name="cash_id" required class="w-full p-2.5 bg-white border border-gray-300 rounded-md focus:border-rose-500 focus:ring-1 focus:ring-rose-500 cursor-pointer">
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Expense Category -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Expense Category</label>
                    <select name="expense_id" required class="w-full p-2.5 bg-white border border-gray-300 rounded-md focus:border-rose-500 focus:ring-1 focus:ring-rose-500 cursor-pointer">
                        <option value="" disabled selected>Select what you are paying for...</option>
                        @foreach($expenses as $expense)
                            <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Expense Amount -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Expense Amount (৳)</label>
                    <input type="number" step="0.01" name="amount" required 
                           class="w-full p-2.5 border border-gray-300 rounded-md focus:border-rose-500 focus:ring-1 focus:ring-rose-500" placeholder="0.00">
                </div>

                <!-- Details / Notes -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Details / Notes</label>
                    <input type="text" name="notes" 
                           class="w-full p-2.5 border border-gray-300 rounded-md focus:border-rose-500 focus:ring-1 focus:ring-rose-500" placeholder="e.g. Weekly worker wages">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 px-6 rounded-md shadow-sm transition-colors active:scale-[0.99] flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                    Save Expense
                </button>
            </div>
        </form>
    </div>

@endsection
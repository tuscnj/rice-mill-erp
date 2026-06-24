@extends('layouts.app')
@section('title', 'Log Expense')
@section('content')

    <div class="bg-white w-full max-w-md mx-auto rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
        <div class="bg-gradient-to-r from-rose-500 to-rose-600 p-6 text-center sm:text-left sm:px-8">
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Log Expense</h2>
            <p class="text-rose-100 text-sm mt-1">Record daily mill costs</p>
        </div>

        <form action="/run-expense" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf
            
            <div class="mb-4">
    <label class="block text-sm font-bold text-gray-700 mb-1">Transaction Date</label>
    <input type="date" name="voucher_date" value="{{ date('Y-m-d') }}" 
           class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-blue-500">
</div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Pay From (Bank/Cash)</label>
                <select name="cash_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 focus:bg-white transition-all duration-200 cursor-pointer appearance-none">
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Expense Category</label>
                <select name="expense_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 focus:bg-white transition-all duration-200 cursor-pointer appearance-none">
                    <option value="" disabled selected>Select what you are paying for...</option>
                    @foreach($expenses as $expense)
                        <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="bg-rose-50/50 p-5 rounded-2xl border-2 border-rose-100">
                <label class="block text-xs font-bold text-rose-800 uppercase tracking-wider mb-3 text-center">Expense Amount (৳)</label>
                <input type="number" step="0.01" name="amount" required class="w-full text-3xl p-4 border-2 border-rose-200 rounded-xl focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 text-center font-bold text-rose-600 bg-white placeholder-rose-200 transition-all duration-200" placeholder="0.00">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Details / Notes</label>
                <input type="text" name="notes" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 focus:bg-white transition-all duration-200" placeholder="e.g. Weekly worker wages">
            </div>

            <hr class="border-gray-200 mt-2 mb-2">

            <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-lg py-4 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 active:scale-[0.99] flex justify-center items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                Save Expense
            </button>
        </form>
    </div>
@endsection
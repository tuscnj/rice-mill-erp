@extends('layouts.app')
@section('title', 'Log Expense')
@section('content')

<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4 font-sans">

    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-rose-600 p-4 text-center">
            <h2 class="text-2xl font-bold text-white">Log Expense</h2>
            <p class="text-rose-200 text-sm">Record daily mill costs</p>
        </div>

        <form action="/run-expense" method="POST" class="p-6 space-y-5">
            @csrf

            <!-- Pay FROM (Cash) -->
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                <label class="block text-sm font-bold text-gray-700 mb-1">Pay From (Bank/Cash)</label>
                <select name="cash_id" required class="w-full p-2 border border-gray-300 rounded focus:border-rose-500 bg-white">
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Expense Category -->
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                <label class="block text-sm font-bold text-gray-700 mb-1">Expense Category</label>
                <select name="expense_id" required class="w-full p-2 border border-gray-300 rounded focus:border-rose-500 bg-white">
                    <option value="" disabled selected>Select what you are paying for...</option>
                    @foreach($expenses as $expense)
                        <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Amount -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Expense Amount (৳)</label>
                <input type="number" name="amount" required class="w-full text-2xl p-3 border-2 border-rose-200 rounded-lg focus:border-rose-500 text-center font-bold text-rose-600" placeholder="0.00">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Details / Notes</label>
                <input type="text" name="notes" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-rose-500" placeholder="e.g. Weekly worker wages">
            </div>

            <button type="submit" class="w-full bg-rose-600 text-white font-bold text-lg py-3 rounded-lg shadow-lg hover:bg-rose-700 transition mt-4">
                Save Expense
            </button>
        </form>
    </div>
@endsection
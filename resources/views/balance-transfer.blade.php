@extends('layouts.app')
@section('title', 'Balance Transfer')
@section('content')

    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-sky-700 p-4 text-center">
            <h2 class="text-2xl font-bold text-white">Balance Transfer</h2>
            <p class="text-sky-100 text-sm">Journal style transfer between accounts</p>
        </div>

        <form action="/run-balance-transfer" method="POST" class="p-6 space-y-5">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">From Account</label>
                    <select name="from_account_id" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-sky-500 bg-white">
                        <option value="" disabled selected>Select account...</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->group_type }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">To Account</label>
                    <select name="to_account_id" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-sky-500 bg-white">
                        <option value="" disabled selected>Select account...</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->group_type }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Amount</label>
                <input type="number" step="0.01" name="amount" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-sky-500" placeholder="e.g. 5000">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Reference Number</label>
                    <input type="text" name="reference_number" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-sky-500" placeholder="Optional">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Narration</label>
                    <input type="text" name="narration" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-sky-500" placeholder="e.g. Transfer between accounts">
                </div>
            </div>

            <button type="submit" class="w-full bg-sky-700 text-white font-bold text-lg py-3 rounded-lg shadow-lg hover:bg-sky-800 transition mt-4">Post Balance Transfer</button>
        </form>
    </div>
@endsection

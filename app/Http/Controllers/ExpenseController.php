<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            
            // 1. Create the Expense Voucher
            $voucher = Voucher::create([
                'voucher_type' => 'Expense',
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->reference,
                'notes' => $request->notes ?? 'Daily operating expense'
            ]);

            // 2. Debit the Expense Account (Increases your total expenses)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->expense_id,
                'amount' => $request->amount,
                'entry_type' => 'Debit'
            ]);
            Account::where('id', $request->expense_id)->increment('balance', $request->amount);

            // 3. Credit your Cash/Bank (Decreases your available money)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->cash_id,
                'amount' => $request->amount,
                'entry_type' => 'Credit'
            ]);
            Account::where('id', $request->cash_id)->decrement('balance', $request->amount);

        });

        // Redirect straight back to the dashboard to see the cash drop
        return redirect('/');
    }
}
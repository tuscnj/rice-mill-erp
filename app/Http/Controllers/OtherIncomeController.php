<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class OtherIncomeController extends Controller
{
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            // 1. Create the Other Income Voucher
            $voucher = Voucher::create([
                'voucher_type' => 'Other Income',
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->reference_number,
                'notes' => $request->notes ?? 'Other income received'
            ]);

            // 2. Debit the Cash/Bank (Increases available money)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->cash_id,
                'amount' => $request->amount,
                'entry_type' => 'Debit'
            ]);
            Account::where('id', $request->cash_id)->increment('balance', $request->amount);

            // 3. Credit the Other Income Account (Increases income)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->income_id,
                'amount' => $request->amount,
                'entry_type' => 'Credit'
            ]);
            Account::where('id', $request->income_id)->decrement('balance', $request->amount);

        });

        return redirect('/');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            
            // 1. Create the Payment Voucher Header
            $voucher = Voucher::create([
                'voucher_type' => 'Payment',
                'voucher_date' => now(),
                'reference_number' => $request->receipt_number,
                'notes' => 'Payment made to supplier'
            ]);

            // 2. Debit the Supplier (This DECREASES the amount you owe them)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->supplier_id, 
                'amount' => $request->amount,
                'entry_type' => 'Debit'
            ]);
            Account::where('id', $request->supplier_id)->decrement('balance', $request->amount);

            // 3. Credit your Bank/Cash (This DECREASES your cash balance)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->cash_id,
                'amount' => $request->amount,
                'entry_type' => 'Credit'
            ]);
            Account::where('id', $request->cash_id)->decrement('balance', $request->amount);

        });

        // After paying, redirect straight back to the supplier's ledger so we can see the new balance!
        return redirect('/ledger/' . $request->supplier_id);
    }
}
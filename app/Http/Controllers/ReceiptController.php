<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class ReceiptController extends Controller
{
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            
            // 1. Create the Receipt Voucher Header
            $voucher = Voucher::create([
                'voucher_type' => 'Receipt',
                'voucher_date' => now(),
                'reference_number' => $request->receipt_number,
                'notes' => 'Payment received from customer'
            ]);

            // 2. Debit your Bank/Cash (This INCREASES your cash balance)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->cash_id,
                'amount' => $request->amount,
                'entry_type' => 'Debit'
            ]);
            Account::where('id', $request->cash_id)->increment('balance', $request->amount);

            // 3. Credit the Customer (This DECREASES the amount they owe you)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->customer_id, 
                'amount' => $request->amount,
                'entry_type' => 'Credit'
            ]);
            Account::where('id', $request->customer_id)->decrement('balance', $request->amount);

        });

        // Redirect back to the dashboard so we can see our cash go up!
        return redirect('/');
    }
}
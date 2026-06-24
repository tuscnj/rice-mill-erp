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
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->receipt_number,
                'notes' => 'Payment received from party'
            ]);

            // 2. Debit your Bank/Cash (Money coming IN to your account)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->cash_id,
                'amount' => $request->amount,
                'entry_type' => 'Debit'
            ]);

            // 3. Credit the Party (Money given BY them)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->party_id, 
                'amount' => $request->amount,
                'entry_type' => 'Credit'
            ]);

            // 4. SMART BALANCE RECALCULATION
            $this->updateAccountBalance($request->party_id);
            $this->updateAccountBalance($request->cash_id);

        });

        return redirect('/ledger/' . $request->party_id);
    }

    // Helper function to safely calculate balances based on Account Type
    private function updateAccountBalance($accountId) {
        $account = Account::findOrFail($accountId);
        $isDebit = in_array($account->group_type, ['Sundry Debtors', 'Cash', 'Direct Expenses', 'Indirect Expenses']);
        
        $totalDebit = VoucherEntry::where('account_id', $accountId)->where('entry_type', 'Debit')->sum('amount');
        $totalCredit = VoucherEntry::where('account_id', $accountId)->where('entry_type', 'Credit')->sum('amount');
        
        $account->balance = $isDebit ? ($totalDebit - $totalCredit) : ($totalCredit - $totalDebit);
        $account->save();
    }
}
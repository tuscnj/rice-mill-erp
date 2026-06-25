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

    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $voucher = Voucher::with('entries')->findOrFail($id);

            // 1. Identify the old accounts before we delete the entries
            $oldDebit = $voucher->entries->where('entry_type', 'Debit')->first();
            $oldCredit = $voucher->entries->where('entry_type', 'Credit')->first();
            
            $oldCashId = $oldDebit ? $oldDebit->account_id : null;
            $oldPartyId = $oldCredit ? $oldCredit->account_id : null;

            // 2. Delete the old entry records completely
            $voucher->entries()->delete();

            // 3. UPDATE THE VOUCHER DETAILS
            $voucher->update([
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->receipt_number
            ]);

            // 4. CREATE THE NEW LEDGER ENTRIES
            // Debit Cash/Bank Account (Money coming in)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->cash_id,
                'amount' => $request->amount,
                'entry_type' => 'Debit'
            ]);

            // Credit Party (Reduces what the customer owes)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->party_id,
                'amount' => $request->amount,
                'entry_type' => 'Credit'
            ]);

            // 5. SMART BALANCE RECALCULATION
            // First, recalculate the old accounts (in case you changed the customer/bank in the dropdown)
            if ($oldCashId) { $this->updateAccountBalance($oldCashId); }
            if ($oldPartyId) { $this->updateAccountBalance($oldPartyId); }
            
            // Second, recalculate the newly selected accounts
            $this->updateAccountBalance($request->cash_id);
            $this->updateAccountBalance($request->party_id);
        });

        // Redirect back to the Daybook
        return redirect('/transactions')->with('success', 'Receipt updated successfully!');
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
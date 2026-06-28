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
            $voucher = Voucher::create([
                'voucher_type' => 'Payment',
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->receipt_number,
                'notes' => 'Payment made to party'
            ]);

            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->party_id, 
                'amount' => $request->amount,
                'entry_type' => 'Debit'
            ]);

            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->cash_id,
                'amount' => $request->amount,
                'entry_type' => 'Credit'
            ]);

            $this->updateAccountBalance($request->party_id);
            $this->updateAccountBalance($request->cash_id);
        });

        return redirect('/ledger/' . $request->party_id)->with('success', 'Payment recorded successfully!');
    }

    // 🚨 NEW UPDATE METHOD (Handles Edit Saving)
    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $voucher = Voucher::with('entries')->findOrFail($id);
            
            // Track old accounts so we can mathematically fix them
            $oldAccountIds = $voucher->entries->pluck('account_id')->toArray();
            $voucher->entries()->delete();

            $voucher->update([
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->receipt_number,
            ]);

            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->party_id, 
                'amount' => $request->amount,
                'entry_type' => 'Debit'
            ]);

            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->cash_id,
                'amount' => $request->amount,
                'entry_type' => 'Credit'
            ]);

            // Recalculate ALL affected accounts
            $allAffectedIds = array_unique(array_merge($oldAccountIds, [$request->party_id, $request->cash_id]));
            foreach ($allAffectedIds as $accId) {
                $this->updateAccountBalance($accId);
            }
        });

        return redirect('/transactions')->with('success', 'Payment updated successfully!');
    }

    private function updateAccountBalance($accountId) {
        $account = Account::find($accountId);
        if (!$account) return;
        
        $isDebit = in_array($account->group_type, ['Sundry Debtors', 'Cash', 'Direct Expenses', 'Indirect Expenses']);
        $totalDebit = VoucherEntry::where('account_id', $accountId)->where('entry_type', 'Debit')->sum('amount');
        $totalCredit = VoucherEntry::where('account_id', $accountId)->where('entry_type', 'Credit')->sum('amount');
        
        $account->balance = $isDebit ? ($totalDebit - $totalCredit) : ($totalCredit - $totalDebit);
        $account->save();
    }
}
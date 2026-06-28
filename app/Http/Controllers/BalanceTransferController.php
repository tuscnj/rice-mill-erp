<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class BalanceTransferController extends Controller
{
    // 🚨 1. ADDED MISSING STORE METHOD (For New Transfers)
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            // Auto-detect field names to match your view file perfectly
            $fromAccountId = $request->input('from_account_id') ?? $request->input('from_account');
            $toAccountId = $request->input('to_account_id') ?? $request->input('to_account');
            $amount = (float) $request->input('amount', 0);

            if (!$fromAccountId || !$toAccountId || $amount <= 0) {
                return back()->with('error', 'Missing account details or invalid amount.');
            }

            $narration = trim((string) $request->input('narration', $request->input('notes', '')));
            if ($narration === '') $narration = 'Balance transfer between accounts';

            $voucher = Voucher::create([
                'voucher_type' => 'Balance Transfer',
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->input('reference_number', ''),
                'notes' => $narration,
            ]);

            // 1. Take money OUT of 'From Account' (Credit)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $fromAccountId,
                'amount' => $amount,
                'entry_type' => 'Credit',
            ]);
            
            // 2. Put money INTO 'To Account' (Debit)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $toAccountId,
                'amount' => $amount,
                'entry_type' => 'Debit',
            ]);
            
            // Mathematically recalculate both ledgers from scratch
            $this->recalculateBalance($fromAccountId);
            $this->recalculateBalance($toAccountId);
        });

        return redirect('/transactions')->with('success', 'Balance Transfer recorded successfully!');
    }

    // 🚨 2. BULLETPROOF UPDATE METHOD (For Editing Transfers)
    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $voucher = Voucher::with('entries')->findOrFail($id);
            
            // Keep track of the old accounts so we can fix their math after deleting
            $oldAccountIds = $voucher->entries->pluck('account_id')->toArray();
            $voucher->entries()->delete();

            $fromAccountId = $request->input('from_account_id') ?? $request->input('from_account');
            $toAccountId = $request->input('to_account_id') ?? $request->input('to_account');
            $amount = (float) $request->input('amount', 0);

            $narration = trim((string) $request->input('narration', $request->input('notes', '')));
            if ($narration === '') $narration = 'Balance transfer between accounts';

            $voucher->update([
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->input('reference_number', ''),
                'notes' => $narration,
            ]);

            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $fromAccountId,
                'amount' => $amount,
                'entry_type' => 'Credit',
            ]);
            
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $toAccountId,
                'amount' => $amount,
                'entry_type' => 'Debit',
            ]);

            // Recalculate all affected accounts (Old and New)
            $allAffectedIds = array_unique(array_merge($oldAccountIds, [$fromAccountId, $toAccountId]));
            foreach ($allAffectedIds as $accId) {
                $this->recalculateBalance($accId);
            }
        });

        return redirect('/transactions')->with('success', 'Balance Transfer updated successfully!');
    }

    // 🚨 3. SELF-HEALING MATH ENGINE (Prevents ledger drift)
    private function recalculateBalance($accountId)
    {
        $account = Account::find($accountId);
        if (!$account) return;

        $totalDebit = VoucherEntry::where('account_id', $account->id)->where('entry_type', 'Debit')->sum('amount');
        $totalCredit = VoucherEntry::where('account_id', $account->id)->where('entry_type', 'Credit')->sum('amount');
        
        $isDebit = in_array($account->group_type, ['Sundry Debtors', 'Cash', 'Direct Expenses', 'Indirect Expenses']);
        
        if ($isDebit) {
            $account->balance = $totalDebit - $totalCredit;
        } else {
            $account->balance = $totalCredit - $totalDebit;
        }
        $account->save();
    }
}
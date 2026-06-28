<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class OtherIncomeController extends Controller
{
    // 🚨 1. STORE METHOD (For New Incomes)
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            // Auto-detect field names to match your view file
            $cashId = $request->input('cash_id') ?? $request->input('bank_account');
            $incomeId = $request->input('income_id') ?? $request->input('income_account');
            $amount = (float) $request->input('amount', 0);

            if (!$cashId || !$incomeId || $amount <= 0) {
                return back()->with('error', 'Missing account details or invalid amount.');
            }

            $narration = trim((string) $request->input('narration', $request->input('notes', '')));
            if ($narration === '') $narration = 'Other income received';

            $voucher = Voucher::create([
                'voucher_type' => 'Other Income',
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->input('reference_number', ''),
                'notes' => $narration,
            ]);

            // 1. Debit Cash/Bank (Increases your available cash)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $cashId,
                'amount' => $amount,
                'entry_type' => 'Debit',
            ]);
            
            // 2. Credit Income Account (Increases your total revenue)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $incomeId,
                'amount' => $amount,
                'entry_type' => 'Credit',
            ]);
            
            // Mathematically recalculate both ledgers from scratch
            $this->recalculateBalance($cashId);
            $this->recalculateBalance($incomeId);
        });

        return redirect('/transactions')->with('success', 'Other Income recorded successfully!');
    }

    // 🚨 2. UPDATE METHOD (For Editing Incomes)
    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $voucher = Voucher::with('entries')->findOrFail($id);
            
            // Keep track of the old accounts so we can fix their math after deleting
            $oldAccountIds = $voucher->entries->pluck('account_id')->toArray();
            $voucher->entries()->delete();

            $cashId = $request->input('cash_id') ?? $request->input('bank_account');
            $incomeId = $request->input('income_id') ?? $request->input('income_account');
            $amount = (float) $request->input('amount', 0);

            $narration = trim((string) $request->input('narration', $request->input('notes', '')));
            if ($narration === '') $narration = 'Other income received';

            $voucher->update([
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->input('reference_number', ''),
                'notes' => $narration,
            ]);

            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $cashId,
                'amount' => $amount,
                'entry_type' => 'Debit',
            ]);
            
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $incomeId,
                'amount' => $amount,
                'entry_type' => 'Credit',
            ]);

            // Recalculate all affected accounts (Old and New)
            $allAffectedIds = array_unique(array_merge($oldAccountIds, [$cashId, $incomeId]));
            foreach ($allAffectedIds as $accId) {
                $this->recalculateBalance($accId);
            }
        });

        return redirect('/transactions')->with('success', 'Other Income updated successfully!');
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
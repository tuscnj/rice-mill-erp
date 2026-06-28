<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    // 🚨 1. STORE METHOD (For New Expenses)
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            // Auto-detect field names to match your view file perfectly
            $cashId = $request->input('cash_id') ?? $request->input('bank_account');
            $expenseId = $request->input('expense_id') ?? $request->input('expense_account');
            $amount = (float) $request->input('amount', 0);

            if (!$cashId || !$expenseId || $amount <= 0) {
                return back()->with('error', 'Missing account details or invalid amount.');
            }

            $narration = trim((string) $request->input('narration', $request->input('notes', '')));
            if ($narration === '') $narration = 'Daily operating expense';

            $voucher = Voucher::create([
                'voucher_type' => 'Expense',
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->input('reference_number', ''),
                'notes' => $narration,
            ]);

            // 1. Debit the Expense Account (Increases your total expenses)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $expenseId,
                'amount' => $amount,
                'entry_type' => 'Debit',
            ]);
            
            // 2. Credit the Cash/Bank Account (Reduces your available cash)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $cashId,
                'amount' => $amount,
                'entry_type' => 'Credit',
            ]);
            
            // Mathematically recalculate both ledgers from scratch
            $this->recalculateBalance($expenseId);
            $this->recalculateBalance($cashId);
        });

        return redirect('/transactions')->with('success', 'Expense recorded successfully!');
    }

    // 🚨 2. UPDATE METHOD (For Editing Expenses)
    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $voucher = Voucher::with('entries')->findOrFail($id);
            
            // Keep track of the old accounts so we can fix their math after deleting
            $oldAccountIds = $voucher->entries->pluck('account_id')->toArray();
            $voucher->entries()->delete();

            $cashId = $request->input('cash_id') ?? $request->input('bank_account');
            $expenseId = $request->input('expense_id') ?? $request->input('expense_account');
            $amount = (float) $request->input('amount', 0);

            $narration = trim((string) $request->input('narration', $request->input('notes', '')));
            if ($narration === '') $narration = 'Daily operating expense';

            $voucher->update([
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->input('reference_number', ''),
                'notes' => $narration,
            ]);

            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $expenseId,
                'amount' => $amount,
                'entry_type' => 'Debit',
            ]);
            
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $cashId,
                'amount' => $amount,
                'entry_type' => 'Credit',
            ]);

            // Recalculate all affected accounts (Old and New)
            $allAffectedIds = array_unique(array_merge($oldAccountIds, [$expenseId, $cashId]));
            foreach ($allAffectedIds as $accId) {
                $this->recalculateBalance($accId);
            }
        });

        return redirect('/transactions')->with('success', 'Expense updated successfully!');
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
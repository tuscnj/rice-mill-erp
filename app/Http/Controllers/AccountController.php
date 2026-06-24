<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::orderBy('group_type')->orderBy('name')->get();
        return view('accounts', ['accounts' => $accounts]);
    }

    public function edit($id)
    {
        $account = Account::findOrFail($id);
        
        // Find existing Opening Balance
        $obEntry = VoucherEntry::where('account_id', $id)
            ->whereHas('voucher', function($q) {
                $q->where('voucher_type', 'Opening Balance');
            })->first();
        
        $openingBalance = $obEntry ? $obEntry->amount : 0;

        return view('edit-account', ['account' => $account, 'openingBalance' => $openingBalance]);
    }

    public function update(Request $request, $id)
    {
        // 🚨 ADDED VALIDATION: Prevents renaming an account to a name that already exists (ignores its own ID)
        $request->validate([
            'name' => 'required|string|max:255|unique:accounts,name,' . $id,
            'group_type' => 'required|string'
        ], [
            'name.unique' => 'An account with this exact name already exists!'
        ]);

        DB::transaction(function () use ($request, $id) {
            $account = Account::findOrFail($id);
            $account->update([
                'name' => $request->name,
                'group_type' => $request->group_type
            ]);

            $newObAmount = (float) $request->opening_balance;
            $isDebit = in_array($request->group_type, ['Sundry Debtors', 'Cash', 'Direct Expenses', 'Indirect Expenses']);

            $obVoucher = Voucher::where('voucher_type', 'Opening Balance')
                ->whereHas('entries', function($q) use ($id) {
                    $q->where('account_id', $id);
                })->first();

            if ($obVoucher) {
                // Update existing opening balance
                $entry = $obVoucher->entries()->where('account_id', $id)->first();
                $entry->update([
                    'amount' => $newObAmount,
                    'entry_type' => $isDebit ? 'Debit' : 'Credit'
                ]);
            } else if ($newObAmount > 0) {
                // Create opening balance if it didn't exist
                $voucher = Voucher::create([
                    'voucher_type' => 'Opening Balance',
                    'voucher_date' => now()->subYears(10), // Logged in the past so it stays at the top of the ledger
                    'reference_number' => 'OP-BAL',
                    'notes' => 'Initial account balance'
                ]);
                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $account->id,
                    'amount' => $newObAmount,
                    'entry_type' => $isDebit ? 'Debit' : 'Credit'
                ]);
            }

            // SELF-HEALING MATH: Recalculate entire account balance from scratch!
            $totalDebit = VoucherEntry::where('account_id', $id)->where('entry_type', 'Debit')->sum('amount');
            $totalCredit = VoucherEntry::where('account_id', $id)->where('entry_type', 'Credit')->sum('amount');
            
            if ($isDebit) {
                $account->balance = $totalDebit - $totalCredit;
            } else {
                $account->balance = $totalCredit - $totalDebit;
            }
            $account->save();
        });
        
        return redirect('/accounts');
    }

    public function destroy($id)
    {
        // SAFETY LOCK: This is required for accounting integrity. 
        $hasTransactions = \App\Models\VoucherEntry::where('account_id', $id)->exists();
        
        if ($hasTransactions) {
            return "<script>alert('🛑 CANNOT DELETE: This account has recorded financial transactions. You may only edit its name.'); window.location.href='/accounts';</script>";
        }

        Account::findOrFail($id)->delete();
        return redirect('/accounts');
    }

    public function store(Request $request)
    {
        // 🚨 ADDED VALIDATION: Prevents creating a new account if the name already exists
        $request->validate([
            'name' => 'required|string|max:255|unique:accounts,name',
            'group_type' => 'required|string'
        ], [
            'name.unique' => 'An account with this exact name already exists!'
        ]);

        DB::transaction(function () use ($request) {
            $openingBalance = (float) ($request->opening_balance ?? 0);

            $account = Account::create([
                'name' => $request->name,
                'group_type' => $request->group_type,
                'balance' => $openingBalance
            ]);

            if ($openingBalance > 0) {
                $isDebit = in_array($request->group_type, ['Sundry Debtors', 'Cash', 'Direct Expenses', 'Indirect Expenses']);
                
                $voucher = Voucher::create([
                    'voucher_type' => 'Opening Balance',
                    'voucher_date' => now()->subYears(10), // Logged in the past
                    'reference_number' => 'OP-BAL',
                    'notes' => 'Initial account balance'
                ]);

                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $account->id,
                    'amount' => $openingBalance,
                    'entry_type' => $isDebit ? 'Debit' : 'Credit'
                ]);
            }
        });

        return redirect('/accounts');
    }
}
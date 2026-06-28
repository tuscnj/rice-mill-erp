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
        
        $obEntry = VoucherEntry::where('account_id', $id)
            ->whereHas('voucher', function($q) {
                $q->where('voucher_type', 'Opening Balance');
            })->first();
        
        $openingBalance = $obEntry ? $obEntry->amount : 0;

        return view('edit-account', ['account' => $account, 'openingBalance' => $openingBalance]);
    }

    public function update(Request $request, $id)
    {
        // 🚨 SAFER VALIDATION: Forces 0 or 1 instead of strict boolean logic
        $request->validate([
            'name' => 'required|string|max:255|unique:accounts,name,' . $id,
            'group_type' => 'required|string',
            'mobile_number' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'required|in:0,1', 
            'opening_balance' => 'nullable|numeric'
        ], [
            'name.unique' => 'An account with this exact name already exists!'
        ]);

        DB::transaction(function () use ($request, $id) {
            $account = Account::findOrFail($id);
            $account->update([
                'name' => $request->name,
                'group_type' => $request->group_type,
                'mobile_number' => $request->mobile_number,
                'address' => $request->address,
                'is_active' => $request->is_active,
            ]);

            $newObAmount = (float) $request->opening_balance;
            $isDebit = in_array($request->group_type, ['Sundry Debtors', 'Cash', 'Direct Expenses', 'Indirect Expenses']);

            $obVoucher = Voucher::where('voucher_type', 'Opening Balance')
                ->whereHas('entries', function($q) use ($id) {
                    $q->where('account_id', $id);
                })->first();

            if ($obVoucher) {
                $entry = $obVoucher->entries()->where('account_id', $id)->first();
                $entry->update([
                    'amount' => $newObAmount,
                    'entry_type' => $isDebit ? 'Debit' : 'Credit'
                ]);
            } else if ($newObAmount > 0) {
                $voucher = Voucher::create([
                    'voucher_type' => 'Opening Balance',
                    'voucher_date' => now()->subYears(10), 
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
        $hasTransactions = \App\Models\VoucherEntry::where('account_id', $id)->exists();
        
        if ($hasTransactions) {
            return "<script>alert('🛑 CANNOT DELETE: This account has recorded financial transactions. You may only edit its name.'); window.location.href='/accounts';</script>";
        }

        Account::findOrFail($id)->delete();
        return redirect('/accounts');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:accounts,name',
            'group_type' => 'required|string',
            'mobile_number' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'required|in:0,1',
            'opening_balance' => 'nullable|numeric'
        ], [
            'name.unique' => 'An account with this exact name already exists!'
        ]);

        DB::transaction(function () use ($request) {
            $openingBalance = (float) ($request->opening_balance ?? 0);

            $account = Account::create([
                'name' => $request->name,
                'group_type' => $request->group_type,
                'balance' => $openingBalance,
                'mobile_number' => $request->mobile_number,
                'address' => $request->address,
                'is_active' => $request->is_active,
            ]);

            if ($openingBalance > 0) {
                $isDebit = in_array($request->group_type, ['Sundry Debtors', 'Cash', 'Direct Expenses', 'Indirect Expenses']);
                
                $voucher = Voucher::create([
                    'voucher_type' => 'Opening Balance',
                    'voucher_date' => now()->subYears(10), 
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
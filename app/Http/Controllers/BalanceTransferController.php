<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceTransferController extends Controller
{
public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $voucher = Voucher::with('entries')->findOrFail($id);

            // 1. REVERSE OLD ENTRIES
            // We loop through the old entries and apply the exact opposite math of what the store method did
            foreach ($voucher->entries as $entry) {
                $account = Account::find($entry->account_id);
                if ($account) {
                    if ($account->group_type == 'Cash' || $account->group_type == 'Sundry Debtors') {
                        // For assets: Debit increases, Credit decreases. So to reverse:
                        $entry->entry_type == 'Debit' ? $account->decrement('balance', $entry->amount) : $account->increment('balance', $entry->amount);
                    } elseif ($account->group_type == 'Sundry Creditors') {
                        // For liabilities: Credit increases, Debit decreases. So to reverse:
                        $entry->entry_type == 'Credit' ? $account->decrement('balance', $entry->amount) : $account->increment('balance', $entry->amount);
                    }
                }
            }
            $voucher->entries()->delete();

            // 2. FETCH NEW ACCOUNTS
            $fromAccount = Account::findOrFail($request->from_account_id);
            $toAccount = Account::findOrFail($request->to_account_id);
            $amount = (float) $request->amount;

            // 3. UPDATE VOUCHER DETAILS
            $voucher->update([
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->reference_number,
                'notes' => trim((string) $request->narration) ?: 'Balance transfer',
            ]);

            // Prevent empty or circular transfers
            if ($amount <= 0 || $fromAccount->id == $toAccount->id) {
                return; 
            }

            // 4. POST NEW ENTRIES
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $toAccount->id,
                'amount' => $amount,
                'entry_type' => 'Debit', // Receiving account is debited
            ]);

            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $fromAccount->id,
                'amount' => $amount,
                'entry_type' => 'Credit', // Giving account is credited
            ]);

            // 5. UPDATE NEW BALANCES
            if ($fromAccount->group_type == 'Cash' || $fromAccount->group_type == 'Sundry Debtors') {
                $fromAccount->decrement('balance', $amount);
            } elseif ($fromAccount->group_type == 'Sundry Creditors') {
                $fromAccount->increment('balance', $amount);
            }

            if ($toAccount->group_type == 'Cash' || $toAccount->group_type == 'Sundry Debtors') {
                $toAccount->increment('balance', $amount);
            } elseif ($toAccount->group_type == 'Sundry Creditors') {
                $toAccount->decrement('balance', $amount);
            }
        });

        return redirect('/transactions')->with('success', 'Transfer updated successfully!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceTransferController extends Controller
{
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $fromAccount = Account::findOrFail($request->from_account_id);
            $toAccount = Account::findOrFail($request->to_account_id);
            $amount = (float) $request->amount;

            if ($amount <= 0 || $fromAccount->id == $toAccount->id) {
                return;
            }

            $voucher = Voucher::create([
                'voucher_type' => 'Balance Transfer',
                'voucher_date' => now(),
                'reference_number' => $request->reference_number,
                'notes' => trim((string) $request->narration) ?: 'Balance transfer',
            ]);

            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $toAccount->id,
                'amount' => $amount,
                'entry_type' => 'Debit',
            ]);

            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $fromAccount->id,
                'amount' => $amount,
                'entry_type' => 'Credit',
            ]);

            if ($fromAccount->group_type == 'Cash') {
                $fromAccount->decrement('balance', $amount);
            } elseif ($fromAccount->group_type == 'Sundry Debtors') {
                $fromAccount->decrement('balance', $amount);
            } elseif ($fromAccount->group_type == 'Sundry Creditors') {
                $fromAccount->increment('balance', $amount);
            }

            if ($toAccount->group_type == 'Cash') {
                $toAccount->increment('balance', $amount);
            } elseif ($toAccount->group_type == 'Sundry Debtors') {
                $toAccount->increment('balance', $amount);
            } elseif ($toAccount->group_type == 'Sundry Creditors') {
                $toAccount->decrement('balance', $amount);
            }
        });

        return redirect('/transactions');
    }
}

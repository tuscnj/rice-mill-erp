<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class OtherIncomeController extends Controller
{
  public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $voucher = Voucher::with('entries')->findOrFail($id);

            // 1. REVERSE THE OLD LEDGER ENTRIES
            foreach ($voucher->entries as $entry) {
                if ($entry->entry_type == 'Debit') {
                    // Reverse the cash debit (decreases available money back to original)
                    Account::where('id', $entry->account_id)->decrement('balance', $entry->amount);
                } elseif ($entry->entry_type == 'Credit') {
                    // Reverse the income credit 
                    Account::where('id', $entry->account_id)->increment('balance', $entry->amount);
                }
            }

            // Delete the old entry records completely
            $voucher->entries()->delete();

            // 2. UPDATE THE VOUCHER DETAILS
            $voucher->update([
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->reference_number,
                'notes' => $request->notes ?? 'Other income received'
            ]);

            // 3. CREATE THE NEW LEDGER ENTRIES
            // Debit the Cash/Bank (Increases available money)
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->cash_id,
                'amount' => $request->amount,
                'entry_type' => 'Debit'
            ]);
            Account::where('id', $request->cash_id)->increment('balance', $request->amount);

            // Credit the Other Income Account
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->income_id,
                'amount' => $request->amount,
                'entry_type' => 'Credit'
            ]);
            Account::where('id', $request->income_id)->decrement('balance', $request->amount);
        });

        // Redirect back to the Daybook
        return redirect('/transactions')->with('success', 'Other Income updated successfully!');
    }
}

public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            // Changed 'voucherEntries' to 'entries' here
            $voucher = Voucher::with('entries')->findOrFail($id);

            // 1. REVERSE THE OLD LEDGER ENTRIES
            foreach ($voucher->entries as $entry) {
                if ($entry->entry_type == 'Debit') {
                    // Reverse the expense debit (decreases your total expenses)
                    Account::where('id', $entry->account_id)->decrement('balance', $entry->amount);
                } elseif ($entry->entry_type == 'Credit') {
                    // Reverse the cash credit (adds the money back to your bank/cash)
                    Account::where('id', $entry->account_id)->increment('balance', $entry->amount);
                }
            }

            // Delete the old entry records completely
            $voucher->entries()->delete();

            // 2. UPDATE THE VOUCHER DETAILS
            $voucher->update([
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->reference,
                'notes' => $request->notes ?? 'Daily operating expense'
            ]);

            // 3. CREATE THE NEW LEDGER ENTRIES
            // Debit Expense Account
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->expense_id,
                'amount' => $request->amount,
                'entry_type' => 'Debit'
            ]);
            Account::where('id', $request->expense_id)->increment('balance', $request->amount);

            // Credit Cash/Bank Account
            VoucherEntry::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->cash_id,
                'amount' => $request->amount,
                'entry_type' => 'Credit'
            ]);
            Account::where('id', $request->cash_id)->decrement('balance', $request->amount);
        });

        // Redirect back to the Daybook
        return redirect('/transactions')->with('success', 'Expense updated successfully!');
    }
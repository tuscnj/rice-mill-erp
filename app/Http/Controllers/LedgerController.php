<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\VoucherEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class LedgerController extends Controller
{
public function show($id, Request $request)
    {
        $account = Account::findOrFail($id);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        
        // 1. Check if the user wants the Simple or Detailed view
        $isDetailed = $request->has('detailed');

        // 2. Fetch Global Company Settings
        $setting = \App\Models\Setting::firstOrCreate(['id' => 1]);

        $entries = VoucherEntry::where('account_id', $id)
                    ->whereHas('voucher', function ($query) use ($startDate, $endDate) {
                        $query->whereDate('voucher_date', '>=', $startDate)
                              ->whereDate('voucher_date', '<=', $endDate);
                    })
                    ->with(['voucher.entries.account', 'voucher.inventoryMovements.item'])
                    ->orderBy('created_at', 'asc')
                    ->get();

        $openingBalanceRaw = 0;
        $openingEntries = VoucherEntry::where('account_id', $id)
            ->whereHas('voucher', function($query) use ($startDate) {
                $query->whereDate('voucher_date', '<', $startDate);
            })->get();

        foreach ($openingEntries as $entry) {
            $openingBalanceRaw += ($entry->entry_type == 'Debit' ? $entry->amount : -$entry->amount);
        }

        $ledgerEntries = [];
        $runningBalance = $openingBalanceRaw;

        foreach ($entries as $entry) {
            $runningBalance += ($entry->entry_type == 'Debit' ? $entry->amount : -$entry->amount);
            $particulars = $entry->voucher->entries->where('account_id', '!=', $account->id);

            $ledgerEntries[] = [
                'entry' => $entry,
                'particulars' => $particulars,
                'inventory' => $entry->voucher->inventoryMovements,
                'running_balance' => $runningBalance,
            ];
        }

        return view('ledger', [
            'account' => $account,
            'entries' => $ledgerEntries,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'openingBalanceRaw' => $openingBalanceRaw,
            'closingBalanceRaw' => $runningBalance,
            'setting' => $setting,         // Pass Settings to View
            'isDetailed' => $isDetailed,   // Pass Toggle State to View
        ]);
    }

    public function export($id, Request $request)
    {
        $account = Account::findOrFail($id);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $entries = VoucherEntry::where('account_id', $id)
            ->whereHas('voucher', function ($query) use ($startDate, $endDate) {
                $query->whereDate('voucher_date', '>=', $startDate)
                      ->whereDate('voucher_date', '<=', $endDate);
            })
            ->with(['voucher.entries.account'])
            ->orderBy('created_at', 'asc')
            ->get();

        $openingBalanceRaw = 0;
        $openingEntries = VoucherEntry::where('account_id', $id)
            ->whereHas('voucher', function($query) use ($startDate) {
                $query->whereDate('voucher_date', '<', $startDate);
            })->get();

        foreach ($openingEntries as $entry) {
            $openingBalanceRaw += ($entry->entry_type == 'Debit' ? $entry->amount : -$entry->amount);
        }

        $runningBalance = $openingBalanceRaw;
        $fileName = strtolower(str_replace(' ', '-', $account->name)) . '-ledger.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($account, $startDate, $endDate, $entries, $openingBalanceRaw, &$runningBalance) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Account Ledger', $account->name]);
            fputcsv($handle, ['From Date', $startDate, 'To Date', $endDate]);
            
            $opType = $openingBalanceRaw >= 0 ? 'Dr' : 'Cr';
            fputcsv($handle, ['Opening Balance', abs($openingBalanceRaw) . ' ' . $opType]);
            fputcsv($handle, []);
            fputcsv($handle, ['Date', 'Particulars', 'Vch Type', 'Ref / Narration', 'Debit (In)', 'Credit (Out)', 'Balance', 'Dr/Cr']);

            foreach ($entries as $entry) {
                $runningBalance += ($entry->entry_type == 'Debit' ? $entry->amount : -$entry->amount);
                
                $particularsNames = $entry->voucher->entries->where('account_id', '!=', $account->id)->pluck('account.name')->implode(', ');
                $particularsNames = $particularsNames ?: 'Self / Adjustment';

                fputcsv($handle, [
                    $entry->voucher->voucher_date ?? '',
                    $particularsNames,
                    $entry->voucher->voucher_type ?? '',
                    ($entry->voucher->reference_number ? 'Ref: ' . $entry->voucher->reference_number . ' - ' : '') . ($entry->voucher->notes ?? ''),
                    $entry->entry_type == 'Debit' ? $entry->amount : '',
                    $entry->entry_type == 'Credit' ? $entry->amount : '',
                    abs($runningBalance),
                    $runningBalance >= 0 ? 'Dr' : 'Cr'
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }
}
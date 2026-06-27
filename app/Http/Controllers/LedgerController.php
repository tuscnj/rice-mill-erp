<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\VoucherEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf; // 🚨 Added DOMPDF Facade

class LedgerController extends Controller
{
    public function show($id, Request $request)
    {
        $data = $this->getLedgerData($id, $request);
        return view('ledger', $data);
    }

    // 🚨 NEW METHOD: Triggers the PDF Download
    public function downloadPdf($id, Request $request)
    {
        $data = $this->getLedgerData($id, $request);
        
        $pdf = Pdf::loadView('ledger-pdf', $data);
        
        $fileName = strtolower(str_replace(' ', '-', $data['account']->name)) . '-statement.pdf';
        return $pdf->download($fileName);
    }

    // Reusable core logic so we don't duplicate code
    private function getLedgerData($id, Request $request)
    {
        $account = Account::findOrFail($id);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $isDetailed = $request->has('detailed');
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

        return [
            'account' => $account,
            'entries' => $ledgerEntries,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'openingBalanceRaw' => $openingBalanceRaw,
            'closingBalanceRaw' => $runningBalance,
            'setting' => $setting,
            'isDetailed' => $isDetailed,
        ];
    }

    public function export($id, Request $request)
    {
        $data = $this->getLedgerData($id, $request);
        $fileName = strtolower(str_replace(' ', '-', $data['account']->name)) . '-ledger.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Account Ledger', $data['account']->name]);
            fputcsv($handle, ['From Date', $data['startDate'], 'To Date', $data['endDate']]);
            
            $opType = $data['openingBalanceRaw'] >= 0 ? 'Dr' : 'Cr';
            fputcsv($handle, ['Opening Balance', abs($data['openingBalanceRaw']) . ' ' . $opType]);
            fputcsv($handle, []);
            fputcsv($handle, ['Date', 'Particulars', 'Vch Type', 'Ref / Narration', 'Debit (In)', 'Credit (Out)', 'Balance', 'Dr/Cr']);

            $runningBalance = $data['openingBalanceRaw'];
            foreach ($data['entries'] as $row) {
                $entry = $row['entry'];
                $runningBalance += ($entry->entry_type == 'Debit' ? $entry->amount : -$entry->amount);
                
                $particularsNames = $row['particulars']->pluck('account.name')->implode(', ');
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
<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ItemLedgerController extends Controller
{
    public function show($id, Request $request)
    {
        $item = Item::findOrFail($id);

        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $allMovements = InventoryMovement::where('item_id', $id)
            ->with('voucher')
            ->get();

        $openingBalance = 0;
        foreach ($allMovements as $movement) {
            if ($movement->voucher && $movement->voucher->voucher_date < $startDate) {
                $openingBalance += $movement->movement_type === 'In'
                    ? (float) $movement->quantity
                    : -(float) $movement->quantity;
            }
        }

        $movements = InventoryMovement::where('item_id', $id)
            ->whereHas('voucher', function ($query) use ($startDate, $endDate) {
                $query->whereDate('voucher_date', '>=', $startDate)
                    ->whereDate('voucher_date', '<=', $endDate);
            })
            ->with('voucher')
            ->orderBy('created_at', 'asc')
            ->get();

        $runningBalance = $openingBalance;
        $details = [];

        foreach ($movements as $movement) {
            if ($movement->movement_type === 'In') {
                $runningBalance += (float) $movement->quantity;
                $inQty = (float) $movement->quantity;
                $outQty = 0;
            } else {
                $runningBalance -= (float) $movement->quantity;
                $inQty = 0;
                $outQty = (float) $movement->quantity;
            }

            $details[] = [
                'movement' => $movement,
                'in_qty' => $inQty,
                'out_qty' => $outQty,
                'running_balance' => $runningBalance,
            ];
        }

        return view('item-ledger', compact(
            'item',
            'details',
            'openingBalance',
            'runningBalance',
            'startDate',
            'endDate'
        ));
    }

    public function export($id, Request $request)
    {
        $item = Item::findOrFail($id);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $movements = InventoryMovement::where('item_id', $id)
            ->whereHas('voucher', function ($query) use ($startDate, $endDate) {
                $query->whereDate('voucher_date', '>=', $startDate)
                    ->whereDate('voucher_date', '<=', $endDate);
            })
            ->with('voucher')
            ->orderBy('created_at', 'asc')
            ->get();

        $fileName = 'item-ledger-' . str_replace(' ', '-', strtolower($item->name)) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($movements, $item, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Item Ledger', $item->name]);
            fputcsv($handle, ['From Date', $startDate]);
            fputcsv($handle, ['To Date', $endDate]);
            fputcsv($handle, []);
            fputcsv($handle, ['Date', 'Voucher Type', 'Reference', 'Narration', 'In Qty', 'Out Qty', 'Rate', 'Amount', 'Balance']);

            $balance = 0;
            foreach ($movements as $movement) {
                if ($movement->movement_type === 'In') {
                    $balance += (float) $movement->quantity;
                    $inQty = (float) $movement->quantity;
                    $outQty = 0;
                } else {
                    $balance -= (float) $movement->quantity;
                    $inQty = 0;
                    $outQty = (float) $movement->quantity;
                }

                $amount = (float) $movement->quantity * (float) $movement->rate;
                $voucher = $movement->voucher;

                fputcsv($handle, [
                    $voucher ? $voucher->voucher_date : '',
                    $voucher ? $voucher->voucher_type : '',
                    $voucher ? ($voucher->reference_number ?? '') : '',
                    $voucher ? ($voucher->notes ?? '') : '',
                    $inQty,
                    $outQty,
                    $movement->rate,
                    $amount,
                    $balance,
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }
}
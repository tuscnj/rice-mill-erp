<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\Account;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    public function invoices(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $type = $request->query('type', 'All');
        $search = $request->query('search', '');

        $query = Voucher::with(['entries.account', 'inventoryMovements.item'])
            ->whereDate('voucher_date', '>=', $startDate)
            ->whereDate('voucher_date', '<=', $endDate)
            ->whereIn('voucher_type', ['Sales', 'Purchase', 'Sales Return', 'Purchase Return']);

        if ($type !== 'All') $query->where('voucher_type', $type);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhereHas('entries.account', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->orderBy('voucher_date', 'desc')->orderBy('id', 'desc')->get();
        return view('invoices', compact('invoices', 'startDate', 'endDate', 'type', 'search'));
    }

    // 🚨 NEW HELPER ENGINE: Calculates past balances for invoices flawlessly
    private function getInvoiceData($id)
    {
        $voucher = Voucher::with(['entries.account', 'inventoryMovements.item'])->findOrFail($id);
        $setting = \App\Models\Setting::firstOrCreate(['id' => 1]);

        $partyEntry = $voucher->entries->filter(function ($e) { 
            return in_array($e->account->group_type ?? '', ['Sundry Debtors', 'Sundry Creditors']); 
        })->first();
        
        $party = $partyEntry ? $partyEntry->account : null;
        $totalAmount = $partyEntry ? $partyEntry->amount : $voucher->entries->where('entry_type', 'Debit')->sum('amount');

        $previousBalanceRaw = 0;
        
        // Scan the ledger for every transaction that happened BEFORE this exact invoice
        if ($party) {
            $previousEntries = VoucherEntry::where('account_id', $party->id)
                ->whereHas('voucher', function($q) use ($voucher) {
                    $q->whereDate('voucher_date', '<', $voucher->voucher_date)
                      ->orWhere(function($q2) use ($voucher) {
                          $q2->whereDate('voucher_date', '=', $voucher->voucher_date)
                             ->where('id', '<', $voucher->id);
                      });
                })->get();

            foreach ($previousEntries as $entry) {
                $previousBalanceRaw += ($entry->entry_type == 'Debit' ? $entry->amount : -$entry->amount);
            }
        }
        
        // Add the current invoice to the previous balance to get the new total
        $currentEntryAmount = $partyEntry ? ($partyEntry->entry_type == 'Debit' ? $partyEntry->amount : -$partyEntry->amount) : 0;
        $currentBalanceRaw = $previousBalanceRaw + $currentEntryAmount;

        return compact('voucher', 'setting', 'party', 'totalAmount', 'previousBalanceRaw', 'currentBalanceRaw');
    }

    public function showInvoice($id)
    {
        return view('invoice-preview', $this->getInvoiceData($id));
    }

    public function downloadInvoicePdf($id)
    {
        $data = $this->getInvoiceData($id);
        $pdf = Pdf::loadView('invoice-pdf', $data);
        $fileName = 'Invoice-' . $data['voucher']->voucher_type . '-' . $data['voucher']->id . '.pdf';
        return $pdf->download($fileName);
    }

    // ==========================================
    // EXISTING DAYBOOK & EDIT METHODS
    // ==========================================
    public function index(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $vouchers = Voucher::whereDate('voucher_date', '>=', $startDate)
            ->whereDate('voucher_date', '<=', $endDate)
            ->orderBy('voucher_date', 'desc')
            ->get();

        $voucherIds = $vouchers->pluck('id');

        $movements = InventoryMovement::whereIn('voucher_id', $voucherIds)
            ->leftJoin('items', 'inventory_movements.item_id', '=', 'items.id')
            ->select('inventory_movements.*', 'items.name as item_name', 'items.unit as item_unit')
            ->get()
            ->groupBy('voucher_id');

        $entries = VoucherEntry::whereIn('voucher_id', $voucherIds)
            ->leftJoin('accounts', 'voucher_entries.account_id', '=', 'accounts.id')
            ->select('voucher_entries.*', 'accounts.name as account_name', 'accounts.group_type')
            ->get()
            ->groupBy('voucher_id');

        return view('transactions', compact('vouchers', 'startDate', 'endDate', 'movements', 'entries'));
    }

    public function export(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $vouchers = Voucher::whereDate('voucher_date', '>=', $startDate)
            ->whereDate('voucher_date', '<=', $endDate)
            ->orderBy('voucher_date', 'asc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="daybook-export.csv"',
        ];

        $callback = function () use ($vouchers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Type', 'Reference', 'Narration']);
            foreach ($vouchers as $voucher) {
                fputcsv($handle, [
                    $voucher->voucher_date,
                    $voucher->voucher_type,
                    $voucher->reference_number ?? '',
                    $voucher->notes ?? '',
                ]);
            }
            fclose($handle);
        };
        return Response::stream($callback, 200, $headers);
    }

    public function edit($id)
    {
        $voucher = Voucher::with(['entries.account', 'inventoryMovements.item'])->findOrFail($id);

        if ($voucher->voucher_type == 'Purchase') {
            $suppliers = Account::where('group_type', 'Sundry Creditors')->get();
            $units = Unit::all();
            $items = Item::orderBy('category')->orderBy('name')->get();
            $supplierEntry = $voucher->entries->where('entry_type', 'Credit')->first();
            $partyId = $supplierEntry ? $supplierEntry->account_id : null;
            return view('edit-purchase', compact('voucher', 'suppliers', 'units', 'items', 'partyId'));
        }
        elseif ($voucher->voucher_type == 'Production') {
            $rawMaterials = Item::where('category', 'Raw Material')->get();
            $finishedGoods = Item::where('category', 'Finished Goods')->get();
            $byproducts = Item::where('category', 'Byproduct')->get();
            $units = Unit::all();

            foreach($rawMaterials as $item) $item->display_rate = $item->purchase_rate;
            foreach($finishedGoods->merge($byproducts) as $item) {
                $lastSaleRate = DB::table('inventory_movements')
                    ->join('vouchers', 'inventory_movements.voucher_id', '=', 'vouchers.id')
                    ->where('inventory_movements.item_id', $item->id)
                    ->where('vouchers.voucher_type', 'Sales')
                    ->orderBy('inventory_movements.id', 'desc')
                    ->value('inventory_movements.rate');
                $item->display_rate = $lastSaleRate ?: ($item->purchase_rate ?? 0);
            }

            $rawMovement = $voucher->inventoryMovements->where('movement_type', 'Out')->first();
            $riceMovements = $voucher->inventoryMovements->filter(function($m) { return $m->movement_type == 'In' && $m->item && $m->item->category == 'Finished Goods'; });
            $byproductMovements = $voucher->inventoryMovements->filter(function($m) { return $m->movement_type == 'In' && $m->item && $m->item->category == 'Byproduct'; });

            return view('edit-mill', compact('voucher', 'rawMaterials', 'finishedGoods', 'byproducts', 'units', 'rawMovement', 'riceMovements', 'byproductMovements'));
        }
        elseif ($voucher->voucher_type == 'Sales') {
            $customers = Account::where('group_type', 'Sundry Debtors')->get();
            $units = Unit::all();
            $items = Item::whereIn('category', ['Finished Goods', 'Byproduct'])->orderBy('name')->get();

            $customerEntry = $voucher->entries->where('entry_type', 'Debit')->first();
            $partyId = $customerEntry ? $customerEntry->account_id : null;

            return view('edit-sales', compact('voucher', 'customers', 'units', 'items', 'partyId'));
        }
        elseif ($voucher->voucher_type == 'Expense') return view('edit-expense', compact('voucher'));
        elseif ($voucher->voucher_type == 'Receipt') return view('edit-receipt', compact('voucher'));
        elseif ($voucher->voucher_type == 'Other Income') return view('edit-other-income', compact('voucher'));
        elseif ($voucher->voucher_type == 'Sales Return') {
            $customers = Account::where('group_type', 'Sundry Debtors')->get();
            $units = Unit::all();
            $items = Item::whereIn('category', ['Finished Goods', 'Byproduct'])->orderBy('name')->get();

            $customerEntry = $voucher->entries->where('entry_type', 'Credit')->first();
            $partyId = $customerEntry ? $customerEntry->account_id : null;

            return view('edit-sales-return', compact('voucher', 'customers', 'units', 'items', 'partyId'));
        }
        elseif ($voucher->voucher_type == 'Balance Transfer') {
            $accounts = Account::orderBy('name')->get();
            return view('edit-balance-transfer', compact('voucher', 'accounts'));
        }
        elseif ($voucher->voucher_type == 'Journal' || $voucher->voucher_type == 'Stock Adjustment') {
            $items = Item::orderBy('name')->get();
            return view('edit-stock-adjustment', compact('voucher', 'items'));
        }
        elseif ($voucher->voucher_type == 'Purchase Return') {
            $suppliers = Account::where('group_type', 'Sundry Creditors')->get();
            $units = Unit::all();
            $items = Item::orderBy('category')->orderBy('name')->get();

            $supplierEntry = $voucher->entries->where('entry_type', 'Debit')->first();
            $partyId = $supplierEntry ? $supplierEntry->account_id : null;

            return view('edit-purchase-return', compact('voucher', 'suppliers', 'units', 'items', 'partyId'));
        }

        return redirect('/transactions')->with('error', 'The full edit screen is currently under construction!');
    }
    
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $voucher = Voucher::findOrFail($id);

            // 1. REVERSE PHYSICAL STOCK
            $movements = InventoryMovement::where('voucher_id', $id)->get();
            foreach ($movements as $movement) {
                if ($movement->movement_type == 'In') {
                    Item::where('id', $movement->item_id)->decrement('current_stock', $movement->quantity);
                } else if ($movement->movement_type == 'Out') {
                    Item::where('id', $movement->item_id)->increment('current_stock', $movement->quantity);
                }
            }
            InventoryMovement::where('voucher_id', $id)->delete();

            // 2. REVERSE FINANCIAL BALANCES
            $entries = VoucherEntry::where('voucher_id', $id)->get();
            foreach ($entries as $entry) {
                $account = Account::find($entry->account_id);
                if ($account) {
                    if ($account->group_type == 'Sundry Debtors') {
                        $entry->entry_type == 'Debit' ? $account->decrement('balance', $entry->amount) : $account->increment('balance', $entry->amount);
                    } 
                    elseif ($account->group_type == 'Sundry Creditors') {
                        $entry->entry_type == 'Credit' ? $account->decrement('balance', $entry->amount) : $account->increment('balance', $entry->amount);
                    } 
                    elseif ($account->group_type == 'Cash') {
                        $entry->entry_type == 'Debit' ? $account->decrement('balance', $entry->amount) : $account->increment('balance', $entry->amount);
                    } 
                    elseif ($account->group_type == 'Indirect Expenses') {
                        if ($entry->entry_type == 'Debit') $account->decrement('balance', $entry->amount);
                    }
                    elseif ($account->group_type == 'Indirect Incomes') {
                        if ($entry->entry_type == 'Credit') $account->increment('balance', $entry->amount);
                    }
                    elseif ($account->group_type == 'Direct Incomes') {
                        if ($entry->entry_type == 'Credit') $account->decrement('balance', $entry->amount); 
                    }
                    elseif ($account->group_type == 'Direct Expenses') {
                        if ($entry->entry_type == 'Debit') $account->decrement('balance', $entry->amount); 
                    }
                }
            }
            VoucherEntry::where('voucher_id', $id)->delete();
            $voucher->delete();
        });

        return redirect('/transactions');
    }
}
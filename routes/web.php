<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB; // Added DB facade for the query

// ==========================================
// 1. PUBLIC ROUTES (Login System)
// ==========================================
Route::get('/login', [App\Http\Controllers\AuthController::class, 'show'])->name('login');
Route::post('/run-login', [App\Http\Controllers\AuthController::class, 'authenticate']);
Route::get('/setup-admin', [App\Http\Controllers\AuthController::class, 'setupAdmin']);

// ==========================================
// 2. SECURE VAULT (Requires Login)
// ==========================================
Route::middleware('auth')->group(function () {

    // --- LOGOUT ---
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);

    // --- DASHBOARDS ---
    Route::get('/', [App\Http\Controllers\DashboardController::class, 'index']);
    
    Route::get('/stock', [App\Http\Controllers\ItemController::class, 'stock']);

    // --- OPERATIONS: PURCHASE ---
    Route::get('/purchase', function () {
        $suppliers = App\Models\Account::where('group_type', 'Sundry Creditors')->get();
        $customers = App\Models\Account::where('group_type', 'Sundry Debtors')->get();
        $units = App\Models\Unit::all(); 
        $items = App\Models\Item::orderBy('category')->orderBy('name')->get(); 
        return view('purchase', [
            'suppliers' => $suppliers,
            'customers' => $customers,
            'units' => $units,
            'items' => $items
        ]);
    });
    Route::post('/run-purchase', [App\Http\Controllers\PurchaseController::class, 'store']);
    Route::get('/run-purchase', function () { return redirect('/purchase'); }); 

    // --- OPERATIONS: MILLING ---
Route::get('/edit-transaction/{id}', [App\Http\Controllers\TransactionController::class, 'edit']);
Route::post('/update-purchase/{id}', [App\Http\Controllers\PurchaseController::class, 'update']);
Route::post('/update-mill/{id}', [App\Http\Controllers\MillController::class, 'update']); // <-- ADD THIS LINE
    Route::get('/mill', function () {
        $rawMaterials = App\Models\Item::where('category', 'Raw Material')->get();
        $finishedGoods = App\Models\Item::where('category', 'Finished Goods')->get();
        $byproducts = App\Models\Item::where('category', 'Byproduct')->get();
        $units = App\Models\Unit::all();
        
        // --- SMART PRICING ENGINE ---
        // 1. Raw Materials use Moving Average (purchase_rate)
        foreach($rawMaterials as $item) {
            $item->display_rate = $item->purchase_rate;
        }

        // 2. Finished Goods & Byproducts scan database for Last Sales Price
        foreach($finishedGoods->merge($byproducts) as $item) {
            $lastSaleRate = DB::table('inventory_movements')
                ->join('vouchers', 'inventory_movements.voucher_id', '=', 'vouchers.id')
                ->where('inventory_movements.item_id', $item->id)
                ->where('vouchers.voucher_type', 'Sales')
                ->orderBy('inventory_movements.id', 'desc')
                ->value('inventory_movements.rate');
                
            // If it has never been sold, fallback to its valuation rate
            $item->display_rate = $lastSaleRate ?: ($item->purchase_rate ?? 0);
        }

        return view('mill', [
            'rawMaterials' => $rawMaterials,
            'finishedGoods' => $finishedGoods,
            'byproducts' => $byproducts,
            'units' => $units
        ]);
    });
    Route::post('/run-mill', [App\Http\Controllers\MillController::class, 'store']);
    Route::get('/run-mill', function () { return redirect('/mill'); }); 

    // --- OPERATIONS: SALES ---
    Route::get('/sales', function () {
        $customers = App\Models\Account::where('group_type', 'Sundry Debtors')->get();
        $suppliers = App\Models\Account::where('group_type', 'Sundry Creditors')->get();
        $units = App\Models\Unit::all();
        $items = App\Models\Item::whereIn('category', ['Finished Goods', 'Byproduct'])->orderBy('name')->get();
        return view('sales', [
            'customers' => $customers,
            'suppliers' => $suppliers,
            'units' => $units,
            'items' => $items
        ]);
    });
    Route::post('/run-sales', [App\Http\Controllers\SalesController::class, 'store']);
    Route::get('/run-sales', function () { return redirect('/sales'); }); 

    // --- OPERATIONS: RETURNS ---
    Route::get('/sales-return', function () {
        $customers = App\Models\Account::where('group_type', 'Sundry Debtors')->get();
        $units = App\Models\Unit::all();
        $items = App\Models\Item::whereIn('category', ['Finished Goods', 'Byproduct'])->orderBy('name')->get();
        return view('sales-return', [
            'customers' => $customers,
            'units' => $units,
            'items' => $items,
        ]);
    });
    Route::post('/run-sales-return', [App\Http\Controllers\SalesReturnController::class, 'store']);
    Route::get('/run-sales-return', function () { return redirect('/sales-return'); }); 

    Route::get('/purchase-return', function () {
        $suppliers = App\Models\Account::where('group_type', 'Sundry Creditors')->get();
        $units = App\Models\Unit::all();
        $items = App\Models\Item::orderBy('category')->orderBy('name')->get();
        return view('purchase-return', [
            'suppliers' => $suppliers,
            'units' => $units,
            'items' => $items,
        ]);
    });
    Route::post('/run-purchase-return', [App\Http\Controllers\PurchaseReturnController::class, 'store']);
    Route::get('/run-purchase-return', function () { return redirect('/purchase-return'); }); 

    // --- OPERATIONS: STOCK ADJUSTMENT ---
    Route::get('/stock-adjustment', [App\Http\Controllers\StockAdjustmentController::class, 'index']);
    Route::post('/run-stock-adjustment', [App\Http\Controllers\StockAdjustmentController::class, 'store']);

    // --- FINANCIALS: JOURNAL / BALANCE TRANSFER ---
    Route::get('/balance-transfer', function () {
        $accounts = App\Models\Account::orderBy('name')->get();
        return view('balance-transfer', ['accounts' => $accounts]);
    });
    Route::post('/run-balance-transfer', [App\Http\Controllers\BalanceTransferController::class, 'store']);
    Route::get('/run-balance-transfer', function () { return redirect('/balance-transfer'); }); 

    // --- FINANCIALS: PAYMENTS & RECEIPTS ---
    Route::get('/ledger/{id}', [App\Http\Controllers\LedgerController::class, 'show']);
    Route::get('/ledger/{id}/export', [App\Http\Controllers\LedgerController::class, 'export']);
    Route::get('/item-ledger/{id}', [App\Http\Controllers\ItemLedgerController::class, 'show']);
    Route::get('/item-ledger/{id}/export', [App\Http\Controllers\ItemLedgerController::class, 'export']);
    
    Route::get('/payment', function () {
        App\Models\Account::firstOrCreate(
            ['name' => 'Cash in Hand'], 
            ['group_type' => 'Cash', 'balance' => 500000]
        );
        $banks = App\Models\Account::where('group_type', 'Cash')->get();
        // Load BOTH Suppliers and Customers
        $parties = App\Models\Account::whereIn('group_type', ['Sundry Creditors', 'Sundry Debtors'])->orderBy('name')->get();
        return view('payment', ['banks' => $banks, 'parties' => $parties]);
    });
    Route::post('/run-payment', [App\Http\Controllers\PaymentController::class, 'store']);
    Route::get('/run-payment', function () { return redirect('/payment'); }); 

    Route::get('/receipt', function () {
        $banks = App\Models\Account::where('group_type', 'Cash')->get();
        // Load BOTH Suppliers and Customers
        $parties = App\Models\Account::whereIn('group_type', ['Sundry Creditors', 'Sundry Debtors'])->orderBy('name')->get();
        return view('receipt', ['banks' => $banks, 'parties' => $parties]);
    });
    Route::post('/run-receipt', [App\Http\Controllers\ReceiptController::class, 'store']);
    Route::get('/run-receipt', function () { return redirect('/receipt'); }); 

    Route::get('/expense', function () {
        $banks = App\Models\Account::where('group_type', 'Cash')->get();
        $expenses = App\Models\Account::where('group_type', 'Indirect Expenses')->get();
        return view('expense', ['banks' => $banks, 'expenses' => $expenses]);
    });
    Route::post('/run-expense', [App\Http\Controllers\ExpenseController::class, 'store']);
    Route::get('/run-expense', function () { return redirect('/expense'); }); 

    Route::get('/other-income', function () {
        $banks = App\Models\Account::where('group_type', 'Cash')->get();
        $incomes = App\Models\Account::where('group_type', 'Indirect Incomes')->get();
        return view('other-income', ['banks' => $banks, 'incomes' => $incomes]);
    });
    Route::post('/run-other-income', [App\Http\Controllers\OtherIncomeController::class, 'store']);
    Route::get('/run-other-income', function () { return redirect('/other-income'); }); 

    // --- REPORTS & DAYBOOK ---
    Route::get('/report', [App\Http\Controllers\ReportController::class, 'profitAndLoss']);
    Route::get('/transactions', [App\Http\Controllers\TransactionController::class, 'index']);
    Route::get('/edit-transaction/{id}', [App\Http\Controllers\TransactionController::class, 'edit']); // THE NEW EDIT ROUTE
    Route::get('/transactions/export', [App\Http\Controllers\TransactionController::class, 'export']);
    Route::post('/delete-transaction/{id}', [App\Http\Controllers\TransactionController::class, 'destroy']);
    
    Route::get('/transactions', [App\Http\Controllers\TransactionController::class, 'index']);
    Route::get('/transactions/export', [App\Http\Controllers\TransactionController::class, 'export']);
    Route::post('/delete-transaction/{id}', [App\Http\Controllers\TransactionController::class, 'destroy']);

    // --- DIRECTORIES: ACCOUNTS ---
    Route::get('/accounts', [App\Http\Controllers\AccountController::class, 'index']);
    Route::post('/run-account', [App\Http\Controllers\AccountController::class, 'store']);
    Route::get('/run-account', function () { return redirect('/accounts'); }); 
    Route::get('/edit-account/{id}', [App\Http\Controllers\AccountController::class, 'edit']);
    Route::post('/update-account/{id}', [App\Http\Controllers\AccountController::class, 'update']);
    Route::get('/delete-account/{id}', [App\Http\Controllers\AccountController::class, 'destroy']);

// --- DIRECTORIES: ITEMS ---
    Route::post('/items', [App\Http\Controllers\ItemController::class, 'store']); 
    Route::post('/run-item', [App\Http\Controllers\ItemController::class, 'store']); 
    Route::get('/run-item', function () { return redirect('/stock'); }); // Redirect to stock
    Route::get('/edit-item/{id}', [App\Http\Controllers\ItemController::class, 'edit']);
    Route::post('/update-item/{id}', [App\Http\Controllers\ItemController::class, 'update']);
    Route::get('/delete-item/{id}', [App\Http\Controllers\ItemController::class, 'destroy']);

    // --- DIRECTORIES: UNITS ---
    Route::get('/units', [App\Http\Controllers\UnitController::class, 'index']);
    Route::post('/run-unit', [App\Http\Controllers\UnitController::class, 'store']);
    Route::get('/run-unit', function () { return redirect('/units'); }); 
    Route::get('/edit-unit/{id}', [App\Http\Controllers\UnitController::class, 'edit']);
    Route::post('/update-unit/{id}', [App\Http\Controllers\UnitController::class, 'update']);
    Route::get('/delete-unit/{id}', [App\Http\Controllers\UnitController::class, 'destroy']);

    // --- INITIAL SETUP MACROS ---
    Route::get('/setup-accounts', function () {
        // 🚨 FIX: Updated all of these to check by Name so it doesn't duplicate if ID is taken
        App\Models\Account::firstOrCreate(['name' => 'Purchase Account'], ['group_type' => 'Direct Expenses', 'balance' => 0]);
        App\Models\Account::firstOrCreate(['name' => 'Rahman Traders (Supplier)'], ['group_type' => 'Sundry Creditors', 'balance' => 0]);
        App\Models\Account::firstOrCreate(['name' => 'Karim Farmers (Supplier)'], ['group_type' => 'Sundry Creditors', 'balance' => 0]);
        return "Accounts setup complete!";
    });

    Route::get('/setup-customers', function () {
        App\Models\Account::firstOrCreate(['name' => 'Sales Account'], ['group_type' => 'Direct Incomes', 'balance' => 0]);
        App\Models\Account::firstOrCreate(['name' => 'Mahi Groceries (Customer)'], ['group_type' => 'Sundry Debtors', 'balance' => 0]);
        return "Customers setup complete!";
    });

    Route::get('/setup-expenses', function () {
        App\Models\Account::firstOrCreate(['name' => 'Worker Wages'], ['group_type' => 'Indirect Expenses', 'balance' => 0]);
        App\Models\Account::firstOrCreate(['name' => 'Electricity Bill'], ['group_type' => 'Indirect Expenses', 'balance' => 0]);
        App\Models\Account::firstOrCreate(['name' => 'Mill Maintenance'], ['group_type' => 'Indirect Expenses', 'balance' => 0]);
        return "Expense accounts created!";
    });
});

Route::post('/update-purchase/{id}', [App\Http\Controllers\PurchaseController::class, 'update']);

Route::get('/edit-transaction/{id}', [App\Http\Controllers\TransactionController::class, 'edit']);
Route::post('/update-purchase/{id}', [App\Http\Controllers\PurchaseController::class, 'update']);
Route::post('/update-mill/{id}', [App\Http\Controllers\MillController::class, 'update']);
Route::post('/update-sales/{id}', [App\Http\Controllers\SalesController::class, 'update']); // <-- ADD THIS LINE

// --- AUTOMATED DEPLOYMENT WEBHOOK ---
Route::get('/auto-deploy-cache-clear/{secret}', function($secret) {
    // Only allow the GitHub robot with this exact password to trigger this
    if ($secret !== 'AtikAutoDeploy2026!') {
        return 'Unauthorized';
    }
    
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return 'Deployment Complete: All caches cleared successfully!';
});
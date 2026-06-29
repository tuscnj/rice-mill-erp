<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

Route::get('/login', [App\Http\Controllers\AuthController::class, 'show'])->name('login');
Route::post('/run-login', [App\Http\Controllers\AuthController::class, 'authenticate']);
Route::get('/setup-admin', [App\Http\Controllers\AuthController::class, 'setupAdmin']);
Route::get('/upgrade-me', [App\Http\Controllers\AuthController::class, 'upgradeMe']);

// 🚨 LIVE SERVER MIGRATION COMMAND 
Route::get('/force-migrate', function () {
    Artisan::call('migrate', ['--force' => true]);
    return "Live Database Successfully Migrated! The new account fields are ready.";
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::get('/', [App\Http\Controllers\DashboardController::class, 'index']);
    Route::get('/stock', [App\Http\Controllers\ItemController::class, 'stock']);

    // --- INVOICE CENTER ROUTES ---
    Route::get('/invoices', [App\Http\Controllers\TransactionController::class, 'invoices']);
    Route::get('/invoice/{id}', [App\Http\Controllers\TransactionController::class, 'showInvoice']);
    Route::get('/invoice/{id}/pdf', [App\Http\Controllers\TransactionController::class, 'downloadInvoicePdf']);

    // --- DAYBOOK VIEW ---
    Route::get('/transactions', [App\Http\Controllers\TransactionController::class, 'index']);
    Route::get('/transactions/export', [App\Http\Controllers\TransactionController::class, 'export']);

    // ==========================================
    // 🚨 OPERATIONS ZONE: INACTIVE LOCK APPLIED
    // ==========================================
    Route::get('/purchase', function () { 
        return view('purchase', [
            'suppliers' => App\Models\Account::where('group_type', 'Sundry Creditors')->where('is_active', true)->orderBy('name')->get(), 
            'customers' => App\Models\Account::where('group_type', 'Sundry Debtors')->where('is_active', true)->orderBy('name')->get(), 
            'units' => App\Models\Unit::all(), 
            'items' => App\Models\Item::orderBy('category')->orderBy('name')->get()
        ]); 
    });
    Route::post('/run-purchase', [App\Http\Controllers\PurchaseController::class, 'store']);
    
    Route::get('/mill', function () {
        $rawMaterials = App\Models\Item::where('category', 'Raw Material')->get();
        $finishedGoods = App\Models\Item::where('category', 'Finished Goods')->get();
        $byproducts = App\Models\Item::where('category', 'Byproduct')->get();
        foreach($rawMaterials as $item) { $item->display_rate = $item->purchase_rate; }
        foreach($finishedGoods->merge($byproducts) as $item) {
            $item->display_rate = DB::table('inventory_movements')->join('vouchers', 'inventory_movements.voucher_id', '=', 'vouchers.id')->where('inventory_movements.item_id', $item->id)->where('vouchers.voucher_type', 'Sales')->orderBy('inventory_movements.id', 'desc')->value('inventory_movements.rate') ?: ($item->purchase_rate ?? 0);
        }
        return view('mill', ['rawMaterials' => $rawMaterials, 'finishedGoods' => $finishedGoods, 'byproducts' => $byproducts, 'units' => App\Models\Unit::all()]);
    });
    Route::post('/run-mill', [App\Http\Controllers\MillController::class, 'store']);
    
    Route::get('/sales', function () { 
        return view('sales', [
            'customers' => App\Models\Account::where('group_type', 'Sundry Debtors')->where('is_active', true)->orderBy('name')->get(), 
            'suppliers' => App\Models\Account::where('group_type', 'Sundry Creditors')->where('is_active', true)->orderBy('name')->get(), 
            'units' => App\Models\Unit::all(), 
            'items' => App\Models\Item::whereIn('category', ['Finished Goods', 'Byproduct'])->orderBy('name')->get()
        ]); 
    });
    Route::post('/run-sales', [App\Http\Controllers\SalesController::class, 'store']);
    
    Route::get('/sales-return', function () { 
        return view('sales-return', [
            'customers' => App\Models\Account::where('group_type', 'Sundry Debtors')->where('is_active', true)->orderBy('name')->get(), 
            'units' => App\Models\Unit::all(), 
            'items' => App\Models\Item::whereIn('category', ['Finished Goods', 'Byproduct'])->orderBy('name')->get()
        ]); 
    });
    Route::post('/run-sales-return', [App\Http\Controllers\SalesReturnController::class, 'store']);
    
    Route::get('/purchase-return', function () { 
        return view('purchase-return', [
            'suppliers' => App\Models\Account::where('group_type', 'Sundry Creditors')->where('is_active', true)->orderBy('name')->get(), 
            'units' => App\Models\Unit::all(), 
            'items' => App\Models\Item::orderBy('category')->orderBy('name')->get()
        ]); 
    });
    Route::post('/run-purchase-return', [App\Http\Controllers\PurchaseReturnController::class, 'store']);
    
    Route::get('/stock-adjustment', [App\Http\Controllers\StockAdjustmentController::class, 'index']);
    Route::post('/run-stock-adjustment', [App\Http\Controllers\StockAdjustmentController::class, 'store']);
    
    Route::get('/balance-transfer', function () { 
        return view('balance-transfer', [
            'accounts' => App\Models\Account::where('is_active', true)->orderBy('name')->get()
        ]); 
    });
    Route::post('/run-balance-transfer', [App\Http\Controllers\BalanceTransferController::class, 'store']);
    
    Route::get('/payment', function () { 
        App\Models\Account::firstOrCreate(['name' => 'Cash in Hand'], ['group_type' => 'Cash', 'balance' => 500000]); 
        return view('payment', [
            'banks' => App\Models\Account::where('group_type', 'Cash')->where('is_active', true)->orderBy('name')->get(), 
            'parties' => App\Models\Account::whereIn('group_type', ['Sundry Creditors', 'Sundry Debtors'])->where('is_active', true)->orderBy('name')->get()
        ]); 
    });
    Route::post('/run-payment', [App\Http\Controllers\PaymentController::class, 'store']);
    
    Route::get('/receipt', function () { 
        return view('receipt', [
            'banks' => App\Models\Account::where('group_type', 'Cash')->where('is_active', true)->orderBy('name')->get(), 
            'parties' => App\Models\Account::whereIn('group_type', ['Sundry Creditors', 'Sundry Debtors'])->where('is_active', true)->orderBy('name')->get()
        ]); 
    });
    Route::post('/run-receipt', [App\Http\Controllers\ReceiptController::class, 'store']);
    
    Route::get('/expense', function () { 
        return view('expense', [
            'banks' => App\Models\Account::where('group_type', 'Cash')->where('is_active', true)->orderBy('name')->get(), 
            'expenses' => App\Models\Account::where('group_type', 'Indirect Expenses')->where('is_active', true)->orderBy('name')->get()
        ]); 
    });
    Route::post('/run-expense', [App\Http\Controllers\ExpenseController::class, 'store']);
    
    Route::get('/other-income', function () { 
        return view('other-income', [
            'banks' => App\Models\Account::where('group_type', 'Cash')->where('is_active', true)->orderBy('name')->get(), 
            'incomes' => App\Models\Account::where('group_type', 'Indirect Incomes')->where('is_active', true)->orderBy('name')->get()
        ]); 
    });
    Route::post('/run-other-income', [App\Http\Controllers\OtherIncomeController::class, 'store']);

    // ==========================================
    // 3. ADMIN ZONE (STRICT CLEARANCE ONLY)
    // ==========================================
    Route::middleware([\App\Http\Middleware\Admin::class])->group(function () {
        Route::get('/report', [App\Http\Controllers\ReportController::class, 'index']);
        Route::post('/update-payment/{id}', [App\Http\Controllers\PaymentController::class, 'update']);
        Route::post('/delete-transaction/{id}', [App\Http\Controllers\TransactionController::class, 'destroy']);
        Route::get('/edit-transaction/{id}', [App\Http\Controllers\TransactionController::class, 'edit']);
        Route::post('/update-purchase/{id}', [App\Http\Controllers\PurchaseController::class, 'update']);
        Route::post('/update-mill/{id}', [App\Http\Controllers\MillController::class, 'update']);
        Route::post('/update-sales/{id}', [App\Http\Controllers\SalesController::class, 'update']);
        Route::post('/update-sales-return/{id}', [App\Http\Controllers\SalesReturnController::class, 'update']);
        Route::post('/update-purchase-return/{id}', [App\Http\Controllers\PurchaseReturnController::class, 'update']);
        Route::post('/update-expense/{id}', [App\Http\Controllers\ExpenseController::class, 'update']);
        Route::post('/update-receipt/{id}', [App\Http\Controllers\ReceiptController::class, 'update']);
        Route::post('/update-other-income/{id}', [App\Http\Controllers\OtherIncomeController::class, 'update']);
        Route::post('/update-balance-transfer/{id}', [App\Http\Controllers\BalanceTransferController::class, 'update']);
        Route::post('/update-stock-adjustment/{id}', [App\Http\Controllers\StockAdjustmentController::class, 'update']);
        
        Route::get('/ledger/{id}', [App\Http\Controllers\LedgerController::class, 'show']);
        Route::get('/ledger/{id}/export', [App\Http\Controllers\LedgerController::class, 'export']);
        Route::get('/ledger/{id}/pdf', [App\Http\Controllers\LedgerController::class, 'downloadPdf']);
        
        Route::get('/item-ledger/{id}', [App\Http\Controllers\ItemLedgerController::class, 'show']);
        Route::get('/item-ledger/{id}/export', [App\Http\Controllers\ItemLedgerController::class, 'export']);

        Route::get('/accounts', [App\Http\Controllers\AccountController::class, 'index']);
        Route::post('/run-account', [App\Http\Controllers\AccountController::class, 'store']);
        Route::get('/edit-account/{id}', [App\Http\Controllers\AccountController::class, 'edit']);
        Route::post('/update-account/{id}', [App\Http\Controllers\AccountController::class, 'update']);
        Route::get('/delete-account/{id}', [App\Http\Controllers\AccountController::class, 'destroy']);
        
        Route::get('/users', [App\Http\Controllers\UserController::class, 'index']);
        Route::post('/run-user', [App\Http\Controllers\UserController::class, 'store']);
        Route::post('/update-user/{id}', [App\Http\Controllers\UserController::class, 'update']);
        Route::get('/delete-user/{id}', [App\Http\Controllers\UserController::class, 'destroy']);

        Route::post('/items', [App\Http\Controllers\ItemController::class, 'store']); 
        Route::get('/edit-item/{id}', [App\Http\Controllers\ItemController::class, 'edit']);
        Route::post('/update-item/{id}', [App\Http\Controllers\ItemController::class, 'update']);
        Route::get('/delete-item/{id}', [App\Http\Controllers\ItemController::class, 'destroy']);

        Route::get('/units', [App\Http\Controllers\UnitController::class, 'index']);
        Route::post('/run-unit', [App\Http\Controllers\UnitController::class, 'store']);
        Route::get('/edit-unit/{id}', [App\Http\Controllers\UnitController::class, 'edit']);
        Route::post('/update-unit/{id}', [App\Http\Controllers\UnitController::class, 'update']);
        Route::get('/delete-unit/{id}', [App\Http\Controllers\UnitController::class, 'destroy']);

        Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index']);
        Route::post('/update-settings', [App\Http\Controllers\SettingsController::class, 'update']);

        // 🚨 MAGIC MIGRATE ROUTE FOR cPANEL
Route::get('/run-migrations', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return '<h1>✅ Database Updated Successfully!</h1><p>The item_group column has been created. You can now close this tab and go back to your ERP.</p>';
    } catch (\Exception $e) {
        return '<h1>❌ Error:</h1><p>' . $e->getMessage() . '</p>';
    }
});

    });
});

use App\Http\Controllers\BrandController;

// Brand Management Routes
Route::get('/brands', [BrandController::class, 'index']);
Route::post('/brands', [BrandController::class, 'store']);
Route::get('/delete-brand/{id}', [BrandController::class, 'destroy']);

// 🚨 MAGIC MIGRATE ROUTE
Route::get('/setup-brands', function () {
    try {
        if (!\Illuminate\Support\Facades\Schema::hasTable('brands')) {
            \Illuminate\Support\Facades\Schema::create('brands', function ($table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
            return '<h1>✅ Brands Table Created Successfully!</h1><p>You can close this tab and go back to your ERP.</p>';
        }
        return '<h1>Brands table already exists!</h1>';
    } catch (\Exception $e) {
        return '<h1>❌ Error:</h1><p>' . $e->getMessage() . '</p>';
    }
});

Route::get('/auto-deploy-cache-clear/{secret}', function($secret) {
    if ($secret !== 'AtikAutoDeploy2026!') return 'Unauthorized';
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return 'Deployment Complete: All caches cleared successfully!';
});
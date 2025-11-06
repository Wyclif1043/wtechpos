<?php
// routes/web.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\CustomerCreditController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\CustomerReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\WarrantyClaimController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ProductMovementController;
use App\Http\Controllers\ProductWarrantyController;


use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Registration Routes
Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register');

// Password Reset Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Audit Log Routes
Route::prefix('audit')->group(function () {
    Route::get('/', [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('/{activity}', [AuditLogController::class, 'show'])->name('audit.show');
    Route::get('/user/{userId}', [AuditLogController::class, 'userActivity'])->name('audit.user-activity');
});
// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Products Management
    Route::resource('products', ProductController::class);
    Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::post('/products/bulk-stock-update', [ProductController::class, 'bulkUpdateStock'])->name('products.bulk-stock-update');
    // Product regeneration routes
    Route::patch('/products/{product}/regenerate-barcode', [ProductController::class, 'regenerateBarcode'])->name('products.regenerate-barcode');
    Route::patch('/products/{product}/regenerate-sku', [ProductController::class, 'regenerateSKU'])->name('products.regenerate-sku');
    Route::get('/branches/{branch}/products', [ProductController::class, 'index'])->name('branches.products.index');
    Route::resource('categories', CategoryController::class);

    // POS Routes
    Route::get('/pos', [SaleController::class, 'posInterface'])->name('pos.interface');
    Route::post('/pos/add-to-cart', [SaleController::class, 'addToCart'])->name('pos.add-to-cart');
    Route::post('/pos/complete-sale', [SaleController::class, 'completeSale'])->name('pos.complete-sale');
    Route::get('/pos/get-cart', [SaleController::class, 'getCart'])->name('pos.get-cart');
    Route::post('/pos/update-cart', [SaleController::class, 'updateCart'])->name('pos.update-cart');
    Route::get('/pos/search-products', [SaleController::class, 'searchProducts'])->name('pos.search-products');

    // Inventory Routes
    Route::prefix('inventory')->group(function () {
        Route::get('/dashboard', [InventoryController::class, 'dashboard'])->name('inventory.dashboard');
        Route::get('/products', [InventoryController::class, 'products'])->name('inventory.products');
        Route::get('/stock-movements', [InventoryController::class, 'stockMovements'])->name('inventory.stock-movements');
        Route::get('/low-stock-report', [InventoryController::class, 'lowStockReport'])->name('inventory.low-stock-report');
        Route::get('/stock-valuation', [InventoryController::class, 'stockValuation'])->name('inventory.stock-valuation');
    });

    // Purchase Order Routes
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    // Stock Adjustment Routes
    Route::resource('stock-adjustments', StockAdjustmentController::class);

    // Customer Routes (Single definition - remove the duplicate)
    Route::resource('customers', CustomerController::class);
    Route::get('/customers/{customer}/sales-history', [CustomerController::class, 'salesHistory'])->name('customers.sales-history');

    // Customer Credit Routes
    Route::prefix('customers/{customer}/credit')->group(function () {
        Route::get('/', [CustomerCreditController::class, 'show'])->name('customers.credit.show');
        Route::get('/payment', [CustomerCreditController::class, 'createPayment'])->name('customers.credit.payment');
        Route::post('/payment', [CustomerCreditController::class, 'processPayment'])->name('customers.credit.payment.process');
        Route::post('/adjust', [CustomerCreditController::class, 'adjustCredit'])->name('customers.credit.adjust');
    });

    // Customer Loyalty Routes
    Route::prefix('customers/{customer}/loyalty')->group(function () {
        Route::get('/', [LoyaltyController::class, 'show'])->name('customers.loyalty.show');
        Route::post('/redeem', [LoyaltyController::class, 'redeemPoints'])->name('customers.loyalty.redeem');
        Route::post('/adjust', [LoyaltyController::class, 'adjustPoints'])->name('customers.loyalty.adjust');
    });

    // Credit Management Routes
    Route::get('/customer-credit', [CustomerCreditController::class, 'index'])->name('customer-credit.index');
    Route::get('/customer-loyalty', [LoyaltyController::class, 'index'])->name('customer-loyalty.index');

    // Sales Routes
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/daily-report', [SaleController::class, 'dailyReport'])->name('sales.daily');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');

    // Reporting Routes
    Route::prefix('reports')->group(function () {
        // Dashboard
        Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
        Route::post('/export', [ReportController::class, 'export'])->name('reports.export');

        // Sales Reports
        Route::prefix('sales')->group(function () {
            Route::get('/summary', [SalesReportController::class, 'salesSummary'])->name('reports.sales.summary');
            Route::get('/detailed', [SalesReportController::class, 'detailedSales'])->name('reports.sales.detailed');
            Route::get('/product-performance', [SalesReportController::class, 'productPerformance'])->name('reports.sales.product-performance');
            Route::get('/tax-report', [SalesReportController::class, 'taxReport'])->name('reports.sales.tax-report');
        });

        // Inventory Reports
        Route::prefix('inventory')->group(function () {
            Route::get('/stock-levels', [InventoryReportController::class, 'stockLevels'])->name('reports.inventory.stock-levels');
            Route::get('/movement-report', [InventoryReportController::class, 'movementReport'])->name('reports.inventory.movement-report');
            Route::get('/slow-moving', [InventoryReportController::class, 'slowMoving'])->name('reports.inventory.slow-moving');
            Route::get('/stock-valuation', [InventoryReportController::class, 'stockValuation'])->name('reports.inventory.stock-valuation');
        });

        // Customer Reports
        Route::prefix('customers')->group(function () {
            Route::get('/spending', [CustomerReportController::class, 'customerSpending'])->name('reports.customers.spending');
            Route::get('/frequency', [CustomerReportController::class, 'customerFrequency'])->name('reports.customers.frequency');
            Route::get('/loyalty', [CustomerReportController::class, 'customerLoyalty'])->name('reports.customers.loyalty');
            Route::get('/acquisition', [CustomerReportController::class, 'customerAcquisition'])->name('reports.customers.acquisition');
        });
    });

    // User Management Routes
    Route::middleware(['auth'])->group(function () {
        Route::resource('users', UserController::class);
        
        Route::prefix('users/{user}')->group(function () {
            Route::get('/permissions', [UserPermissionController::class, 'edit'])->name('users.permissions.edit');
            Route::put('/permissions', [UserPermissionController::class, 'update'])->name('users.permissions.update');
            Route::patch('/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::put('/password', [UserController::class, 'updatePassword'])->name('users.password.update');
        });
    });

    // Supplier Routes
    Route::resource('suppliers', SupplierController::class);
    Route::patch('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');
    Route::get('/suppliers/{supplier}/products', [SupplierController::class, 'products'])->name('suppliers.products');
    Route::get('/suppliers/{supplier}/purchase-orders', [SupplierController::class, 'purchaseOrders'])->name('suppliers.purchase-orders');

    // PDF Routes
    Route::get('/purchase-orders/{purchaseOrder}/pdf', [PurchaseOrderController::class, 'purchaseOrderPdf'])->name('purchase-orders.pdf');
    Route::get('/purchase-orders/{purchaseOrder}/delivery-note-pdf', [PurchaseOrderController::class, 'deliveryNotePdf'])->name('purchase-orders.delivery-note-pdf');
    Route::get('/purchase-orders/{purchaseOrder}/grn-pdf', [PurchaseOrderController::class, 'grnPdf'])->name('purchase-orders.grn-pdf');
    Route::get('/purchase-orders/{purchaseOrder}/invoice-pdf', [PurchaseOrderController::class, 'supplierInvoicePdf'])->name('purchase-orders.invoice-pdf');
    Route::post('/purchase-orders/{purchaseOrder}/payment-voucher-pdf', [PurchaseOrderController::class, 'paymentVoucherPdf'])->name('purchase-orders.payment-voucher-pdf');
    Route::post('/purchase-orders/{purchaseOrder}/payment-receipt-pdf', [PurchaseOrderController::class, 'paymentReceiptPdf'])->name('purchase-orders.payment-receipt-pdf');

    // Warranty Routes
    Route::resource('warranties', WarrantyController::class);
    Route::get('/sales/{sale}/items', [WarrantyController::class, 'getSaleItems'])->name('warranties.sale-items');

    // Warranty Claim Routes
    Route::resource('warranty-claims', WarrantyClaimController::class);
    Route::patch('/warranty-claims/{warrantyClaim}/status', [WarrantyClaimController::class, 'updateStatus'])->name('warranty-claims.update-status');
    // Product Warranties
    Route::resource('product-warranties', ProductWarrantyController::class);
    Route::get('products/{product}/warranties', [ProductWarrantyController::class, 'getProductWarranties'])->name('products.warranties');
    // Warranty Claims
    Route::get('sales/{sale}/warranty-items', [WarrantyClaimController::class, 'getSaleItems'])->name('sales.warranty-items');

    // Hold/Recall Routes
    Route::post('/pos/hold-sale', [SaleController::class, 'holdSale'])->name('pos.hold-sale');
    Route::get('/pos/held-sales', [SaleController::class, 'getHeldSales'])->name('pos.get-held-sales');
    Route::post('/pos/held-sales/{heldSale}/recall', [SaleController::class, 'recallSale'])->name('pos.recall-sale');
    Route::post('/pos/held-sales/{heldSale}/cancel', [SaleController::class, 'cancelHeldSale'])->name('pos.cancel-held-sale');

    // Return Routes
    Route::resource('returns', ReturnController::class);
    Route::post('/returns/{return}/process', [ReturnController::class, 'process'])->name('returns.process');
    Route::post('/returns/{return}/refund', [ReturnController::class, 'refund'])->name('returns.refund');
    Route::get('/customers/{customer}/sales', [ReturnController::class, 'getCustomerSales'])->name('returns.customer-sales');
    Route::get('/sales/{sale}/returnable-items', [ReturnController::class, 'getSaleItems'])->name('returns.sale-items');

    // Discount Routes
    Route::resource('discounts', DiscountController::class);
    Route::patch('/discounts/{discount}/toggle-status', [DiscountController::class, 'toggleStatus'])->name('discounts.toggle-status');

    // POS Discount Routes
    Route::post('/pos/apply-discount', [SaleController::class, 'applyDiscount'])->name('pos.apply-discount');
    Route::post('/pos/remove-discount', [SaleController::class, 'removeDiscount'])->name('pos.remove-discount');
    Route::get('/pos/get-discounts', [SaleController::class, 'getApplicableDiscounts'])->name('pos.get-discounts');

    // Add payment routes
    Route::post('/sales/{sale}/payments', [SaleController::class, 'addPaymentToSale'])->name('sales.add-payment');

    // Shift Routes
    Route::resource('shifts', ShiftController::class)->except(['edit', 'update']);
    Route::get('/shifts/current', [ShiftController::class, 'current'])->name('shifts.current');
    Route::post('/shifts/{shift}/end', [ShiftController::class, 'end'])->name('shifts.end');
    Route::post('/shifts/{shift}/cash-drops', [ShiftController::class, 'addCashDrop'])->name('shifts.cash-drops');
    Route::patch('/shifts/{shift}/suspend', [ShiftController::class, 'suspend'])->name('shifts.suspend');
    Route::patch('/shifts/{shift}/resume', [ShiftController::class, 'resume'])->name('shifts.resume');
    Route::get('/shifts/{shift}/report', [ShiftController::class, 'report'])->name('shifts.report');
    Route::get('/users/{user}/shifts', [ShiftController::class, 'getUserShifts'])->name('shifts.user-shifts');

    // Branch Routes
    Route::resource('branches', BranchController::class);
    Route::resource('product-movements', ProductMovementController::class);
    Route::get('/branches/{branch}/inventory-report', [BranchController::class, 'inventoryReport'])->name('branches.inventory-report');


    // Product Movement Routes
    Route::resource('product-movements', ProductMovementController::class);
    Route::get('/product-movements/product/stock', [ProductMovementController::class, 'getProductStock'])->name('product-movements.get-stock');
    Route::post('/product-movements/{productMovement}/approve', [ProductMovementController::class, 'approve'])->name('product-movements.approve');
    Route::post('/product-movements/{productMovement}/reject', [ProductMovementController::class, 'reject'])->name('product-movements.reject');
    Route::post('/product-movements/{productMovement}/ship', [ProductMovementController::class, 'ship'])->name('product-movements.ship');
    Route::post('/product-movements/{productMovement}/deliver', [ProductMovementController::class, 'deliver'])->name('product-movements.deliver');
    Route::post('/product-movements/{productMovement}/receive', [ProductMovementController::class, 'receive'])->name('product-movements.receive');
    Route::get('/product-movements/{productMovement}/receipt/download', [ProductMovementController::class, 'downloadReceipt'])->name('product-movements.receipt.download');
    Route::get('/product-movements/{productMovement}/receipt/view', [ProductMovementController::class, 'viewReceipt'])->name('product-movements.receipt.view');
    Route::get('/users/branch-assignment', [UserController::class, 'branchAssignment'])->name('users.branch-assignment');
    Route::put('/users/{user}/assign-branch', [UserController::class, 'assignBranch'])->name('users.assign-branch');
        // Admin only routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/users', function () {
            return view('admin.users');
        })->name('admin.users');
    });

    // In routes/web.php - add this temporarily
Route::get('/users/branch-assignment-test', function () {
    \Log::info('Direct route called');
    
    $users = \App\Models\User::with('branch')
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    $branches = \App\Models\Branch::where('is_active', true)
        ->orderBy('name')
        ->get();

    return view('users.branch-assignment', compact('users', 'branches'));
})->middleware('auth')->name('users.branch-assignment-test');
});
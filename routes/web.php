<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\TaxProfileController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\DebitNoteController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\ContraEntryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\SalaryStructureController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\EmployeeLoanController;
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\HrLetterController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\BankReconciliationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');

Route::post('/login', function () {
    $credentials = request()->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    if (auth()->attempt($credentials, request()->boolean('remember'))) {
        request()->session()->regenerate();
        return redirect()->intended('/dashboard');
    }
    return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
})->name('login.post')->middleware('guest');

Route::get('/register', function () {
    return view('auth.register');
})->name('register')->middleware('guest');

Route::post('/register', function () {
    $validated = request()->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|confirmed|min:8',
    ]);
    $user = \App\Models\User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
    ]);
    auth()->login($user);
    return redirect('/dashboard');
})->name('register.post')->middleware('guest');

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Modules
    Route::resource('companies', CompanyController::class);
    Route::resource('countries', CountryController::class);
    Route::resource('currencies', CurrencyController::class);
    Route::resource('exchange-rates', ExchangeRateController::class);
    Route::resource('branches', BranchController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('designations', DesignationController::class);
    Route::resource('customers', CustomerController::class);
    Route::get('customers/{customer}/ledger', [CustomerController::class, 'ledger'])->name('customers.ledger');
    Route::resource('suppliers', SupplierController::class);
    Route::get('suppliers/{supplier}/ledger', [SupplierController::class, 'ledger'])->name('suppliers.ledger');
    Route::resource('items', ItemController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('item-categories', ItemCategoryController::class);
    Route::resource('chart-of-accounts', ChartOfAccountController::class);
    Route::get('chart-of-accounts/{chartOfAccount}/ledger', [ChartOfAccountController::class, 'ledger'])->name('chart-of-accounts.ledger');
    Route::resource('tax-profiles', TaxProfileController::class);
    Route::resource('tax-rates', TaxRateController::class);
    Route::resource('bank-accounts', BankAccountController::class);
    Route::resource('warehouses', WarehouseController::class);
    Route::resource('cost-centers', CostCenterController::class);
    Route::resource('projects', ProjectController::class);

    // Sales Module
    Route::resource('quotations', QuotationController::class);
    Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convertToOrder'])->name('quotations.convert');
    Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');
    Route::post('quotations/{quotation}/send', [QuotationController::class, 'send'])->name('quotations.send');
    Route::post('quotations/{quotation}/approve', [QuotationController::class, 'approve'])->name('quotations.approve');
    Route::post('quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');

    Route::resource('sales-orders', SalesOrderController::class);
    Route::post('sales-orders/{salesOrder}/convert', [SalesOrderController::class, 'convertToInvoice'])->name('sales-orders.convert');
    Route::post('sales-orders/{salesOrder}/approve', [SalesOrderController::class, 'approve'])->name('sales-orders.approve');

    Route::resource('sales-invoices', SalesInvoiceController::class);
    Route::get('sales-invoices/{salesInvoice}/print', [SalesInvoiceController::class, 'print'])->name('sales-invoices.print');

    Route::resource('sales-returns', SalesReturnController::class);
    Route::resource('credit-notes', CreditNoteController::class);

    // Purchase Module
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');

    Route::resource('purchase-invoices', PurchaseInvoiceController::class);
    Route::get('purchase-invoices/{purchaseInvoice}/print', [PurchaseInvoiceController::class, 'print'])->name('purchase-invoices.print');

    Route::resource('purchase-returns', PurchaseReturnController::class);
    Route::resource('debit-notes', DebitNoteController::class);

    // Finance Module
    Route::resource('receipts', ReceiptController::class);
    Route::get('receipts/{receipt}/print', [ReceiptController::class, 'print'])->name('receipts.print');

    Route::resource('payments', PaymentController::class);
    Route::post('payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
    Route::get('payments/{payment}/print', [PaymentController::class, 'print'])->name('payments.print');

    Route::resource('journal-entries', JournalEntryController::class);
    Route::resource('contra-entries', ContraEntryController::class);
    Route::resource('expenses', ExpenseController::class);

    // Inventory
    Route::resource('stock-transactions', StockTransactionController::class)->only(['index', 'create', 'store']);

    // HR Module
    Route::resource('employees', EmployeeController::class);
    Route::post('employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])->name('employee-documents.store');
    Route::delete('employee-documents/{employeeDocument}', [EmployeeDocumentController::class, 'destroy'])->name('employee-documents.destroy');

    Route::resource('leave-types', LeaveTypeController::class);
    Route::resource('leave-requests', LeaveRequestController::class);
    Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');

    Route::resource('attendance', AttendanceController::class)->only(['index', 'create', 'store']);
    Route::post('attendance/bulk', [AttendanceController::class, 'bulkCreate'])->name('attendance.bulk');
    Route::get('attendance/monthly', [AttendanceController::class, 'monthlyReport'])->name('attendance.monthly');

    Route::post('salary-structures/{employee}', [SalaryStructureController::class, 'store'])->name('salary-structures.store');

    Route::resource('payroll', PayrollController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
    Route::post('payroll/{payroll}/approve', [PayrollController::class, 'approve'])->name('payroll.approve');
    Route::get('payroll/{payroll}/payslip', [PayrollController::class, 'payslip'])->name('payroll.payslip');

    Route::resource('employee-loans', EmployeeLoanController::class);
    Route::resource('recruitment', RecruitmentController::class);
    Route::resource('candidates', CandidateController::class);
    Route::resource('hr-letters', HrLetterController::class);
    Route::get('hr-letters/{hrLetter}/print', [HrLetterController::class, 'print'])->name('hr-letters.print');

    // Assets
    Route::resource('assets', AssetController::class);
    Route::post('assets/depreciate', [AssetController::class, 'depreciate'])->name('assets.depreciate');

    // Bank Reconciliation
    Route::resource('bank-reconciliations', BankReconciliationController::class);

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'salesReport'])->name('sales');
        Route::get('/purchase', [ReportController::class, 'purchaseReport'])->name('purchase');
        Route::get('/receipt', [ReportController::class, 'receiptReport'])->name('receipt');
        Route::get('/payment', [ReportController::class, 'paymentReport'])->name('payment');
        Route::get('/trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('/profit-and-loss', [ReportController::class, 'profitAndLoss'])->name('profit-and-loss');
        Route::get('/balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('/cash-flow', [ReportController::class, 'cashFlow'])->name('cash-flow');
        Route::get('/customer-aging', [ReportController::class, 'customerAging'])->name('customer-aging');
        Route::get('/supplier-aging', [ReportController::class, 'supplierAging'])->name('supplier-aging');
        Route::get('/general-ledger', [ReportController::class, 'generalLedger'])->name('general-ledger');
        Route::get('/day-book', [ReportController::class, 'dayBook'])->name('day-book');
        Route::get('/vat', [ReportController::class, 'vatReport'])->name('vat');
        Route::get('/employee', [ReportController::class, 'employeeReport'])->name('employee');
        Route::get('/attendance', [ReportController::class, 'attendanceReport'])->name('attendance');
        Route::get('/payroll', [ReportController::class, 'payrollReport'])->name('payroll');
    });

    // Settings & Admin
    Route::resource('users', UserController::class);
    Route::resource('permissions', PermissionController::class)->only(['index', 'edit', 'update']);
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\SparePartController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\CustomerController;

use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\VehiclePurchaseOrderController;
use App\Http\Controllers\Admin\VehicleSalesInvoiceController;
use App\Http\Controllers\Admin\PartSalesInvoiceController;
use App\Http\Controllers\Admin\SparePartStockController;
use App\Http\Controllers\Admin\VehicleMasterController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\FinanceMasterController;
use App\Http\Controllers\Admin\QuotationController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::name('admin.')->prefix('admin')->group(function () {
    Route::get('/', [AdminAuthController::class, 'index']);

    Route::get('login', [AdminAuthController::class, 'login'])->name('login');

    Route::post('login', [AdminAuthController::class, 'postLogin'])->name('login.post')->middleware('throttle:5,1');

    Route::get('forget-password', [AdminAuthController::class, 'showForgetPasswordForm'])->name('forget.password.get');

    Route::post('forget-password', [AdminAuthController::class, 'submitForgetPasswordForm'])->name('forget.password.post');

    Route::get('reset-password/{token}', [AdminAuthController::class, 'showResetPasswordForm'])->name('reset.password.get');

    Route::post('reset-password', [AdminAuthController::class, 'submitResetPasswordForm'])->name('reset.password.post');

    // Public PDF links (accessible via shared WhatsApp/Email links without admin login)
    Route::get('purchase-orders/{purchase_order}/pdf', [PurchaseOrderController::class, 'generatePdf'])->name('purchase-orders.pdf');
    Route::get('vehicle-purchase-orders/{vehicle_purchase_order}/pdf', [VehiclePurchaseOrderController::class, 'generatePdf'])->name('vehicle-purchase-orders.pdf');
    Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'generatePdf'])->name('quotations.pdf');

    Route::middleware(['admin'])->group(function () {
            Route::get('dashboard', [AdminAuthController::class, 'adminDashboard'])->name('dashboard');

            Route::get('change-password', [AdminAuthController::class, 'changePassword'])->name('change.password');

            Route::post('update-password', [AdminAuthController::class, 'updatePassword'])->name('update.password');

            Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');

            Route::get('profile', [AdminAuthController::class, 'adminProfile'])->name('profile');

            Route::post('profile', [AdminAuthController::class, 'updateAdminProfile'])->name('update.profile');

            Route::get('vehicle-masters/import-template', [VehicleMasterController::class, 'downloadTemplate'])->name('vehicle-masters.import-template');
            Route::post('vehicle-masters/import', [VehicleMasterController::class, 'import'])->name('vehicle-masters.import');
            Route::get('vehicle-masters/export', [VehicleMasterController::class, 'export'])->name('vehicle-masters.export');
            Route::resource('vehicle-masters', VehicleMasterController::class)->except(['show']);
            Route::post('vehicle-masters/{vehicle_master}/toggle-status', [VehicleMasterController::class, 'toggleStatus'])->name('vehicle-masters.toggle-status');

            Route::get('spare-parts/import-template', [SparePartController::class, 'downloadTemplate'])->name('spare-parts.import-template');
            Route::post('spare-parts/import', [SparePartController::class, 'import'])->name('spare-parts.import');
            Route::get('spare-parts/export', [SparePartController::class, 'export'])->name('spare-parts.export');
            Route::resource('spare-parts', SparePartController::class)->except(['show']);
            Route::post('spare-parts/{spare_part}/toggle-status', [SparePartController::class, 'toggleStatus'])->name('spare-parts.toggle-status');

            Route::get('suppliers/import-template', [SupplierController::class, 'downloadTemplate'])->name('suppliers.import-template');
            Route::post('suppliers/import', [SupplierController::class, 'import'])->name('suppliers.import');
            Route::get('suppliers/export', [SupplierController::class, 'export'])->name('suppliers.export');
            Route::resource('suppliers', SupplierController::class)->except(['show']);
            Route::post('suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');

            Route::get('customers/import-template', [CustomerController::class, 'downloadTemplate'])->name('customers.import-template');
            Route::post('customers/import', [CustomerController::class, 'import'])->name('customers.import');
            Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
            Route::resource('customers', CustomerController::class);
            Route::post('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');

            Route::get('finance-masters/import-template', [FinanceMasterController::class, 'downloadTemplate'])->name('finance-masters.import-template');
            Route::post('finance-masters/import', [FinanceMasterController::class, 'import'])->name('finance-masters.import');
            Route::get('finance-masters/export', [FinanceMasterController::class, 'export'])->name('finance-masters.export');
            Route::resource('finance-masters', FinanceMasterController::class);
            Route::post('finance-masters/{finance_master}/toggle-status', [FinanceMasterController::class, 'toggleStatus'])->name('finance-masters.toggle-status');

            Route::resource('purchase-orders', PurchaseOrderController::class);
            Route::get('purchase-orders/export', [PurchaseOrderController::class, 'export'])->name('purchase-orders.export');
            Route::get('purchase-orders/outstanding/list', [PurchaseOrderController::class, 'outstanding'])->name('purchase-orders.outstanding');
            Route::get('purchase-orders/outstanding/export', [PurchaseOrderController::class, 'exportOutstanding'])->name('purchase-orders.outstanding.export');
            Route::post('purchase-orders/{purchase_order}/toggle-status', [PurchaseOrderController::class, 'toggleStatus'])->name('purchase-orders.toggle-status');
            Route::get('purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
            Route::post('purchase-orders/{purchase_order}/receive-store', [PurchaseOrderController::class, 'receiveStore'])->name('purchase-orders.receive-store');
            Route::get('purchase-orders/{purchase_order}/whatsapp', [PurchaseOrderController::class, 'sendWhatsapp'])->name('purchase-orders.whatsapp');
            Route::post('purchase-orders/{purchase_order}/receive-payment', [PurchaseOrderController::class, 'receivePayment'])->name('purchase-orders.receive-payment');

            Route::resource('vehicle-purchase-orders', VehiclePurchaseOrderController::class);
            Route::get('vehicle-purchase-orders/export', [VehiclePurchaseOrderController::class, 'export'])->name('vehicle-purchase-orders.export');
            Route::get('vehicle-purchase-orders/outstanding/list', [VehiclePurchaseOrderController::class, 'outstanding'])->name('vehicle-purchase-orders.outstanding');
            Route::get('vehicle-purchase-orders/outstanding/export', [VehiclePurchaseOrderController::class, 'exportOutstanding'])->name('vehicle-purchase-orders.outstanding.export');
            Route::post('vehicle-purchase-orders/{vehicle_purchase_order}/toggle-status', [VehiclePurchaseOrderController::class, 'toggleStatus'])->name('vehicle-purchase-orders.toggle-status');
            Route::get('vehicle-purchase-orders/{vehicle_purchase_order}/receive', [VehiclePurchaseOrderController::class, 'receive'])->name('vehicle-purchase-orders.receive');
            Route::post('vehicle-purchase-orders/{vehicle_purchase_order}/receive-store', [VehiclePurchaseOrderController::class, 'receiveStore'])->name('vehicle-purchase-orders.receive-store');
            Route::get('vehicle-purchase-orders/{vehicle_purchase_order}/whatsapp', [VehiclePurchaseOrderController::class, 'sendWhatsapp'])->name('vehicle-purchase-orders.whatsapp');
            Route::post('vehicle-purchase-orders/{vehicle_purchase_order}/receive-payment', [VehiclePurchaseOrderController::class, 'receivePayment'])->name('vehicle-purchase-orders.receive-payment');
            Route::get('vehicle-inventories', [VehiclePurchaseOrderController::class, 'inventory'])->name('vehicle-inventories.index');
            Route::get('vehicle-inventories/export', [VehiclePurchaseOrderController::class, 'exportInventory'])->name('vehicle-inventories.export');
            Route::post('vehicle-inventories/check-unique', [VehiclePurchaseOrderController::class, 'checkUnique'])->name('vehicle-inventories.check-unique');
            Route::get('spare-part-stocks', [SparePartStockController::class, 'index'])->name('spare-part-stocks.index');
            Route::get('spare-part-stocks/export', [SparePartStockController::class, 'export'])->name('spare-part-stocks.export');
            Route::post('spare-part-stocks/{spare_part_stock}/toggle-status', [SparePartStockController::class, 'toggleStatus'])->name('spare-part-stocks.toggle-status');
            Route::post('spare-part-stocks/{spare_part_stock}/destroy', [SparePartStockController::class, 'destroy'])->name('spare-part-stocks.destroy');
            Route::post('spare-part-stocks/adjust', [SparePartStockController::class, 'adjust'])->name('spare-part-stocks.adjust');

            Route::post('vehicle-inventories/{vehicle_inventory}/toggle-status-sold', [VehiclePurchaseOrderController::class, 'toggleInventoryStatus'])->name('vehicle-inventories.toggle-status-sold');

            Route::resource('vehicle-sales-invoices', VehicleSalesInvoiceController::class)->except(['edit', 'update']);
            Route::get('vehicle-sales-invoices/export', [VehicleSalesInvoiceController::class, 'export'])->name('vehicle-sales-invoices.export');
            Route::get('vehicle-sales-invoices/outstanding/list', [VehicleSalesInvoiceController::class, 'outstanding'])->name('vehicle-sales-invoices.outstanding');
            Route::get('vehicle-sales-invoices/outstanding/export', [VehicleSalesInvoiceController::class, 'exportOutstanding'])->name('vehicle-sales-invoices.outstanding.export');
            Route::post('vehicle-sales-invoices/{vehicle_sales_invoice}/receive-payment', [VehicleSalesInvoiceController::class, 'receivePayment'])->name('vehicle-sales-invoices.receive-payment');

            Route::resource('part-sales-invoices', PartSalesInvoiceController::class)->except(['edit', 'update']);
            Route::get('part-sales-invoices/export', [PartSalesInvoiceController::class, 'export'])->name('part-sales-invoices.export');
            Route::get('part-sales-invoices/outstanding/list', [PartSalesInvoiceController::class, 'outstanding'])->name('part-sales-invoices.outstanding');
            Route::get('part-sales-invoices/outstanding/export', [PartSalesInvoiceController::class, 'exportOutstanding'])->name('part-sales-invoices.outstanding.export');
            Route::post('part-sales-invoices/{part_sales_invoice}/receive-payment', [PartSalesInvoiceController::class, 'receivePayment'])->name('part-sales-invoices.receive-payment');

            Route::get('quotations/create/vehicle', [QuotationController::class, 'createVehicle'])->name('quotations.create-vehicle');
            Route::get('quotations/create/parts', [QuotationController::class, 'createParts'])->name('quotations.create-parts');
            Route::get('quotations/{quotation}/edit-vehicle', [QuotationController::class, 'editVehicle'])->name('quotations.edit-vehicle');
            Route::get('quotations/{quotation}/edit-parts', [QuotationController::class, 'editParts'])->name('quotations.edit-parts');
            Route::get('quotations/{quotation}/whatsapp', [QuotationController::class, 'sendWhatsapp'])->name('quotations.whatsapp');
            Route::resource('quotations', QuotationController::class);

            Route::get('reports/vehicle-ledger', [ReportController::class, 'vehicleLedger'])->name('reports.vehicle-ledger');
            Route::get('reports/part-ledger', [ReportController::class, 'partLedger'])->name('reports.part-ledger');
            Route::get('reports/outstanding-ledger', [ReportController::class, 'outstandingLedger'])->name('reports.outstanding-ledger');
            Route::get('reports/party-report-by-item', [ReportController::class, 'partyReportByItem'])->name('reports.party-report-by-item');
            Route::get('reports/party-report-by-item/export-excel', [ReportController::class, 'exportPartyReportExcel'])->name('reports.party-report-by-item.export-excel');
            Route::post('reports/party-report-by-item/email-excel', [ReportController::class, 'emailPartyReportExcel'])->name('reports.party-report-by-item.email-excel');
            Route::get('reports/party-report-by-item/print-pdf', [ReportController::class, 'printPartyReportPdf'])->name('reports.party-report-by-item.print-pdf');
    });


    
});

Route::get('run-migration', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return response(Artisan::output());
    } catch (\Throwable $e) {
        return response('Migration failed: ' . $e->getMessage(), 500);
    }
})->name('run.migration');

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleSalesInvoice;
use App\Models\VehicleInventory;
use App\Models\Customer;
use App\Models\FinanceMaster;
use App\Models\VehicleMaster;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Barryvdh\DomPDF\Facade\Pdf;

class VehicleSalesInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = VehicleSalesInvoice::with('customer', 'vehicleInventory')
            ->orderBy('invoice_date', 'desc')
            ->orderBy('id', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('invoice_number', 'like', $escapedSearch)
                  ->orWhere('customer_name', 'like', $escapedSearch)
                  ->orWhere('customer_mobile', 'like', $escapedSearch);
            });
        }

        $invoices = $query->paginate(20);
        return view('admin.vehicle_sales_invoices.index', compact('invoices', 'search'));
    }

    public function outstanding(Request $request)
    {
        $search = $request->input('search');
        $query = VehicleSalesInvoice::with('customer', 'vehicleInventory')
            ->where('balance', '>', 0)
            ->orderBy('invoice_date', 'desc')
            ->orderBy('id', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('invoice_number', 'like', $escapedSearch)
                  ->orWhere('customer_name', 'like', $escapedSearch)
                  ->orWhere('customer_mobile', 'like', $escapedSearch);
            });
        }

        $invoices = $query->paginate(20);
        return view('admin.vehicle_sales_invoices.outstanding', compact('invoices', 'search'));
    }

    public function exportOutstanding(Request $request)
    {
        $search = $request->input('search');
        $query = VehicleSalesInvoice::with('customer', 'vehicleInventory')
            ->where('balance', '>', 0)
            ->orderBy('invoice_date', 'desc')
            ->orderBy('id', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('invoice_number', 'like', $escapedSearch)
                  ->orWhere('customer_name', 'like', $escapedSearch)
                  ->orWhere('customer_mobile', 'like', $escapedSearch);
            });
        }

        $invoices = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Invoice No');
        $sheet->setCellValue('B1', 'Date');
        $sheet->setCellValue('C1', 'Customer Name');
        $sheet->setCellValue('D1', 'Mobile');
        $sheet->setCellValue('E1', 'Vehicle');
        $sheet->setCellValue('F1', 'Grand Total');
        $sheet->setCellValue('G1', 'Received Amount');
        $sheet->setCellValue('H1', 'Balance');
        $sheet->setCellValue('I1', 'Payment Mode');

        $row = 2;
        foreach ($invoices as $inv) {
            $sheet->setCellValue('A' . $row, $inv->invoice_number);
            $sheet->setCellValue('B' . $row, $inv->invoice_date->format('d-m-Y'));
            $sheet->setCellValue('C' . $row, $inv->customer_name);
            $sheet->setCellValue('D' . $row, $inv->customer_mobile);
            $sheet->setCellValue('E' . $row, $inv->vehicleInventory->vehicle_description ?? '-');
            $sheet->setCellValue('F' . $row, $inv->grand_total);
            $sheet->setCellValue('G' . $row, $inv->received_amount);
            $sheet->setCellValue('H' . $row, $inv->balance);
            $sheet->setCellValue('I' . $row, $inv->payment_mode ?? '-');
            $row++;
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/vehicle_sales_outstanding_export.xls');
        $writer->save($path);

        return response()->download($path, 'vehicle_sales_outstanding_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $query = VehicleSalesInvoice::with('customer', 'vehicleInventory')
            ->orderBy('invoice_date', 'desc')
            ->orderBy('id', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('invoice_number', 'like', $escapedSearch)
                  ->orWhere('customer_name', 'like', $escapedSearch)
                  ->orWhere('customer_mobile', 'like', $escapedSearch);
            });
        }

        $invoices = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Invoice No');
        $sheet->setCellValue('B1', 'Date');
        $sheet->setCellValue('C1', 'Customer Name');
        $sheet->setCellValue('D1', 'Customer Mobile');
        $sheet->setCellValue('E1', 'Vehicle');
        $sheet->setCellValue('F1', 'Chassis No');
        $sheet->setCellValue('G1', 'Grand Total');
        $sheet->setCellValue('H1', 'Payment Mode');

        $row = 2;
        foreach ($invoices as $inv) {
            $sheet->setCellValue('A' . $row, $inv->invoice_number);
            $sheet->setCellValue('B' . $row, $inv->invoice_date->format('d-m-Y'));
            $sheet->setCellValue('C' . $row, $inv->customer_name);
            $sheet->setCellValue('D' . $row, $inv->customer_mobile);
            $sheet->setCellValue('E' . $row, $inv->vehicleInventory->vehicle_description ?? '-');
            $sheet->setCellValue('F' . $row, $inv->vehicleInventory->chassis_number ?? '-');
            $sheet->setCellValue('G' . $row, $inv->grand_total);
            $sheet->setCellValue('H' . $row, $inv->payment_mode);
            $row++;
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/vehicle_sales_invoices_export.xls');
        $writer->save($path);

        return response()->download($path, 'vehicle_sales_invoices_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
    }

    private function isLastFourDigitsUnique($invoiceNumber, $ignoreVehicleId = null, $ignorePartId = null)
    {
        if (preg_match('/(\d{4})$/', trim($invoiceNumber), $matches)) {
            $digits = $matches[1];
            
            $vQuery = DB::table('vehicle_sales_invoices')
                ->whereNull('deleted_at')
                ->whereRaw("RIGHT(invoice_number, 4) = ?", [$digits]);
            if ($ignoreVehicleId) {
                $vQuery->where('id', '!=', $ignoreVehicleId);
            }
            if ($vQuery->exists()) {
                return false;
            }

            $pQuery = DB::table('part_sales_invoices')
                ->whereNull('deleted_at')
                ->whereRaw("RIGHT(invoice_number, 4) = ?", [$digits]);
            if ($ignorePartId) {
                $pQuery->where('id', '!=', $ignorePartId);
            }
            if ($pQuery->exists()) {
                return false;
            }
        }
        return true;
    }

    public function generateNextInvoiceNumber($invoiceDate = null)
    {
        $dateStr = $invoiceDate ? date('Ymd', strtotime($invoiceDate)) : date('Ymd');
        
        $vInvoices = DB::table('vehicle_sales_invoices')->whereNull('deleted_at')->pluck('invoice_number');
        $pInvoices = DB::table('part_sales_invoices')->whereNull('deleted_at')->pluck('invoice_number');
        $allInvoices = $vInvoices->concat($pInvoices);

        $maxNum = 850;
        foreach ($allInvoices as $invNum) {
            if (preg_match('/(\d+)$/', $invNum, $matches)) {
                $num = (int)$matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }
        $nextNum = $maxNum + 1;
        return 'INV-' . $dateStr . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        
        $vehicles = VehicleInventory::where('status', 'available')
            ->where('is_active', true)
            ->get()
            ->map(function ($item) {
                // Find matching vehicle master
                $master = VehicleMaster::where('is_active', true)
                    ->get()
                    ->first(function ($m) use ($item) {
                        $desc = trim($m->variant_name . ' ' . $m->color_name);
                        return strtolower($desc) === strtolower($item->vehicle_description)
                            || strtolower($m->variant_name) === strtolower($item->vehicle_description);
                    });
                
                $item->ex_showroom_price = $master ? $master->ex_showroom_price : $item->purchase_price;
                $item->battery_type = $master ? $master->battery_type : 'LITHIUM';
                $item->battery_make = $master ? $master->battery_make : 'LITHIUM';
                return $item;
            });

        $financeMasters = FinanceMaster::where('is_active', true)->orderBy('name')->get();
        $nextInvoiceNumber = $this->generateNextInvoiceNumber();

        return view('admin.vehicle_sales_invoices.create', compact('customers', 'vehicles', 'financeMasters', 'nextInvoiceNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'nullable|string|max:255|unique:vehicle_sales_invoices,invoice_number',
            'invoice_date' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'customer_age' => 'nullable|integer|min:0',
            'customer_occupation' => 'nullable|string|max:255',
            'customer_mobile' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'customer_residence_phone' => 'nullable|string|max:20',
            'vehicle_inventory_id' => 'required|exists:vehicle_inventories,id',
            'rate' => 'required|numeric|min:0',
            'nemmp_incentive' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payment_mode' => 'nullable|string|max:255',
            'finance_name' => 'nullable|string|max:255',
            'previous_balance' => 'nullable|numeric|min:0',
            'received_amount' => 'nullable|numeric|min:0',
            'warranty_notes' => 'nullable|string',
        ]);

        if ($request->filled('invoice_number')) {
            if (!$this->isLastFourDigitsUnique($request->invoice_number)) {
                return back()->withErrors(['invoice_number' => 'The last 4 digits of the invoice number must be unique across both vehicle and parts invoices.'])->withInput();
            }
        }

        $vehicle = VehicleInventory::findOrFail($request->vehicle_inventory_id);
        if ($vehicle->status !== 'available') {
            return back()->withErrors(['vehicle_inventory_id' => 'This vehicle is not available.'])->withInput();
        }

        // Calculations
        $rate = floatval($request->rate);
        $sub_total = $rate;
        $total = $sub_total;
        
        $nemmp = floatval($request->input('nemmp_incentive', 0));
        $discount = floatval($request->input('discount', 0));
        
        $grand_total = $total - $nemmp - $discount;

        $prev_bal = floatval($request->input('previous_balance', 0));
        $received = floatval($request->input('received_amount', 0));
        $balance = $grand_total - $received;
        $curr_bal = $prev_bal + $balance;

        $invoice = DB::transaction(function () use ($request, $vehicle, $rate, $sub_total, $total, $nemmp, $discount, $grand_total, $prev_bal, $received, $balance, $curr_bal) {
            // Generate or use provided invoice number
            $invoiceNumber = $request->filled('invoice_number')
                ? trim($request->input('invoice_number'))
                : $this->generateNextInvoiceNumber($request->invoice_date);

            // Mark vehicle as sold
            $vehicle->update(['status' => 'sold']);

            return VehicleSalesInvoice::create([
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $request->invoice_date,
                'customer_id' => $request->customer_id,
                'customer_name' => $request->customer_name,
                'customer_age' => $request->customer_age,
                'customer_occupation' => $request->customer_occupation,
                'customer_mobile' => $request->customer_mobile,
                'customer_address' => $request->customer_address,
                'customer_residence_phone' => $request->customer_residence_phone,
                'vehicle_inventory_id' => $vehicle->id,
                'rate' => $rate,
                'sub_total' => $sub_total,
                'total' => $total,
                'nemmp_incentive' => $nemmp,
                'discount' => $discount,
                'grand_total' => $grand_total,
                'payment_mode' => $request->payment_mode,
                'finance_name' => $request->input('finance_name'),
                'received_amount' => $received,
                'balance' => $balance,
                'previous_balance' => $prev_bal,
                'current_balance' => $curr_bal,
                'warranty_notes' => $request->input('warranty_notes', "MOTOR, CONTROLLER WARRANTY - 1 YEAR\nBATTERY WARRANTY - 3 YEAR\nCHARGER WARRANTY - 2 YEAR"),
            ]);
        });

        return redirect()->route('admin.vehicle-sales-invoices.show', $invoice)->withSuccess('Vehicle Sales Invoice created successfully.');
    }

    public function edit(VehicleSalesInvoice $vehicleSalesInvoice)
    {
        $vehicleSalesInvoice->load('customer', 'vehicleInventory');
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        
        $vehicles = VehicleInventory::where(function($q) use ($vehicleSalesInvoice) {
                $q->where('status', 'available')
                  ->orWhere('id', $vehicleSalesInvoice->vehicle_inventory_id);
            })
            ->where('is_active', true)
            ->get()
            ->map(function ($item) {
                $master = VehicleMaster::where('is_active', true)
                    ->get()
                    ->first(function ($m) use ($item) {
                        $desc = trim($m->variant_name . ' ' . $m->color_name);
                        return strtolower($desc) === strtolower($item->vehicle_description)
                            || strtolower($m->variant_name) === strtolower($item->vehicle_description);
                    });
                
                $item->ex_showroom_price = $master ? $master->ex_showroom_price : $item->purchase_price;
                $item->battery_type = $master ? $master->battery_type : 'LITHIUM';
                $item->battery_make = $master ? $master->battery_make : 'LITHIUM';
                return $item;
            });

        $financeMasters = FinanceMaster::where('is_active', true)->orderBy('name')->get();

        return view('admin.vehicle_sales_invoices.edit', compact('vehicleSalesInvoice', 'customers', 'vehicles', 'financeMasters'));
    }

    public function update(Request $request, VehicleSalesInvoice $vehicleSalesInvoice)
    {
        $request->validate([
            'invoice_number' => 'required|string|max:255|unique:vehicle_sales_invoices,invoice_number,' . $vehicleSalesInvoice->id,
            'invoice_date' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'customer_age' => 'nullable|integer|min:0',
            'customer_occupation' => 'nullable|string|max:255',
            'customer_mobile' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'customer_residence_phone' => 'nullable|string|max:20',
            'vehicle_inventory_id' => 'required|exists:vehicle_inventories,id',
            'rate' => 'required|numeric|min:0',
            'nemmp_incentive' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payment_mode' => 'nullable|string|max:255',
            'finance_name' => 'nullable|string|max:255',
            'previous_balance' => 'nullable|numeric|min:0',
            'received_amount' => 'nullable|numeric|min:0',
            'warranty_notes' => 'nullable|string',
        ]);

        if (!$this->isLastFourDigitsUnique($request->invoice_number, $vehicleSalesInvoice->id, null)) {
            return back()->withErrors(['invoice_number' => 'The last 4 digits of the invoice number must be unique across both vehicle and parts invoices.'])->withInput();
        }

        if ($vehicleSalesInvoice->vehicle_inventory_id != $request->vehicle_inventory_id) {
            $newVehicle = VehicleInventory::findOrFail($request->vehicle_inventory_id);
            if ($newVehicle->status !== 'available') {
                return back()->withErrors(['vehicle_inventory_id' => 'The newly selected vehicle is not available.'])->withInput();
            }
        }

        // Calculations
        $rate = floatval($request->rate);
        $sub_total = $rate;
        $total = $sub_total;
        
        $nemmp = floatval($request->input('nemmp_incentive', 0));
        $discount = floatval($request->input('discount', 0));
        
        $grand_total = $total - $nemmp - $discount;

        $prev_bal = floatval($request->input('previous_balance', 0));
        $received = floatval($request->input('received_amount', 0));
        $balance = $grand_total - $received;
        $curr_bal = $prev_bal + $balance;

        DB::transaction(function () use ($request, $vehicleSalesInvoice, $rate, $sub_total, $total, $nemmp, $discount, $grand_total, $prev_bal, $received, $balance, $curr_bal) {
            // Handle vehicle inventory status change if changed
            if ($vehicleSalesInvoice->vehicle_inventory_id != $request->vehicle_inventory_id) {
                VehicleInventory::where('id', $vehicleSalesInvoice->vehicle_inventory_id)->update(['status' => 'available']);
                VehicleInventory::where('id', $request->vehicle_inventory_id)->update(['status' => 'sold']);
            }

            $vehicleSalesInvoice->update([
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'customer_id' => $request->customer_id,
                'customer_name' => $request->customer_name,
                'customer_age' => $request->customer_age,
                'customer_occupation' => $request->customer_occupation,
                'customer_mobile' => $request->customer_mobile,
                'customer_address' => $request->customer_address,
                'customer_residence_phone' => $request->customer_residence_phone,
                'vehicle_inventory_id' => $request->vehicle_inventory_id,
                'rate' => $rate,
                'sub_total' => $sub_total,
                'total' => $total,
                'nemmp_incentive' => $nemmp,
                'discount' => $discount,
                'grand_total' => $grand_total,
                'payment_mode' => $request->payment_mode,
                'finance_name' => $request->input('finance_name'),
                'received_amount' => $received,
                'balance' => $balance,
                'previous_balance' => $prev_bal,
                'current_balance' => $curr_bal,
                'warranty_notes' => $request->input('warranty_notes'),
            ]);
        });

        return redirect()->route('admin.vehicle-sales-invoices.show', $vehicleSalesInvoice)->withSuccess('Vehicle Sales Invoice updated successfully.');
    }

    public function quickUpdateDate(Request $request, VehicleSalesInvoice $vehicleSalesInvoice)
    {
        $request->validate([
            'invoice_number' => 'required|string|max:255|unique:vehicle_sales_invoices,invoice_number,' . $vehicleSalesInvoice->id,
            'invoice_date' => 'required|date',
        ]);

        if (!$this->isLastFourDigitsUnique($request->invoice_number, $vehicleSalesInvoice->id, null)) {
            return response()->json(['success' => false, 'message' => 'The last 4 digits of the invoice number must be unique across both vehicle and parts invoices.']);
        }

        $vehicleSalesInvoice->update([
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
        ]);

        return response()->json(['success' => true, 'message' => 'Invoice Date & Number updated successfully.']);
    }

    public function show(VehicleSalesInvoice $vehicleSalesInvoice)
    {
        $vehicleSalesInvoice->load('customer', 'vehicleInventory.purchaseOrder');
        
        // Find matching vehicle master for battery info
        $vehicle = $vehicleSalesInvoice->vehicleInventory;
        $master = VehicleMaster::where('is_active', true)
            ->get()
            ->first(function ($m) use ($vehicle) {
                $desc = trim($m->variant_name . ' ' . $m->color_name);
                return strtolower($desc) === strtolower($vehicle->vehicle_description)
                    || strtolower($m->variant_name) === strtolower($vehicle->vehicle_description);
            });
            
        $battery_type = $master ? $master->battery_type : 'LITHIUM';
        $battery_make = $master ? $master->battery_make : 'LITHIUM';
        $color_name = $master ? $master->color_name : '-';

        return view('admin.vehicle_sales_invoices.show', compact('vehicleSalesInvoice', 'battery_type', 'battery_make', 'color_name'));
    }

    public function destroy(VehicleSalesInvoice $vehicleSalesInvoice)
    {
        DB::transaction(function () use ($vehicleSalesInvoice) {
            $vehicle = VehicleInventory::find($vehicleSalesInvoice->vehicle_inventory_id);
            if ($vehicle) {
                $vehicle->update(['status' => 'available']);
            }
            $vehicleSalesInvoice->delete();
        });

        return response()->json(['success' => true, 'message' => 'Invoice deleted successfully.']);
    }

    public function receivePayment(Request $request, VehicleSalesInvoice $vehicleSalesInvoice)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'nullable|string|max:255',
        ]);

        $amount = floatval($request->input('amount'));
        $paymentMode = $request->input('payment_mode', $vehicleSalesInvoice->payment_mode ?? 'Cash');

        if ($amount > $vehicleSalesInvoice->balance) {
            return response()->json(['success' => false, 'message' => 'Amount cannot exceed the balance (' . number_format($vehicleSalesInvoice->balance, 2) . ')']);
        }

        DB::transaction(function () use ($vehicleSalesInvoice, $amount, $paymentMode) {
            $vehicleSalesInvoice->received_amount += $amount;
            $vehicleSalesInvoice->balance -= $amount;
            $vehicleSalesInvoice->current_balance -= $amount;
            $vehicleSalesInvoice->save();

            PaymentTransaction::create([
                'transaction_type' => 'sales',
                'bill_type' => 'vehicle_sales',
                'bill_id' => $vehicleSalesInvoice->id,
                'party_type' => 'customer',
                'party_id' => $vehicleSalesInvoice->customer_id,
                'party_name' => $vehicleSalesInvoice->customer_name,
                'payment_date' => date('Y-m-d'),
                'amount' => $amount,
                'payment_mode' => $paymentMode,
                'type' => 'payment',
                'created_by' => auth()->id() ?? null,
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Payment received successfully.']);
    }

    public function rollbackPayment(Request $request, VehicleSalesInvoice $vehicleSalesInvoice, PaymentTransaction $paymentTransaction)
    {
        $request->validate([
            'rollback_reason' => 'required|string|max:255',
        ]);

        if ($paymentTransaction->bill_id != $vehicleSalesInvoice->id || $paymentTransaction->bill_type != 'vehicle_sales') {
            return response()->json(['success' => false, 'message' => 'Invalid payment transaction for this invoice.']);
        }

        if ($paymentTransaction->type === 'rollback' || $paymentTransaction->isRolledBack()) {
            return response()->json(['success' => false, 'message' => 'This payment has already been rolled back.']);
        }

        $rollbackAmount = (float)$paymentTransaction->amount;

        DB::transaction(function () use ($vehicleSalesInvoice, $paymentTransaction, $rollbackAmount, $request) {
            $vehicleSalesInvoice->received_amount = max(0, $vehicleSalesInvoice->received_amount - $rollbackAmount);
            $vehicleSalesInvoice->balance += $rollbackAmount;
            $vehicleSalesInvoice->current_balance += $rollbackAmount;
            $vehicleSalesInvoice->save();

            PaymentTransaction::create([
                'transaction_type' => 'sales',
                'bill_type' => 'vehicle_sales',
                'bill_id' => $vehicleSalesInvoice->id,
                'party_type' => 'customer',
                'party_id' => $vehicleSalesInvoice->customer_id,
                'party_name' => $vehicleSalesInvoice->customer_name,
                'payment_date' => date('Y-m-d'),
                'amount' => -$rollbackAmount,
                'payment_mode' => $paymentTransaction->payment_mode,
                'type' => 'rollback',
                'rollback_reason' => $request->input('rollback_reason'),
                'reversed_payment_id' => $paymentTransaction->id,
                'created_by' => auth()->id() ?? null,
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Payment rolled back successfully. Ledger and balance updated.']);
    }

    public function generatePdf(Request $request, VehicleSalesInvoice $vehicleSalesInvoice)
    {
        $vehicleSalesInvoice->load('customer', 'vehicleInventory.purchaseOrder');

        $vehicle = $vehicleSalesInvoice->vehicleInventory;
        $master = VehicleMaster::where('is_active', true)
            ->get()
            ->first(function ($m) use ($vehicle) {
                $desc = trim($m->variant_name . ' ' . $m->color_name);
                return strtolower($desc) === strtolower($vehicle->vehicle_description)
                    || strtolower($m->variant_name) === strtolower($vehicle->vehicle_description);
            });

        $battery_type = $master ? $master->battery_type : 'LITHIUM';
        $battery_make = $master ? $master->battery_make : 'LITHIUM';
        $color_name = $master ? $master->color_name : '-';

        $pdf = Pdf::loadView('admin.vehicle_sales_invoices.pdf', compact('vehicleSalesInvoice', 'battery_type', 'battery_make', 'color_name'));
        $pdf->setPaper('a4');
        $pdf->setOption('isRemoteEnabled', true);

        if ($request->has('print')) {
            $pdf->render();
            $canvas = $pdf->getCanvas();
            $canvas->javascript("this.print();");
            return $pdf->stream('Vehicle-Invoice-' . $vehicleSalesInvoice->invoice_number . '.pdf');
        }

        if ($request->has('stream')) {
            return $pdf->stream('Vehicle-Invoice-' . $vehicleSalesInvoice->invoice_number . '.pdf');
        }

        return $pdf->download('Vehicle-Invoice-' . $vehicleSalesInvoice->invoice_number . '.pdf');
    }
}

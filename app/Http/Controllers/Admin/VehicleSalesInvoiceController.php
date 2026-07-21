<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleSalesInvoice;
use App\Models\VehicleInventory;
use App\Models\Customer;
use App\Models\VehicleMaster;
use App\Models\FinanceMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

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

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        
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

        return view('admin.vehicle_sales_invoices.create', compact('customers', 'vehicles', 'financeMasters'));
    }

    public function store(Request $request)
    {
        $request->validate([
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
            'gst_type' => 'required|string|in:exclusive,inclusive',
            'nemmp_incentive' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payment_mode' => 'nullable|string|max:255',
            'finance_name' => 'nullable|string|max:255',
            'tax_regime' => 'required|string|in:cgst_sgst,igst',
            'previous_balance' => 'nullable|numeric|min:0',
            'received_amount' => 'nullable|numeric|min:0',
            'warranty_notes' => 'nullable|string',
        ]);

        $vehicle = VehicleInventory::findOrFail($request->vehicle_inventory_id);
        if ($vehicle->status !== 'available') {
            return back()->withErrors(['vehicle_inventory_id' => 'This vehicle is not available.'])->withInput();
        }

        // Calculations
        $rate_input = floatval($request->rate);
        $gst_type = $request->input('gst_type', 'exclusive');
        $tax_regime = $request->input('tax_regime', 'cgst_sgst');
        $cgst_rate = config('app.cgst_rate', 2.50);
        $sgst_rate = config('app.sgst_rate', 2.50);
        $igst_rate = config('app.igst_rate', 5.00);
        
        if ($gst_type === 'inclusive') {
            $sub_total = round($rate_input / 1.05, 2);
            if ($tax_regime === 'igst') {
                $cgst_amount = 0;
                $sgst_amount = 0;
                $igst_amount = round(($sub_total * $igst_rate) / 100, 2);
            } else {
                $cgst_amount = round(($sub_total * $cgst_rate) / 100, 2);
                $sgst_amount = round(($sub_total * $sgst_rate) / 100, 2);
                $igst_amount = 0;
            }
            $total = $rate_input;
            $rate = $sub_total;
        } else {
            $sub_total = $rate_input;
            if ($tax_regime === 'igst') {
                $cgst_amount = 0;
                $sgst_amount = 0;
                $igst_amount = round(($sub_total * $igst_rate) / 100, 2);
            } else {
                $cgst_amount = round(($sub_total * $cgst_rate) / 100, 2);
                $sgst_amount = round(($sub_total * $sgst_rate) / 100, 2);
                $igst_amount = 0;
            }
            $total = $sub_total + $cgst_amount + $sgst_amount + $igst_amount;
            $rate = $rate_input;
        }
        
        $nemmp = floatval($request->input('nemmp_incentive', 0));
        $discount = floatval($request->input('discount', 0));
        
        $grand_total = $total - $nemmp - $discount;

        $prev_bal = floatval($request->input('previous_balance', 0));
        $received = floatval($request->input('received_amount', 0));
        $balance = $grand_total - $received;
        $curr_bal = $prev_bal + $balance;

        $invoice = DB::transaction(function () use ($request, $vehicle, $rate, $sub_total, $cgst_rate, $cgst_amount, $sgst_rate, $sgst_amount, $igst_amount, $tax_regime, $total, $nemmp, $discount, $grand_total, $prev_bal, $received, $balance, $curr_bal) {
            // Generate invoice number
            $last = DB::table('vehicle_sales_invoices')->lockForUpdate()->orderBy('id', 'desc')->first();
            $nextId = $last ? $last->id + 1 : 1;
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

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
                'cgst_rate' => $cgst_rate,
                'cgst_amount' => $cgst_amount,
                'sgst_rate' => $sgst_rate,
                'sgst_amount' => $sgst_amount,
                'total' => $total,
                'nemmp_incentive' => $nemmp,
                'discount' => $discount,
                'grand_total' => $grand_total,
                'payment_mode' => $request->payment_mode,
                'finance_name' => $request->input('finance_name'),
                'tax_regime' => $tax_regime,
                'igst_amount' => $igst_amount,
                'received_amount' => $received,
                'balance' => $balance,
                'previous_balance' => $prev_bal,
                'current_balance' => $curr_bal,
                'warranty_notes' => $request->input('warranty_notes', "MOTOR, CONTROLLER WARRANTY - 1 YEAR\nBATTERY WARRANTY - 3 YEAR\nCHARGER WARRANTY - 2 YEAR"),
            ]);
        });

        return redirect()->route('admin.vehicle-sales-invoices.show', $invoice)->withSuccess('Vehicle Sales Invoice created successfully.');
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
        ]);

        $amount = floatval($request->input('amount'));

        if ($amount > $vehicleSalesInvoice->balance) {
            return response()->json(['success' => false, 'message' => 'Amount cannot exceed the balance (' . number_format($vehicleSalesInvoice->balance, 2) . ')']);
        }

        DB::transaction(function () use ($vehicleSalesInvoice, $amount) {
            $vehicleSalesInvoice->received_amount += $amount;
            $vehicleSalesInvoice->balance -= $amount;
            $vehicleSalesInvoice->current_balance -= $amount;
            $vehicleSalesInvoice->save();
        });

        return response()->json(['success' => true, 'message' => 'Payment received successfully.']);
    }
}

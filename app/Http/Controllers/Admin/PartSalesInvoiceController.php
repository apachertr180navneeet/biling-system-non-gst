<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartSalesInvoice;
use App\Models\PartSalesInvoiceItem;
use App\Models\Customer;
use App\Models\SparePart;
use App\Models\SparePartStock;
use App\Models\SparePartStockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class PartSalesInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = PartSalesInvoice::with('customer', 'items')
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

        return view('admin.part_sales_invoices.index', compact('invoices', 'search'));
    }

    public function outstanding(Request $request)
    {
        $search = $request->input('search');
        $query = PartSalesInvoice::with('customer', 'items.sparePart')
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
        return view('admin.part_sales_invoices.outstanding', compact('invoices', 'search'));
    }

    public function exportOutstanding(Request $request)
    {
        $search = $request->input('search');
        $query = PartSalesInvoice::with('customer', 'items.sparePart')
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
        $sheet->setCellValue('E1', 'Items');
        $sheet->setCellValue('F1', 'Total Amount');
        $sheet->setCellValue('G1', 'Received Amount');
        $sheet->setCellValue('H1', 'Balance');
        $sheet->setCellValue('I1', 'Payment Mode');

        $row = 2;
        foreach ($invoices as $inv) {
            $sheet->setCellValue('A' . $row, $inv->invoice_number);
            $sheet->setCellValue('B' . $row, $inv->invoice_date->format('d-m-Y'));
            $sheet->setCellValue('C' . $row, $inv->customer_name);
            $sheet->setCellValue('D' . $row, $inv->customer_mobile);
            $sheet->setCellValue('E' . $row, $inv->items->count());
            $sheet->setCellValue('F' . $row, $inv->total_amount);
            $sheet->setCellValue('G' . $row, $inv->received_amount);
            $sheet->setCellValue('H' . $row, $inv->balance);
            $sheet->setCellValue('I' . $row, $inv->payment_mode);
            $row++;
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/part_sales_outstanding_export.xls');
        $writer->save($path);

        return response()->download($path, 'part_sales_outstanding_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $query = PartSalesInvoice::with('customer', 'items')
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
        $sheet->setCellValue('E1', 'GSTIN');
        $sheet->setCellValue('F1', 'Items');
        $sheet->setCellValue('G1', 'Taxable Amount');
        $sheet->setCellValue('H1', 'CGST');
        $sheet->setCellValue('I1', 'SGST');
        $sheet->setCellValue('J1', 'Total');
        $sheet->setCellValue('K1', 'Payment Mode');

        $row = 2;
        foreach ($invoices as $inv) {
            $sheet->setCellValue('A' . $row, $inv->invoice_number);
            $sheet->setCellValue('B' . $row, $inv->invoice_date->format('d-m-Y'));
            $sheet->setCellValue('C' . $row, $inv->customer_name);
            $sheet->setCellValue('D' . $row, $inv->customer_mobile);
            $sheet->setCellValue('E' . $row, $inv->customer_gstin);
            $sheet->setCellValue('F' . $row, $inv->items->count());
            $sheet->setCellValue('G' . $row, $inv->taxable_amount);
            $sheet->setCellValue('H' . $row, $inv->cgst_amount);
            $sheet->setCellValue('I' . $row, $inv->sgst_amount);
            $sheet->setCellValue('J' . $row, $inv->total_amount);
            $sheet->setCellValue('K' . $row, $inv->payment_mode);
            $row++;
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/part_sales_invoices_export.xls');
        $writer->save($path);

        return response()->download($path, 'part_sales_invoices_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
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

        $maxNum = 550;
        foreach ($allInvoices as $invNum) {
            if (preg_match('/(\d+)$/', $invNum, $matches)) {
                $num = (int)$matches[1];
                if ($num >= 550 && $num < 850 && $num > $maxNum) {
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
        
        $spareParts = SparePart::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($part) {
                $stock = SparePartStock::where('spare_part_id', $part->id)->first();
                $part->qty_available = $stock ? $stock->quantity : 0;
                return $part;
            });

        $nextInvoiceNumber = $this->generateNextInvoiceNumber();

        return view('admin.part_sales_invoices.create', compact('customers', 'spareParts', 'nextInvoiceNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'nullable|string|max:255|unique:part_sales_invoices,invoice_number',
            'invoice_date' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'customer_mobile' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'customer_gstin' => 'nullable|string|max:15',
            'customer_pan' => 'nullable|string|max:10',
            'place_of_supply' => 'required|string|max:255',
            'payment_mode' => 'required|string|max:255',
            'tax_regime' => 'required|string|in:cgst_sgst,igst',
            'previous_balance' => 'nullable|numeric|min:0',
            'received_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.spare_part_id' => 'required|exists:spare_parts,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.tax_percentage' => 'required|numeric|min:0|max:100',
            'items.*.gst_type' => 'required|string|in:exclusive,inclusive',
            'items.*.serial_no_warranty_notes' => 'nullable|string|max:255',
        ]);

        if ($request->filled('invoice_number')) {
            if (!$this->isLastFourDigitsUnique($request->invoice_number)) {
                return back()->withErrors(['invoice_number' => 'The last 4 digits of the invoice number must be unique across both vehicle and parts invoices.'])->withInput();
            }
        }

        // Validate stock availability first
        foreach ($request->items as $itemData) {
            $part = SparePart::findOrFail($itemData['spare_part_id']);
            $stock = SparePartStock::where('spare_part_id', $part->id)->first();
            $available = $stock ? $stock->quantity : 0;
            if ($available < intval($itemData['quantity'])) {
                return back()->withErrors([
                    'items' => "Insufficient stock for part: {$part->name}. Available: {$available}, Requested: {$itemData['quantity']}"
                ])->withInput();
            }
        }

        // Calculations
        $taxable_amount = 0;
        $cgst_amount = 0;
        $sgst_amount = 0;
        $igst_amount = 0;
        $subtotal = 0;
        $tax_regime = $request->input('tax_regime', 'cgst_sgst');

        foreach ($request->items as $itemData) {
            $qty = intval($itemData['quantity']);
            $rate = floatval($itemData['rate']);
            $tax_pct = floatval($itemData['tax_percentage']);
            $gst_type = $itemData['gst_type'] ?? 'exclusive';

            if ($gst_type === 'inclusive') {
                $rate_excl_tax = $rate / (1 + ($tax_pct / 100));
                $line_taxable = $qty * $rate_excl_tax;
            } else {
                $line_taxable = $qty * $rate;
            }
            $line_tax = ($line_taxable * $tax_pct) / 100;
            
            $taxable_amount += $line_taxable;
            if ($tax_regime === 'igst') {
                $igst_amount += round($line_tax, 2);
            } else {
                $cgst_amount += round($line_tax / 2, 2);
                $sgst_amount += round($line_tax / 2, 2);
            }
            $subtotal += ($line_taxable + $line_tax);
        }

        $prev_bal = floatval($request->input('previous_balance', 0));
        $received = floatval($request->input('received_amount', 0));
        
        $total_before_round = $subtotal;
        $total_rounded = round($total_before_round);
        $round_off = $total_rounded - $total_before_round;

        $grand_total = $total_rounded;
        $balance = $grand_total - $received;
        $curr_bal = $prev_bal + $balance;

        $invoice = DB::transaction(function () use ($request, $taxable_amount, $cgst_amount, $sgst_amount, $igst_amount, $tax_regime, $round_off, $total_rounded, $received, $balance, $prev_bal, $curr_bal) {
            // Generate or use provided invoice number
            $invoiceNumber = $request->filled('invoice_number')
                ? trim($request->input('invoice_number'))
                : $this->generateNextInvoiceNumber($request->invoice_date);

            $inv = PartSalesInvoice::create([
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $request->invoice_date,
                'customer_id' => $request->customer_id,
                'customer_name' => $request->customer_name,
                'customer_mobile' => $request->customer_mobile,
                'customer_address' => $request->customer_address,
                'customer_gstin' => $request->customer_gstin,
                'customer_pan' => $request->customer_pan,
                'place_of_supply' => $request->place_of_supply,
                'tax_regime' => $tax_regime,
                'taxable_amount' => $taxable_amount,
                'cgst_amount' => $cgst_amount,
                'sgst_amount' => $sgst_amount,
                'igst_amount' => $igst_amount,
                'round_off' => $round_off,
                'total_amount' => $total_rounded,
                'received_amount' => $received,
                'balance' => $balance,
                'payment_mode' => $request->payment_mode,
                'previous_balance' => $prev_bal,
                'current_balance' => $curr_bal,
                'is_active' => true,
            ]);

            foreach ($request->items as $itemData) {
                $qty = intval($itemData['quantity']);
                $rate = floatval($itemData['rate']);
                $tax_pct = floatval($itemData['tax_percentage']);
                $gst_type = $itemData['gst_type'] ?? 'exclusive';

                if ($gst_type === 'inclusive') {
                    $rate = $rate / (1 + ($tax_pct / 100));
                }

                $line_taxable = $qty * $rate;
                $line_tax = ($line_taxable * $tax_pct) / 100;
                $line_amount = $line_taxable + $line_tax;

                // Decrement stock
                $stock = SparePartStock::where('spare_part_id', $itemData['spare_part_id'])->lockForUpdate()->first();
                $stock->decrement('quantity', $qty);

                // Transaction log
                SparePartStockTransaction::create([
                    'spare_part_id' => $itemData['spare_part_id'],
                    'transaction_type' => 'out',
                    'quantity' => $qty,
                    'reference_no' => $invoiceNumber,
                    'notes' => 'Sold via Parts Sales Invoice #' . $invoiceNumber,
                ]);

                PartSalesInvoiceItem::create([
                    'part_sales_invoice_id' => $inv->id,
                    'spare_part_id' => $itemData['spare_part_id'],
                    'quantity' => $qty,
                    'rate' => $rate,
                    'tax_percentage' => $tax_pct,
                    'tax_amount' => $line_tax,
                    'amount' => $line_amount,
                    'serial_no_warranty_notes' => $itemData['serial_no_warranty_notes'],
                ]);
            }

            return $inv;
        });

        return redirect()->route('admin.part-sales-invoices.show', $invoice)->withSuccess('Parts Sales Invoice created successfully.');
    }

    public function show(PartSalesInvoice $partSalesInvoice)
    {
        $partSalesInvoice->load('customer', 'items.sparePart');
        return view('admin.part_sales_invoices.show', compact('partSalesInvoice'));
    }

    public function destroy(PartSalesInvoice $partSalesInvoice)
    {
        DB::transaction(function () use ($partSalesInvoice) {
            $partSalesInvoice->load('items');
            foreach ($partSalesInvoice->items as $item) {
                $stock = SparePartStock::firstOrCreate(
                    ['spare_part_id' => $item->spare_part_id],
                    ['quantity' => 0, 'min_quantity' => 0, 'purchase_price' => 0]
                );
                $stock->increment('quantity', $item->quantity);

                // Transaction log for restore
                SparePartStockTransaction::create([
                    'spare_part_id' => $item->spare_part_id,
                    'transaction_type' => 'in',
                    'quantity' => $item->quantity,
                    'reference_no' => $partSalesInvoice->invoice_number,
                    'notes' => 'Restored via Parts Sales Invoice deletion #' . $partSalesInvoice->invoice_number,
                ]);
            }

            $partSalesInvoice->items()->delete();
            $partSalesInvoice->delete();
        });

        return response()->json(['success' => true, 'message' => 'Invoice deleted successfully.']);
    }

    public function receivePayment(Request $request, PartSalesInvoice $partSalesInvoice)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $amount = floatval($request->input('amount'));

        if ($amount > $partSalesInvoice->balance) {
            return response()->json(['success' => false, 'message' => 'Amount cannot exceed the balance (' . number_format($partSalesInvoice->balance, 2) . ')']);
        }

        DB::transaction(function () use ($partSalesInvoice, $amount) {
            $partSalesInvoice->received_amount += $amount;
            $partSalesInvoice->balance -= $amount;
            $partSalesInvoice->current_balance -= $amount;
            $partSalesInvoice->save();
        });

        return response()->json(['success' => true, 'message' => 'Payment received successfully.']);
    }

    public function quickUpdateDate(Request $request, PartSalesInvoice $partSalesInvoice)
    {
        $request->validate([
            'invoice_number' => 'required|string|max:255|unique:part_sales_invoices,invoice_number,' . $partSalesInvoice->id,
            'invoice_date' => 'required|date',
        ]);

        if (!$this->isLastFourDigitsUnique($request->invoice_number, null, $partSalesInvoice->id)) {
            return response()->json(['success' => false, 'message' => 'The last 4 digits of the invoice number must be unique across both vehicle and parts invoices.']);
        }

        $partSalesInvoice->update([
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
        ]);

        return response()->json(['success' => true, 'message' => 'Invoice Date & Number updated successfully.']);
    }
}

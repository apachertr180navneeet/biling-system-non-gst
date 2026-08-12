<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartSalesInvoice;
use App\Models\PartSalesInvoiceItem;
use App\Models\Customer;
use App\Models\SparePart;
use App\Models\SparePartStock;
use App\Models\SparePartStockTransaction;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $sheet->setCellValue('E1', 'Items');
        $sheet->setCellValue('F1', 'Amount');
        $sheet->setCellValue('G1', 'Total');
        $sheet->setCellValue('H1', 'Payment Mode');

        $row = 2;
        foreach ($invoices as $inv) {
            $sheet->setCellValue('A' . $row, $inv->invoice_number);
            $sheet->setCellValue('B' . $row, $inv->invoice_date->format('d-m-Y'));
            $sheet->setCellValue('C' . $row, $inv->customer_name);
            $sheet->setCellValue('D' . $row, $inv->customer_mobile);
            $sheet->setCellValue('E' . $row, $inv->items->count());
            $sheet->setCellValue('F' . $row, $inv->taxable_amount);
            $sheet->setCellValue('G' . $row, $inv->total_amount);
            $sheet->setCellValue('H' . $row, $inv->payment_mode);
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
            'customer_pan' => 'nullable|string|max:10',
            'place_of_supply' => 'required|string|max:255',
            'payment_mode' => 'required|string|max:255',
            'previous_balance' => 'nullable|numeric|min:0',
            'received_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.spare_part_id' => 'required|exists:spare_parts,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.serial_no_warranty_notes' => 'nullable|string|max:255',
        ]);

        if ($request->filled('invoice_number')) {
            if (!$this->isLastFourDigitsUnique($request->invoice_number)) {
                return back()->withErrors(['invoice_number' => 'The last 4 digits of the invoice number must be unique across both vehicle and parts invoices.'])->withInput();
            }
        }

        // Calculations
        $taxable_amount = 0;
        $subtotal = 0;

        foreach ($request->items as $itemData) {
            $qty = intval($itemData['quantity']);
            $rate = floatval($itemData['rate']);
            $line_amount = $qty * $rate;
            $subtotal += $line_amount;
        }

        $taxable_amount = $subtotal;

        $prev_bal = floatval($request->input('previous_balance', 0));
        $received = floatval($request->input('received_amount', 0));
        
        $total_before_round = $subtotal;
        $total_rounded = round($total_before_round);
        $round_off = $total_rounded - $total_before_round;

        $grand_total = $total_rounded;
        $balance = $grand_total - $received;
        $curr_bal = $prev_bal + $balance;

        $invoice = DB::transaction(function () use ($request, $taxable_amount, $round_off, $total_rounded, $received, $balance, $prev_bal, $curr_bal) {
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
                'customer_pan' => $request->customer_pan,
                'place_of_supply' => $request->place_of_supply,
                'taxable_amount' => $taxable_amount,
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
                $line_amount = $qty * $rate;

                // Decrement stock
                $stock = SparePartStock::firstOrCreate(
                    ['spare_part_id' => $itemData['spare_part_id']],
                    ['quantity' => 0, 'min_quantity' => 0, 'purchase_price' => 0]
                );
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
        $customerLedger = $this->getCustomerLedgerSummary($partSalesInvoice);
        return view('admin.part_sales_invoices.show', compact('partSalesInvoice', 'customerLedger'));
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
            'payment_mode' => 'nullable|string|max:255',
        ]);

        $amount = floatval($request->input('amount'));
        $paymentMode = $request->input('payment_mode', $partSalesInvoice->payment_mode ?? 'Cash');

        if ($amount > $partSalesInvoice->balance) {
            return response()->json(['success' => false, 'message' => 'Amount cannot exceed the balance (' . number_format($partSalesInvoice->balance, 2) . ')']);
        }

        DB::transaction(function () use ($partSalesInvoice, $amount, $paymentMode) {
            $partSalesInvoice->received_amount += $amount;
            $partSalesInvoice->balance -= $amount;
            $partSalesInvoice->current_balance -= $amount;
            $partSalesInvoice->save();

            PaymentTransaction::create([
                'transaction_type' => 'sales',
                'bill_type' => 'part_sales',
                'bill_id' => $partSalesInvoice->id,
                'party_type' => 'customer',
                'party_id' => $partSalesInvoice->customer_id,
                'party_name' => $partSalesInvoice->customer_name,
                'payment_date' => date('Y-m-d'),
                'amount' => $amount,
                'payment_mode' => $paymentMode,
                'type' => 'payment',
                'created_by' => auth()->id() ?? null,
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Payment received successfully.']);
    }

    public function rollbackPayment(Request $request, PartSalesInvoice $partSalesInvoice, PaymentTransaction $paymentTransaction)
    {
        $request->validate([
            'rollback_reason' => 'required|string|max:255',
        ]);

        if ($paymentTransaction->bill_id != $partSalesInvoice->id || $paymentTransaction->bill_type != 'part_sales') {
            return response()->json(['success' => false, 'message' => 'Invalid payment transaction for this invoice.']);
        }

        if ($paymentTransaction->type === 'rollback' || $paymentTransaction->isRolledBack()) {
            return response()->json(['success' => false, 'message' => 'This payment has already been rolled back.']);
        }

        $rollbackAmount = (float)$paymentTransaction->amount;

        DB::transaction(function () use ($partSalesInvoice, $paymentTransaction, $rollbackAmount, $request) {
            $partSalesInvoice->received_amount = max(0, $partSalesInvoice->received_amount - $rollbackAmount);
            $partSalesInvoice->balance += $rollbackAmount;
            $partSalesInvoice->current_balance += $rollbackAmount;
            $partSalesInvoice->save();

            PaymentTransaction::create([
                'transaction_type' => 'sales',
                'bill_type' => 'part_sales',
                'bill_id' => $partSalesInvoice->id,
                'party_type' => 'customer',
                'party_id' => $partSalesInvoice->customer_id,
                'party_name' => $partSalesInvoice->customer_name,
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

    public function edit(PartSalesInvoice $partSalesInvoice)
    {
        $partSalesInvoice->load('items.sparePart');
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        
        $spareParts = SparePart::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($part) {
                $stock = SparePartStock::where('spare_part_id', $part->id)->first();
                $part->qty_available = $stock ? $stock->quantity : 0;
                return $part;
            });

        return view('admin.part_sales_invoices.edit', compact('partSalesInvoice', 'customers', 'spareParts'));
    }

    public function update(Request $request, PartSalesInvoice $partSalesInvoice)
    {
        $request->validate([
            'invoice_number' => 'required|string|max:255|unique:part_sales_invoices,invoice_number,' . $partSalesInvoice->id,
            'invoice_date' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'customer_mobile' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'customer_pan' => 'nullable|string|max:10',
            'place_of_supply' => 'required|string|max:255',
            'payment_mode' => 'required|string|max:255',
            'previous_balance' => 'nullable|numeric|min:0',
            'received_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.spare_part_id' => 'required|exists:spare_parts,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.serial_no_warranty_notes' => 'nullable|string|max:255',
        ]);

        if (!$this->isLastFourDigitsUnique($request->invoice_number, null, $partSalesInvoice->id)) {
            return back()->withErrors(['invoice_number' => 'The last 4 digits of the invoice number must be unique across both vehicle and parts invoices.'])->withInput();
        }

        DB::transaction(function () use ($request, $partSalesInvoice) {
            // 1. Restore previous stock
            $partSalesInvoice->load('items');
            foreach ($partSalesInvoice->items as $oldItem) {
                $stock = SparePartStock::firstOrCreate(
                    ['spare_part_id' => $oldItem->spare_part_id],
                    ['quantity' => 0, 'min_quantity' => 0, 'purchase_price' => 0]
                );
                $stock->increment('quantity', $oldItem->quantity);
            }

            // 2. Delete existing items
            $partSalesInvoice->items()->delete();

            // 3. Calculations
            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $qty = intval($itemData['quantity']);
                $rate = floatval($itemData['rate']);
                $subtotal += ($qty * $rate);
            }

            $taxable_amount = $subtotal;
            $prev_bal = floatval($request->input('previous_balance', 0));
            $received = floatval($request->input('received_amount', 0));
            
            $total_before_round = $subtotal;
            $total_rounded = round($total_before_round);
            $round_off = $total_rounded - $total_before_round;

            $grand_total = $total_rounded;
            $balance = $grand_total - $received;
            $curr_bal = $prev_bal + $balance;

            // 4. Update invoice
            $partSalesInvoice->update([
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'customer_id' => $request->customer_id,
                'customer_name' => $request->customer_name,
                'customer_mobile' => $request->customer_mobile,
                'customer_address' => $request->customer_address,
                'customer_pan' => $request->customer_pan,
                'place_of_supply' => $request->place_of_supply,
                'taxable_amount' => $taxable_amount,
                'round_off' => $round_off,
                'total_amount' => $total_rounded,
                'received_amount' => $received,
                'balance' => $balance,
                'payment_mode' => $request->payment_mode,
                'previous_balance' => $prev_bal,
                'current_balance' => $curr_bal,
            ]);

            // 5. Create updated items and decrement stock
            foreach ($request->items as $itemData) {
                $qty = intval($itemData['quantity']);
                $rate = floatval($itemData['rate']);
                $line_amount = $qty * $rate;

                $stock = SparePartStock::firstOrCreate(
                    ['spare_part_id' => $itemData['spare_part_id']],
                    ['quantity' => 0, 'min_quantity' => 0, 'purchase_price' => 0]
                );
                $stock->decrement('quantity', $qty);

                SparePartStockTransaction::create([
                    'spare_part_id' => $itemData['spare_part_id'],
                    'transaction_type' => 'out',
                    'quantity' => $qty,
                    'reference_no' => $request->invoice_number,
                    'notes' => 'Sold via updated Parts Sales Invoice #' . $request->invoice_number,
                ]);

                PartSalesInvoiceItem::create([
                    'part_sales_invoice_id' => $partSalesInvoice->id,
                    'spare_part_id' => $itemData['spare_part_id'],
                    'quantity' => $qty,
                    'rate' => $rate,
                    'amount' => $line_amount,
                    'serial_no_warranty_notes' => $itemData['serial_no_warranty_notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.part-sales-invoices.show', $partSalesInvoice)->withSuccess('Parts Sales Invoice updated successfully.');
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

    public function generatePdf(Request $request, PartSalesInvoice $partSalesInvoice)
    {
        $partSalesInvoice->load('customer', 'items.sparePart');
        $customerLedger = $this->getCustomerLedgerSummary($partSalesInvoice);

        $pdf = Pdf::loadView('admin.part_sales_invoices.pdf', [
            'partSalesInvoice' => $partSalesInvoice,
            'customerLedger' => $customerLedger,
        ]);
        $pdf->setPaper('a4');
        $pdf->setOption('isRemoteEnabled', true);

        if ($request->has('print')) {
            $pdf->render();
            $canvas = $pdf->getCanvas();
            $canvas->javascript("this.print();");
            return $pdf->stream('Part-Invoice-' . $partSalesInvoice->invoice_number . '.pdf');
        }

        if ($request->has('stream')) {
            return $pdf->stream('Part-Invoice-' . $partSalesInvoice->invoice_number . '.pdf');
        }

        return $pdf->download('Part-Invoice-' . $partSalesInvoice->invoice_number . '.pdf');
    }

    private function getCustomerLedgerSummary(PartSalesInvoice $invoice)
    {
        $customer = $invoice->customer;
        if (!$customer && $invoice->customer_mobile) {
            $customer = Customer::where('phone', $invoice->customer_mobile)->first();
        }

        $customerId = $customer ? $customer->id : $invoice->customer_id;

        if ($customerId) {
            $vInvoices = DB::table('vehicle_sales_invoices')->where('customer_id', $customerId)->whereNull('deleted_at');
            $pInvoices = DB::table('part_sales_invoices')->where('customer_id', $customerId)->whereNull('deleted_at');
            
            $vBilled = (float) $vInvoices->sum('grand_total');
            $vPaid = (float) $vInvoices->sum('received_amount');

            $pBilled = (float) $pInvoices->sum('total_amount');
            $pPaid = (float) $pInvoices->sum('received_amount');

            $totalBilled = $vBilled + $pBilled;
            $totalPaid = $vPaid + $pPaid;
            $outstanding = $totalBilled - $totalPaid;
        } else {
            $totalBilled = (float) $invoice->total_amount + (float) ($invoice->previous_balance ?? 0);
            $totalPaid = (float) $invoice->received_amount;
            $outstanding = (float) ($invoice->current_balance ?? ($totalBilled - $totalPaid));
        }

        return (object) [
            'customer_name' => $invoice->customer_name,
            'customer_id' => $customerId,
            'total_billed' => $totalBilled,
            'total_paid' => $totalPaid,
            'outstanding_balance' => $outstanding,
        ];
    }
}

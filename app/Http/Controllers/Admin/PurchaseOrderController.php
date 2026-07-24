<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SparePart;
use App\Models\SparePartStock;
use App\Models\SparePartStockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = PurchaseOrder::with('supplier', 'items')->orderBy('created_at', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('order_number', 'like', $escapedSearch)
                  ->orWhereHas('supplier', function($sq) use ($escapedSearch) {
                      $sq->where('name', 'like', $escapedSearch);
                  });
            });
        }

        $orders = $query->paginate(20);
        return view('admin.purchase_orders.index', compact('orders', 'search'));
    }

    public function outstanding(Request $request)
    {
        $search = $request->input('search');
        $query = PurchaseOrder::with('supplier', 'items.sparePart')
            ->where('balance', '>', 0)
            ->orderBy('created_at', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('order_number', 'like', $escapedSearch)
                  ->orWhereHas('supplier', function($sq) use ($escapedSearch) {
                      $sq->where('name', 'like', $escapedSearch);
                  });
            });
        }

        $orders = $query->paginate(20);
        return view('admin.purchase_orders.outstanding', compact('orders', 'search'));
    }

    public function exportOutstanding(Request $request)
    {
        $search = $request->input('search');
        $query = PurchaseOrder::with('supplier', 'items.sparePart')
            ->where('balance', '>', 0)
            ->orderBy('created_at', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('order_number', 'like', $escapedSearch)
                  ->orWhereHas('supplier', function($sq) use ($escapedSearch) {
                      $sq->where('name', 'like', $escapedSearch);
                  });
            });
        }

        $orders = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Order No');
        $sheet->setCellValue('B1', 'Supplier');
        $sheet->setCellValue('C1', 'Order Date');
        $sheet->setCellValue('D1', 'Item');
        $sheet->setCellValue('E1', 'Part No');
        $sheet->setCellValue('F1', 'Ordered Qty');
        $sheet->setCellValue('G1', 'Received Qty');
        $sheet->setCellValue('H1', 'Outstanding Qty');
        $sheet->setCellValue('I1', 'Unit Price');
        $sheet->setCellValue('J1', 'Outstanding Amount');
        $sheet->setCellValue('K1', 'Status');

        $row = 2;
        foreach ($orders as $o) {
            foreach ($o->items as $item) {
                $outstandingQty = $item->quantity - $item->received_quantity;
                if ($outstandingQty <= 0) continue;
                $sheet->setCellValue('A' . $row, $o->order_number);
                $sheet->setCellValue('B' . $row, $o->supplier->name ?? '-');
                $sheet->setCellValue('C' . $row, $o->order_date->format('d-m-Y'));
                $sheet->setCellValue('D' . $row, $item->sparePart->name ?? '-');
                $sheet->setCellValue('E' . $row, $item->sparePart->part_no ?? '-');
                $sheet->setCellValue('F' . $row, $item->quantity);
                $sheet->setCellValue('G' . $row, $item->received_quantity);
                $sheet->setCellValue('H' . $row, $outstandingQty);
                $sheet->setCellValue('I' . $row, $item->unit_price);
                $sheet->setCellValue('J' . $row, $outstandingQty * $item->unit_price);
                $sheet->setCellValue('K' . $row, ucfirst($o->status));
                $row++;
            }
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/purchase_orders_outstanding_export.xls');
        $writer->save($path);

        return response()->download($path, 'purchase_orders_outstanding_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $query = PurchaseOrder::with('supplier', 'items')->orderBy('created_at', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('order_number', 'like', $escapedSearch)
                  ->orWhereHas('supplier', function($sq) use ($escapedSearch) {
                      $sq->where('name', 'like', $escapedSearch);
                  });
            });
        }

        $orders = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Order No');
        $sheet->setCellValue('B1', 'Supplier');
        $sheet->setCellValue('C1', 'Order Date');
        $sheet->setCellValue('D1', 'Items');
        $sheet->setCellValue('E1', 'Total Amount');
        $sheet->setCellValue('F1', 'Status');
        $sheet->setCellValue('G1', 'Active');

        $row = 2;
        foreach ($orders as $o) {
            $sheet->setCellValue('A' . $row, $o->order_number);
            $sheet->setCellValue('B' . $row, $o->supplier->name ?? '-');
            $sheet->setCellValue('C' . $row, $o->order_date->format('d-m-Y'));
            $sheet->setCellValue('D' . $row, $o->items->count());
            $sheet->setCellValue('E' . $row, $o->total_amount);
            $sheet->setCellValue('F' . $row, ucfirst($o->status));
            $sheet->setCellValue('G' . $row, $o->is_active ? 'Active' : 'Inactive');
            $row++;
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/purchase_orders_export.xls');
        $writer->save($path);

        return response()->download($path, 'purchase_orders_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $spareParts = SparePart::orderBy('name')->get();
        return view('admin.purchase_orders.create', compact('suppliers', 'spareParts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.spare_part_id' => 'required|exists:spare_parts,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $data['created_by'] = Auth::id();

        $total = 0;
        $items = [];
        foreach ($data['items'] as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $total += $lineTotal;
            $items[] = new PurchaseOrderItem([
                'spare_part_id' => $item['spare_part_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $lineTotal,
            ]);
        }
        $data['total_amount'] = $total;
        $data['received_amount'] = 0;
        $data['balance'] = $total;
        unset($data['items']);

        $order = DB::transaction(function () use ($data, $items) {
            $lastOrder = DB::table('purchase_orders')->lockForUpdate()->orderBy('id', 'desc')->first();
            $nextId = $lastOrder ? $lastOrder->id + 1 : 1;
            $data['order_number'] = 'PO-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            $order = PurchaseOrder::create($data);
            $order->items()->saveMany($items);
            return $order;
        });

        return redirect()->route('admin.purchase-orders.index')->withSuccess('Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier', 'items.sparePart', 'createdBy');
        return view('admin.purchase_orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('admin.purchase-orders.index')->with('error', 'Only pending orders can be edited.');
        }
        $purchaseOrder->load('items');
        $suppliers = Supplier::orderBy('name')->get();
        $spareParts = SparePart::orderBy('name')->get();
        return view('admin.purchase_orders.edit', compact('purchaseOrder', 'suppliers', 'spareParts'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('admin.purchase-orders.index')->with('error', 'Only pending orders can be edited.');
        }

        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.spare_part_id' => 'required|exists:spare_parts,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $total = 0;
        $newItems = [];
        foreach ($data['items'] as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $total += $lineTotal;
            $newItems[] = new PurchaseOrderItem([
                'spare_part_id' => $item['spare_part_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $lineTotal,
            ]);
        }
        unset($data['items']);
        $data['total_amount'] = $total;

        $purchaseOrder->update($data);
        $purchaseOrder->items()->delete();
        $purchaseOrder->items()->saveMany($newItems);

        return redirect()->route('admin.purchase-orders.index')->withSuccess('Purchase order updated successfully.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        DB::transaction(function () use ($purchaseOrder) {
            $purchaseOrder->load('items');
            foreach ($purchaseOrder->items as $item) {
                if ($item->received_quantity > 0) {
                    $stock = SparePartStock::where('spare_part_id', $item->spare_part_id)->lockForUpdate()->first();
                    if ($stock) {
                        $stock->decrement('quantity', $item->received_quantity);
                        SparePartStockTransaction::create([
                            'spare_part_id' => $item->spare_part_id,
                            'transaction_type' => 'out',
                            'quantity' => $item->received_quantity,
                            'reference_no' => $purchaseOrder->order_number,
                            'notes' => 'Stock out due to Purchase Order deletion',
                        ]);
                    }
                }
            }
            $purchaseOrder->items()->delete();
            $purchaseOrder->delete();
        });
        return response()->json(['success' => true, 'message' => 'Purchase order deleted successfully.']);
    }

    public function toggleStatus(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update(['is_active' => !$purchaseOrder->is_active]);
        $purchaseOrder->refresh();
        return response()->json(['success' => true, 'is_active' => $purchaseOrder->is_active]);
    }

    public function receive(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('items.sparePart', 'supplier');
        if ($purchaseOrder->status === 'received') {
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder)->with('error', 'Already fully received.');
        }
        return view('admin.purchase_orders.receive', compact('purchaseOrder'));
    }

    public function receiveStore(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'received') {
            return back()->with('error', 'Already fully received.');
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:purchase_order_items,id',
            'items.*.received_qty' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $purchaseOrder) {
            $allFullyReceived = true;
            $anyReceived = false;

            foreach ($request->items as $itemData) {
                $poItem = $purchaseOrder->items()->findOrFail($itemData['id']);
                $previousReceived = $poItem->received_quantity;
                $newReceived = min($itemData['received_qty'] + $previousReceived, $poItem->quantity);
                $delta = $newReceived - $previousReceived;
                $poItem->update(['received_quantity' => $newReceived]);

                if ($delta > 0) {
                    $anyReceived = true;
                    $stock = SparePartStock::firstOrCreate(
                        ['spare_part_id' => $poItem->spare_part_id],
                        ['quantity' => 0, 'min_quantity' => 0, 'purchase_price' => 0]
                    );
                    $stock->increment('quantity', $delta);
                    $stock->update(['purchase_price' => $poItem->unit_price, 'purchase_order_id' => $purchaseOrder->id]);
                    
                    SparePartStockTransaction::create([
                        'spare_part_id' => $poItem->spare_part_id,
                        'transaction_type' => 'in',
                        'quantity' => $delta,
                        'reference_no' => $purchaseOrder->order_number,
                        'notes' => 'Received via Purchase Order',
                    ]);
                }

                if ($newReceived < $poItem->quantity) {
                    $allFullyReceived = false;
                }
            }

            if (!$anyReceived) {
                throw new \Exception('Receive at least one item.');
            }

            $purchaseOrder->update([
                'status' => $allFullyReceived ? 'received' : 'partial',
            ]);
        });

        return redirect()->route('admin.purchase-orders.show', $purchaseOrder)->withSuccess('Items received successfully.');
    }

    public function generatePdf(Request $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier', 'items.sparePart', 'createdBy');

        $pdf = Pdf::loadView('admin.purchase_orders.pdf', [
            'purchaseOrder' => $purchaseOrder,
            'order' => $purchaseOrder
        ]);
        $pdf->setPaper('a4');
        $pdf->setOption('isRemoteEnabled', true);

        if ($request->has('print')) {
            $pdf->render();
            $canvas = $pdf->getCanvas();
            $canvas->javascript("this.print();");
        }

        return $pdf->stream('PO-' . $purchaseOrder->order_number . '.pdf');
    }

    public function sendWhatsapp(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier', 'items.sparePart');

        $phone = $purchaseOrder->supplier->phone ?? '';
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (empty($phone)) {
            return back()->with('error', 'Supplier phone number is not available.');
        }

        if (strlen($phone) == 10) {
            $phone = '91' . $phone;
        }

        $itemsList = '';
        foreach ($purchaseOrder->items as $i => $item) {
            $sparePart = $item->sparePart;
            $partName = $sparePart ? ($sparePart->name ?? 'N/A') : 'N/A';
            $partNo = $sparePart ? ($sparePart->part_no ?? '') : '';
            $itemsList .= ($i + 1) . ". " . $partName . " (" . $partNo . ") x " . $item->quantity . "\n";
        }

        $pdfUrl = request()->getSchemeAndHttpHost() . route('admin.purchase-orders.pdf', $purchaseOrder, false);

        $message = "*PURCHASE ORDER - {$purchaseOrder->order_number}*\n"
            . "━━━━━━━━━━━━━━━━━━━━━━\n"
            . "📅 *Date:* {$purchaseOrder->order_date->format('d/m/Y')}\n"
            . "🏢 *Supplier:* " . ($purchaseOrder->supplier->name ?? '-') . "\n"
            . "📦 *Items:*\n{$itemsList}"
            . "━━━━━━━━━━━━━━━━━━━━━━\n"
            . "💰 *Total:* ₹" . number_format($purchaseOrder->total_amount, 2) . "\n"
            . "📌 *Status:* " . ucfirst($purchaseOrder->status) . "\n"
            . "━━━━━━━━━━━━━━━━━━━━━━\n\n"
            . "📄 *PDF Link:* {$pdfUrl}\n\n"
            . "Please find the attached PO document. Kindly confirm.";

        $whatsappUrl = "https://wa.me/{$phone}?text=" . urlencode($message);

        return redirect($whatsappUrl);
    }

    public function receivePayment(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $amount = floatval($request->input('amount'));

        if ($amount > $purchaseOrder->balance) {
            return response()->json(['success' => false, 'message' => 'Amount cannot exceed the balance (' . number_format($purchaseOrder->balance, 2) . ')']);
        }

        DB::transaction(function () use ($purchaseOrder, $amount) {
            $purchaseOrder->received_amount += $amount;
            $purchaseOrder->balance -= $amount;
            $purchaseOrder->save();
        });

        return response()->json(['success' => true, 'message' => 'Payment received successfully.']);
    }
}

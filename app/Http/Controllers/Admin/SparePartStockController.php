<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SparePartStock;
use App\Models\SparePartStockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class SparePartStockController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');
        $query = SparePartStock::with('sparePart', 'purchaseOrder')->orderBy('created_at', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->whereHas('sparePart', function($q) use ($escapedSearch) {
                $q->where('part_no', 'like', $escapedSearch)
                  ->orWhere('name', 'like', $escapedSearch);
            });
        }

        if ($statusFilter === 'low_stock') {
            $query->whereHas('sparePart', function($q) {
                $q->whereColumn('spare_part_stocks.quantity', '<=', 'spare_parts.min_stock')
                  ->where('spare_parts.min_stock', '>', 0)
                  ->where('spare_part_stocks.quantity', '>', 0);
            });
        } elseif ($statusFilter === 'out_of_stock') {
            $query->where('quantity', '<', 1);
        } elseif ($statusFilter === 'available') {
            $query->where('quantity', '>=', 1)
                  ->where(function($q) {
                      $q->whereHas('sparePart', function($sq) {
                          $sq->whereColumn('spare_part_stocks.quantity', '>', 'spare_parts.min_stock');
                      })->orWhereHas('sparePart', function($sq) {
                          $sq->where('spare_parts.min_stock', 0);
                      });
                  });
        }

        $stocks = $query->paginate(20)->withQueryString();
        $spareParts = \App\Models\SparePart::where('is_active', true)->orderBy('name')->get();
        return view('admin.spare_part_stocks.index', compact('stocks', 'spareParts', 'search', 'statusFilter'));
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');
        $query = SparePartStock::with('sparePart', 'purchaseOrder')->orderBy('created_at', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->whereHas('sparePart', function($q) use ($escapedSearch) {
                $q->where('part_no', 'like', $escapedSearch)
                  ->orWhere('name', 'like', $escapedSearch);
            });
        }

        if ($statusFilter === 'low_stock') {
            $query->whereHas('sparePart', function($q) {
                $q->whereColumn('spare_part_stocks.quantity', '<=', 'spare_parts.min_stock')
                  ->where('spare_parts.min_stock', '>', 0)
                  ->where('spare_part_stocks.quantity', '>', 0);
            });
        } elseif ($statusFilter === 'out_of_stock') {
            $query->where('quantity', '<', 1);
        } elseif ($statusFilter === 'available') {
            $query->where('quantity', '>=', 1)
                  ->where(function($q) {
                      $q->whereHas('sparePart', function($sq) {
                          $sq->whereColumn('spare_part_stocks.quantity', '>', 'spare_parts.min_stock');
                      })->orWhereHas('sparePart', function($sq) {
                          $sq->where('spare_parts.min_stock', 0);
                      });
                  });
        }

        $stocks = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Part No');
        $sheet->setCellValue('B1', 'Part Name');
        $sheet->setCellValue('C1', 'Quantity');
        $sheet->setCellValue('D1', 'Min Stock Threshold');
        $sheet->setCellValue('E1', 'Purchase Price');
        $sheet->setCellValue('F1', 'Status');
        $sheet->setCellValue('G1', 'PO Ref');

        $row = 2;
        foreach ($stocks as $s) {
            $effectiveMinStock = ($s->sparePart && $s->sparePart->min_stock > 0) ? $s->sparePart->min_stock : $s->min_quantity;
            $status = 'Available';
            if ($s->quantity < 1) {
                $status = 'Out of Stock';
            } elseif ($effectiveMinStock > 0 && $s->quantity <= $effectiveMinStock) {
                $status = 'Low Stock';
            }

            $sheet->setCellValue('A' . $row, $s->sparePart->part_no ?? '-');
            $sheet->setCellValue('B' . $row, $s->sparePart->name ?? '-');
            $sheet->setCellValue('C' . $row, $s->quantity);
            $sheet->setCellValue('D' . $row, $effectiveMinStock);
            $sheet->setCellValue('E' . $row, $s->purchase_price);
            $sheet->setCellValue('F' . $row, $status);
            $sheet->setCellValue('G' . $row, $s->purchaseOrder->order_number ?? '-');
            $row++;
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/spare_part_stocks_export.xls');
        $writer->save($path);

        return response()->download($path, 'spare_part_stocks_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
    }

    public function toggleStatus(SparePartStock $sparePartStock)
    {
        $sparePartStock->update(['is_active' => !$sparePartStock->is_active]);
        return response()->json(['success' => true, 'is_active' => $sparePartStock->fresh()->is_active]);
    }

    public function destroy(SparePartStock $sparePartStock)
    {
        $sparePartStock->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully.']);
    }

    public function adjust(Request $request)
    {
        $data = $request->validate([
            'spare_part_id' => 'required|exists:spare_parts,id',
            'adjustment_type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($data) {
                $stock = SparePartStock::firstOrCreate(
                    ['spare_part_id' => $data['spare_part_id']],
                    ['quantity' => 0, 'min_quantity' => 0, 'purchase_price' => 0]
                );

                if ($data['adjustment_type'] === 'out') {
                    if ($stock->quantity < $data['quantity']) {
                        throw new \Exception("Insufficient stock. Current stock is only {$stock->quantity}.");
                    }
                    $stock->decrement('quantity', $data['quantity']);
                } else {
                    $stock->increment('quantity', $data['quantity']);
                }

                SparePartStockTransaction::create([
                    'spare_part_id' => $data['spare_part_id'],
                    'transaction_type' => $data['adjustment_type'],
                    'quantity' => $data['quantity'],
                    'reference_no' => 'ADJ-' . date('YmdHis'),
                    'notes' => $data['notes'] ?? 'Manual stock adjustment',
                ]);
            });

            return back()->withSuccess('Stock adjusted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }
    }
}


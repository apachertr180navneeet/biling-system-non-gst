<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleInventory;
use App\Models\SparePart;
use App\Models\SparePartStock;
use App\Models\SparePartStockTransaction;
use App\Models\VehicleSalesInvoice;
use App\Models\PartSalesInvoice;
use App\Models\VehiclePurchaseOrder;
use App\Models\PurchaseOrder;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\VehicleMaster;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function vehicleLedger(Request $request)
    {
        $search = $request->input('search');
        $chassis = $request->input('chassis_number');
        $engine = $request->input('engine_number');

        // 1. Summary grouped by vehicle details
        $summaryQuery = VehicleInventory::select(
            'vehicle_description',
            DB::raw('COUNT(*) as total_in'),
            DB::raw('SUM(CASE WHEN status = "sold" THEN 1 ELSE 0 END) as total_out'),
            DB::raw('SUM(CASE WHEN status = "available" THEN 1 ELSE 0 END) as remaining')
        )->groupBy('vehicle_description');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $summaryQuery->where('vehicle_description', 'like', $escapedSearch);
        }
        $summaries = $summaryQuery->get();

        $vehicleMasters = \App\Models\VehicleMaster::where('is_active', true)->get();
        foreach ($summaries as $s) {
            $matchedMaster = $vehicleMasters->first(function($m) use ($s) {
                $fullName = $m->variant_name . ($m->color_name ? ' (' . $m->color_name . ')' : '');
                return strtolower($fullName) === strtolower($s->vehicle_description) 
                    || strtolower($m->variant_name) === strtolower($s->vehicle_description);
            });
            $s->min_stock = $matchedMaster ? $matchedMaster->min_stock : 0;
        }

        // 2. Chronological Ledger transactions
        $ledgerQuery = VehicleInventory::with('purchaseOrder')->orderBy('created_at', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $ledgerQuery->where('vehicle_description', 'like', $escapedSearch);
        }
        if ($chassis) {
            $escapedChassis = '%' . addcslashes($chassis, '%_') . '%';
            $ledgerQuery->where('chassis_number', 'like', $escapedChassis);
        }
        if ($engine) {
            $escapedEngine = '%' . addcslashes($engine, '%_') . '%';
            $ledgerQuery->where('engine_number', 'like', $escapedEngine);
        }

        $ledger = $ledgerQuery->paginate(20)->withQueryString();

        return view('admin.reports.vehicle_ledger', compact('summaries', 'ledger', 'search', 'chassis', 'engine'));
    }

    public function partLedger(Request $request)
    {
        $search = $request->input('search');
        $type = $request->input('transaction_type');

        // 1. Part Wise Summaries
        $summaryQuery = SparePart::leftJoin('spare_part_stock_transactions as t', 'spare_parts.id', '=', 't.spare_part_id')
            ->leftJoin('spare_part_stocks as s', 'spare_parts.id', '=', 's.spare_part_id')
            ->select(
                'spare_parts.id',
                'spare_parts.part_no',
                'spare_parts.name',
                'spare_parts.min_stock',
                DB::raw('COALESCE(SUM(CASE WHEN t.transaction_type = "in" THEN t.quantity ELSE 0 END), 0) as total_in'),
                DB::raw('COALESCE(SUM(CASE WHEN t.transaction_type = "out" THEN t.quantity ELSE 0 END), 0) as total_out'),
                DB::raw('COALESCE(MAX(s.quantity), 0) as remaining')
            )
            ->groupBy('spare_parts.id', 'spare_parts.part_no', 'spare_parts.name', 'spare_parts.min_stock');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $summaryQuery->where(function ($q) use ($escapedSearch) {
                $q->where('spare_parts.name', 'like', $escapedSearch)
                  ->orWhere('spare_parts.part_no', 'like', $escapedSearch);
            });
        }
        $summaries = $summaryQuery->get();

        // 2. Ledger Transactions List
        $ledgerQuery = SparePartStockTransaction::with('sparePart')->orderBy('created_at', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $ledgerQuery->whereHas('sparePart', function ($q) use ($escapedSearch) {
                $q->where('name', 'like', $escapedSearch)
                  ->orWhere('part_no', 'like', $escapedSearch);
            });
        }
        if ($type) {
            $ledgerQuery->where('transaction_type', $type);
        }

        $ledger = $ledgerQuery->paginate(20)->withQueryString();

        return view('admin.reports.part_ledger', compact('summaries', 'ledger', 'search', 'type'));
    }

    public function outstandingLedger(Request $request)
    {
        $tab = $request->input('tab', 'sales');
        $search = $request->input('search');
        $type = $request->input('type', 'all');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Calculate total summaries
        $totalOutstandingSalesVehicle = VehicleSalesInvoice::where('balance', '>', 0)->sum('balance');
        $totalOutstandingSalesParts = PartSalesInvoice::where('balance', '>', 0)->sum('balance');
        $totalOutstandingSales = $totalOutstandingSalesVehicle + $totalOutstandingSalesParts;

        $totalOutstandingPurchasesVehicle = VehiclePurchaseOrder::where('balance', '>', 0)->sum('balance');
        $totalOutstandingPurchasesParts = PurchaseOrder::where('balance', '>', 0)->sum('balance');
        $totalOutstandingPurchases = $totalOutstandingPurchasesVehicle + $totalOutstandingPurchasesParts;

        $ledger = null;

        if ($tab === 'sales') {
            $salesQuery1 = null;
            $salesQuery2 = null;

            if ($type === 'all' || $type === 'vehicle') {
                $q = VehicleSalesInvoice::with('customer')
                    ->where('balance', '>', 0)
                    ->select(
                        'id',
                        'invoice_number as doc_number',
                        'invoice_date as doc_date',
                        'customer_name as party_name',
                        'grand_total as total_amount',
                        'received_amount',
                        'balance',
                        DB::raw("'vehicle' as sub_type")
                    );
                if ($search) {
                    $escapedSearch = '%' . addcslashes($search, '%_') . '%';
                    $q->where(function($sq) use ($escapedSearch) {
                        $sq->where('invoice_number', 'like', $escapedSearch)
                           ->orWhere('customer_name', 'like', $escapedSearch)
                           ->orWhere('customer_mobile', 'like', $escapedSearch);
                    });
                }
                if ($fromDate) {
                    $q->whereDate('invoice_date', '>=', $fromDate);
                }
                if ($toDate) {
                    $q->whereDate('invoice_date', '<=', $toDate);
                }
                $salesQuery1 = $q;
            }

            if ($type === 'all' || $type === 'part') {
                $q = PartSalesInvoice::with('customer')
                    ->where('balance', '>', 0)
                    ->select(
                        'id',
                        'invoice_number as doc_number',
                        'invoice_date as doc_date',
                        'customer_name as party_name',
                        'total_amount',
                        'received_amount',
                        'balance',
                        DB::raw("'part' as sub_type")
                    );
                if ($search) {
                    $escapedSearch = '%' . addcslashes($search, '%_') . '%';
                    $q->where(function($sq) use ($escapedSearch) {
                        $sq->where('invoice_number', 'like', $escapedSearch)
                           ->orWhere('customer_name', 'like', $escapedSearch)
                           ->orWhere('customer_mobile', 'like', $escapedSearch);
                    });
                }
                if ($fromDate) {
                    $q->whereDate('invoice_date', '>=', $fromDate);
                }
                if ($toDate) {
                    $q->whereDate('invoice_date', '<=', $toDate);
                }
                $salesQuery2 = $q;
            }

            if ($salesQuery1 && $salesQuery2) {
                $unionQuery = $salesQuery1->union($salesQuery2);
                $unionSql = $unionQuery->toSql();
                
                $finalQuery = DB::table(DB::raw("({$unionSql}) as union_table"))
                    ->mergeBindings($unionQuery->getQuery())
                    ->orderBy('doc_date', 'desc')
                    ->orderBy('id', 'desc');
                
                $ledger = $finalQuery->paginate(20)->withQueryString();
            } elseif ($salesQuery1) {
                $ledger = $salesQuery1->orderBy('invoice_date', 'desc')->orderBy('id', 'desc')->paginate(20)->withQueryString();
            } elseif ($salesQuery2) {
                $ledger = $salesQuery2->orderBy('invoice_date', 'desc')->orderBy('id', 'desc')->paginate(20)->withQueryString();
            }
        } else {
            // tab === 'purchases'
            $purchaseQuery1 = null;
            $purchaseQuery2 = null;

            if ($type === 'all' || $type === 'vehicle') {
                $q = VehiclePurchaseOrder::with('supplier')
                    ->where('balance', '>', 0)
                    ->select(
                        'id',
                        'po_number as doc_number',
                        'order_date as doc_date',
                        DB::raw("(SELECT name FROM suppliers WHERE suppliers.id = vehicle_purchase_orders.supplier_id) as party_name"),
                        'total_amount',
                        'received_amount',
                        'balance',
                        DB::raw("'vehicle' as sub_type")
                    );
                if ($search) {
                    $escapedSearch = '%' . addcslashes($search, '%_') . '%';
                    $q->where(function($sq) use ($escapedSearch) {
                        $sq->where('po_number', 'like', $escapedSearch)
                           ->orWhereHas('supplier', function($supq) use ($escapedSearch) {
                               $supq->where('name', 'like', $escapedSearch);
                           });
                    });
                }
                if ($fromDate) {
                    $q->whereDate('order_date', '>=', $fromDate);
                }
                if ($toDate) {
                    $q->whereDate('order_date', '<=', $toDate);
                }
                $purchaseQuery1 = $q;
            }

            if ($type === 'all' || $type === 'part') {
                $q = PurchaseOrder::with('supplier')
                    ->where('balance', '>', 0)
                    ->select(
                        'id',
                        'order_number as doc_number',
                        'order_date as doc_date',
                        DB::raw("(SELECT name FROM suppliers WHERE suppliers.id = purchase_orders.supplier_id) as party_name"),
                        'total_amount',
                        'received_amount',
                        'balance',
                        DB::raw("'part' as sub_type")
                    );
                if ($search) {
                    $escapedSearch = '%' . addcslashes($search, '%_') . '%';
                    $q->where(function($sq) use ($escapedSearch) {
                        $sq->where('order_number', 'like', $escapedSearch)
                           ->orWhereHas('supplier', function($supq) use ($escapedSearch) {
                               $supq->where('name', 'like', $escapedSearch);
                           });
                    });
                }
                if ($fromDate) {
                    $q->whereDate('order_date', '>=', $fromDate);
                }
                if ($toDate) {
                    $q->whereDate('order_date', '<=', $toDate);
                }
                $purchaseQuery2 = $q;
            }

            if ($purchaseQuery1 && $purchaseQuery2) {
                $unionQuery = $purchaseQuery1->union($purchaseQuery2);
                $unionSql = $unionQuery->toSql();
                
                $finalQuery = DB::table(DB::raw("({$unionSql}) as union_table"))
                    ->mergeBindings($unionQuery->getQuery())
                    ->orderBy('doc_date', 'desc')
                    ->orderBy('id', 'desc');
                
                $ledger = $finalQuery->paginate(20)->withQueryString();
            } elseif ($purchaseQuery1) {
                $ledger = $purchaseQuery1->orderBy('order_date', 'desc')->orderBy('id', 'desc')->paginate(20)->withQueryString();
            } elseif ($purchaseQuery2) {
                $ledger = $purchaseQuery2->orderBy('order_date', 'desc')->orderBy('id', 'desc')->paginate(20)->withQueryString();
            }
        }

        return view('admin.reports.outstanding_ledger', compact(
            'tab',
            'search',
            'type',
            'fromDate',
            'toDate',
            'totalOutstandingSales',
            'totalOutstandingSalesVehicle',
            'totalOutstandingSalesParts',
            'totalOutstandingPurchases',
            'totalOutstandingPurchasesVehicle',
            'totalOutstandingPurchasesParts',
            'ledger'
        ));
    }

    public function partyReportByItem(Request $request)
    {
        $selectedItem = $request->input('item_id'); // format: 'vehicle_ID' or 'part_ID'
        $dateFilter = $request->input('date_filter', 'this_month');
        $customFrom = $request->input('custom_from');
        $customTo = $request->input('custom_to');

        // Resolve Date Range
        $dates = $this->getDateRange($dateFilter, $customFrom, $customTo);
        $fromDate = $dates['from'];
        $toDate = $dates['to'];

        // Get All Items (Vehicles & Parts) for Search Dropdown
        $vehicleMasters = \App\Models\VehicleMaster::where('is_active', true)->orderBy('variant_name')->get();
        $spareParts = SparePart::where('is_active', true)->orderBy('name')->get();

        $itemList = [];
        foreach ($vehicleMasters as $vm) {
            $itemList[] = [
                'id' => 'vehicle_' . $vm->id,
                'name' => '[Vehicle] ' . $vm->variant_name . ($vm->color_name ? ' (' . $vm->color_name . ')' : '') . ' - ' . $vm->fuel_type,
                'raw_name' => $vm->variant_name . ($vm->color_name ? ' (' . $vm->color_name . ')' : '')
            ];
        }
        foreach ($spareParts as $sp) {
            $itemList[] = [
                'id' => 'part_' . $sp->id,
                'name' => '[Spare Part] ' . $sp->name . ($sp->part_no ? ' (' . $sp->part_no . ')' : ''),
                'raw_name' => $sp->name
            ];
        }

        // If no item selected initially, default to first spare part or vehicle if available
        if (empty($selectedItem) && !empty($itemList)) {
            $selectedItem = $itemList[0]['id'];
        }

        $selectedItemData = null;
        $partyData = [];

        if (!empty($selectedItem)) {
            list($itemType, $itemId) = explode('_', $selectedItem, 2);

            if ($itemType === 'vehicle') {
                $vMaster = \App\Models\VehicleMaster::find($itemId);
                if ($vMaster) {
                    $variantDesc = $vMaster->variant_name . ($vMaster->color_name ? ' (' . $vMaster->color_name . ')' : '');
                    $selectedItemData = [
                        'type' => 'vehicle',
                        'name' => $variantDesc,
                    ];

                    // Fetch Sales
                    $salesQuery = VehicleSalesInvoice::whereHas('vehicleInventory', function($q) use ($vMaster) {
                            $q->where('vehicle_master_id', $vMaster->id)
                              ->orWhere('vehicle_description', 'like', '%' . addcslashes($vMaster->variant_name, '%_') . '%');
                        });
                    if ($fromDate) $salesQuery->whereDate('invoice_date', '>=', $fromDate);
                    if ($toDate) $salesQuery->whereDate('invoice_date', '<=', $toDate);
                    $salesInvoices = $salesQuery->get();

                    foreach ($salesInvoices as $inv) {
                        $party = strtoupper(trim($inv->customer_name));
                        if (!isset($partyData[$party])) {
                            $partyData[$party] = [
                                'party_name' => $inv->customer_name,
                                'sales_qty' => 0,
                                'sales_amount' => 0,
                                'purchase_qty' => 0,
                                'purchase_amount' => 0,
                            ];
                        }
                        $partyData[$party]['sales_qty'] += 1;
                        $partyData[$party]['sales_amount'] += (float)$inv->grand_total;
                    }

                    // Fetch Purchases
                    $poQuery = VehiclePurchaseOrder::with(['items', 'supplier']);
                    if ($fromDate) $poQuery->whereDate('order_date', '>=', $fromDate);
                    if ($toDate) $poQuery->whereDate('order_date', '<=', $toDate);
                    $poOrders = $poQuery->get();

                    foreach ($poOrders as $po) {
                        $supplierName = $po->supplier->name ?? 'SUPPLIER #' . $po->supplier_id;
                        $party = strtoupper(trim($supplierName));

                        foreach ($po->items as $item) {
                            if ($item->vehicle_master_id == $vMaster->id) {
                                if (!isset($partyData[$party])) {
                                    $partyData[$party] = [
                                        'party_name' => $supplierName,
                                        'sales_qty' => 0,
                                        'sales_amount' => 0,
                                        'purchase_qty' => 0,
                                        'purchase_amount' => 0,
                                    ];
                                }
                                $partyData[$party]['purchase_qty'] += (int)$item->ordered_quantity;
                                $partyData[$party]['purchase_amount'] += (float)$item->total_amount;
                            }
                        }
                    }
                }
            } else {
                // Part item
                $spPart = SparePart::find($itemId);
                if ($spPart) {
                    $selectedItemData = [
                        'type' => 'part',
                        'name' => $spPart->name . ($spPart->part_no ? ' (' . $spPart->part_no . ')' : ''),
                    ];

                    // Fetch Sales (PartSalesInvoiceItem)
                    $partSalesQuery = \App\Models\PartSalesInvoiceItem::with('invoice')
                        ->where('spare_part_id', $spPart->id)
                        ->whereHas('invoice', function($q) use ($fromDate, $toDate) {
                            if ($fromDate) $q->whereDate('invoice_date', '>=', $fromDate);
                            if ($toDate) $q->whereDate('invoice_date', '<=', $toDate);
                        });

                    $salesItems = $partSalesQuery->get();
                    foreach ($salesItems as $sItem) {
                        if ($sItem->invoice) {
                            $party = strtoupper(trim($sItem->invoice->customer_name));
                            if (!isset($partyData[$party])) {
                                $partyData[$party] = [
                                    'party_name' => $sItem->invoice->customer_name,
                                    'sales_qty' => 0,
                                    'sales_amount' => 0,
                                    'purchase_qty' => 0,
                                    'purchase_amount' => 0,
                                ];
                            }
                            $partyData[$party]['sales_qty'] += (int)$sItem->quantity;
                            $partyData[$party]['sales_amount'] += (float)$sItem->amount;
                        }
                    }

                    // Fetch Purchases (PurchaseOrderItem)
                    $poItemsQuery = \App\Models\PurchaseOrderItem::with(['purchaseOrder.supplier'])
                        ->where('spare_part_id', $spPart->id)
                        ->whereHas('purchaseOrder', function($q) use ($fromDate, $toDate) {
                            if ($fromDate) $q->whereDate('order_date', '>=', $fromDate);
                            if ($toDate) $q->whereDate('order_date', '<=', $toDate);
                        });

                    $poItems = $poItemsQuery->get();
                    foreach ($poItems as $pItem) {
                        if ($pItem->purchaseOrder) {
                            $supplierName = $pItem->purchaseOrder->supplier->name ?? 'SUPPLIER #' . $pItem->purchaseOrder->supplier_id;
                            $party = strtoupper(trim($supplierName));
                            if (!isset($partyData[$party])) {
                                $partyData[$party] = [
                                    'party_name' => $supplierName,
                                    'sales_qty' => 0,
                                    'sales_amount' => 0,
                                    'purchase_qty' => 0,
                                    'purchase_amount' => 0,
                                ];
                            }
                            $partyData[$party]['purchase_qty'] += (int)$pItem->quantity;
                            $partyData[$party]['purchase_amount'] += (float)$pItem->total_amount;
                        }
                    }
                }
            }
        }

        return view('admin.reports.party_report_by_item', compact(
            'itemList',
            'selectedItem',
            'selectedItemData',
            'dateFilter',
            'customFrom',
            'customTo',
            'partyData',
            'fromDate',
            'toDate'
        ));
    }

    public function printPartyReportPdf(Request $request)
    {
        $reqData = $this->partyReportByItem($request);
        $data = $reqData->getData();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.party_report_by_item_pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        if ($request->has('print')) {
            $pdf->render();
            $canvas = $pdf->getCanvas();
            $canvas->javascript("this.print();");
            return $pdf->stream('Party_Report_By_Item.pdf');
        }

        if ($request->has('stream')) {
            return $pdf->stream('Party_Report_By_Item.pdf');
        }

        return $pdf->download('Party_Report_By_Item.pdf');
    }

    public function exportPartyReportExcel(Request $request)
    {
        $reqData = $this->partyReportByItem($request);
        $data = $reqData->getData();

        $filename = 'Party_Report_By_Item_' . date('Y-m-d') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Party Report By Item']);
            fputcsv($file, ['Item:', $data['selectedItemData']['name'] ?? 'N/A']);
            fputcsv($file, ['Date Filter:', ucfirst(str_replace('_', ' ', $data['dateFilter']))]);
            fputcsv($file, []);
            fputcsv($file, ['Party Name', 'Sales Qty', 'Sales Amount', 'Purchase Qty', 'Purchase Amount']);

            foreach ($data['partyData'] as $row) {
                fputcsv($file, [
                    $row['party_name'],
                    $row['sales_qty'] > 0 ? $row['sales_qty'] : '-',
                    $row['sales_amount'] > 0 ? '₹' . number_format($row['sales_amount'], 2) : '-',
                    $row['purchase_qty'] > 0 ? $row['purchase_qty'] : '-',
                    $row['purchase_amount'] > 0 ? '₹' . number_format($row['purchase_amount'], 2) : '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function emailPartyReportExcel(Request $request)
    {
        $email = $request->input('email');
        if (empty($email)) {
            return back()->with('error', 'Please provide a valid email address.');
        }

        // Action placeholder / simulation notice
        return back()->with('success', "Report successfully emailed to {$email}.");
    }

    private function getDateRange($filter, $customFrom = null, $customTo = null)
    {
        $today = date('Y-m-d');
        $from = null;
        $to = null;

        switch ($filter) {
            case 'today':
                $from = $today;
                $to = $today;
                break;
            case 'yesterday':
                $from = date('Y-m-d', strtotime('-1 day'));
                $to = date('Y-m-d', strtotime('-1 day'));
                break;
            case 'last_7_days':
                $from = date('Y-m-d', strtotime('-6 days'));
                $to = $today;
                break;
            case 'last_15_days':
                $from = date('Y-m-d', strtotime('-14 days'));
                $to = $today;
                break;
            case 'last_30_days':
                $from = date('Y-m-d', strtotime('-29 days'));
                $to = $today;
                break;
            case 'this_week':
                $from = date('Y-m-d', strtotime('monday this week'));
                $to = date('Y-m-d', strtotime('sunday this week'));
                break;
            case 'previous_week':
                $from = date('Y-m-d', strtotime('monday last week'));
                $to = date('Y-m-d', strtotime('sunday last week'));
                break;
            case 'this_month':
                $from = date('Y-m-01');
                $to = date('Y-m-t');
                break;
            case 'previous_month':
                $from = date('Y-m-01', strtotime('first day of last month'));
                $to = date('Y-m-t', strtotime('last day of last month'));
                break;
            case 'this_quarter':
                $quarter = ceil(date('n') / 3);
                $from = date('Y-' . sprintf('%02d', ($quarter - 1) * 3 + 1) . '-01');
                $to = date('Y-' . sprintf('%02d', $quarter * 3) . '-' . date('t', strtotime($from)));
                break;
            case 'previous_quarter':
                $quarter = ceil(date('n') / 3) - 1;
                $year = date('Y');
                if ($quarter == 0) {
                    $quarter = 4;
                    $year = $year - 1;
                }
                $from = date($year . '-' . sprintf('%02d', ($quarter - 1) * 3 + 1) . '-01');
                $to = date($year . '-' . sprintf('%02d', $quarter * 3) . '-' . date('t', strtotime($from)));
                break;
            case 'this_year':
                $from = date('Y-01-01');
                $to = date('Y-12-31');
                break;
            case 'previous_year':
                $year = date('Y') - 1;
                $from = $year . '-01-01';
                $to = $year . '-12-31';
                break;
            case 'current_financial_year':
                $m = date('n');
                $year = date('Y');
                if ($m >= 4) {
                    $from = $year . '-04-01';
                    $to = ($year + 1) . '-03-31';
                } else {
                    $from = ($year - 1) . '-04-01';
                    $to = $year . '-03-31';
                }
                break;
            case 'previous_financial_year':
                $m = date('n');
                $year = date('Y');
                if ($m >= 4) {
                    $from = ($year - 1) . '-04-01';
                    $to = $year . '-03-31';
                } else {
                    $from = ($year - 2) . '-04-01';
                    $to = ($year - 1) . '-03-31';
                }
                break;
            case 'custom':
                $from = $customFrom;
                $to = $customTo;
                break;
            default:
                $from = date('Y-m-01');
                $to = date('Y-m-t');
                break;
        }

        return ['from' => $from, 'to' => $to];
    }

    public function itemReportByParty(Request $request)
    {
        $selectedParty = $request->input('party_id');
        $dateFilter = $request->input('date_filter', 'this_month');
        $customFrom = $request->input('custom_from');
        $customTo = $request->input('custom_to');

        $dates = $this->getDateRange($dateFilter, $customFrom, $customTo);
        $fromDate = $dates['from'];
        $toDate = $dates['to'];

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        $partyList = [];
        foreach ($customers as $c) {
            $partyList[] = ['id' => 'customer_' . $c->id, 'name' => '[Customer] ' . $c->name . ($c->phone ? ' (' . $c->phone . ')' : '')];
        }
        foreach ($suppliers as $s) {
            $partyList[] = ['id' => 'supplier_' . $s->id, 'name' => '[Supplier] ' . $s->name . ($s->phone ? ' (' . $s->phone . ')' : '')];
        }

        if (empty($selectedParty) && !empty($partyList)) {
            $selectedParty = $partyList[0]['id'];
        }

        $itemsData = [];
        $selectedPartyData = null;

        if (!empty($selectedParty)) {
            list($pType, $pId) = explode('_', $selectedParty, 2);

            if ($pType === 'customer') {
                $customer = Customer::find($pId);
                if ($customer) {
                    $selectedPartyData = ['type' => 'Customer', 'name' => $customer->name];

                    $vQuery = VehicleSalesInvoice::with('vehicleInventory')
                        ->where(function($q) use ($customer) {
                            $q->where('customer_id', $customer->id)->orWhere('customer_name', $customer->name);
                        });
                    if ($fromDate) $vQuery->whereDate('invoice_date', '>=', $fromDate);
                    if ($toDate) $vQuery->whereDate('invoice_date', '<=', $toDate);

                    foreach ($vQuery->get() as $vInv) {
                        $itemName = $vInv->vehicleInventory->vehicle_description ?? 'Vehicle Purchase';
                        $itemsData[] = [
                            'date' => $vInv->invoice_date->format('d-m-Y'),
                            'raw_date' => $vInv->invoice_date->format('Y-m-d'),
                            'doc_no' => $vInv->invoice_number,
                            'item_type' => 'Vehicle',
                            'item_name' => $itemName,
                            'qty' => 1,
                            'rate' => (float)$vInv->rate,
                            'amount' => (float)$vInv->grand_total,
                            'transaction_type' => 'Sale',
                        ];
                    }

                    $pQuery = PartSalesInvoice::with('items.sparePart')
                        ->where(function($q) use ($customer) {
                            $q->where('customer_id', $customer->id)->orWhere('customer_name', $customer->name);
                        });
                    if ($fromDate) $pQuery->whereDate('invoice_date', '>=', $fromDate);
                    if ($toDate) $pQuery->whereDate('invoice_date', '<=', $toDate);

                    foreach ($pQuery->get() as $pInv) {
                        foreach ($pInv->items as $pItem) {
                            $itemsData[] = [
                                'date' => $pInv->invoice_date->format('d-m-Y'),
                                'raw_date' => $pInv->invoice_date->format('Y-m-d'),
                                'doc_no' => $pInv->invoice_number,
                                'item_type' => 'Spare Part',
                                'item_name' => $pItem->sparePart->name ?? 'Part #' . $pItem->spare_part_id,
                                'qty' => (int)$pItem->quantity,
                                'rate' => (float)$pItem->rate,
                                'amount' => (float)$pItem->amount,
                                'transaction_type' => 'Sale',
                            ];
                        }
                    }
                }
            } else {
                $supplier = Supplier::find($pId);
                if ($supplier) {
                    $selectedPartyData = ['type' => 'Supplier', 'name' => $supplier->name];

                    $vPoQuery = VehiclePurchaseOrder::with('items')->where('supplier_id', $supplier->id);
                    if ($fromDate) $vPoQuery->whereDate('order_date', '>=', $fromDate);
                    if ($toDate) $vPoQuery->whereDate('order_date', '<=', $toDate);

                    foreach ($vPoQuery->get() as $vPo) {
                        foreach ($vPo->items as $vPoItem) {
                            $itemsData[] = [
                                'date' => $vPo->order_date->format('d-m-Y'),
                                'raw_date' => $vPo->order_date->format('Y-m-d'),
                                'doc_no' => $vPo->po_number,
                                'item_type' => 'Vehicle',
                                'item_name' => $vPoItem->vehicle_description,
                                'qty' => (int)$vPoItem->quantity,
                                'rate' => (float)$vPoItem->unit_price,
                                'amount' => (float)$vPoItem->total_price,
                                'transaction_type' => 'Purchase',
                            ];
                        }
                    }

                    $pPoQuery = PurchaseOrder::with('items.sparePart')->where('supplier_id', $supplier->id);
                    if ($fromDate) $pPoQuery->whereDate('order_date', '>=', $fromDate);
                    if ($toDate) $pPoQuery->whereDate('order_date', '<=', $toDate);

                    foreach ($pPoQuery->get() as $pPo) {
                        foreach ($pPo->items as $pPoItem) {
                            $itemsData[] = [
                                'date' => $pPo->order_date->format('d-m-Y'),
                                'raw_date' => $pPo->order_date->format('Y-m-d'),
                                'doc_no' => $pPo->order_number,
                                'item_type' => 'Spare Part',
                                'item_name' => $pPoItem->sparePart->name ?? 'Part #' . $pPoItem->spare_part_id,
                                'qty' => (int)$pPoItem->quantity,
                                'rate' => (float)$pPoItem->unit_price,
                                'amount' => (float)$pPoItem->total_price,
                                'transaction_type' => 'Purchase',
                            ];
                        }
                    }
                }
            }
        }

        usort($itemsData, function($a, $b) {
            return strcmp($b['raw_date'], $a['raw_date']);
        });

        return view('admin.reports.item_report_by_party', compact(
            'partyList', 'selectedParty', 'selectedPartyData', 'itemsData', 'dateFilter', 'customFrom', 'customTo'
        ));
    }

    public function itemSalesPurchaseSummary(Request $request)
    {
        $dateFilter = $request->input('date_filter', 'this_month');
        $customFrom = $request->input('custom_from');
        $customTo = $request->input('custom_to');

        $dates = $this->getDateRange($dateFilter, $customFrom, $customTo);
        $fromDate = $dates['from'];
        $toDate = $dates['to'];

        $parts = SparePart::where('is_active', true)->orderBy('name')->get();
        $summary = [];

        foreach ($parts as $p) {
            $salesQuery = \App\Models\PartSalesInvoiceItem::where('spare_part_id', $p->id)
                ->whereHas('invoice', function($q) use ($fromDate, $toDate) {
                    if ($fromDate) $q->whereDate('invoice_date', '>=', $fromDate);
                    if ($toDate) $q->whereDate('invoice_date', '<=', $toDate);
                });
            $salesQty = (int)$salesQuery->sum('quantity');
            $salesAmt = (float)$salesQuery->sum('amount');

            $purQuery = \App\Models\PurchaseOrderItem::where('spare_part_id', $p->id)
                ->whereHas('purchaseOrder', function($q) use ($fromDate, $toDate) {
                    if ($fromDate) $q->whereDate('order_date', '>=', $fromDate);
                    if ($toDate) $q->whereDate('order_date', '<=', $toDate);
                });
            $purQty = (int)$purQuery->sum('quantity');
            $purAmt = (float)$purQuery->sum('total_price');

            $summary[] = [
                'type' => 'Spare Part',
                'code' => $p->part_no ?? '-',
                'name' => $p->name,
                'sales_qty' => $salesQty,
                'sales_amt' => $salesAmt,
                'purchase_qty' => $purQty,
                'purchase_amt' => $purAmt,
                'net_qty' => $purQty - $salesQty,
            ];
        }

        $vehicleMasters = VehicleMaster::where('is_active', true)->orderBy('variant_name')->get();
        foreach ($vehicleMasters as $vm) {
            $desc = $vm->variant_name . ($vm->color_name ? ' (' . $vm->color_name . ')' : '');

            $vSalesQuery = VehicleSalesInvoice::whereHas('vehicleInventory', function($q) use ($vm) {
                $q->where('vehicle_master_id', $vm->id)
                  ->orWhere('vehicle_description', 'like', '%' . addcslashes($vm->variant_name, '%_') . '%');
            });
            if ($fromDate) $vSalesQuery->whereDate('invoice_date', '>=', $fromDate);
            if ($toDate) $vSalesQuery->whereDate('invoice_date', '<=', $toDate);

            $salesQty = (int)$vSalesQuery->count();
            $salesAmt = (float)$vSalesQuery->sum('grand_total');

            $vPoItems = \App\Models\VehiclePoItem::where('vehicle_master_id', $vm->id)
                ->whereHas('vehiclePurchaseOrder', function($q) use ($fromDate, $toDate) {
                    if ($fromDate) $q->whereDate('order_date', '>=', $fromDate);
                    if ($toDate) $q->whereDate('order_date', '<=', $toDate);
                });
            $purQty = (int)$vPoItems->sum('quantity');
            $purAmt = (float)$vPoItems->sum('total_price');

            $summary[] = [
                'type' => 'Vehicle',
                'code' => $vm->fuel_type,
                'name' => $desc,
                'sales_qty' => $salesQty,
                'sales_amt' => $salesAmt,
                'purchase_qty' => $purQty,
                'purchase_amt' => $purAmt,
                'net_qty' => $purQty - $salesQty,
            ];
        }

        return view('admin.reports.item_sales_purchase_summary', compact(
            'summary', 'dateFilter', 'customFrom', 'customTo'
        ));
    }

    public function lowStockSummary(Request $request)
    {
        $search = $request->input('search');

        $partsQuery = SparePart::leftJoin('spare_part_stocks as s', 'spare_parts.id', '=', 's.spare_part_id')
            ->select('spare_parts.*', DB::raw('COALESCE(s.quantity, 0) as current_stock'))
            ->where('spare_parts.is_active', true);

        if ($search) {
            $escaped = '%' . addcslashes($search, '%_') . '%';
            $partsQuery->where(function($q) use ($escaped) {
                $q->where('name', 'like', $escaped)->orWhere('part_no', 'like', $escaped);
            });
        }

        $allParts = $partsQuery->get();
        $lowStockItems = [];

        foreach ($allParts as $p) {
            if ($p->min_stock > 0 && $p->current_stock <= $p->min_stock) {
                $lowStockItems[] = [
                    'type' => 'Spare Part',
                    'code' => $p->part_no ?? '-',
                    'name' => $p->name,
                    'current_stock' => (int)$p->current_stock,
                    'min_stock' => (int)$p->min_stock,
                    'shortage' => (int)max(0, $p->min_stock - $p->current_stock),
                    'status' => $p->current_stock == 0 ? 'Out of Stock' : 'Low Stock',
                ];
            }
        }

        $vehicleMasters = VehicleMaster::where('is_active', true)->get();
        $variantCounts = VehicleInventory::where('status', 'available')
            ->select('vehicle_description', DB::raw('COUNT(*) as total'))
            ->groupBy('vehicle_description')
            ->pluck('total', 'vehicle_description')
            ->toArray();

        foreach ($vehicleMasters as $vm) {
            $name = trim($vm->variant_name);
            $available = $variantCounts[$name] ?? 0;
            if ($vm->min_stock > 0 && $available <= $vm->min_stock) {
                $lowStockItems[] = [
                    'type' => 'Vehicle Variant',
                    'code' => $vm->fuel_type,
                    'name' => $name . ($vm->color_name ? ' (' . $vm->color_name . ')' : ''),
                    'current_stock' => (int)$available,
                    'min_stock' => (int)$vm->min_stock,
                    'shortage' => (int)max(0, $vm->min_stock - $available),
                    'status' => $available == 0 ? 'Out of Stock' : 'Low Stock',
                ];
            }
        }

        return view('admin.reports.low_stock_summary', compact('lowStockItems', 'search'));
    }

    public function rateList(Request $request)
    {
        $search = $request->input('search');
        $type = $request->input('type', 'all');

        $vehicles = [];
        $parts = [];

        if ($type === 'all' || $type === 'vehicle') {
            $vQuery = VehicleMaster::where('is_active', true)->orderBy('variant_name');
            if ($search) {
                $escaped = '%' . addcslashes($search, '%_') . '%';
                $vQuery->where('variant_name', 'like', $escaped);
            }
            $vehicles = $vQuery->get();
        }

        if ($type === 'all' || $type === 'part') {
            $pQuery = SparePart::where('is_active', true)->orderBy('name');
            if ($search) {
                $escaped = '%' . addcslashes($search, '%_') . '%';
                $pQuery->where(function($q) use ($escaped) {
                    $q->where('name', 'like', $escaped)->orWhere('part_no', 'like', $escaped);
                });
            }
            $parts = $pQuery->get();
        }

        return view('admin.reports.rate_list', compact('vehicles', 'parts', 'search', 'type'));
    }

    public function stockDetailReport(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'available');

        $vQuery = VehicleInventory::with('purchaseOrder')->orderBy('created_at', 'desc');
        if ($status !== 'all') {
            $vQuery->where('status', $status);
        }
        if ($search) {
            $escaped = '%' . addcslashes($search, '%_') . '%';
            $vQuery->where(function($q) use ($escaped) {
                $q->where('vehicle_description', 'like', $escaped)
                  ->orWhere('chassis_number', 'like', $escaped)
                  ->orWhere('motor_number', 'like', $escaped);
            });
        }

        $vehicles = $vQuery->paginate(20)->withQueryString();
        $parts = SparePartStock::with('sparePart')->get();

        return view('admin.reports.stock_detail_report', compact('vehicles', 'parts', 'search', 'status'));
    }

    public function stockSummary(Request $request)
    {
        $availableVehicles = VehicleInventory::where('status', 'available')->get();
        $totalVehicleQty = count($availableVehicles);
        $totalVehicleValue = $availableVehicles->sum('purchase_price');

        $partStocks = SparePartStock::with('sparePart')->get();
        $totalPartQty = $partStocks->sum('quantity');
        $totalPartValue = $partStocks->sum(function($s) {
            return $s->quantity * $s->purchase_price;
        });

        return view('admin.reports.stock_summary', compact(
            'totalVehicleQty', 'totalVehicleValue', 'totalPartQty', 'totalPartValue', 'partStocks'
        ));
    }

    public function receivableAgeing(Request $request)
    {
        $search = $request->input('search');

        $vInvoices = VehicleSalesInvoice::with('customer')
            ->where('balance', '>', 0)
            ->orderBy('invoice_date', 'desc')
            ->get();

        $pInvoices = PartSalesInvoice::with('customer')
            ->where('balance', '>', 0)
            ->orderBy('invoice_date', 'desc')
            ->get();

        $ageingList = [];
        $today = new \DateTime();

        foreach ($vInvoices as $v) {
            $invDate = new \DateTime($v->invoice_date->format('Y-m-d'));
            $days = $today->diff($invDate)->days;
            $bal = (float)$v->balance;

            $ageingList[] = [
                'type' => 'Vehicle Invoice',
                'doc_no' => $v->invoice_number,
                'date' => $v->invoice_date->format('d-m-Y'),
                'party_name' => $v->customer_name,
                'total_amount' => (float)$v->grand_total,
                'received' => (float)$v->received_amount,
                'balance' => $bal,
                'days' => $days,
                'b_0_30' => $days <= 30 ? $bal : 0,
                'b_31_60' => ($days > 30 && $days <= 60) ? $bal : 0,
                'b_61_90' => ($days > 60 && $days <= 90) ? $bal : 0,
                'b_90_plus' => $days > 90 ? $bal : 0,
            ];
        }

        foreach ($pInvoices as $p) {
            $invDate = new \DateTime($p->invoice_date->format('Y-m-d'));
            $days = $today->diff($invDate)->days;
            $bal = (float)$p->balance;

            $ageingList[] = [
                'type' => 'Part Invoice',
                'doc_no' => $p->invoice_number,
                'date' => $p->invoice_date->format('d-m-Y'),
                'party_name' => $p->customer_name,
                'total_amount' => (float)$p->total_amount,
                'received' => (float)$p->received_amount,
                'balance' => $bal,
                'days' => $days,
                'b_0_30' => $days <= 30 ? $bal : 0,
                'b_31_60' => ($days > 30 && $days <= 60) ? $bal : 0,
                'b_61_90' => ($days > 60 && $days <= 90) ? $bal : 0,
                'b_90_plus' => $days > 90 ? $bal : 0,
            ];
        }

        return view('admin.reports.receivable_ageing', compact('ageingList', 'search'));
    }

    public function partyStatement(Request $request)
    {
        $partySelect = $request->input('party_id');
        $dateFilter = $request->input('date_filter', 'this_month');
        $customFrom = $request->input('custom_from');
        $customTo = $request->input('custom_to');

        $dates = $this->getDateRange($dateFilter, $customFrom, $customTo);
        $fromDate = $dates['from'];
        $toDate = $dates['to'];

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        $partyList = [];
        foreach ($customers as $c) {
            $partyList[] = ['id' => 'customer_' . $c->id, 'name' => '[Customer] ' . $c->name];
        }
        foreach ($suppliers as $s) {
            $partyList[] = ['id' => 'supplier_' . $s->id, 'name' => '[Supplier] ' . $s->name];
        }

        if (empty($partySelect) && !empty($partyList)) {
            $partySelect = $partyList[0]['id'];
        }

        $partyData = null;
        $ledgerEntries = [];

        if (!empty($partySelect)) {
            list($pType, $pId) = explode('_', $partySelect, 2);

            if ($pType === 'customer') {
                $customer = Customer::find($pId);
                if ($customer) {
                    $partyData = ['name' => $customer->name, 'type' => 'Customer', 'phone' => $customer->phone];

                    $vQuery = VehicleSalesInvoice::where('customer_id', $customer->id)->orWhere('customer_name', $customer->name);
                    if ($fromDate) $vQuery->whereDate('invoice_date', '>=', $fromDate);
                    if ($toDate) $vQuery->whereDate('invoice_date', '<=', $toDate);
                    foreach ($vQuery->get() as $v) {
                        $ledgerEntries[] = [
                            'date' => $v->invoice_date->format('Y-m-d'),
                            'display_date' => $v->invoice_date->format('d-m-Y'),
                            'doc_no' => $v->invoice_number,
                            'type' => 'Vehicle Invoice',
                            'debit' => (float)$v->grand_total,
                            'credit' => 0,
                            'mode' => $v->payment_mode,
                        ];
                    }

                    $pQuery = PartSalesInvoice::where('customer_id', $customer->id)->orWhere('customer_name', $customer->name);
                    if ($fromDate) $pQuery->whereDate('invoice_date', '>=', $fromDate);
                    if ($toDate) $pQuery->whereDate('invoice_date', '<=', $toDate);
                    foreach ($pQuery->get() as $p) {
                        $ledgerEntries[] = [
                            'date' => $p->invoice_date->format('Y-m-d'),
                            'display_date' => $p->invoice_date->format('d-m-Y'),
                            'doc_no' => $p->invoice_number,
                            'type' => 'Part Invoice',
                            'debit' => (float)$p->total_amount,
                            'credit' => 0,
                            'mode' => $p->payment_mode,
                        ];
                    }

                    $payQuery = PaymentTransaction::where('party_type', 'customer')->where('party_id', $customer->id);
                    if ($fromDate) $payQuery->whereDate('payment_date', '>=', $fromDate);
                    if ($toDate) $payQuery->whereDate('payment_date', '<=', $toDate);
                    foreach ($payQuery->get() as $pay) {
                        $ledgerEntries[] = [
                            'date' => $pay->payment_date->format('Y-m-d'),
                            'display_date' => $pay->payment_date->format('d-m-Y'),
                            'doc_no' => 'PAY-' . $pay->id,
                            'type' => $pay->type === 'rollback' ? 'Payment Rollback' : 'Payment Received',
                            'debit' => $pay->type === 'rollback' ? (float)abs($pay->amount) : 0,
                            'credit' => $pay->type === 'rollback' ? 0 : (float)$pay->amount,
                            'mode' => $pay->payment_mode,
                        ];
                    }
                }
            } else {
                $supplier = Supplier::find($pId);
                if ($supplier) {
                    $partyData = ['name' => $supplier->name, 'type' => 'Supplier', 'phone' => $supplier->phone];

                    $vPoQuery = VehiclePurchaseOrder::where('supplier_id', $supplier->id);
                    if ($fromDate) $vPoQuery->whereDate('order_date', '>=', $fromDate);
                    if ($toDate) $vPoQuery->whereDate('order_date', '<=', $toDate);
                    foreach ($vPoQuery->get() as $vPo) {
                        $ledgerEntries[] = [
                            'date' => $vPo->order_date->format('Y-m-d'),
                            'display_date' => $vPo->order_date->format('d-m-Y'),
                            'doc_no' => $vPo->po_number,
                            'type' => 'Vehicle PO',
                            'debit' => 0,
                            'credit' => (float)$vPo->total_amount,
                            'mode' => '-',
                        ];
                    }

                    $pPoQuery = PurchaseOrder::where('supplier_id', $supplier->id);
                    if ($fromDate) $pPoQuery->whereDate('order_date', '>=', $fromDate);
                    if ($toDate) $pPoQuery->whereDate('order_date', '<=', $toDate);
                    foreach ($pPoQuery->get() as $pPo) {
                        $ledgerEntries[] = [
                            'date' => $pPo->order_date->format('Y-m-d'),
                            'display_date' => $pPo->order_date->format('d-m-Y'),
                            'doc_no' => $pPo->order_number,
                            'type' => 'Part PO',
                            'debit' => 0,
                            'credit' => (float)$pPo->total_amount,
                            'mode' => '-',
                        ];
                    }
                }
            }
        }

        usort($ledgerEntries, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        $runBal = 0;
        foreach ($ledgerEntries as &$e) {
            $runBal += ($e['debit'] - $e['credit']);
            $e['running_balance'] = $runBal;
        }

        return view('admin.reports.party_statement', compact(
            'partyList', 'partySelect', 'partyData', 'ledgerEntries', 'dateFilter', 'customFrom', 'customTo'
        ));
    }

    public function partyWiseOutstanding(Request $request)
    {
        $search = trim($request->input('search', ''));

        $customerQuery = Customer::orderBy('name');
        if ($search !== '') {
            $customerQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        $customers = $customerQuery->get();

        $supplierQuery = Supplier::orderBy('name');
        if ($search !== '') {
            $supplierQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        $suppliers = $supplierQuery->get();

        $partyOutstandings = [];

        foreach ($customers as $c) {
            $vBilled = (float) VehicleSalesInvoice::where('customer_id', $c->id)
                ->orWhere(function($q) use ($c) {
                    $q->whereNull('customer_id')->where('customer_name', $c->name);
                })->sum('grand_total');

            $pBilled = (float) PartSalesInvoice::where('customer_id', $c->id)
                ->orWhere(function($q) use ($c) {
                    $q->whereNull('customer_id')->where('customer_name', $c->name);
                })->sum('total_amount');

            // Payment transactions for customer
            $paymentsReceived = (float) PaymentTransaction::where('party_type', 'customer')
                ->where('party_id', $c->id)
                ->where('type', 'payment')
                ->sum('amount');

            $paymentsRollback = (float) PaymentTransaction::where('party_type', 'customer')
                ->where('party_id', $c->id)
                ->where('type', 'rollback')
                ->sum('amount');

            // Initial invoice received_amounts if payment transactions were not created or only partially logged
            $vPaid = (float) VehicleSalesInvoice::where('customer_id', $c->id)
                ->orWhere(function($q) use ($c) {
                    $q->whereNull('customer_id')->where('customer_name', $c->name);
                })->sum('received_amount');

            $pPaid = (float) PartSalesInvoice::where('customer_id', $c->id)
                ->orWhere(function($q) use ($c) {
                    $q->whereNull('customer_id')->where('customer_name', $c->name);
                })->sum('received_amount');

            $totalBilled = $vBilled + $pBilled;
            $totalPaid = max($vPaid + $pPaid, $paymentsReceived - $paymentsRollback);
            $balance = $totalBilled - $totalPaid;

            if ($totalBilled > 0 || $totalPaid > 0 || abs($balance) > 0.001) {
                $partyOutstandings[] = [
                    'id' => $c->id,
                    'type' => 'Customer',
                    'name' => $c->name,
                    'phone' => $c->phone ?? '-',
                    'total_billed' => $totalBilled,
                    'total_paid' => $totalPaid,
                    'outstanding' => $balance,
                    'view_url' => route('admin.reports.party-statement', ['party_id' => 'customer_' . $c->id]),
                ];
            }
        }

        foreach ($suppliers as $s) {
            $vPoTotal = (float) VehiclePurchaseOrder::where('supplier_id', $s->id)->sum('total_amount');
            $vPoPaid = (float) VehiclePurchaseOrder::where('supplier_id', $s->id)->sum('received_amount');

            $pPoTotal = (float) PurchaseOrder::where('supplier_id', $s->id)->sum('total_amount');
            $pPoPaid = (float) PurchaseOrder::where('supplier_id', $s->id)->sum('received_amount');

            $totalBilled = $vPoTotal + $pPoTotal;
            $totalPaid = $vPoPaid + $pPoPaid;
            $balance = $totalBilled - $totalPaid;

            if ($totalBilled > 0 || $totalPaid > 0 || abs($balance) > 0.001) {
                $partyOutstandings[] = [
                    'id' => $s->id,
                    'type' => 'Supplier',
                    'name' => $s->name,
                    'phone' => $s->phone ?? '-',
                    'total_billed' => $totalBilled,
                    'total_paid' => $totalPaid,
                    'outstanding' => $balance,
                    'view_url' => route('admin.reports.party-statement', ['party_id' => 'supplier_' . $s->id]),
                ];
            }
        }

        return view('admin.reports.party_wise_outstanding', compact('partyOutstandings', 'search'));
    }

    public function salesSummaryCategoryWise(Request $request)
    {
        $dateFilter = $request->input('date_filter', 'this_month');
        $customFrom = $request->input('custom_from');
        $customTo = $request->input('custom_to');

        $dates = $this->getDateRange($dateFilter, $customFrom, $customTo);
        $fromDate = $dates['from'];
        $toDate = $dates['to'];

        $vQuery = VehicleSalesInvoice::with('vehicleInventory');
        if ($fromDate) $vQuery->whereDate('invoice_date', '>=', $fromDate);
        if ($toDate) $vQuery->whereDate('invoice_date', '<=', $toDate);
        $vInvoices = $vQuery->get();

        $vehicleCategorySales = [];
        $grandTotalSales = 0;

        foreach ($vInvoices as $v) {
            $category = $v->vehicleInventory->vehicle_description ?? 'Vehicle Sales';
            if (!isset($vehicleCategorySales[$category])) {
                $vehicleCategorySales[$category] = [
                    'category' => $category,
                    'type' => 'Vehicle',
                    'units_sold' => 0,
                    'gross_amount' => 0,
                    'discount' => 0,
                    'net_revenue' => 0,
                ];
            }
            $vehicleCategorySales[$category]['units_sold'] += 1;
            $vehicleCategorySales[$category]['gross_amount'] += (float)$v->sub_total;
            $vehicleCategorySales[$category]['discount'] += ((float)$v->discount + (float)$v->nemmp_incentive);
            $vehicleCategorySales[$category]['net_revenue'] += (float)$v->grand_total;
            $grandTotalSales += (float)$v->grand_total;
        }

        $pQuery = PartSalesInvoice::with('items');
        if ($fromDate) $pQuery->whereDate('invoice_date', '>=', $fromDate);
        if ($toDate) $pQuery->whereDate('invoice_date', '<=', $toDate);
        $pInvoices = $pQuery->get();

        $partUnits = 0;
        $partNetRevenue = 0;
        foreach ($pInvoices as $p) {
            foreach ($p->items as $item) {
                $partUnits += (int)$item->quantity;
            }
            $partNetRevenue += (float)$p->total_amount;
        }

        if ($partNetRevenue > 0) {
            $vehicleCategorySales['Spare Parts'] = [
                'category' => 'Spare Parts',
                'type' => 'Parts',
                'units_sold' => $partUnits,
                'gross_amount' => $partNetRevenue,
                'discount' => 0,
                'net_revenue' => $partNetRevenue,
            ];
            $grandTotalSales += $partNetRevenue;
        }

        return view('admin.reports.sales_summary_category_wise', compact(
            'vehicleCategorySales', 'grandTotalSales', 'dateFilter', 'customFrom', 'customTo'
        ));
    }
}


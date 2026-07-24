<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleInventory;
use App\Models\SparePart;
use App\Models\SparePartStockTransaction;
use App\Models\VehicleSalesInvoice;
use App\Models\PartSalesInvoice;
use App\Models\VehiclePurchaseOrder;
use App\Models\PurchaseOrder;
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
                'spare_parts.unit',
                'spare_parts.min_stock',
                DB::raw('COALESCE(SUM(CASE WHEN t.transaction_type = "in" THEN t.quantity ELSE 0 END), 0) as total_in'),
                DB::raw('COALESCE(SUM(CASE WHEN t.transaction_type = "out" THEN t.quantity ELSE 0 END), 0) as total_out'),
                DB::raw('COALESCE(MAX(s.quantity), 0) as remaining')
            )
            ->groupBy('spare_parts.id', 'spare_parts.part_no', 'spare_parts.name', 'spare_parts.unit', 'spare_parts.min_stock');

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
        return $pdf->stream('Party_Report_By_Item.pdf');
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
}

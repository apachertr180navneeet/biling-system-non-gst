<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehiclePurchaseOrder;
use App\Models\VehicleInventory;
use App\Models\VehiclePoItem;
use App\Models\Supplier;
use App\Models\VehicleMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Barryvdh\DomPDF\Facade\Pdf;

class VehiclePurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = VehiclePurchaseOrder::with('supplier')->withCount('items')->orderBy('created_at', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('po_number', 'like', $escapedSearch)
                  ->orWhereHas('supplier', function($sq) use ($escapedSearch) {
                      $sq->where('name', 'like', $escapedSearch);
                  });
            });
        }

        $orders = $query->paginate(20);
        return view('admin.vehicle_purchase_orders.index', compact('orders', 'search'));
    }

    public function outstanding(Request $request)
    {
        $search = $request->input('search');
        $query = VehiclePurchaseOrder::with('supplier', 'items')
            ->where('balance', '>', 0)
            ->orderBy('created_at', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('po_number', 'like', $escapedSearch)
                  ->orWhereHas('supplier', function($sq) use ($escapedSearch) {
                      $sq->where('name', 'like', $escapedSearch);
                  });
            });
        }

        $orders = $query->paginate(20);
        return view('admin.vehicle_purchase_orders.outstanding', compact('orders', 'search'));
    }

    public function exportOutstanding(Request $request)
    {
        $search = $request->input('search');
        $query = VehiclePurchaseOrder::with('supplier', 'items')
            ->where('balance', '>', 0)
            ->orderBy('created_at', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('po_number', 'like', $escapedSearch)
                  ->orWhereHas('supplier', function($sq) use ($escapedSearch) {
                      $sq->where('name', 'like', $escapedSearch);
                  });
            });
        }

        $orders = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'PO No');
        $sheet->setCellValue('B1', 'Supplier');
        $sheet->setCellValue('C1', 'Order Date');
        $sheet->setCellValue('D1', 'Vehicle');
        $sheet->setCellValue('E1', 'Ordered Qty');
        $sheet->setCellValue('F1', 'Received Qty');
        $sheet->setCellValue('G1', 'Outstanding Qty');
        $sheet->setCellValue('H1', 'Unit Price');
        $sheet->setCellValue('I1', 'Outstanding Amount');
        $sheet->setCellValue('J1', 'Status');

        $row = 2;
        foreach ($orders as $o) {
            foreach ($o->items as $item) {
                $outstandingQty = $item->quantity - $item->received_quantity;
                if ($outstandingQty <= 0) continue;
                $sheet->setCellValue('A' . $row, $o->po_number);
                $sheet->setCellValue('B' . $row, $o->supplier->name ?? '-');
                $sheet->setCellValue('C' . $row, $o->order_date->format('d-m-Y'));
                $sheet->setCellValue('D' . $row, $item->vehicle_description);
                $sheet->setCellValue('E' . $row, $item->quantity);
                $sheet->setCellValue('F' . $row, $item->received_quantity);
                $sheet->setCellValue('G' . $row, $outstandingQty);
                $sheet->setCellValue('H' . $row, $item->unit_price);
                $sheet->setCellValue('I' . $row, $outstandingQty * $item->unit_price);
                $sheet->setCellValue('J' . $row, ucfirst($o->status));
                $row++;
            }
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/vehicle_purchase_orders_outstanding_export.xls');
        $writer->save($path);

        return response()->download($path, 'vehicle_purchase_orders_outstanding_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $query = VehiclePurchaseOrder::with('supplier')->withCount('items')->orderBy('created_at', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('po_number', 'like', $escapedSearch)
                  ->orWhereHas('supplier', function($sq) use ($escapedSearch) {
                      $sq->where('name', 'like', $escapedSearch);
                  });
            });
        }

        $orders = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'PO No');
        $sheet->setCellValue('B1', 'Supplier');
        $sheet->setCellValue('C1', 'Order Date');
        $sheet->setCellValue('D1', 'Items');
        $sheet->setCellValue('E1', 'Total Amount');
        $sheet->setCellValue('F1', 'Status');
        $sheet->setCellValue('G1', 'Active');

        $row = 2;
        foreach ($orders as $o) {
            $sheet->setCellValue('A' . $row, $o->po_number);
            $sheet->setCellValue('B' . $row, $o->supplier->name ?? '-');
            $sheet->setCellValue('C' . $row, $o->order_date->format('d-m-Y'));
            $sheet->setCellValue('D' . $row, $o->items_count);
            $sheet->setCellValue('E' . $row, $o->total_amount);
            $sheet->setCellValue('F' . $row, ucfirst($o->status));
            $sheet->setCellValue('G' . $row, $o->is_active ? 'Active' : 'Inactive');
            $row++;
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/vehicle_purchase_orders_export.xls');
        $writer->save($path);

        return response()->download($path, 'vehicle_purchase_orders_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $vehicleData = $this->getVehicleOptions();
        $vehicleList = $vehicleData['list'];
        $vehiclePrices = $vehicleData['prices'];
        $colorOptions = VehicleInventory::whereNotNull('color_name')->where('color_name', '!=', '')->distinct()->pluck('color_name')->toArray();
        if (empty($colorOptions)) {
            $colorOptions = ['Black', 'Red', 'Blue', 'White', 'Silver', 'Grey', 'Green'];
        }
        return view('admin.vehicle_purchase_orders.create', compact('suppliers', 'vehicleList', 'vehiclePrices', 'colorOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.vehicle_description' => 'required|string|max:255',
            'items.*.color_name' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $data['status'] = 'pending';

        $total = 0;
        $items = [];
        foreach ($data['items'] as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $total += $lineTotal;

            $items[] = new VehiclePoItem([
                'vehicle_description' => $item['vehicle_description'],
                'color_name' => $item['color_name'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $lineTotal,
            ]);
        }
        unset($data['items']);
        $data['total_amount'] = $total;
        $data['received_amount'] = 0;
        $data['balance'] = $total;

        $order = DB::transaction(function () use ($data, $items) {
            $last = DB::table('vehicle_purchase_orders')->lockForUpdate()->orderBy('id', 'desc')->first();
            $nextId = $last ? $last->id + 1 : 1;
            $data['po_number'] = 'VPO-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            $order = VehiclePurchaseOrder::create($data);
            $order->items()->saveMany($items);
            return $order;
        });

        return redirect()->route('admin.vehicle-purchase-orders.show', $order)->withSuccess('Vehicle PO created.');
    }

    public function show(VehiclePurchaseOrder $vehiclePurchaseOrder)
    {
        $vehiclePurchaseOrder->load('supplier', 'items');
        return view('admin.vehicle_purchase_orders.show', compact('vehiclePurchaseOrder'));
    }

    public function edit(VehiclePurchaseOrder $vehiclePurchaseOrder)
    {
        if ($vehiclePurchaseOrder->status !== 'pending') {
            return redirect()->route('admin.vehicle-purchase-orders.index')->with('error', 'Only pending orders can be edited.');
        }
        $vehiclePurchaseOrder->load('items');
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $vehicleData = $this->getVehicleOptions();
        $vehicleList = $vehicleData['list'];
        $vehiclePrices = $vehicleData['prices'];
        $colorOptions = VehicleInventory::whereNotNull('color_name')->where('color_name', '!=', '')->distinct()->pluck('color_name')->toArray();
        if (empty($colorOptions)) {
            $colorOptions = ['Black', 'Red', 'Blue', 'White', 'Silver', 'Grey', 'Green'];
        }
        return view('admin.vehicle_purchase_orders.edit', compact('vehiclePurchaseOrder', 'suppliers', 'vehicleList', 'vehiclePrices', 'colorOptions'));
    }

    public function update(Request $request, VehiclePurchaseOrder $vehiclePurchaseOrder)
    {
        if ($vehiclePurchaseOrder->status !== 'pending') {
            return redirect()->route('admin.vehicle-purchase-orders.index')->with('error', 'Only pending orders can be edited.');
        }

        $data = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.vehicle_description' => 'required|string|max:255',
            'items.*.color_name' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $total = 0;
        $newItems = [];
        foreach ($data['items'] as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $total += $lineTotal;

            $newItems[] = new VehiclePoItem([
                'vehicle_description' => $item['vehicle_description'],
                'color_name' => $item['color_name'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $lineTotal,
            ]);
        }
        unset($data['items']);
        $data['total_amount'] = $total;

        DB::transaction(function () use ($vehiclePurchaseOrder, $data, $newItems) {
            $vehiclePurchaseOrder->update($data);
            $vehiclePurchaseOrder->items()->delete();
            $vehiclePurchaseOrder->items()->saveMany($newItems);
        });

        return redirect()->route('admin.vehicle-purchase-orders.index')->withSuccess('Vehicle PO updated.');
    }

    public function destroy(VehiclePurchaseOrder $vehiclePurchaseOrder)
    {
        DB::transaction(function () use ($vehiclePurchaseOrder) {
            VehicleInventory::where('vehicle_po_id', $vehiclePurchaseOrder->id)
                ->where('status', 'available')
                ->update(['status' => 'unlinked']);
            $vehiclePurchaseOrder->items()->delete();
            $vehiclePurchaseOrder->delete();
        });
        return response()->json(['success' => true, 'message' => 'Deleted successfully.']);
    }

    public function toggleStatus(VehiclePurchaseOrder $vehiclePurchaseOrder)
    {
        $vehiclePurchaseOrder->update(['is_active' => !$vehiclePurchaseOrder->is_active]);
        $vehiclePurchaseOrder->refresh();
        return response()->json(['success' => true, 'is_active' => $vehiclePurchaseOrder->is_active]);
    }

    public function receive(VehiclePurchaseOrder $vehiclePurchaseOrder)
    {
        if ($vehiclePurchaseOrder->status === 'received') {
            return redirect()->route('admin.vehicle-purchase-orders.show', $vehiclePurchaseOrder)->with('error', 'Already fully received.');
        }
        $vehiclePurchaseOrder->load('items');
        $receivedVehicles = VehicleInventory::where('vehicle_po_id', $vehiclePurchaseOrder->id)->get();
        $colorOptions = VehicleInventory::whereNotNull('color_name')->where('color_name', '!=', '')->distinct()->pluck('color_name')->toArray();
        if (empty($colorOptions)) {
            $colorOptions = ['Black', 'Red', 'Blue', 'White', 'Silver', 'Grey', 'Green'];
        }
        return view('admin.vehicle_purchase_orders.receive', compact('vehiclePurchaseOrder', 'receivedVehicles', 'colorOptions'));
    }

    public function receiveStore(Request $request, VehiclePurchaseOrder $vehiclePurchaseOrder)
    {
        if ($vehiclePurchaseOrder->status === 'received') {
            return back()->with('error', 'Already fully received.');
        }

        $request->validate([
            'items' => 'nullable|array',
            'items.*.id' => 'required_with:items|exists:vehicle_po_items,id',
            'items.*.vehicles' => 'nullable|array',
            'edit_vehicles' => 'nullable|array',
            'edit_vehicles.*.id' => 'required_with:edit_vehicles|exists:vehicle_inventories,id',
            'edit_vehicles.*.chassis_number' => 'required_with:edit_vehicles|string|max:255',
            'edit_vehicles.*.motor_number' => 'required_with:edit_vehicles|string|max:255',
            'edit_vehicles.*.color_name' => 'nullable|string|max:255',
            'edit_vehicles.*.battery_number' => 'nullable|string|max:255',
            'edit_vehicles.*.charger_number' => 'nullable|string|max:255',
            'edit_vehicles.*.controller_number' => 'nullable|string|max:255',
            'edit_vehicles.*.convertor_number' => 'nullable|string|max:255',
            'edit_vehicles.*.manual_number' => 'nullable|string|max:255',
            'items.*.vehicles.*.color_name' => 'nullable|string|max:255',
            'delete_vehicles' => 'nullable|array',
            'delete_vehicles.*' => 'exists:vehicle_inventories,id',
        ]);

        $allChassis = [];

        // 1. Validate edited vehicles (exclude any marked for deletion)
        $deletedIds = $request->input('delete_vehicles', []);
        if ($request->has('edit_vehicles')) {
            foreach ($request->edit_vehicles as $id => $vehicle) {
                if (in_array($id, $deletedIds)) {
                    continue;
                }
                if (!empty($vehicle['chassis_number']) && !empty($vehicle['motor_number']) && $vehicle['chassis_number'] === $vehicle['motor_number']) {
                    return back()->withErrors(["edit_vehicles.{$id}.motor_number" => "Chassis number and motor number must be different."])->withInput();
                }
                if (in_array($vehicle['chassis_number'], $allChassis)) {
                    return back()->withErrors(["edit_vehicles.{$id}.chassis_number" => "Duplicate chassis number: {$vehicle['chassis_number']}."])->withInput();
                }

                // Database unique check ignoring current ID
                $chassisExists = VehicleInventory::where('chassis_number', $vehicle['chassis_number'])->where('id', '!=', $id)->exists();
                if ($chassisExists) {
                    return back()->withErrors(["edit_vehicles.{$id}.chassis_number" => "Chassis number is already taken."])->withInput();
                }

                $allChassis[] = $vehicle['chassis_number'];
            }
        }

        // 2. Validate new vehicles
        if ($request->has('items')) {
            foreach ($request->items as $itemIdx => $itemData) {
                if (isset($itemData['vehicles'])) {
                    foreach ($itemData['vehicles'] as $vehIdx => $vehicle) {
                        if (empty($vehicle['chassis_number']) || empty($vehicle['motor_number'])) {
                            continue;
                        }
                        if ($vehicle['chassis_number'] === $vehicle['motor_number']) {
                            return back()->withErrors(["items.{$itemIdx}.vehicles.{$vehIdx}.motor_number" => "Chassis number and motor number must be different."])->withInput();
                        }
                        if (in_array($vehicle['chassis_number'], $allChassis)) {
                            return back()->withErrors(["items.{$itemIdx}.vehicles.{$vehIdx}.chassis_number" => "Duplicate chassis number: {$vehicle['chassis_number']}."])->withInput();
                        }

                        // DB unique check for new ones
                        $chassisExists = VehicleInventory::where('chassis_number', $vehicle['chassis_number'])->exists();
                        if ($chassisExists) {
                            return back()->withErrors(["items.{$itemIdx}.vehicles.{$vehIdx}.chassis_number" => "Chassis number is already taken."])->withInput();
                        }

                        $allChassis[] = $vehicle['chassis_number'];
                    }
                }
            }
        }

        // Check if anything was actually submitted
        $hasNew = false;
        if ($request->has('items')) {
            foreach ($request->items as $itemData) {
                if (!empty($itemData['vehicles'])) {
                    $hasNew = true;
                    break;
                }
            }
        }
        $hasEdit = false;
        if ($request->has('edit_vehicles')) {
            foreach ($request->edit_vehicles as $id => $val) {
                if (!in_array($id, $deletedIds)) {
                    $hasEdit = true;
                    break;
                }
            }
        }
        $hasDelete = !empty($deletedIds);

        if (!$hasNew && !$hasEdit && !$hasDelete) {
            return back()->with('error', 'No vehicle details were submitted.')->withInput();
        }

        DB::transaction(function () use ($request, $vehiclePurchaseOrder, $hasNew, $hasEdit, $hasDelete, $deletedIds) {
            // Delete removed vehicles and decrement received_quantity
            if ($hasDelete) {
                foreach ($deletedIds as $id) {
                    $vehicle = VehicleInventory::find($id);
                    if ($vehicle) {
                        $poItem = $vehiclePurchaseOrder->items()
                            ->where('vehicle_description', $vehicle->vehicle_description)
                            ->first();
                        if ($poItem) {
                            $poItem->decrement('received_quantity');
                        }
                        $vehicle->delete();
                    }
                }
            }

            // Update edited vehicles
            if ($hasEdit) {
                foreach ($request->edit_vehicles as $id => $val) {
                    if (in_array($id, $deletedIds)) {
                        continue;
                    }
                    VehicleInventory::where('id', $id)->update([
                        'chassis_number' => $val['chassis_number'],
                        'engine_number' => $val['motor_number'],
                        'motor_number' => $val['motor_number'],
                        'color_name' => $val['color_name'] ?? null,
                        'battery_number' => $val['battery_number'] ?? null,
                        'charger_number' => $val['charger_number'] ?? null,
                        'controller_number' => $val['controller_number'] ?? null,
                        'convertor_number' => $val['convertor_number'] ?? null,
                        'manual_number' => $val['manual_number'] ?? null,
                    ]);
                }
            }

            // Create new vehicles and update PO items
            $allFullyReceived = true;
            foreach ($vehiclePurchaseOrder->items()->get() as $poItem) {
                $previousReceived = $poItem->received_quantity;
                $delta = 0;

                // Find matching item index in request
                if ($request->has('items')) {
                    foreach ($request->items as $itemData) {
                        if ((int)$itemData['id'] === (int)$poItem->id && !empty($itemData['vehicles'])) {
                            $validVehicles = array_filter($itemData['vehicles'], function($v) {
                                return !empty($v['chassis_number']) && !empty($v['motor_number']);
                            });
                            $delta = count($validVehicles);
                            foreach ($validVehicles as $vehicle) {
                                $inventoryData = [
                                    'vehicle_po_id' => $vehiclePurchaseOrder->id,
                                    'vehicle_description' => $poItem->vehicle_description,
                                    'chassis_number' => $vehicle['chassis_number'],
                                    'engine_number' => $vehicle['motor_number'],
                                    'motor_number' => $vehicle['motor_number'],
                                    'color_name' => !empty($vehicle['color_name']) ? $vehicle['color_name'] : $poItem->color_name,
                                    'battery_number' => $vehicle['battery_number'] ?? null,
                                    'charger_number' => $vehicle['charger_number'] ?? null,
                                    'controller_number' => $vehicle['controller_number'] ?? null,
                                    'convertor_number' => $vehicle['convertor_number'] ?? null,
                                    'manual_number' => $vehicle['manual_number'] ?? null,
                                    'quantity' => 1,
                                    'purchase_price' => $poItem->unit_price,
                                    'status' => 'available',
                                ];
                                if (\Illuminate\Support\Facades\Schema::hasColumn('vehicle_inventories', 'mfg_year')) {
                                    $inventoryData['mfg_year'] = $poItem->mfg_year ?? null;
                                }
                                VehicleInventory::create($inventoryData);
                            }
                        }
                    }
                }

                $newReceived = min($delta + $previousReceived, $poItem->quantity);
                $poItem->update(['received_quantity' => $newReceived]);

                if ($newReceived < $poItem->quantity) {
                    $allFullyReceived = false;
                }
            }

            $vehiclePurchaseOrder->update([
                'status' => $allFullyReceived ? 'received' : ($vehiclePurchaseOrder->items()->where('received_quantity', '>', 0)->exists() ? 'partial' : 'pending'),
            ]);
        });

        return redirect()->route('admin.vehicle-purchase-orders.show', $vehiclePurchaseOrder)->withSuccess('Vehicles updated successfully.');
    }

    public function checkUnique(Request $request)
    {
        $field = $request->input('field');
        $value = $request->input('value');
        $ignoreId = $request->input('ignore_id');

        if (!in_array($field, ['chassis_number', 'engine_number'])) {
            return response()->json(['valid' => false, 'message' => 'Invalid field.']);
        }

        $query = VehicleInventory::where($field, $value);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        $exists = $query->exists();

        return response()->json([
            'valid' => !$exists,
            'message' => $exists ? "This {$field} is already taken." : '',
        ]);
    }

    public function inventory(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');
        $query = VehicleInventory::where('is_active', true)->orderBy('created_at', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('vehicle_description', 'like', $escapedSearch)
                  ->orWhere('chassis_number', 'like', $escapedSearch)
                  ->orWhere('motor_number', 'like', $escapedSearch)
                  ->orWhere('battery_number', 'like', $escapedSearch);
            });
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $inventories = $query->paginate(20)->withQueryString();

        // Calculate available count per vehicle variant to evaluate against VehicleMaster.min_stock
        $vehicleMasters = \App\Models\VehicleMaster::where('is_active', true)->get();
        $variantCounts = VehicleInventory::where('status', 'available')
            ->select('vehicle_description', DB::raw('COUNT(*) as total'))
            ->groupBy('vehicle_description')
            ->pluck('total', 'vehicle_description')
            ->toArray();

        $lowStockVariants = [];
        foreach ($vehicleMasters as $vm) {
            $name = trim($vm->variant_name);
            $available = $variantCounts[$name] ?? 0;
            if ($vm->min_stock > 0 && $available <= $vm->min_stock) {
                $lowStockVariants[$name] = [
                    'min_stock' => $vm->min_stock,
                    'available' => $available,
                ];
            }
        }

        return view('admin.vehicle_inventories.index', compact('inventories', 'search', 'statusFilter', 'lowStockVariants'));
    }

    public function exportInventory(Request $request)
    {
        $search = $request->input('search');
        $query = VehicleInventory::where('is_active', true)->orderBy('created_at', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('vehicle_description', 'like', $escapedSearch)
                  ->orWhere('chassis_number', 'like', $escapedSearch)
                  ->orWhere('motor_number', 'like', $escapedSearch)
                  ->orWhere('color_name', 'like', $escapedSearch)
                  ->orWhere('battery_number', 'like', $escapedSearch);
            });
        }

        $inventories = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Vehicle');
        $sheet->setCellValue('B1', 'Color');
        $sheet->setCellValue('C1', 'Chassis No');
        $sheet->setCellValue('D1', 'Motor No');
        $sheet->setCellValue('E1', 'Battery No');
        $sheet->setCellValue('F1', 'Charger No');
        $sheet->setCellValue('G1', 'Controller No');
        $sheet->setCellValue('H1', 'Convertor No');
        $sheet->setCellValue('I1', 'Manual No');
        $sheet->setCellValue('J1', 'Purchase Price');
        $sheet->setCellValue('K1', 'Status');
        $sheet->setCellValue('L1', 'PO Ref');

        $row = 2;
        foreach ($inventories as $i) {
            $sheet->setCellValue('A' . $row, $i->vehicle_description);
            $sheet->setCellValue('B' . $row, $i->color_name ?? '-');
            $sheet->setCellValue('C' . $row, $i->chassis_number);
            $sheet->setCellValue('D' . $row, $i->motor_number);
            $sheet->setCellValue('E' . $row, $i->battery_number);
            $sheet->setCellValue('F' . $row, $i->charger_number);
            $sheet->setCellValue('G' . $row, $i->controller_number);
            $sheet->setCellValue('H' . $row, $i->convertor_number);
            $sheet->setCellValue('I' . $row, $i->manual_number);
            $sheet->setCellValue('J' . $row, $i->purchase_price);
            $sheet->setCellValue('K' . $row, ucfirst($i->status));
            $sheet->setCellValue('L' . $row, $i->purchaseOrder->po_number ?? '-');
            $row++;
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/vehicle_inventories_export.xls');
        $writer->save($path);

        return response()->download($path, 'vehicle_inventories_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
    }

    public function toggleInventoryStatus(Request $request, $id)
    {
        $vehicle = VehicleInventory::findOrFail($id);
        $newStatus = $vehicle->status === 'available' ? 'sold' : 'available';
        $vehicle->update(['status' => $newStatus]);

        return back()->withSuccess("Vehicle status updated to {$newStatus}.");
    }

    public function generatePdf(Request $request, VehiclePurchaseOrder $vehiclePurchaseOrder)
    {
        $vehiclePurchaseOrder->load('supplier', 'items');

        $pdf = Pdf::loadView('admin.vehicle_purchase_orders.pdf', [
            'vehiclePurchaseOrder' => $vehiclePurchaseOrder,
        ]);
        $pdf->setPaper('a4');
        $pdf->setOption('isRemoteEnabled', true);

        if ($request->has('print')) {
            $pdf->render();
            $canvas = $pdf->getCanvas();
            $canvas->javascript("this.print();");
        }

        return $pdf->stream('VPO-' . $vehiclePurchaseOrder->po_number . '.pdf');
    }

    public function sendWhatsapp(VehiclePurchaseOrder $vehiclePurchaseOrder)
    {
        $vehiclePurchaseOrder->load('supplier', 'items');

        $phone = $vehiclePurchaseOrder->supplier->phone ?? '';
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (empty($phone)) {
            return back()->with('error', 'Supplier phone number is not available.');
        }

        if (strlen($phone) == 10) {
            $phone = '91' . $phone;
        }

        $itemsList = '';
        foreach ($vehiclePurchaseOrder->items as $i => $item) {
            $itemsList .= ($i + 1) . ". " . $item->vehicle_description . " x " . $item->quantity . "\n";
        }

        $pdfUrl = request()->getSchemeAndHttpHost() . route('admin.vehicle-purchase-orders.pdf', $vehiclePurchaseOrder, false);

        $message = "*VEHICLE PURCHASE ORDER - {$vehiclePurchaseOrder->po_number}*\n"
            . "━━━━━━━━━━━━━━━━━━━━━━\n"
            . "📅 *Date:* {$vehiclePurchaseOrder->order_date->format('d/m/Y')}\n"
            . "🏢 *Supplier:* " . ($vehiclePurchaseOrder->supplier->name ?? '-') . "\n"
            . "📦 *Items:*\n{$itemsList}"
            . "━━━━━━━━━━━━━━━━━━━━━━\n"
            . "💰 *Total:* ₹" . number_format($vehiclePurchaseOrder->total_amount, 2) . "\n"
            . "📌 *Status:* " . ucfirst($vehiclePurchaseOrder->status) . "\n"
            . "━━━━━━━━━━━━━━━━━━━━━━\n\n"
            . "📄 *PDF Link:* {$pdfUrl}\n\n"
            . "Please find the attached PO details. Kindly confirm.";

        $whatsappUrl = "https://wa.me/{$phone}?text=" . urlencode($message);

        return redirect($whatsappUrl);
    }

    public function receivePayment(Request $request, VehiclePurchaseOrder $vehiclePurchaseOrder)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $amount = floatval($request->input('amount'));

        if ($amount > $vehiclePurchaseOrder->balance) {
            return response()->json(['success' => false, 'message' => 'Amount cannot exceed the balance (' . number_format($vehiclePurchaseOrder->balance, 2) . ')']);
        }

        DB::transaction(function () use ($vehiclePurchaseOrder, $amount) {
            $vehiclePurchaseOrder->received_amount += $amount;
            $vehiclePurchaseOrder->balance -= $amount;
            $vehiclePurchaseOrder->save();
        });

        return response()->json(['success' => true, 'message' => 'Payment received successfully.']);
    }

    private function getVehicleOptions(): array
    {
        $list = [];
        $prices = [];
        VehicleMaster::where('is_active', true)->orderBy('variant_name')->get()->each(function ($v) use (&$list, &$prices) {
            $desc = trim($v->variant_name);
            if ($desc && !in_array($desc, $list)) {
                $list[] = $desc;
            }
            if ($v->ex_showroom_price && !isset($prices[$desc])) {
                $prices[$desc] = $v->ex_showroom_price;
            }
        });
        return ['list' => $list, 'prices' => $prices];
    }
}

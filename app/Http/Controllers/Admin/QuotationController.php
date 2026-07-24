<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Customer;
use App\Models\VehicleMaster;
use App\Models\SparePart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $type = $request->input('type');
        
        $query = Quotation::with('customer', 'vehicleMaster')
            ->orderBy('quotation_date', 'desc')
            ->orderBy('id', 'desc');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('quotation_number', 'like', $escapedSearch)
                  ->orWhere('customer_name', 'like', $escapedSearch)
                  ->orWhere('customer_mobile', 'like', $escapedSearch);
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        $quotations = $query->paginate(20);

        return view('admin.quotations.index', compact('quotations', 'search', 'type'));
    }

    public function createVehicle()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $vehicles = VehicleMaster::where('is_active', true)->orderBy('variant_name')->get();
        return view('admin.quotations.create_vehicle', compact('customers', 'vehicles'));
    }

    public function editVehicle(Quotation $quotation)
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $vehicles = VehicleMaster::where('is_active', true)->orderBy('variant_name')->get();
        return view('admin.quotations.edit_vehicle', compact('quotation', 'customers', 'vehicles'));
    }

    public function createParts()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $spareParts = SparePart::where('is_active', true)->orderBy('name')->get();
        return view('admin.quotations.create_parts', compact('customers', 'spareParts'));
    }

    public function store(Request $request)
    {
        $type = $request->input('type');

        if ($type === 'vehicle') {
            $request->validate([
                'quotation_date' => 'required|date',
                'customer_id' => 'nullable|exists:customers,id',
                'customer_name' => 'required|string|max:255',
                'customer_mobile' => 'nullable|string|max:20',
                'customer_address' => 'nullable|string',
                'customer_pan' => 'nullable|string|max:10',
                'place_of_supply' => 'required|string|max:255',
                'vehicle_master_id' => 'required|exists:vehicle_masters,id',
                'rate' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'nemmp_incentive' => 'nullable|numeric|min:0',
            ]);
        } else {
            $request->validate([
                'quotation_date' => 'required|date',
                'customer_id' => 'nullable|exists:customers,id',
                'customer_name' => 'required|string|max:255',
                'customer_mobile' => 'nullable|string|max:20',
                'customer_address' => 'nullable|string',
                'customer_pan' => 'nullable|string|max:10',
                'place_of_supply' => 'required|string|max:255',
                'items' => 'required|array|min:1',
                'items.*.spare_part_id' => 'required|exists:spare_parts,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.rate' => 'required|numeric|min:0',
            ]);
        }

        try {
            DB::beginTransaction();

            $data = $request->only([
                'type', 'quotation_date', 'customer_id', 'customer_name',
                'customer_mobile', 'customer_address',
                'customer_pan', 'place_of_supply', 'remarks',
                'model_maker_name', 'gross_weight', 'charging_time', 'performance',
                'charger_output', 'motor_output', 'seating_capacity', 'type_of_break',
                'roof_top_abs', 'front_fiber_wind_shield', 'meter_type'
            ]);
            $data['created_by'] = Auth::id();

            if ($type === 'vehicle') {
                $data['vehicle_master_id'] = $request->input('vehicle_master_id');
                $rate = (float) $request->input('rate');
                $discount = (float) $request->input('discount', 0);
                $incentive = (float) $request->input('nemmp_incentive', 0);
                
                $sub_total = $rate;
                $taxable = $sub_total - $discount - $incentive;
                if ($taxable < 0) $taxable = 0;

                $data['rate'] = $rate;
                $data['sub_total'] = $sub_total;
                $data['discount'] = $discount;
                $data['nemmp_incentive'] = $incentive;
                $data['taxable_amount'] = $taxable;

                $total = $taxable;

                $grandTotal = round($total);
                $data['round_off'] = $grandTotal - $total;
                $data['total_amount'] = $grandTotal;

                $quotation = Quotation::create($data);
            } else {
                // Parts quotation
                $items = $request->input('items');
                $taxable_amount = 0;
                $total_sum = 0;

                $quotationItemsData = [];

                foreach ($items as $item) {
                    $qty = (int) $item['quantity'];
                    $rate = (float) $item['rate'];
                    $item_amount = $qty * $rate;
                    
                    $taxable_amount += $item_amount;
                    $total_sum += $item_amount;

                    $quotationItemsData[] = [
                        'spare_part_id' => $item['spare_part_id'],
                        'quantity' => $qty,
                        'rate' => $rate,
                        'amount' => $item_amount,
                        'serial_no_warranty_notes' => $item['serial_no_warranty_notes'] ?? null,
                    ];
                }

                $grandTotal = round($total_sum);
                $round_off = $grandTotal - $total_sum;

                $data['taxable_amount'] = $taxable_amount;
                $data['round_off'] = $round_off;
                $data['total_amount'] = $grandTotal;

                $quotation = Quotation::create($data);

                foreach ($quotationItemsData as $itemData) {
                    $itemData['quotation_id'] = $quotation->id;
                    QuotationItem::create($itemData);
                }
            }

            DB::commit();
            return redirect()->route('admin.quotations.show', $quotation)->with('success', 'Quotation created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating quotation: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Quotation $quotation)
    {
        $type = $request->input('type', $quotation->type);

        if ($type === 'vehicle') {
            $request->validate([
                'quotation_date' => 'required|date',
                'customer_id' => 'nullable|exists:customers,id',
                'customer_name' => 'required|string|max:255',
                'customer_mobile' => 'nullable|string|max:20',
                'customer_address' => 'nullable|string',
                'customer_pan' => 'nullable|string|max:10',
                'place_of_supply' => 'required|string|max:255',
                'vehicle_master_id' => 'required|exists:vehicle_masters,id',
                'rate' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'nemmp_incentive' => 'nullable|numeric|min:0',
            ]);

            try {
                DB::beginTransaction();

                $data = $request->only([
                    'quotation_date', 'customer_id', 'customer_name',
                    'customer_mobile', 'customer_address',
                    'customer_pan', 'place_of_supply', 'remarks',
                    'model_maker_name', 'gross_weight', 'charging_time', 'performance',
                    'charger_output', 'motor_output', 'seating_capacity', 'type_of_break',
                    'roof_top_abs', 'front_fiber_wind_shield', 'meter_type'
                ]);

                $data['vehicle_master_id'] = $request->input('vehicle_master_id');
                $rate = (float) $request->input('rate');
                $discount = (float) $request->input('discount', 0);
                $incentive = (float) $request->input('nemmp_incentive', 0);
                
                $sub_total = $rate;
                $taxable = $sub_total - $discount - $incentive;
                if ($taxable < 0) $taxable = 0;

                $data['rate'] = $rate;
                $data['sub_total'] = $sub_total;
                $data['discount'] = $discount;
                $data['nemmp_incentive'] = $incentive;
                $data['taxable_amount'] = $taxable;

                $total = $taxable;

                $grandTotal = round($total);
                $data['round_off'] = $grandTotal - $total;
                $data['total_amount'] = $grandTotal;

                $quotation->update($data);

                DB::commit();
                return redirect()->route('admin.quotations.show', $quotation)->with('success', 'Quotation updated successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Error updating quotation: ' . $e->getMessage());
            }
        }

        return back()->with('error', 'Only vehicle quotations can be edited currently.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load('customer', 'vehicleMaster', 'items.sparePart', 'creator');
        return view('admin.quotations.show', compact('quotation'));
    }

    public function generatePdf(Request $request, Quotation $quotation)
    {
        $quotation->load('customer', 'vehicleMaster', 'items.sparePart', 'creator');
        
        $pdf = Pdf::loadView('admin.quotations.pdf', [
            'quotation' => $quotation
        ]);
        $pdf->setPaper('a4');
        $pdf->setOption('isRemoteEnabled', true);

        if ($request->has('print')) {
            $pdf->render();
            $canvas = $pdf->getCanvas();
            $canvas->javascript("this.print();");
        }

        return $pdf->stream($quotation->quotation_number . '.pdf');
    }

    public function sendWhatsapp(Quotation $quotation)
    {
        $phone = $quotation->customer_mobile ?? '';
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (empty($phone)) {
            return back()->with('error', 'Customer phone number is not available.');
        }

        if (strlen($phone) == 10) {
            $phone = '91' . $phone;
        }

        $pdfUrl = request()->getSchemeAndHttpHost() . route('admin.quotations.pdf', $quotation, false);

        $itemsList = '';
        if ($quotation->type === 'vehicle') {
            $itemsList = "1. " . ($quotation->vehicleMaster->variant_name ?? 'Vehicle') . "\n";
        } else {
            foreach ($quotation->items as $i => $item) {
                $itemsList .= ($i + 1) . ". " . ($item->sparePart->name ?? 'N/A') . " x " . $item->quantity . "\n";
            }
        }

        $message = "*QUOTATION - {$quotation->quotation_number}*\n"
            . "━━━━━━━━━━━━━━━━━━━━━━\n"
            . "📅 *Date:* {$quotation->quotation_date->format('d/m/Y')}\n"
            . "👤 *Customer:* {$quotation->customer_name}\n"
            . "📦 *Details:*\n{$itemsList}"
            . "━━━━━━━━━━━━━━━━━━━━━━\n"
            . "💰 *Total Amount:* ₹" . number_format($quotation->total_amount, 2) . "\n"
            . "━━━━━━━━━━━━━━━━━━━━━━\n\n"
            . "📄 *PDF Link:* {$pdfUrl}\n\n"
            . "Please find the attached Quotation. Let us know if you have any questions.";

        $whatsappUrl = "https://wa.me/{$phone}?text=" . urlencode($message);

        return redirect($whatsappUrl);
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();
        return redirect()->route('admin.quotations.index')->with('success', 'Quotation deleted successfully.');
    }
}

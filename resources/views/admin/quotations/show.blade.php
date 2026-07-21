@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin / Quotations /</span> {{ $quotation->quotation_number }}
    </h4>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Quotation Details</h5>
            <div>
                @if($quotation->type === 'vehicle')
                    <a href="{{ route('admin.quotations.edit-vehicle', $quotation) }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-edit-alt"></i> Edit Quotation
                    </a>
                @endif
                <a href="{{ route('admin.quotations.pdf', $quotation) }}" class="btn btn-sm btn-danger" target="_blank">
                    <i class="bx bxs-file-pdf"></i> Download PDF
                </a>
                <a href="{{ route('admin.quotations.whatsapp', $quotation) }}" class="btn btn-sm btn-success" target="_blank">
                    <i class="bx bxl-whatsapp"></i> Send to WhatsApp
                </a>
                <a href="{{ route('admin.quotations.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <h6 class="text-primary border-bottom pb-2">General Info</h6>
                    <strong>Quotation Number:</strong> {{ $quotation->quotation_number }}<br>
                    <strong>Date:</strong> {{ $quotation->quotation_date->format('d-m-Y') }}<br>
                    <strong>Type:</strong> 
                    @if($quotation->type === 'vehicle')
                        <span class="badge bg-primary">Vehicle</span>
                    @else
                        <span class="badge bg-success">Parts</span>
                    @endif
                    <br>
                    <strong>Tax Regime:</strong> 
                    @if($quotation->tax_regime === 'cgst_sgst')
                        CGST + SGST (9% + 9% typical)
                    @else
                        IGST (18% typical)
                    @endif
                    <br>
                    <strong>Created By:</strong> {{ $quotation->creator->full_name ?? 'System' }}<br>
                    <strong>Remarks/Notes:</strong> {{ $quotation->remarks ?? '-' }}
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="text-primary border-bottom pb-2">Customer Info</h6>
                    <strong>Name:</strong> {{ $quotation->customer_name }}<br>
                    <strong>Mobile:</strong> {{ $quotation->customer_mobile ?? '-' }}<br>
                    <strong>Address:</strong> {{ $quotation->customer_address ?? '-' }}<br>
                    <strong>GSTIN:</strong> {{ $quotation->customer_gstin ?? '-' }}<br>
                    <strong>PAN:</strong> {{ $quotation->customer_pan ?? '-' }}<br>
                    <strong>Place of Supply:</strong> {{ $quotation->place_of_supply }}
                </div>
            </div>

            @if($quotation->type === 'vehicle')
                <h5 class="mt-4 text-primary">Vehicle Specifications</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Vehicle Model</th>
                                <th>Ex-Showroom Price</th>
                                <th>Discount</th>
                                <th>Incentive</th>
                                <th>Taxable Value</th>
                                <th>GST Rate</th>
                                <th>GST Amount</th>
                                <th>Grand Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    {{ $quotation->vehicleMaster->variant_name ?? '-' }} ({{ $quotation->vehicleMaster->color_name ?? '' }} - {{ $quotation->vehicleMaster->fuel_type ?? '' }})
                                    <div class="small text-muted fw-bold">ON ROAD PRICE INCLUDING GST, RTO, INSURANCE</div>
                                </td>
                                <td>₹{{ number_format($quotation->rate, 2) }}</td>
                                <td class="text-danger">-₹{{ number_format($quotation->discount, 2) }}</td>
                                <td class="text-danger">-₹{{ number_format($quotation->nemmp_incentive, 2) }}</td>
                                <td>₹{{ number_format($quotation->taxable_amount, 2) }}</td>
                                <td>
                                    @if($quotation->tax_regime === 'cgst_sgst')
                                        CGST: {{ $quotation->cgst_rate }}% <br> SGST: {{ $quotation->sgst_rate }}%
                                    @else
                                        IGST: {{ $quotation->igst_rate }}%
                                    @endif
                                </td>
                                <td>
                                    @if($quotation->tax_regime === 'cgst_sgst')
                                        ₹{{ number_format($quotation->cgst_amount + $quotation->sgst_amount, 2) }}
                                    @else
                                        ₹{{ number_format($quotation->igst_amount, 2) }}
                                    @endif
                                </td>
                                <td class="fw-bold">₹{{ number_format($quotation->total_amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <h5 class="mt-4 text-primary">Parts & Items</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Part Details</th>
                                <th>Rate</th>
                                <th>GST (%)</th>
                                <th>Qty</th>
                                <th>GST Amount</th>
                                <th>Total</th>
                                <th>Warranty/Serial No</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quotation->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item->sparePart->name ?? '-' }}</strong><br>
                                    <small class="text-muted">Part No: {{ $item->sparePart->part_no ?? '-' }}</small>
                                </td>
                                <td>₹{{ number_format($item->rate, 2) }}</td>
                                <td>{{ $item->tax_percentage }}%</td>
                                <td>{{ $item->quantity }}</td>
                                <td>₹{{ number_format($item->tax_amount, 2) }}</td>
                                <td>₹{{ number_format($item->amount, 2) }}</td>
                                <td>{{ $item->serial_no_warranty_notes ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="row mt-4">
                <div class="col-md-6">
                    @if($quotation->type === 'vehicle')
                        <div class="card border mb-3">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0 text-primary fw-bold">TECHNICAL SPECIFICATION</h6>
                            </div>
                            <div class="card-body py-2">
                                <table class="table table-sm table-borderless mb-0 small">
                                    <tr><td style="width: 45%;"><strong>Model/ Maker's:</strong></td><td>{{ $quotation->model_maker_name ?? 'E- PASSENGER/ARZOO/PASSANGER' }}</td></tr>
                                    <tr><td><strong>GROSS WEIGHT:</strong></td><td>{{ $quotation->gross_weight ?? '60 KG' }}</td></tr>
                                    <tr><td><strong>CHARGING TIME:</strong></td><td>{{ $quotation->charging_time ?? '3-4 HR' }}</td></tr>
                                    <tr><td><strong>PERFORMANCE:</strong></td><td>{{ $quotation->performance ?? 'HIGH SPEED 25 KM/HR' }}</td></tr>
                                    <tr><td><strong>CHARGER OUTPUT:</strong></td><td>{{ $quotation->charger_output ?? 'DC 51V 105 AH (1 LITHIUM BATTERY)' }}</td></tr>
                                    <tr><td><strong>MOTOR OUTPUT:</strong></td><td>{{ $quotation->motor_output ?? '1200 W' }}</td></tr>
                                    <tr><td><strong>SEATING CAPACITY:</strong></td><td>{{ $quotation->seating_capacity ?? '5' }}</td></tr>
                                    <tr><td><strong>TYPE OF BRAKE:</strong></td><td>{{ $quotation->type_of_break ?? 'DRUM BREAK' }}</td></tr>
                                    <tr><td><strong>ROOF TOP ABS HARD ROOF:</strong></td><td>{{ $quotation->roof_top_abs ?? 'YES' }}</td></tr>
                                    <tr><td><strong>FRONT FIBER WIND SHIELD:</strong></td><td>{{ $quotation->front_fiber_wind_shield ?? 'YES' }}</td></tr>
                                    <tr><td><strong>METER:</strong></td><td>{{ $quotation->meter_type ?? 'DIGITAL' }}</td></tr>
                                    <tr><td><strong>ON ROAD PRICE:</strong></td><td>₹{{ number_format($quotation->total_amount, 2) }}/- INCLUDING GST, RTO, INSURANCE</td></tr>
                                    <tr><td><strong>ACCESSORIES:</strong></td><td>STEPNY, JACK, TOOL KIT,STERIO, SIDE MIRROR</td></tr>
                                </table>
                            </div>
                        </div>

                        <div class="card border mb-3">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0 text-primary fw-bold">TERMS AND CONDITIONS</h6>
                            </div>
                            <div class="card-body py-2 small">
                                1. THERE IS 12 MONTH WARRANTY FOR MOTOR, CONTROLLER<br>
                                2. THERE IS 36 MONTH WARRANTY FOR BATTERY, CHARGER<br>
                                3. THERE IS NO WARRANTY OR GURANTEE FOR THE WORK CARRIED OUT AND PARTS REPLACED OTHER THAN FOR THE PARTS LIKE BATTERIES, CHARGER, CONTROLLER, MOTOR.
                            </div>
                        </div>

                        <div class="card border">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0 text-primary fw-bold">BANK DETAILS</h6>
                            </div>
                            <div class="card-body py-2">
                                <table class="table table-sm table-borderless mb-0 small">
                                    <tr><td style="width: 35%;"><strong>AC HOLDER:</strong></td><td>SHREE KRISHNA AUTO GREEN</td></tr>
                                    <tr><td><strong>BRANCH:</strong></td><td>STADIUM, SHOPPING CENTRE</td></tr>
                                    <tr><td><strong>AC NUMBER:</strong></td><td>65261516842</td></tr>
                                    <tr><td><strong>IFSC:</strong></td><td>SBIN0050696</td></tr>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Taxable Amount:</strong></td>
                            <td class="text-end">₹{{ number_format($quotation->taxable_amount, 2) }}</td>
                        </tr>
                        @if($quotation->tax_regime === 'cgst_sgst')
                            <tr>
                                <td>CGST Amount:</td>
                                <td class="text-end">₹{{ number_format($quotation->cgst_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td>SGST Amount:</td>
                                <td class="text-end">₹{{ number_format($quotation->sgst_amount, 2) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td>IGST Amount:</td>
                                <td class="text-end">₹{{ number_format($quotation->igst_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td>Round Off:</td>
                            <td class="text-end">₹{{ number_format($quotation->round_off, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td><h5 class="text-primary mb-0">Grand Total:</h5></td>
                            <td class="text-end"><h5 class="text-primary mb-0">₹{{ number_format($quotation->total_amount, 2) }}</h5></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

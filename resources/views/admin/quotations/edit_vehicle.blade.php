@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-4">Edit Vehicle Quotation - {{ $quotation->quotation_number }}</h4>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.quotations.update', $quotation) }}" id="quotationForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="vehicle">
                
                <h5 class="card-title text-primary mb-3">Customer Information</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Select Customer (Existing)</label>
                        <select id="customer_select" name="customer_id" class="form-select">
                            <option value="">-- New Customer / Walk-in --</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" 
                                    {{ old('customer_id', $quotation->customer_id) == $c->id ? 'selected' : '' }}
                                    data-name="{{ $c->first_name }} {{ $c->last_name }}"
                                    data-mobile="{{ $c->phone }}"
                                    data-address="{{ $c->address }}"
                                    data-gstin="{{ $c->gstin }}"
                                    data-pan="{{ $c->pan_no }}">
                                {{ $c->first_name }} {{ $c->last_name }} ({{ $c->phone }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control" value="{{ old('customer_name', $quotation->customer_name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" id="customer_mobile" name="customer_mobile" class="form-control" value="{{ old('customer_mobile', $quotation->customer_mobile) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">GSTIN (Optional)</label>
                        <input type="text" id="customer_gstin" name="customer_gstin" class="form-control" value="{{ old('customer_gstin', $quotation->customer_gstin) }}" placeholder="15-digit GSTIN" maxlength="15">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">PAN Number (Optional)</label>
                        <input type="text" id="customer_pan" name="customer_pan" class="form-control" value="{{ old('customer_pan', $quotation->customer_pan) }}" placeholder="10-digit PAN" maxlength="10">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Place of Supply <span class="text-danger">*</span></label>
                        <input type="text" id="place_of_supply" name="place_of_supply" class="form-control" value="{{ old('place_of_supply', $quotation->place_of_supply) }}" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Billing Address</label>
                        <textarea id="customer_address" name="customer_address" class="form-control" rows="2">{{ old('customer_address', $quotation->customer_address) }}</textarea>
                    </div>
                </div>

                <h5 class="card-title text-primary mb-3">Quotation Details</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Quotation Date <span class="text-danger">*</span></label>
                        <input type="date" name="quotation_date" class="form-control" value="{{ old('quotation_date', $quotation->quotation_date ? $quotation->quotation_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tax Regime <span class="text-danger">*</span></label>
                        <select name="tax_regime" id="tax_regime" class="form-select no-select2" required>
                            <option value="cgst_sgst" {{ old('tax_regime', $quotation->tax_regime) === 'cgst_sgst' ? 'selected' : '' }}>CGST + SGST</option>
                            <option value="igst" {{ old('tax_regime', $quotation->tax_regime) === 'igst' ? 'selected' : '' }}>IGST</option>
                        </select>
                    </div>
                </div>

                <h5 class="card-title text-primary mb-3">Vehicle Details</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Select Vehicle Model <span class="text-danger">*</span></label>
                        <select id="vehicle_select" name="vehicle_master_id" class="form-select" required>
                            <option value="">-- Select Model --</option>
                            @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" {{ old('vehicle_master_id', $quotation->vehicle_master_id) == $v->id ? 'selected' : '' }} data-price="{{ $v->ex_showroom_price }}">
                                {{ $v->variant_name }} ({{ $v->color_name }} - {{ $v->fuel_type }})
                            </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted fw-bold mt-1"><i class="bx bx-info-circle"></i> On Road Price includes GST, RTO, & Insurance.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ex-Showroom Price / Rate <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" id="rate" name="rate" class="form-control" value="{{ old('rate', $quotation->rate) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Discount (₹)</label>
                        <input type="number" step="0.01" id="discount" name="discount" class="form-control" value="{{ old('discount', $quotation->discount) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">NEMMP/Incentive (₹)</label>
                        <input type="number" step="0.01" id="nemmp_incentive" name="nemmp_incentive" class="form-control" value="{{ old('nemmp_incentive', $quotation->nemmp_incentive) }}">
                    </div>
                    
                    <div class="col-md-3 cgst-group">
                        <label class="form-label">CGST Rate (%)</label>
                        <input type="number" step="0.01" id="cgst_rate" name="cgst_rate" class="form-control" value="{{ old('cgst_rate', $quotation->cgst_rate ?: 2.5) }}">
                    </div>
                    <div class="col-md-3 sgst-group">
                        <label class="form-label">SGST Rate (%)</label>
                        <input type="number" step="0.01" id="sgst_rate" name="sgst_rate" class="form-control" value="{{ old('sgst_rate', $quotation->sgst_rate ?: 2.5) }}">
                    </div>
                    <div class="col-md-3 igst-group d-none">
                        <label class="form-label">IGST Rate (%)</label>
                        <input type="number" step="0.01" id="igst_rate" name="igst_rate" class="form-control" value="{{ old('igst_rate', $quotation->igst_rate ?: 5) }}">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 offset-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-3 text-secondary">Summary & Calculations</h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td>Sub Total:</td>
                                        <td class="text-end fw-bold">₹<span id="summary_sub_total">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <td>Discount:</td>
                                        <td class="text-end text-danger fw-bold">-₹<span id="summary_discount">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <td>Incentive:</td>
                                        <td class="text-end text-danger fw-bold">-₹<span id="summary_incentive">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <td>Taxable Amount:</td>
                                        <td class="text-end fw-bold">₹<span id="summary_taxable">0.00</span></td>
                                    </tr>
                                    <tr class="cgst-summary">
                                        <td>CGST Amount:</td>
                                        <td class="text-end fw-bold">₹<span id="summary_cgst">0.00</span></td>
                                    </tr>
                                    <tr class="sgst-summary">
                                        <td>SGST Amount:</td>
                                        <td class="text-end fw-bold">₹<span id="summary_sgst">0.00</span></td>
                                    </tr>
                                    <tr class="igst-summary d-none">
                                        <td>IGST Amount:</td>
                                        <td class="text-end fw-bold">₹<span id="summary_igst">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <td>Round Off:</td>
                                        <td class="text-end fw-bold">₹<span id="summary_round_off">0.00</span></td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="fs-5 fw-bold text-primary">Grand Total:</td>
                                        <td class="text-end fs-5 fw-bold text-primary">₹<span id="summary_grand_total">0.00</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="card-title text-primary mb-3">Technical Specifications</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Model / Maker's Name</label>
                        <input type="text" name="model_maker_name" class="form-control" value="{{ old('model_maker_name', $quotation->model_maker_name ?? 'E- PASSENGER/ARZOO/PASSANGER') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gross Weight</label>
                        <input type="text" name="gross_weight" class="form-control" value="{{ old('gross_weight', $quotation->gross_weight ?? '60 KG') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Charging Time</label>
                        <input type="text" name="charging_time" class="form-control" value="{{ old('charging_time', $quotation->charging_time ?? '3-4 HR') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Performance</label>
                        <input type="text" name="performance" class="form-control" value="{{ old('performance', $quotation->performance ?? 'HIGH SPEED 25 KM/HR') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Charger Output</label>
                        <input type="text" name="charger_output" class="form-control" value="{{ old('charger_output', $quotation->charger_output ?? 'DC 51V 105 AH (1 LITHIUM BATTERY)') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Motor Output</label>
                        <input type="text" name="motor_output" class="form-control" value="{{ old('motor_output', $quotation->motor_output ?? '1200 W') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Seating Capacity</label>
                        <input type="text" name="seating_capacity" class="form-control" value="{{ old('seating_capacity', $quotation->seating_capacity ?? '5') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Type of Brake</label>
                        <input type="text" name="type_of_break" class="form-control" value="{{ old('type_of_break', $quotation->type_of_break ?? 'DRUM BREAK') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Roof Top ABS Hard Roof</label>
                        <input type="text" name="roof_top_abs" class="form-control" value="{{ old('roof_top_abs', $quotation->roof_top_abs ?? 'YES') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Front Fiber Wind Shield</label>
                        <input type="text" name="front_fiber_wind_shield" class="form-control" value="{{ old('front_fiber_wind_shield', $quotation->front_fiber_wind_shield ?? 'YES') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Meter</label>
                        <input type="text" name="meter_type" class="form-control" value="{{ old('meter_type', $quotation->meter_type ?? 'DIGITAL') }}">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label">Remarks / Notes</label>
                        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $quotation->remarks) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.quotations.show', $quotation) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Quotation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerSelect = document.getElementById('customer_select');
    const vehicleSelect = document.getElementById('vehicle_select');
    const taxRegimeSelect = document.getElementById('tax_regime');
    
    const rateInput = document.getElementById('rate');
    const discountInput = document.getElementById('discount');
    const incentiveInput = document.getElementById('nemmp_incentive');
    const cgstRateInput = document.getElementById('cgst_rate');
    const sgstRateInput = document.getElementById('sgst_rate');
    const igstRateInput = document.getElementById('igst_rate');

    // Customer Selection Change
    $(customerSelect).on('change', function() {
        const option = this.options ? this.options[this.selectedIndex] : null;
        if (option && option.value) {
            document.getElementById('customer_name').value = option.getAttribute('data-name') || '';
            document.getElementById('customer_mobile').value = option.getAttribute('data-mobile') || '';
            document.getElementById('customer_address').value = option.getAttribute('data-address') || '';
            document.getElementById('customer_gstin').value = option.getAttribute('data-gstin') || '';
            document.getElementById('customer_pan').value = option.getAttribute('data-pan') || '';
        }
    });

    // Vehicle Selection Change
    $(vehicleSelect).on('change', function() {
        const option = this.options ? this.options[this.selectedIndex] : null;
        if (option && option.value) {
            const price = parseFloat(option.getAttribute('data-price')) || 0;
            rateInput.value = price;
            calculateTotals();
        }
    });

    // Tax Regime Toggle
    function toggleTaxRegime() {
        const regime = taxRegimeSelect.value;
        const cgstGroup = document.querySelectorAll('.cgst-group');
        const sgstGroup = document.querySelectorAll('.sgst-group');
        const igstGroup = document.querySelectorAll('.igst-group');
        const cgstSummary = document.querySelectorAll('.cgst-summary');
        const sgstSummary = document.querySelectorAll('.sgst-summary');
        const igstSummary = document.querySelectorAll('.igst-summary');

        if (regime === 'igst') {
            cgstGroup.forEach(el => el.classList.add('d-none'));
            sgstGroup.forEach(el => el.classList.add('d-none'));
            igstGroup.forEach(el => el.classList.remove('d-none'));

            cgstSummary.forEach(el => el.classList.add('d-none'));
            sgstSummary.forEach(el => el.classList.add('d-none'));
            igstSummary.forEach(el => el.classList.remove('d-none'));
        } else {
            cgstGroup.forEach(el => el.classList.remove('d-none'));
            sgstGroup.forEach(el => el.classList.remove('d-none'));
            igstGroup.forEach(el => el.classList.add('d-none'));

            cgstSummary.forEach(el => el.classList.remove('d-none'));
            sgstSummary.forEach(el => el.classList.remove('d-none'));
            igstSummary.forEach(el => el.classList.add('d-none'));
        }
        calculateTotals();
    }

    taxRegimeSelect.addEventListener('change', toggleTaxRegime);

    function calculateTotals() {
        const rate = parseFloat(rateInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const incentive = parseFloat(incentiveInput.value) || 0;

        const subTotal = rate;
        let taxable = subTotal - discount - incentive;
        if (taxable < 0) taxable = 0;

        let cgstAmount = 0;
        let sgstAmount = 0;
        let igstAmount = 0;
        let total = taxable;

        if (taxRegimeSelect.value === 'cgst_sgst') {
            const cgstRate = parseFloat(cgstRateInput.value) || 0;
            const sgstRate = parseFloat(sgstRateInput.value) || 0;
            cgstAmount = (taxable * cgstRate) / 100;
            sgstAmount = (taxable * sgstRate) / 100;
            total += cgstAmount + sgstAmount;
        } else {
            const igstRate = parseFloat(igstRateInput.value) || 0;
            igstAmount = (taxable * igstRate) / 100;
            total += igstAmount;
        }

        const grandTotal = Math.round(total);
        const roundOff = grandTotal - total;

        document.getElementById('summary_sub_total').textContent = subTotal.toFixed(2);
        document.getElementById('summary_discount').textContent = discount.toFixed(2);
        document.getElementById('summary_incentive').textContent = incentive.toFixed(2);
        document.getElementById('summary_taxable').textContent = taxable.toFixed(2);
        document.getElementById('summary_cgst').textContent = cgstAmount.toFixed(2);
        document.getElementById('summary_sgst').textContent = sgstAmount.toFixed(2);
        document.getElementById('summary_igst').textContent = igstAmount.toFixed(2);
        document.getElementById('summary_round_off').textContent = roundOff.toFixed(2);
        document.getElementById('summary_grand_total').textContent = grandTotal.toFixed(2);
    }

    [rateInput, discountInput, incentiveInput, cgstRateInput, sgstRateInput, igstRateInput].forEach(input => {
        if (input) {
            input.addEventListener('input', calculateTotals);
        }
    });

    toggleTaxRegime();
    calculateTotals();
});
</script>
@endsection

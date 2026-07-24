@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Edit Vehicle Sales Invoice: {{ $vehicleSalesInvoice->invoice_number }}</h4>
        <div>
            <a href="{{ route('admin.vehicle-sales-invoices.show', $vehicleSalesInvoice) }}" class="btn btn-outline-secondary me-2"><i class="bx bx-show"></i> View Invoice</a>
            <a href="{{ route('admin.vehicle-sales-invoices.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back"></i> Back to List</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.vehicle-sales-invoices.update', $vehicleSalesInvoice) }}" id="invoiceForm">
                @csrf
                @method('PUT')
                
                <h5 class="card-title text-primary mb-3">Customer Information</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Select Customer (Existing)</label>
                        <div class="input-group">
                            <select id="customer_select" name="customer_id" class="form-select">
                                <option value="">-- New Customer / Walk-in --</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}" 
                                        data-name="{{ $c->name }}"
                                        data-mobile="{{ $c->phone }}"
                                        data-address="{{ $c->address }}"
                                        {{ old('customer_id', $vehicleSalesInvoice->customer_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->phone }})
                                </option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#quickAddCustomerModal">
                                <i class="bx bx-plus"></i> Add
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name', $vehicleSalesInvoice->customer_name) }}" required>
                        @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" id="customer_mobile" name="customer_mobile" class="form-control @error('customer_mobile') is-invalid @enderror" value="{{ old('customer_mobile', $vehicleSalesInvoice->customer_mobile) }}">
                        @error('customer_mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Age</label>
                        <input type="number" id="customer_age" name="customer_age" class="form-control @error('customer_age') is-invalid @enderror" value="{{ old('customer_age', $vehicleSalesInvoice->customer_age) }}">
                        @error('customer_age')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Occupation</label>
                        <input type="text" id="customer_occupation" name="customer_occupation" class="form-control @error('customer_occupation') is-invalid @enderror" value="{{ old('customer_occupation', $vehicleSalesInvoice->customer_occupation) }}">
                        @error('customer_occupation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Residence Tel. Ph.</label>
                        <input type="text" id="customer_residence_phone" name="customer_residence_phone" class="form-control @error('customer_residence_phone') is-invalid @enderror" value="{{ old('customer_residence_phone', $vehicleSalesInvoice->customer_residence_phone) }}">
                        @error('customer_residence_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payment Mode</label>
                        <select name="payment_mode" id="payment_mode" class="form-select no-select2">
                            <option value="Cash" {{ old('payment_mode', $vehicleSalesInvoice->payment_mode) === 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="UPI / Online" {{ old('payment_mode', $vehicleSalesInvoice->payment_mode) === 'UPI / Online' ? 'selected' : '' }}>UPI / Online</option>
                            <option value="Card" {{ old('payment_mode', $vehicleSalesInvoice->payment_mode) === 'Card' ? 'selected' : '' }}>Card</option>
                            <option value="Finance" {{ old('payment_mode', $vehicleSalesInvoice->payment_mode) === 'Finance' ? 'selected' : '' }}>Finance/HPN (Hypothecation)</option>
                        </select>
                    </div>
                    <div class="col-md-3 {{ old('payment_mode', $vehicleSalesInvoice->payment_mode) === 'Finance' ? '' : 'd-none' }}" id="finance_name_div">
                        <label class="form-label">Finance Name <span class="text-danger">*</span></label>
                        <select name="finance_name" id="finance_name" class="form-select">
                            <option value="">-- Select Finance --</option>
                            @foreach($financeMasters as $fm)
                            <option value="{{ $fm->name }}" {{ old('finance_name', $vehicleSalesInvoice->finance_name) === $fm->name ? 'selected' : '' }}>{{ $fm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Permanent Address</label>
                        <textarea id="customer_address" name="customer_address" class="form-control @error('customer_address') is-invalid @enderror" rows="2">{{ old('customer_address', $vehicleSalesInvoice->customer_address) }}</textarea>
                        @error('customer_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <h5 class="card-title text-primary mb-3">Vehicle Selection</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label">Select Vehicle <span class="text-danger">*</span></label>
                        <select id="vehicle_select" name="vehicle_inventory_id" class="form-select @error('vehicle_inventory_id') is-invalid @enderror" required>
                            <option value="">-- Choose Vehicle --</option>
                            @foreach($vehicles as $v)
                            <option value="{{ $v->id }}"
                                    data-desc="{{ $v->vehicle_description }}"
                                    data-chassis="{{ $v->chassis_number }}"
                                    data-motor="{{ $v->motor_number }}"
                                    data-battery-no="{{ $v->battery_number }}"
                                    data-charger-no="{{ $v->charger_number }}"
                                    data-controller-no="{{ $v->controller_number }}"
                                    data-convertor-no="{{ $v->convertor_number }}"
                                    data-manual-no="{{ $v->manual_number }}"
                                    data-battery-type="{{ $v->battery_type }}"
                                    data-battery-make="{{ $v->battery_make }}"
                                    data-rate="{{ $v->ex_showroom_price }}"
                                    {{ old('vehicle_inventory_id', $vehicleSalesInvoice->vehicle_inventory_id) == $v->id ? 'selected' : '' }}>
                                {{ $v->vehicle_description }} - Chassis: {{ $v->chassis_number }} {{ $v->id == $vehicleSalesInvoice->vehicle_inventory_id ? '(Currently Selected)' : '' }}
                            </option>
                            @endforeach
                        </select>
                        @error('vehicle_inventory_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Live Preview of Selected Vehicle Details -->
                <div id="vehicle_details_card" class="card mb-4 bg-light border border-light-subtle">
                    <div class="card-body">
                        <h6 class="fw-semibold text-secondary mb-3"><i class="bx bx-car me-1"></i> Selected Vehicle Specifications</h6>
                        <div class="row g-3">
                            <div class="col-md-4"><strong>Model/Description:</strong> <span id="lbl_desc">-</span></div>
                            <div class="col-md-4"><strong>Chassis No:</strong> <span id="lbl_chassis">-</span></div>
                            <div class="col-md-4"><strong>Motor No:</strong> <span id="lbl_motor">-</span></div>
                            <div class="col-md-4"><strong>Battery No:</strong> <span id="lbl_battery_no">-</span></div>
                            <div class="col-md-4"><strong>Charger No:</strong> <span id="lbl_charger_no">-</span></div>
                            <div class="col-md-4"><strong>Controller No:</strong> <span id="lbl_controller_no">-</span></div>
                            <div class="col-md-4"><strong>Convertor No:</strong> <span id="lbl_convertor_no">-</span></div>
                            <div class="col-md-4"><strong>Manual No:</strong> <span id="lbl_manual_no">-</span></div>
                            <div class="col-md-4"><strong>Battery Type:</strong> <span id="lbl_battery_type">-</span></div>
                            <div class="col-md-4"><strong>Battery Make:</strong> <span id="lbl_battery_make">-</span></div>
                        </div>
                    </div>
                </div>

                <h5 class="card-title text-primary mb-3">Invoice & Pricing Details</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Invoice Number <span class="text-danger">*</span></label>
                        <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror" value="{{ old('invoice_number', $vehicleSalesInvoice->invoice_number) }}" required>
                        @error('invoice_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror" value="{{ old('invoice_date', $vehicleSalesInvoice->invoice_date ? $vehicleSalesInvoice->invoice_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                        @error('invoice_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @php
                        $inputRate = $vehicleSalesInvoice->gst_type === 'inclusive' ? ($vehicleSalesInvoice->sub_total * 1.05) : $vehicleSalesInvoice->sub_total;
                        // Or if total is already calculated:
                        if ($vehicleSalesInvoice->gst_type === 'exclusive') {
                            $inputRate = $vehicleSalesInvoice->rate;
                        } else {
                            $inputRate = $vehicleSalesInvoice->total;
                        }
                    @endphp
                    <div class="col-md-3">
                        <label class="form-label">Rate / Ex-Showroom Price (INR) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" id="rate" name="rate" class="form-control @error('rate') is-invalid @enderror" value="{{ old('rate', $inputRate) }}" required>
                        @error('rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">GST Type <span class="text-danger">*</span></label>
                        <select name="gst_type" id="gst_type" class="form-select no-select2" required>
                            <option value="exclusive" {{ old('gst_type', $vehicleSalesInvoice->cgst_amount > 0 && $vehicleSalesInvoice->sub_total == $vehicleSalesInvoice->rate ? 'exclusive' : ($vehicleSalesInvoice->sub_total != $vehicleSalesInvoice->total ? 'exclusive' : 'inclusive')) === 'exclusive' ? 'selected' : '' }}>GST Extra (Exclusive)</option>
                            <option value="inclusive" {{ old('gst_type', $vehicleSalesInvoice->sub_total != $vehicleSalesInvoice->rate ? 'inclusive' : 'exclusive') === 'inclusive' ? 'selected' : '' }}>GST Included (Inclusive)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tax Regime <span class="text-danger">*</span></label>
                        <select name="tax_regime" id="tax_regime" class="form-select no-select2" required>
                            <option value="cgst_sgst" {{ old('tax_regime', $vehicleSalesInvoice->tax_regime ?? 'cgst_sgst') === 'cgst_sgst' ? 'selected' : '' }}>CGST + SGST</option>
                            <option value="igst" {{ old('tax_regime', $vehicleSalesInvoice->tax_regime) === 'igst' ? 'selected' : '' }}>IGST</option>
                        </select>
                    </div>
                </div>

                <!-- Tax Breakdown Card -->
                <div class="card mb-4 bg-light border border-light-subtle">
                    <div class="card-body">
                        <h6 class="fw-semibold text-secondary mb-3"><i class="bx bx-calculator me-1"></i> Tax & Total Preview</h6>
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label text-muted">Sub Total (Taxable)</label>
                                <input type="text" id="sub_total" class="form-control bg-white" readonly value="{{ number_format($vehicleSalesInvoice->sub_total, 2, '.', '') }}">
                            </div>
                            <div class="col-md-2 tax-cgst-sgst">
                                <label class="form-label text-muted">CGST (2.5%)</label>
                                <input type="text" id="cgst_amount" class="form-control bg-white" readonly value="{{ number_format($vehicleSalesInvoice->cgst_amount, 2, '.', '') }}">
                            </div>
                            <div class="col-md-2 tax-cgst-sgst">
                                <label class="form-label text-muted">SGST (2.5%)</label>
                                <input type="text" id="sgst_amount" class="form-control bg-white" readonly value="{{ number_format($vehicleSalesInvoice->sgst_amount, 2, '.', '') }}">
                            </div>
                            <div class="col-md-4 tax-igst d-none">
                                <label class="form-label text-muted">IGST (5%)</label>
                                <input type="text" id="igst_amount" class="form-control bg-white" readonly value="{{ number_format($vehicleSalesInvoice->igst_amount, 2, '.', '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted">Total (with GST)</label>
                                <input type="text" id="total_price" class="form-control bg-white fw-bold text-success" readonly value="{{ number_format($vehicleSalesInvoice->total, 2, '.', '') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="card-title text-primary mb-3">Deductions & Grand Total</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">NEMMP Incentive (INR)</label>
                        <input type="number" step="0.01" id="nemmp_incentive" name="nemmp_incentive" class="form-control @error('nemmp_incentive') is-invalid @enderror" value="{{ old('nemmp_incentive', $vehicleSalesInvoice->nemmp_incentive) }}">
                        @error('nemmp_incentive')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Discount / Special Subsidy (INR)</label>
                        <input type="number" step="0.01" id="discount" name="discount" class="form-control @error('discount') is-invalid @enderror" value="{{ old('discount', $vehicleSalesInvoice->discount) }}">
                        @error('discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-dark">Grand Total (INR)</label>
                        <input type="text" id="grand_total" class="form-control bg-white fw-bold fs-5 text-primary" readonly value="{{ number_format($vehicleSalesInvoice->grand_total, 2, '.', '') }}">
                    </div>
                </div>

                <h5 class="card-title text-primary mb-3">Payment & Ledger Settlement</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Previous Balance (INR)</label>
                        <input type="number" step="0.01" id="previous_balance" name="previous_balance" class="form-control @error('previous_balance') is-invalid @enderror" value="{{ old('previous_balance', $vehicleSalesInvoice->previous_balance) }}">
                        @error('previous_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Received Amount (INR)</label>
                        <input type="number" step="0.01" id="received_amount" name="received_amount" class="form-control @error('received_amount') is-invalid @enderror" value="{{ old('received_amount', $vehicleSalesInvoice->received_amount) }}">
                        @error('received_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Current Invoice Balance</label>
                        <input type="text" id="payment_balance" class="form-control bg-white fw-bold text-danger" readonly value="{{ number_format($vehicleSalesInvoice->balance, 2, '.', '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Net Total Balance</label>
                        <input type="text" id="current_balance" class="form-control bg-white fw-bold text-dark" readonly value="{{ number_format($vehicleSalesInvoice->current_balance, 2, '.', '') }}">
                    </div>
                </div>

                <h5 class="card-title text-primary mb-3">Warranty & Guarantee Notes</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label">Warranty Notes / Terms & Conditions</label>
                        <textarea name="warranty_notes" class="form-control" rows="3">{{ old('warranty_notes', $vehicleSalesInvoice->warranty_notes) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.vehicle-sales-invoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bx bx-save me-1"></i> Update Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Quick Add Customer -->
<div class="modal fade" id="quickAddCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAddCustomerForm">
                <div class="modal-body">
                    <div id="modalErrorAlert" class="alert alert-danger d-none" role="alert"></div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveCustomer">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var customerSelect = document.getElementById('customer_select');
    var customerName = document.getElementById('customer_name');
    var customerMobile = document.getElementById('customer_mobile');
    var customerAddress = document.getElementById('customer_address');

    customerSelect.addEventListener('change', function () {
        var selected = this.options[this.selectedIndex];
        if (this.value) {
            customerName.value = selected.getAttribute('data-name') || '';
            customerMobile.value = selected.getAttribute('data-mobile') || '';
            customerAddress.value = selected.getAttribute('data-address') || '';
        }
    });

    var paymentModeSelect = document.getElementById('payment_mode');
    var financeDiv = document.getElementById('finance_name_div');
    var financeSelect = document.getElementById('finance_name');

    paymentModeSelect.addEventListener('change', function () {
        if (this.value === 'Finance') {
            financeDiv.classList.remove('d-none');
            financeSelect.required = true;
        } else {
            financeDiv.classList.add('d-none');
            financeSelect.required = false;
            financeSelect.value = '';
        }
    });

    var vehicleSelect = document.getElementById('vehicle_select');
    var vehicleCard = document.getElementById('vehicle_details_card');
    var rateInp = document.getElementById('rate');

    function updateVehicleDetails() {
        var opt = vehicleSelect.options[vehicleSelect.selectedIndex];
        if (vehicleSelect.value) {
            document.getElementById('lbl_desc').textContent = opt.getAttribute('data-desc') || '-';
            document.getElementById('lbl_chassis').textContent = opt.getAttribute('data-chassis') || '-';
            document.getElementById('lbl_motor').textContent = opt.getAttribute('data-motor') || '-';
            document.getElementById('lbl_battery_no').textContent = opt.getAttribute('data-battery-no') || '-';
            document.getElementById('lbl_charger_no').textContent = opt.getAttribute('data-charger-no') || '-';
            document.getElementById('lbl_controller_no').textContent = opt.getAttribute('data-controller-no') || '-';
            document.getElementById('lbl_convertor_no').textContent = opt.getAttribute('data-convertor-no') || '-';
            document.getElementById('lbl_manual_no').textContent = opt.getAttribute('data-manual-no') || '-';
            document.getElementById('lbl_battery_type').textContent = opt.getAttribute('data-battery-type') || '-';
            document.getElementById('lbl_battery_make').textContent = opt.getAttribute('data-battery-make') || '-';
            vehicleCard.classList.remove('d-none');
        }
    }

    vehicleSelect.addEventListener('change', function () {
        updateVehicleDetails();
        var opt = this.options[this.selectedIndex];
        if (this.value && opt.getAttribute('data-rate')) {
            rateInp.value = parseFloat(opt.getAttribute('data-rate')).toFixed(2);
            calculateInvoice();
        }
    });

    if (vehicleSelect.value) {
        updateVehicleDetails();
    }

    var gstTypeSelect = document.getElementById('gst_type');
    var taxRegimeSelect = document.getElementById('tax_regime');
    var subTotalOut = document.getElementById('sub_total');
    var cgstAmountOut = document.getElementById('cgst_amount');
    var sgstAmountOut = document.getElementById('sgst_amount');
    var igstAmountOut = document.getElementById('igst_amount');
    var totalPriceOut = document.getElementById('total_price');
    var nemmpInp = document.getElementById('nemmp_incentive');
    var discountInp = document.getElementById('discount');
    var grandTotalOut = document.getElementById('grand_total');

    function calculateInvoice() {
        var rateVal = parseFloat(rateInp.value) || 0;
        var gstType = gstTypeSelect.value;
        var taxRegime = taxRegimeSelect.value;

        var cgstRate = 2.50;
        var sgstRate = 2.50;
        var igstRate = 5.00;

        var subTotal = 0, cgstAmt = 0, sgstAmt = 0, igstAmt = 0, totalVal = 0;

        if (gstType === 'inclusive') {
            subTotal = Math.round((rateVal / 1.05) * 100) / 100;
            if (taxRegime === 'igst') {
                cgstAmt = 0;
                sgstAmt = 0;
                igstAmt = Math.round(((subTotal * igstRate) / 100) * 100) / 100;
            } else {
                cgstAmt = Math.round(((subTotal * cgstRate) / 100) * 100) / 100;
                sgstAmt = Math.round(((subTotal * sgstRate) / 100) * 100) / 100;
                igstAmt = 0;
            }
            totalVal = rateVal;
        } else {
            subTotal = rateVal;
            if (taxRegime === 'igst') {
                cgstAmt = 0;
                sgstAmt = 0;
                igstAmt = Math.round(((subTotal * igstRate) / 100) * 100) / 100;
            } else {
                cgstAmt = Math.round(((subTotal * cgstRate) / 100) * 100) / 100;
                sgstAmt = Math.round(((subTotal * sgstRate) / 100) * 100) / 100;
                igstAmt = 0;
            }
            totalVal = subTotal + cgstAmt + sgstAmt + igstAmt;
        }

        subTotalOut.value = subTotal.toFixed(2);
        cgstAmountOut.value = cgstAmt.toFixed(2);
        sgstAmountOut.value = sgstAmt.toFixed(2);
        igstAmountOut.value = igstAmt.toFixed(2);
        totalPriceOut.value = totalVal.toFixed(2);

        var nemmpVal = parseFloat(nemmpInp.value) || 0;
        var discountVal = parseFloat(discountInp.value) || 0;
        var grandTotal = totalVal - nemmpVal - discountVal;
        grandTotalOut.value = grandTotal.toFixed(2);

        calculatePayment();
    }

    rateInp.addEventListener('input', calculateInvoice);
    gstTypeSelect.addEventListener('change', calculateInvoice);
    
    taxRegimeSelect.addEventListener('change', function () {
        var taxCgstSgstElements = document.querySelectorAll('.tax-cgst-sgst');
        var taxIgstElements = document.querySelectorAll('.tax-igst');

        if (this.value === 'igst') {
            taxCgstSgstElements.forEach(el => el.classList.add('d-none'));
            taxIgstElements.forEach(el => el.classList.remove('d-none'));
        } else {
            taxCgstSgstElements.forEach(el => el.classList.remove('d-none'));
            taxIgstElements.forEach(el => el.classList.add('d-none'));
        }
        calculateInvoice();
    });

    if (taxRegimeSelect.value === 'igst') {
        document.querySelectorAll('.tax-cgst-sgst').forEach(el => el.classList.add('d-none'));
        document.querySelectorAll('.tax-igst').forEach(el => el.classList.remove('d-none'));
    }

    nemmpInp.addEventListener('input', calculateInvoice);
    discountInp.addEventListener('input', calculateInvoice);

    var prevBalanceInp = document.getElementById('previous_balance');
    var receivedInp = document.getElementById('received_amount');
    var paymentBalanceOut = document.getElementById('payment_balance');
    var currentBalanceOut = document.getElementById('current_balance');

    function calculatePayment() {
        var grand = parseFloat(grandTotalOut.value) || 0;
        var prev = parseFloat(prevBalanceInp.value) || 0;
        var rec = parseFloat(receivedInp.value) || 0;
        var bal = grand - rec;
        var curBal = prev + bal;
        paymentBalanceOut.value = bal.toFixed(2);
        currentBalanceOut.value = curBal.toFixed(2);
    }

    prevBalanceInp.addEventListener('input', calculatePayment);
    receivedInp.addEventListener('input', calculatePayment);

    calculateInvoice();

    // Quick Add Customer Handler
    var quickAddForm = document.getElementById('quickAddCustomerForm');
    var modalErrorAlert = document.getElementById('modalErrorAlert');
    var saveCustomerBtn = document.getElementById('btnSaveCustomer');
    
    quickAddForm.addEventListener('submit', function(e) {
        e.preventDefault();
        saveCustomerBtn.disabled = true;
        saveCustomerBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
        modalErrorAlert.classList.add('d-none');
        
        var formData = new FormData(this);
        
        fetch('{{ route("admin.customers.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(res => {
            saveCustomerBtn.disabled = false;
            saveCustomerBtn.innerHTML = 'Save Customer';
            
            if (res.status === 200 || res.status === 201) {
                var customer = res.body.customer;
                var fullName = customer.name;
                
                var option = document.createElement('option');
                option.value = customer.id;
                option.text = fullName + ' (' + customer.phone + ')';
                option.setAttribute('data-name', fullName);
                option.setAttribute('data-mobile', customer.phone);
                option.setAttribute('data-address', customer.address || '');
                
                customerSelect.appendChild(option);
                customerSelect.value = customer.id;
                $(customerSelect).trigger('change.select2');
                
                var event = new Event('change');
                customerSelect.dispatchEvent(event);
                
                var modalEl = document.getElementById('quickAddCustomerModal');
                var modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modalInstance.hide();
                quickAddForm.reset();
            } else {
                var errorMsg = 'Error saving customer.';
                if (res.body.errors) {
                    errorMsg = Object.values(res.body.errors).flat().join('<br>');
                } else if (res.body.message) {
                    errorMsg = res.body.message;
                }
                modalErrorAlert.innerHTML = errorMsg;
                modalErrorAlert.classList.remove('d-none');
            }
        })
        .catch(err => {
            saveCustomerBtn.disabled = false;
            saveCustomerBtn.innerHTML = 'Save Customer';
            modalErrorAlert.textContent = 'Server connection error.';
            modalErrorAlert.classList.remove('d-none');
            console.error(err);
        });
    });
});
</script>
@endsection

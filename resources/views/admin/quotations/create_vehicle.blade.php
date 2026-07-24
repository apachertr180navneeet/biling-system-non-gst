@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-4">Create Vehicle Quotation</h4>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.quotations.store') }}" id="quotationForm">
                @csrf
                <input type="hidden" name="type" value="vehicle">
                
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
                                        data-gstin="{{ $c->gstin }}"
                                        data-pan="{{ $c->pan_no }}">
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
                        <input type="text" id="customer_name" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" id="customer_mobile" name="customer_mobile" class="form-control" value="{{ old('customer_mobile') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">GSTIN (Optional)</label>
                        <input type="text" id="customer_gstin" name="customer_gstin" class="form-control" value="{{ old('customer_gstin') }}" placeholder="15-digit GSTIN" maxlength="15">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">PAN Number (Optional)</label>
                        <input type="text" id="customer_pan" name="customer_pan" class="form-control" value="{{ old('customer_pan') }}" placeholder="10-digit PAN" maxlength="10">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Place of Supply <span class="text-danger">*</span></label>
                        <input type="text" id="place_of_supply" name="place_of_supply" class="form-control" value="{{ old('place_of_supply', 'Rajasthan') }}" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Billing Address</label>
                        <textarea id="customer_address" name="customer_address" class="form-control" rows="2">{{ old('customer_address') }}</textarea>
                    </div>
                </div>

                <h5 class="card-title text-primary mb-3">Quotation Details</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Quotation Date <span class="text-danger">*</span></label>
                        <input type="date" name="quotation_date" class="form-control" value="{{ old('quotation_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tax Regime <span class="text-danger">*</span></label>
                        <select name="tax_regime" id="tax_regime" class="form-select no-select2" required>
                            <option value="cgst_sgst" {{ old('tax_regime') === 'cgst_sgst' ? 'selected' : '' }}>CGST + SGST</option>
                            <option value="igst" {{ old('tax_regime') === 'igst' ? 'selected' : '' }}>IGST</option>
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
                            <option value="{{ $v->id }}" data-price="{{ $v->ex_showroom_price }}">
                                {{ $v->variant_name }} ({{ $v->color_name }} - {{ $v->fuel_type }})
                            </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted fw-bold mt-1"><i class="bx bx-info-circle"></i> On Road Price includes GST, RTO, & Insurance.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ex-Showroom Price / Rate <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" id="rate" name="rate" class="form-control" value="{{ old('rate', 0) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Discount (₹)</label>
                        <input type="number" step="0.01" id="discount" name="discount" class="form-control" value="{{ old('discount', 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">NEMMP/Incentive (₹)</label>
                        <input type="number" step="0.01" id="nemmp_incentive" name="nemmp_incentive" class="form-control" value="{{ old('nemmp_incentive', 0) }}">
                    </div>
                    
                    <div class="col-md-3 cgst-group">
                        <label class="form-label">CGST Rate (%)</label>
                        <input type="number" step="0.01" id="cgst_rate" name="cgst_rate" class="form-control" value="{{ old('cgst_rate', 2.5) }}">
                    </div>
                    <div class="col-md-3 sgst-group">
                        <label class="form-label">SGST Rate (%)</label>
                        <input type="number" step="0.01" id="sgst_rate" name="sgst_rate" class="form-control" value="{{ old('sgst_rate', 2.5) }}">
                    </div>
                    <div class="col-md-3 igst-group d-none">
                        <label class="form-label">IGST Rate (%)</label>
                        <input type="number" step="0.01" id="igst_rate" name="igst_rate" class="form-control" value="{{ old('igst_rate', 5) }}">
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
                        <input type="text" name="model_maker_name" class="form-control" value="{{ old('model_maker_name', 'E- PASSENGER/ARZOO/PASSANGER') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gross Weight</label>
                        <input type="text" name="gross_weight" class="form-control" value="{{ old('gross_weight', '60 KG') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Charging Time</label>
                        <input type="text" name="charging_time" class="form-control" value="{{ old('charging_time', '3-4 HR') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Performance</label>
                        <input type="text" name="performance" class="form-control" value="{{ old('performance', 'HIGH SPEED 25 KM/HR') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Charger Output</label>
                        <input type="text" name="charger_output" class="form-control" value="{{ old('charger_output', 'DC 51V 105 AH (1 LITHIUM BATTERY)') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Motor Output</label>
                        <input type="text" name="motor_output" class="form-control" value="{{ old('motor_output', '1200 W') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Seating Capacity</label>
                        <input type="text" name="seating_capacity" class="form-control" value="{{ old('seating_capacity', '5') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Type of Brake</label>
                        <input type="text" name="type_of_break" class="form-control" value="{{ old('type_of_break', 'DRUM BREAK') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Roof Top ABS Hard Roof</label>
                        <input type="text" name="roof_top_abs" class="form-control" value="{{ old('roof_top_abs', 'YES') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Front Fiber Wind Shield</label>
                        <input type="text" name="front_fiber_wind_shield" class="form-control" value="{{ old('front_fiber_wind_shield', 'YES') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Meter</label>
                        <input type="text" name="meter_type" class="form-control" value="{{ old('meter_type', 'DIGITAL') }}">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label">Remarks / Notes</label>
                        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Quotation</button>
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
        } else {
            document.getElementById('customer_name').value = '';
            document.getElementById('customer_mobile').value = '';
            document.getElementById('customer_address').value = '';
            document.getElementById('customer_gstin').value = '';
            document.getElementById('customer_pan').value = '';
        }
    });

    // Vehicle Selection Change
    $(vehicleSelect).on('change', function() {
        const option = this.options ? this.options[this.selectedIndex] : null;
        if (option && option.value) {
            rateInput.value = option.getAttribute('data-price') || 0;
        } else {
            rateInput.value = 0;
        }
        calculateTotals();
    });

    // Tax Regime Change
    taxRegimeSelect.addEventListener('change', function() {
        const regime = this.value;
        if (regime === 'cgst_sgst') {
            document.querySelectorAll('.cgst-group, .sgst-group, .cgst-summary, .sgst-summary').forEach(el => el.classList.remove('d-none'));
            document.querySelectorAll('.igst-group, .igst-summary').forEach(el => el.classList.add('d-none'));
        } else {
            document.querySelectorAll('.cgst-group, .sgst-group, .cgst-summary, .sgst-summary').forEach(el => el.classList.add('d-none'));
            document.querySelectorAll('.igst-group, .igst-summary').forEach(el => el.classList.remove('d-none'));
        }
        calculateTotals();
    });

    // Inputs Input change
    [rateInput, discountInput, incentiveInput, cgstRateInput, sgstRateInput, igstRateInput].forEach(input => {
        input.addEventListener('input', calculateTotals);
    });

    function calculateTotals() {
        const rate = parseFloat(rateInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const incentive = parseFloat(incentiveInput.value) || 0;
        
        const sub_total = rate;
        let taxable = sub_total - discount - incentive;
        if (taxable < 0) taxable = 0;

        const regime = taxRegimeSelect.value;
        let taxTotal = 0;
        
        let cgstAmount = 0;
        let sgstAmount = 0;
        let igstAmount = 0;

        if (regime === 'cgst_sgst') {
            const cgstRate = parseFloat(cgstRateInput.value) || 0;
            const sgstRate = parseFloat(sgstRateInput.value) || 0;
            cgstAmount = (taxable * cgstRate) / 100;
            sgstAmount = (taxable * sgstRate) / 100;
            taxTotal = cgstAmount + sgstAmount;
        } else {
            const igstRate = parseFloat(igstRateInput.value) || 0;
            igstAmount = (taxable * igstRate) / 100;
            taxTotal = igstAmount;
        }

        const total = taxable + taxTotal;
        const grandTotal = Math.round(total);
        const roundOff = grandTotal - total;

        document.getElementById('summary_sub_total').innerText = sub_total.toFixed(2);
        document.getElementById('summary_discount').innerText = discount.toFixed(2);
        document.getElementById('summary_incentive').innerText = incentive.toFixed(2);
        document.getElementById('summary_taxable').innerText = taxable.toFixed(2);
        document.getElementById('summary_cgst').innerText = cgstAmount.toFixed(2);
        document.getElementById('summary_sgst').innerText = sgstAmount.toFixed(2);
        document.getElementById('summary_igst').innerText = igstAmount.toFixed(2);
        document.getElementById('summary_round_off').innerText = roundOff.toFixed(2);
        document.getElementById('summary_grand_total').innerText = grandTotal.toFixed(2);
    }

    // AJAX Quick Add Customer Form Handler
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
                
                // Add new customer to select dropdown list
                var option = document.createElement('option');
                option.value = customer.id;
                option.text = fullName + ' (' + customer.phone + ')';
                option.setAttribute('data-name', fullName);
                option.setAttribute('data-mobile', customer.phone);
                option.setAttribute('data-address', customer.address || '');
                option.setAttribute('data-gstin', customer.gstin || '');
                option.setAttribute('data-pan', customer.pan_no || '');
                
                customerSelect.appendChild(option);
                customerSelect.value = customer.id;
                $(customerSelect).trigger('change.select2');
                
                // Trigger change event to populate input fields
                $(customerSelect).trigger('change');
                
                // Close modal
                var modalEl = document.getElementById('quickAddCustomerModal');
                var modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(modalEl);
                }
                modalInstance.hide();
                
                // Reset form
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

<!-- Quick Add Customer Modal -->
<div class="modal fade" id="quickAddCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="quickAddCustomerForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quick Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="modalErrorAlert"></div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="modal_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="phone" id="modal_phone" class="form-control" maxlength="10" placeholder="10 digits">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <select name="type" id="modal_type" class="form-select no-select2">
                                <option value="individual">Individual</option>
                                <option value="corporate">Corporate</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">GSTIN (Optional)</label>
                            <input type="text" name="gstin" id="modal_gstin" class="form-control" maxlength="15">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">PAN No (Optional)</label>
                            <input type="text" name="pan_no" id="modal_pan_no" class="form-control" maxlength="10">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Billing Address</label>
                            <textarea name="address" id="modal_address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveCustomer">Save Customer</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

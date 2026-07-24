@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-4">Create Vehicle Sales Invoice</h4>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.vehicle-sales-invoices.store') }}" id="invoiceForm">
                @csrf
                
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
                                        data-address="{{ $c->address }}">
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
                        <input type="text" id="customer_name" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" required>
                        @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" id="customer_mobile" name="customer_mobile" class="form-control @error('customer_mobile') is-invalid @enderror" value="{{ old('customer_mobile') }}">
                        @error('customer_mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Age</label>
                        <input type="number" id="customer_age" name="customer_age" class="form-control @error('customer_age') is-invalid @enderror" value="{{ old('customer_age') }}">
                        @error('customer_age')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Occupation</label>
                        <input type="text" id="customer_occupation" name="customer_occupation" class="form-control @error('customer_occupation') is-invalid @enderror" value="{{ old('customer_occupation') }}">
                        @error('customer_occupation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Residence Tel. Ph.</label>
                        <input type="text" id="customer_residence_phone" name="customer_residence_phone" class="form-control @error('customer_residence_phone') is-invalid @enderror" value="{{ old('customer_residence_phone') }}">
                        @error('customer_residence_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payment Mode</label>
                        <select name="payment_mode" id="payment_mode" class="form-select no-select2">
                            <option value="Cash">Cash</option>
                            <option value="UPI / Online">UPI / Online</option>
                            <option value="Card">Card</option>
                            <option value="Finance">Finance/HPN (Hypothecation)</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-none" id="finance_name_div">
                        <label class="form-label">Finance Name <span class="text-danger">*</span></label>
                        <select name="finance_name" id="finance_name" class="form-select">
                            <option value="">-- Select Finance --</option>
                            @foreach($financeMasters as $fm)
                            <option value="{{ $fm->name }}" {{ old('finance_name') === $fm->name ? 'selected' : '' }}>{{ $fm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Permanent Address</label>
                        <textarea id="customer_address" name="customer_address" class="form-control @error('customer_address') is-invalid @enderror" rows="2">{{ old('customer_address') }}</textarea>
                        @error('customer_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <h5 class="card-title text-primary mb-3">Vehicle Selection</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label">Select Available Vehicle <span class="text-danger">*</span></label>
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
                                    data-rate="{{ $v->ex_showroom_price }}">
                                {{ $v->vehicle_description }} - Chassis: {{ $v->chassis_number }}
                            </option>
                            @endforeach
                        </select>
                        @error('vehicle_inventory_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Live Preview of Selected Vehicle Details -->
                <div id="vehicle_details_card" class="card mb-4 bg-light d-none border border-light-subtle">
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
                        <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror" value="{{ old('invoice_number', $nextInvoiceNumber ?? '') }}" required>
                        @error('invoice_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                        @error('invoice_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rate / Ex-Showroom Price (INR) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" id="rate" name="rate" class="form-control @error('rate') is-invalid @enderror" value="{{ old('rate', 0) }}" required>
                        @error('rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">GST Type <span class="text-danger">*</span></label>
                        <select name="gst_type" id="gst_type" class="form-select no-select2" required>
                            <option value="exclusive" {{ old('gst_type') === 'exclusive' ? 'selected' : '' }}>GST Extra (Exclusive)</option>
                            <option value="inclusive" {{ old('gst_type') === 'inclusive' ? 'selected' : '' }}>GST Included (Inclusive)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tax Regime <span class="text-danger">*</span></label>
                        <select name="tax_regime" id="tax_regime" class="form-select no-select2" required>
                            <option value="cgst_sgst" {{ old('tax_regime', 'cgst_sgst') === 'cgst_sgst' ? 'selected' : '' }}>CGST + SGST</option>
                            <option value="igst" {{ old('tax_regime') === 'igst' ? 'selected' : '' }}>IGST</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="cgst_sgst_fields">
                        <label class="form-label">SGST @ 2.5% (Calculated)</label>
                        <input type="text" id="sgst" class="form-control bg-light" readonly value="0.00">
                    </div>
                    <div class="col-md-3" id="cgst_sgst_fields2">
                        <label class="form-label">CGST @ 2.5% (Calculated)</label>
                        <input type="text" id="cgst" class="form-control bg-light" readonly value="0.00">
                    </div>
                    <div class="col-md-3 d-none" id="igst_field">
                        <label class="form-label">IGST @ 5% (Calculated)</label>
                        <input type="text" id="igst" class="form-control bg-light" readonly value="0.00">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Subtotal Incl. GST</label>
                        <input type="text" id="subtotal_incl_gst" class="form-control bg-light" readonly value="0.00">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Less: NEMMP 2020 Incentive</label>
                        <input type="number" step="0.01" id="nemmp_incentive" name="nemmp_incentive" class="form-control" value="0.00">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Less: Discount/Offer</label>
                        <input type="number" step="0.01" id="discount" name="discount" class="form-control" value="0.00">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Grand Total (INR)</label>
                        <input type="text" id="grand_total" class="form-control bg-light fw-bold text-success" readonly value="0.00">
                    </div>
                </div>

                <h5 class="card-title text-primary mb-3">Payment Summary</h5>
                <div class="row g-3 mb-4 bg-light p-3 rounded border border-light-subtle">
                    <div class="col-md-3">
                        <label class="form-label">Previous Balance (INR)</label>
                        <input type="number" step="0.01" name="previous_balance" id="previous_balance" class="form-control" value="0.00" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Received Amount (INR)</label>
                        <input type="number" step="0.01" name="received_amount" id="received_amount" class="form-control fw-bold" value="0.00" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Balance (INR)</label>
                        <input type="text" id="payment_balance" class="form-control bg-white" readonly value="0.00">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-danger">Current Balance (INR)</label>
                        <input type="text" id="current_balance" class="form-control bg-white fw-bold text-danger" readonly value="0.00">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Warranty Terms / Notes</label>
                    <textarea name="warranty_notes" class="form-control" rows="3">MOTOR, CONTROLLER WARRANTY - 1 YEAR
BATTERY WARRANTY - 3 YEAR
CHARGER WARRANTY - 2 YEAR</textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check"></i> Generate Invoice</button>
                    <a href="{{ route('admin.vehicle-sales-invoices.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

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
                        <div class="col-md-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="modal_email" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
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

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var customerSelect = document.getElementById('customer_select');
    var customerNameInput = document.getElementById('customer_name');
    var customerMobileInput = document.getElementById('customer_mobile');
    var customerAddressInput = document.getElementById('customer_address');

    var paymentModeSelect = document.getElementById('payment_mode');
    var financeNameDiv = document.getElementById('finance_name_div');
    var financeNameSelect = document.getElementById('finance_name');

    paymentModeSelect.addEventListener('change', function() {
        if (this.value === 'Finance') {
            financeNameDiv.classList.remove('d-none');
            financeNameSelect.setAttribute('required', 'required');
        } else {
            financeNameDiv.classList.add('d-none');
            financeNameSelect.removeAttribute('required');
            financeNameSelect.value = '';
        }
    });

    if (paymentModeSelect.value === 'Finance') {
        financeNameDiv.classList.remove('d-none');
        financeNameSelect.setAttribute('required', 'required');
    }
    
    $(customerSelect).on('change', function() {
        var opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            customerNameInput.value = opt.getAttribute('data-name') || '';
            customerMobileInput.value = opt.getAttribute('data-mobile') || '';
            customerAddressInput.value = opt.getAttribute('data-address') || '';
        } else {
            customerNameInput.value = '';
            customerMobileInput.value = '';
            customerAddressInput.value = '';
        }
    });

    var vehicleSelect = document.getElementById('vehicle_select');
    var detailsCard = document.getElementById('vehicle_details_card');
    
    var lblDesc = document.getElementById('lbl_desc');
    var lblChassis = document.getElementById('lbl_chassis');
    var lblMotor = document.getElementById('lbl_motor');
    var lblBatteryNo = document.getElementById('lbl_battery_no');
    var lblChargerNo = document.getElementById('lbl_charger_no');
    var lblControllerNo = document.getElementById('lbl_controller_no');
    var lblConvertorNo = document.getElementById('lbl_convertor_no');
    var lblManualNo = document.getElementById('lbl_manual_no');
    var lblBatteryType = document.getElementById('lbl_battery_type');
    var lblBatteryMake = document.getElementById('lbl_battery_make');
    var rateInput = document.getElementById('rate');

    $(vehicleSelect).on('change', function() {
        var opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            lblDesc.textContent = opt.getAttribute('data-desc') || '-';
            lblChassis.textContent = opt.getAttribute('data-chassis') || '-';
            lblMotor.textContent = opt.getAttribute('data-motor') || '-';
            lblBatteryNo.textContent = opt.getAttribute('data-battery-no') || '-';
            lblChargerNo.textContent = opt.getAttribute('data-charger-no') || '-';
            lblControllerNo.textContent = opt.getAttribute('data-controller-no') || '-';
            lblConvertorNo.textContent = opt.getAttribute('data-convertor-no') || '-';
            lblManualNo.textContent = opt.getAttribute('data-manual-no') || '-';
            lblBatteryType.textContent = opt.getAttribute('data-battery-type') || '-';
            lblBatteryMake.textContent = opt.getAttribute('data-battery-make') || '-';
            rateInput.value = opt.getAttribute('data-rate') || '0';
            rateInput.dataset.enteredRate = rateInput.value;
            
            detailsCard.classList.remove('d-none');
        } else {
            detailsCard.classList.add('d-none');
            rateInput.value = '0';
            rateInput.dataset.enteredRate = '0';
        }
        calculateInvoice();
    });

    var rateInp = document.getElementById('rate');
    var gstTypeSelect = document.getElementById('gst_type');
    var taxRegimeSelect = document.getElementById('tax_regime');
    var nemmpInp = document.getElementById('nemmp_incentive');
    var discountInp = document.getElementById('discount');
    
    var sgstOut = document.getElementById('sgst');
    var cgstOut = document.getElementById('cgst');
    var igstOut = document.getElementById('igst');
    var subtotalOut = document.getElementById('subtotal_incl_gst');
    var grandTotalOut = document.getElementById('grand_total');

    function toggleRegimeFields() {
        var isIgst = taxRegimeSelect.value === 'igst';
        document.getElementById('cgst_sgst_fields').classList.toggle('d-none', isIgst);
        document.getElementById('cgst_sgst_fields2').classList.toggle('d-none', isIgst);
        document.getElementById('igst_field').classList.toggle('d-none', !isIgst);
    }

    taxRegimeSelect.addEventListener('change', function() {
        toggleRegimeFields();
        calculateInvoice();
    });

    function calculateInvoice() {
        var gstType = gstTypeSelect.value;
        var taxRegime = taxRegimeSelect.value;
        var enteredRate = parseFloat(rateInp.dataset.enteredRate) || parseFloat(rateInp.value) || 0;
        var subtotal = 0;
        var cgst = 0;
        var sgst = 0;
        var igst = 0;

        if (gstType === 'inclusive') {
            var baseRate = enteredRate / 1.05;
            if (taxRegime === 'igst') {
                igst = Math.round(baseRate * 5) / 100;
            } else {
                cgst = Math.round(baseRate * 2.5) / 100;
                sgst = Math.round(baseRate * 2.5) / 100;
            }
            subtotal = enteredRate;
        } else {
            if (taxRegime === 'igst') {
                igst = Math.round(enteredRate * 5) / 100;
            } else {
                cgst = Math.round(enteredRate * 2.5) / 100;
                sgst = Math.round(enteredRate * 2.5) / 100;
            }
            subtotal = enteredRate + cgst + sgst + igst;
        }
        
        var nemmp = parseFloat(nemmpInp.value) || 0;
        var discount = parseFloat(discountInp.value) || 0;
        var grand = subtotal - nemmp - discount;

        sgstOut.value = sgst.toFixed(2);
        cgstOut.value = cgst.toFixed(2);
        igstOut.value = igst.toFixed(2);
        subtotalOut.value = subtotal.toFixed(2);
        grandTotalOut.value = grand.toFixed(2);
    }

    function convertInclusiveToExclusive() {
        var gstType = gstTypeSelect.value;
        var enteredRate = parseFloat(rateInp.dataset.enteredRate) || parseFloat(rateInp.value) || 0;

        if (gstType === 'inclusive') {
            var baseRate = enteredRate / 1.05;
            rateInp.value = baseRate.toFixed(2);
        } else {
            rateInp.value = enteredRate.toFixed(2);
        }
        calculateInvoice();
    }

    rateInp.addEventListener('input', function() {
        rateInp.dataset.enteredRate = rateInp.value;
        calculateInvoice();
    });

    rateInp.addEventListener('focus', function() {
        if (gstTypeSelect.value === 'inclusive') {
            var enteredRate = parseFloat(rateInp.dataset.enteredRate) || parseFloat(rateInp.value) || 0;
            rateInp.value = enteredRate.toFixed(2);
        }
    });

    gstTypeSelect.addEventListener('change', function() {
        convertInclusiveToExclusive();
    });

    document.getElementById('invoiceForm').addEventListener('submit', function(e) {
        if (document.activeElement) {
            document.activeElement.blur();
        }
        gstTypeSelect.value = 'exclusive';
    });

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

    var origCalcInvoice = calculateInvoice;
    calculateInvoice = function() {
        origCalcInvoice();
        calculatePayment();
    };

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
                
                customerSelect.appendChild(option);
                customerSelect.value = customer.id;
                $(customerSelect).trigger('change.select2');
                
                // Trigger change event to populate input fields
                var event = new Event('change');
                customerSelect.dispatchEvent(event);
                
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
@endsection

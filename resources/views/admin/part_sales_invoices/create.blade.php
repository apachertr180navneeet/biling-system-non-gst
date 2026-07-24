@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-4">Create Part Sales Invoice</h4>
    
    @if ($errors->has('items'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ $errors->first('items') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.part-sales-invoices.store') }}" id="invoiceForm">
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
                        <input type="text" id="customer_name" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" required>
                        @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" id="customer_mobile" name="customer_mobile" class="form-control @error('customer_mobile') is-invalid @enderror" value="{{ old('customer_mobile') }}">
                        @error('customer_mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">GSTIN (Optional)</label>
                        <input type="text" id="customer_gstin" name="customer_gstin" class="form-control @error('customer_gstin') is-invalid @enderror" value="{{ old('customer_gstin') }}" placeholder="15-digit GSTIN" maxlength="15">
                        @error('customer_gstin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">PAN Number (Optional)</label>
                        <input type="text" id="customer_pan" name="customer_pan" class="form-control @error('customer_pan') is-invalid @enderror" value="{{ old('customer_pan') }}" placeholder="10-digit PAN" maxlength="10">
                        @error('customer_pan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Place of Supply <span class="text-danger">*</span></label>
                        <input type="text" id="place_of_supply" name="place_of_supply" class="form-control @error('place_of_supply') is-invalid @enderror" value="{{ old('place_of_supply', 'Rajasthan') }}" required>
                        @error('place_of_supply')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Billing Address</label>
                        <textarea id="customer_address" name="customer_address" class="form-control @error('customer_address') is-invalid @enderror" rows="2">{{ old('customer_address') }}</textarea>
                        @error('customer_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <h5 class="card-title text-primary mb-3">Invoice Details</h5>
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
                        <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-select no-select2 @error('payment_mode') is-invalid @enderror" required>
                            <option value="Cash" {{ old('payment_mode') === 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="UPI / Online" {{ old('payment_mode') === 'UPI / Online' ? 'selected' : '' }}>UPI / Online</option>
                            <option value="Card" {{ old('payment_mode') === 'Card' ? 'selected' : '' }}>Card</option>
                        </select>
                        @error('payment_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tax Regime <span class="text-danger">*</span></label>
                        <select name="tax_regime" id="tax_regime" class="form-select no-select2" required>
                            <option value="cgst_sgst" {{ old('tax_regime', 'cgst_sgst') === 'cgst_sgst' ? 'selected' : '' }}>CGST + SGST</option>
                            <option value="igst" {{ old('tax_regime') === 'igst' ? 'selected' : '' }}>IGST</option>
                        </select>
                    </div>
                </div>

                <h5 class="card-title text-primary mb-3">Invoice Items (Parts)</h5>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead>
                            <tr class="table-dark">
                                <th style="width: 30%;">Part Name / Number <span class="text-danger">*</span></th>
                                <th style="width: 10%; text-align: center;">Stock Available</th>
                                <th style="width: 8%; text-align: center;">Qty <span class="text-danger">*</span></th>
                                <th style="width: 12%;">Rate <span class="text-danger">*</span></th>
                                <th style="width: 12%;">GST Type <span class="text-danger">*</span></th>
                                <th style="width: 10%;">GST %</th>
                                <th style="width: 13%;">Total Amount</th>
                                <th style="width: 5%; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer">
                            <tr class="item-row">
                                <td>
                                    <select name="items[0][spare_part_id]" class="form-select part-select" required>
                                        <option value="">-- Choose Spare Part --</option>
                                        @foreach($spareParts as $p)
                                        <option value="{{ $p->id }}" 
                                                data-price="{{ $p->selling_price }}"
                                                data-stock="{{ $p->qty_available }}">
                                            {{ $p->part_no }} - {{ $p->name }} (Available: {{ $p->qty_available }})
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="mt-2">
                                        <input type="text" name="items[0][serial_no_warranty_notes]" class="form-control form-control-sm" placeholder="Serial No. / Warranty Notes (Optional)">
                                    </div>
                                </td>
                                <td class="text-center bg-light">
                                    <span class="stock-badge fw-bold text-secondary">0</span>
                                </td>
                                <td>
                                    <input type="number" name="items[0][quantity]" class="form-control qty-input text-center" min="1" value="1" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][rate]" class="form-control rate-input" min="0" value="0.00" data-entered-rate="0.00" required>
                                </td>
                                <td>
                                    <select name="items[0][gst_type]" class="form-select gst-type-select no-select2" required>
                                        <option value="exclusive">Exclusive</option>
                                        <option value="inclusive">Inclusive</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="items[0][tax_percentage]" class="form-select tax-select no-select2" required>
                                        <option value="0.00">0%</option>
                                        <option value="5.00">5%</option>
                                        <option value="12.00">12%</option>
                                        <option value="18.00" selected>18%</option>
                                        <option value="28.00">28%</option>
                                    </select>
                                </td>
                                <td class="bg-light">
                                    <input type="text" class="form-control line-total bg-transparent border-0 fw-bold" readonly value="0.00">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bx bx-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mb-4">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddRow"><i class="bx bx-plus me-1"></i> Add Part</button>
                </div>

                <h5 class="card-title text-primary mb-3">Payment Summary</h5>
                <div class="row g-3 mb-4 bg-light p-3 rounded border border-light-subtle">
                    <div class="col-md-3">
                        <label class="form-label">Taxable Amount (INR)</label>
                        <input type="text" id="summary_taxable" class="form-control bg-white" readonly value="0.00">
                    </div>
                    <div class="col-md-3" id="summary_cgst_sgst_fields">
                        <label class="form-label">CGST Amount (INR)</label>
                        <input type="text" id="summary_cgst" class="form-control bg-white" readonly value="0.00">
                    </div>
                    <div class="col-md-3" id="summary_cgst_sgst_fields2">
                        <label class="form-label">SGST Amount (INR)</label>
                        <input type="text" id="summary_sgst" class="form-control bg-white" readonly value="0.00">
                    </div>
                    <div class="col-md-3 d-none" id="summary_igst_field">
                        <label class="form-label">IGST Amount (INR)</label>
                        <input type="text" id="summary_igst" class="form-control bg-white" readonly value="0.00">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Round Off (INR)</label>
                        <input type="text" id="summary_round" class="form-control bg-white" readonly value="0.00">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Grand Total (INR)</label>
                        <input type="text" id="summary_grand" class="form-control bg-white fw-bold text-success" readonly value="0.00">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Previous Balance (INR)</label>
                        <input type="number" step="0.01" name="previous_balance" id="previous_balance" class="form-control" value="0.00" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Received Amount (INR) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="received_amount" id="received_amount" class="form-control fw-bold" value="0.00" required min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-danger">Current Balance (INR)</label>
                        <input type="text" id="summary_current_balance" class="form-control bg-white fw-bold text-danger" readonly value="0.00">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check"></i> Generate Invoice</button>
                    <a href="{{ route('admin.part-sales-invoices.index') }}" class="btn btn-secondary">Cancel</a>
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

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var customerSelect = document.getElementById('customer_select');
    var customerNameInput = document.getElementById('customer_name');
    var customerMobileInput = document.getElementById('customer_mobile');
    var customerAddressInput = document.getElementById('customer_address');
    var customerGstInput = document.getElementById('customer_gstin');
    var customerPanInput = document.getElementById('customer_pan');
    
    $(customerSelect).on('change', function() {
        var opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            customerNameInput.value = opt.getAttribute('data-name') || '';
            customerMobileInput.value = opt.getAttribute('data-mobile') || '';
            customerAddressInput.value = opt.getAttribute('data-address') || '';
            customerGstInput.value = opt.getAttribute('data-gstin') || '';
            customerPanInput.value = opt.getAttribute('data-pan') || '';
        } else {
            customerNameInput.value = '';
            customerMobileInput.value = '';
            customerAddressInput.value = '';
            customerGstInput.value = '';
            customerPanInput.value = '';
        }
    });

    var itemsContainer = document.getElementById('itemsContainer');
    var btnAddRow = document.getElementById('btnAddRow');
    var itemIndex = 1;

    // Add Row Click
    btnAddRow.addEventListener('click', function() {
        var row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td>
                <select name="items[\${itemIndex}][spare_part_id]" class="form-select part-select" required>
                    <option value="">-- Choose Spare Part --</option>
                    @foreach($spareParts as $p)
                    <option value="{{ $p->id }}" 
                            data-price="{{ $p->selling_price }}"
                            data-stock="{{ $p->qty_available }}">
                        {{ $p->part_no }} - {{ $p->name }} (Available: {{ $p->qty_available }})
                    </option>
                    @endforeach
                </select>
                <div class="mt-2">
                    <input type="text" name="items[\${itemIndex}][serial_no_warranty_notes]" class="form-control form-control-sm" placeholder="Serial No. / Warranty Notes (Optional)">
                </div>
            </td>
            <td class="text-center bg-light">
                <span class="stock-badge fw-bold text-secondary">0</span>
            </td>
            <td>
                <input type="number" name="items[\${itemIndex}][quantity]" class="form-control qty-input text-center" min="1" value="1" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[\${itemIndex}][rate]" class="form-control rate-input" min="0" value="0.00" data-entered-rate="0.00" required>
            </td>
            <td>
                <select name="items[\${itemIndex}][gst_type]" class="form-select gst-type-select no-select2" required>
                    <option value="exclusive">Exclusive</option>
                    <option value="inclusive">Inclusive</option>
                </select>
            </td>
            <td>
                <select name="items[\${itemIndex}][tax_percentage]" class="form-select tax-select no-select2" required>
                    <option value="0.00">0%</option>
                    <option value="5.00">5%</option>
                    <option value="12.00">12%</option>
                    <option value="18.00" selected>18%</option>
                    <option value="28.00">28%</option>
                </select>
            </td>
            <td class="bg-light">
                <input type="text" class="form-control line-total bg-transparent border-0 fw-bold" readonly value="0.00">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bx bx-trash"></i></button>
            </td>
        `;
        itemsContainer.appendChild(row);
        itemIndex++;
        initSelect2(row.querySelector('.part-select'));
        bindRowEvents(row);
    });

    // Remove row
    itemsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove-row') || e.target.closest('.btn-remove-row')) {
            var rows = itemsContainer.querySelectorAll('.item-row');
            if (rows.length > 1) {
                var row = e.target.closest('.item-row');
                row.remove();
                calculateSummary();
            } else {
                alert('At least one item is required in the invoice.');
            }
        }
    });

    function convertRowInclusiveToExclusive(row) {
        var rateInput = row.querySelector('.rate-input');
        var gstTypeSelect = row.querySelector('.gst-type-select');
        var taxSelect = row.querySelector('.tax-select');

        var gstType = gstTypeSelect.value;
        var enteredRate = parseFloat(rateInput.dataset.enteredRate) || parseFloat(rateInput.value) || 0;

        if (gstType === 'inclusive') {
            var taxPct = parseFloat(taxSelect.value) || 0;
            var baseRate = enteredRate / (1 + (taxPct / 100));
            rateInput.value = baseRate.toFixed(2);
        } else {
            rateInput.value = enteredRate.toFixed(2);
        }
        calculateRow(row);
    }

    function bindRowEvents(row) {
        var partSelect = row.querySelector('.part-select');
        var qtyInput = row.querySelector('.qty-input');
        var rateInput = row.querySelector('.rate-input');
        var gstTypeSelect = row.querySelector('.gst-type-select');
        var taxSelect = row.querySelector('.tax-select');
        var stockBadge = row.querySelector('.stock-badge');

        $(partSelect).on('change', function() {
            var opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                var price = parseFloat(opt.getAttribute('data-price')) || 0;
                var stock = parseInt(opt.getAttribute('data-stock')) || 0;
                rateInput.value = price.toFixed(2);
                rateInput.dataset.enteredRate = price.toFixed(2);
                stockBadge.textContent = stock;
                qtyInput.setAttribute('max', stock);
            } else {
                rateInput.value = '0.00';
                rateInput.dataset.enteredRate = '0.00';
                stockBadge.textContent = '0';
                qtyInput.removeAttribute('max');
            }
            calculateRow(row);
        });

        qtyInput.addEventListener('input', function() {
            var opt = partSelect.options[partSelect.selectedIndex];
            if (opt && opt.value) {
                var stock = parseInt(opt.getAttribute('data-stock')) || 0;
                var val = parseInt(qtyInput.value) || 0;
                if (val > stock) {
                    alert('Quantity cannot exceed available stock (' + stock + ')');
                    qtyInput.value = stock;
                }
            }
            calculateRow(row);
        });

        rateInput.addEventListener('input', function() {
            rateInput.dataset.enteredRate = rateInput.value;
            calculateRow(row);
        });

        rateInput.addEventListener('focus', function() {
            if (gstTypeSelect.value === 'inclusive') {
                var enteredRate = parseFloat(rateInput.dataset.enteredRate) || parseFloat(rateInput.value) || 0;
                rateInput.value = enteredRate.toFixed(2);
            }
        });

        rateInput.addEventListener('blur', function() {
            convertRowInclusiveToExclusive(row);
        });

        gstTypeSelect.addEventListener('change', function() {
            convertRowInclusiveToExclusive(row);
        });

        taxSelect.addEventListener('change', function() {
            convertRowInclusiveToExclusive(row);
        });
    }

    function calculateRow(row) {
        var qtyInput = row.querySelector('.qty-input');
        var rateInput = row.querySelector('.rate-input');
        var gstTypeSelect = row.querySelector('.gst-type-select');
        var taxSelect = row.querySelector('.tax-select');
        var lineTotal = row.querySelector('.line-total');

        var qty = parseInt(qtyInput.value) || 0;
        var gstType = gstTypeSelect.value;
        var taxPct = parseFloat(taxSelect.value) || 0;

        var enteredRate = parseFloat(rateInput.dataset.enteredRate) || parseFloat(rateInput.value) || 0;

        var taxable = 0;
        var tax = 0;
        var net = 0;

        if (gstType === 'inclusive') {
            var rateExclTax = enteredRate / (1 + (taxPct / 100));
            taxable = qty * rateExclTax;
            tax = (taxable * taxPct) / 100;
            net = qty * enteredRate;
        } else {
            taxable = qty * enteredRate;
            tax = (taxable * taxPct) / 100;
            net = taxable + tax;
        }

        lineTotal.value = net.toFixed(2);
        calculateSummary();
    }

    // Initialize first row
    var firstRow = itemsContainer.querySelector('.item-row');
    bindRowEvents(firstRow);

    // Summary calculations
    var summaryTaxable = document.getElementById('summary_taxable');
    var summaryCgst = document.getElementById('summary_cgst');
    var summarySgst = document.getElementById('summary_sgst');
    var summaryIgst = document.getElementById('summary_igst');
    var taxRegimeSelect = document.getElementById('tax_regime');
    var summaryRound = document.getElementById('summary_round');
    var summaryGrand = document.getElementById('summary_grand');
    var prevBalanceInput = document.getElementById('previous_balance');
    var receivedAmountInput = document.getElementById('received_amount');
    var summaryCurrentBalance = document.getElementById('summary_current_balance');

    function toggleRegimeFields() {
        var isIgst = taxRegimeSelect.value === 'igst';
        document.getElementById('summary_cgst_sgst_fields').classList.toggle('d-none', isIgst);
        document.getElementById('summary_cgst_sgst_fields2').classList.toggle('d-none', isIgst);
        document.getElementById('summary_igst_field').classList.toggle('d-none', !isIgst);
    }

    taxRegimeSelect.addEventListener('change', function() {
        toggleRegimeFields();
        calculateSummary();
    });

    function calculateSummary() {
        var taxableTotal = 0;
        var cgstTotal = 0;
        var sgstTotal = 0;
        var igstTotal = 0;
        var taxRegime = taxRegimeSelect.value;

        var rows = itemsContainer.querySelectorAll('.item-row');
        rows.forEach(function(row) {
            var qtyInput = row.querySelector('.qty-input');
            var rateInput = row.querySelector('.rate-input');
            var gstTypeSelect = row.querySelector('.gst-type-select');
            var taxSelect = row.querySelector('.tax-select');

            var qty = parseInt(qtyInput.value) || 0;
            var gstType = gstTypeSelect.value;
            var taxPct = parseFloat(taxSelect.value) || 0;
            
            var enteredRate = parseFloat(rateInput.dataset.enteredRate) || parseFloat(rateInput.value) || 0;

            var taxable = 0;
            var tax = 0;

            if (gstType === 'inclusive') {
                var rateExclTax = enteredRate / (1 + (taxPct / 100));
                taxable = qty * rateExclTax;
                tax = (taxable * taxPct) / 100;
            } else {
                taxable = qty * enteredRate;
                tax = (taxable * taxPct) / 100;
            }

            taxableTotal += taxable;
            if (taxRegime === 'igst') {
                igstTotal += tax;
            } else {
                cgstTotal += tax / 2;
                sgstTotal += tax / 2;
            }
        });

        var netTotalBeforeRound = taxableTotal + cgstTotal + sgstTotal + igstTotal;
        var netTotalRounded = Math.round(netTotalBeforeRound);
        var roundOff = netTotalRounded - netTotalBeforeRound;

        summaryTaxable.value = taxableTotal.toFixed(2);
        summaryCgst.value = cgstTotal.toFixed(2);
        summarySgst.value = sgstTotal.toFixed(2);
        summaryIgst.value = igstTotal.toFixed(2);
        summaryRound.value = roundOff.toFixed(2);
        summaryGrand.value = netTotalRounded.toFixed(2);

        var prev = parseFloat(prevBalanceInput.value) || 0;
        var rec = parseFloat(receivedAmountInput.value) || 0;
        var invoiceBal = netTotalRounded - rec;
        var curBal = prev + invoiceBal;

        summaryCurrentBalance.value = curBal.toFixed(2);
    }

    prevBalanceInput.addEventListener('input', calculateSummary);
    receivedAmountInput.addEventListener('input', calculateSummary);

    document.getElementById('invoiceForm').addEventListener('submit', function(e) {
        if (document.activeElement) {
            document.activeElement.blur();
        }
        var gstSelects = document.querySelectorAll('.gst-type-select');
        gstSelects.forEach(function(select) {
            select.value = 'exclusive';
        });
    });

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

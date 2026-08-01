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
                    <div class="col-md-4">
                        <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-select no-select2 @error('payment_mode') is-invalid @enderror" required>
                            <option value="Cash" {{ old('payment_mode') === 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="UPI / Online" {{ old('payment_mode') === 'UPI / Online' ? 'selected' : '' }}>UPI / Online</option>
                            <option value="Card" {{ old('payment_mode') === 'Card' ? 'selected' : '' }}>Card</option>
                        </select>
                        @error('payment_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title text-primary mb-0">Invoice Items (Parts)</h5>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#selectPartsModal">
                            <i class="bx bx-search-alt me-1"></i> Browse & Add Parts (Modal)
                        </button>
                    </div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead>
                            <tr class="table-dark">
                                <th style="width: 45%;">Part Name / Number <span class="text-danger">*</span></th>
                                <th style="width: 15%; text-align: center;">Stock Available</th>
                                <th style="width: 10%; text-align: center;">Qty <span class="text-danger">*</span></th>
                                <th style="width: 15%;">Rate <span class="text-danger">*</span></th>
                                <th style="width: 10%;">Total Amount</th>
                                <th style="width: 5%; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4 empty-msg">
                                    <i class="bx bx-package me-1 fs-4"></i> No parts selected yet. Click <strong>"Browse & Add Parts (Modal)"</strong> above to select items.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mb-4">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#selectPartsModal">
                        <i class="bx bx-search-alt me-1"></i> Browse & Add Parts (Modal)
                    </button>
                </div>

                <h5 class="card-title text-primary mb-3">Payment Summary</h5>
                <div class="row g-3 mb-4 bg-light p-3 rounded border border-light-subtle">
                    <div class="col-md-3">
                        <label class="form-label">Subtotal Amount (INR)</label>
                        <input type="text" id="summary_taxable" class="form-control bg-white" readonly value="0.00">
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
        <form id="quickAddCustomerForm" method="POST" action="javascript:void(0);">
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

<!-- Select Parts Modal -->
<div class="modal fade" id="selectPartsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1100px;">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title text-white fw-bold"><i class="bx bx-package me-2 fs-4"></i>Select Spare Parts</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-3 align-items-center">
                    <div class="col-md-7">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-search fs-5"></i></span>
                            <input type="text" id="modalPartSearch" class="form-control form-control-lg" placeholder="Type Part No, Name, or HSN Code to search...">
                        </div>
                    </div>
                    <div class="col-md-5 text-end">
                        <span class="badge bg-label-primary py-2 px-3 fs-6">
                            <i class="bx bx-info-circle me-1"></i> Select items, adjust Qty & Rate, then click Add
                        </span>
                    </div>
                </div>

                <div class="table-responsive rounded border" style="max-height: 480px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" id="modalPartsTable">
                        <thead style="position: sticky; top: 0; z-index: 100; background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                            <tr class="text-uppercase small fw-bold text-secondary">
                                <th style="width: 45px;" class="text-center bg-light">
                                    <input type="checkbox" class="form-check-input" id="checkAllParts">
                                </th>
                                <th style="width: 160px;" class="bg-light">Part No.</th>
                                <th class="bg-light">Part Name</th>
                                <th style="width: 130px;" class="text-center bg-light">Stock Status</th>
                                <th style="width: 130px;" class="bg-light">Rate (₹)</th>
                                <th style="width: 100px;" class="text-center bg-light">Qty</th>
                            </tr>
                        </thead>
                        <tbody id="modalPartsList" class="bg-white">
                            @foreach($spareParts as $p)
                            <tr class="modal-part-row" data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-partno="{{ $p->part_no }}" data-hsn="{{ $p->hsn_sac_code ?? '' }}" data-price="{{ $p->selling_price }}" data-stock="{{ $p->qty_available }}">
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input part-checkbox">
                                </td>
                                <td>
                                    <span class="fw-bold text-primary font-monospace">{{ $p->part_no }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $p->name }}</span>
                                    @if(!empty($p->hsn_sac_code))
                                        <br><small class="text-muted">HSN: {{ $p->hsn_sac_code }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($p->qty_available > 0)
                                        <span class="badge bg-label-success px-3 py-2 fw-bold">{{ $p->qty_available }} available</span>
                                    @else
                                        <span class="badge bg-label-danger px-3 py-2 fw-bold">Out of Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm modal-rate-input fw-semibold" value="{{ number_format($p->selling_price, 2, '.', '') }}" min="0" style="min-width: 100px;">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm modal-qty-input text-center fw-bold" value="1" min="1" style="min-width: 70px;">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between py-3">
                <span class="fw-bold text-secondary" id="selectedCountText">0 parts selected</span>
                <div>
                    <button type="button" class="btn btn-outline-secondary me-2 px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary px-4 fw-bold" id="btnAddSelectedParts">
                        <i class="bx bx-plus me-1 fs-5"></i> Add Selected Items to Invoice
                    </button>
                </div>
            </div>
        </div>
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
    var itemIndex = 0;

    function addPartRowFromModal(partId, partNo, partName, stockVal, rateVal, qtyVal) {
        // Remove empty state message if present
        var emptyMsg = itemsContainer.querySelector('.empty-msg');
        if (emptyMsg) {
            emptyMsg.closest('tr').remove();
        }

        // Check if row already exists for this partId
        var existingRow = itemsContainer.querySelector('tr[data-part-id="' + partId + '"]');
        if (existingRow) {
            var existingQtyInput = existingRow.querySelector('.qty-input');
            var existingRateInput = existingRow.querySelector('.rate-input');
            var currentQty = parseInt(existingQtyInput.value) || 0;
            var newQty = currentQty + qtyVal;
            existingQtyInput.value = newQty;
            existingRateInput.value = rateVal.toFixed(2);
            calculateRow(existingRow);
            return;
        }

        var row = document.createElement('tr');
        row.className = 'item-row';
        row.setAttribute('data-part-id', partId);
        row.innerHTML = `
            <td>
                <div class="fw-bold text-primary font-monospace fs-6">${partNo} - ${partName}</div>
                <input type="hidden" name="items[${itemIndex}][spare_part_id]" value="${partId}">
                <div class="mt-1">
                    <input type="text" name="items[${itemIndex}][serial_no_warranty_notes]" class="form-control form-control-sm" placeholder="Serial No. / Warranty Notes (Optional)">
                </div>
            </td>
            <td class="text-center bg-light">
                <span class="stock-badge fw-bold text-secondary">${stockVal}</span>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input text-center fw-bold" min="1" value="${qtyVal}" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${itemIndex}][rate]" class="form-control rate-input fw-semibold" min="0" value="${rateVal.toFixed(2)}" required>
            </td>
            <td class="bg-light">
                <input type="text" class="form-control line-total bg-transparent border-0 fw-bold text-end" readonly value="0.00">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Remove"><i class="bx bx-trash"></i></button>
            </td>
        `;
        itemsContainer.appendChild(row);
        itemIndex++;

        bindRowEvents(row);
        calculateRow(row);
    }

    // Remove row
    itemsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove-row') || e.target.closest('.btn-remove-row')) {
            var row = e.target.closest('.item-row');
            if (row) {
                row.remove();
                calculateSummary();
                checkEmptyContainer();
            }
        }
    });

    function checkEmptyContainer() {
        var rows = itemsContainer.querySelectorAll('.item-row');
        if (rows.length === 0) {
            itemsContainer.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-4 empty-msg">
                        <i class="bx bx-package me-1 fs-4"></i> No parts selected yet. Click <strong>"Browse & Add Parts (Modal)"</strong> above to select items.
                    </td>
                </tr>
            `;
        }
    }

    function bindRowEvents(row) {
        var qtyInput = row.querySelector('.qty-input');
        var rateInput = row.querySelector('.rate-input');

        qtyInput.addEventListener('input', function() {
            calculateRow(row);
        });

        rateInput.addEventListener('input', function() {
            calculateRow(row);
        });
    }

    function calculateRow(row) {
        var qtyInput = row.querySelector('.qty-input');
        var rateInput = row.querySelector('.rate-input');
        var lineTotal = row.querySelector('.line-total');

        var qty = parseInt(qtyInput.value) || 0;
        var rate = parseFloat(rateInput.value) || 0;
        var net = qty * rate;

        lineTotal.value = net.toFixed(2);
        calculateSummary();
    }

    // Summary calculations
    var summaryTaxable = document.getElementById('summary_taxable');
    var summaryRound = document.getElementById('summary_round');
    var summaryGrand = document.getElementById('summary_grand');
    var prevBalanceInput = document.getElementById('previous_balance');
    var receivedAmountInput = document.getElementById('received_amount');
    var summaryCurrentBalance = document.getElementById('summary_current_balance');

    function calculateSummary() {
        var taxableTotal = 0;

        var rows = itemsContainer.querySelectorAll('.item-row');
        rows.forEach(function(row) {
            var qtyInput = row.querySelector('.qty-input');
            var rateInput = row.querySelector('.rate-input');

            var qty = parseInt(qtyInput.value) || 0;
            var rate = parseFloat(rateInput.value) || 0;
            taxableTotal += (qty * rate);
        });

        var netTotalBeforeRound = taxableTotal;
        var grandTotalRounded = Math.round(netTotalBeforeRound);
        var roundOff = grandTotalRounded - netTotalBeforeRound;

        summaryTaxable.value = taxableTotal.toFixed(2);
        summaryRound.value = roundOff.toFixed(2);
        summaryGrand.value = grandTotalRounded.toFixed(2);

        var prevBal = parseFloat(prevBalanceInput.value) || 0;
        var received = parseFloat(receivedAmountInput.value) || 0;
        var currentBal = prevBal + (grandTotalRounded - received);

        summaryCurrentBalance.value = currentBal.toFixed(2);
    }

    if (prevBalanceInput) prevBalanceInput.addEventListener('input', calculateSummary);
    if (receivedAmountInput) receivedAmountInput.addEventListener('input', calculateSummary);

    // Modal Live Search
    var modalPartSearch = document.getElementById('modalPartSearch');
    if (modalPartSearch) {
        modalPartSearch.addEventListener('input', function() {
            var query = this.value.toLowerCase().trim();
            var rows = document.querySelectorAll('#modalPartsList .modal-part-row');
            rows.forEach(function(row) {
                var name = (row.getAttribute('data-name') || '').toLowerCase();
                var partno = (row.getAttribute('data-partno') || '').toLowerCase();
                var hsn = (row.getAttribute('data-hsn') || '').toLowerCase();
                if (name.includes(query) || partno.includes(query) || hsn.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Modal Check All Checkbox
    var checkAllParts = document.getElementById('checkAllParts');
    if (checkAllParts) {
        checkAllParts.addEventListener('change', function() {
            var isChecked = this.checked;
            var rows = document.querySelectorAll('#modalPartsList .modal-part-row');
            rows.forEach(function(row) {
                if (row.style.display !== 'none') {
                    var cb = row.querySelector('.part-checkbox');
                    if (cb && !cb.disabled) {
                        cb.checked = isChecked;
                    }
                }
            });
            updateSelectedCount();
        });
    }

    document.querySelectorAll('#modalPartsList .part-checkbox').forEach(function(cb) {
        cb.addEventListener('change', updateSelectedCount);
    });

    function updateSelectedCount() {
        var count = document.querySelectorAll('#modalPartsList .part-checkbox:checked').length;
        var textEl = document.getElementById('selectedCountText');
        if (textEl) {
            textEl.textContent = count + ' part(s) selected';
        }
    }

    // Transfer Selected Items from Modal to Invoice Table
    var btnAddSelectedParts = document.getElementById('btnAddSelectedParts');
    if (btnAddSelectedParts) {
        btnAddSelectedParts.addEventListener('click', function() {
            var selectedCheckboxes = document.querySelectorAll('#modalPartsList .part-checkbox:checked');
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one spare part from the list.');
                return;
            }

            selectedCheckboxes.forEach(function(cb) {
                var modalRow = cb.closest('.modal-part-row');
                var partId = modalRow.getAttribute('data-id');
                var partNo = modalRow.getAttribute('data-partno');
                var partName = modalRow.getAttribute('data-name');
                var rateVal = parseFloat(modalRow.querySelector('.modal-rate-input').value) || 0;
                var qtyVal = parseInt(modalRow.querySelector('.modal-qty-input').value) || 1;
                var stockVal = parseInt(modalRow.getAttribute('data-stock')) || 0;

                addPartRowFromModal(partId, partNo, partName, stockVal, rateVal, qtyVal);

                cb.checked = false;
            });

            if (checkAllParts) checkAllParts.checked = false;
            updateSelectedCount();

            // Close modal
            var modalEl = document.getElementById('selectPartsModal');
            var modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
            }
        });
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

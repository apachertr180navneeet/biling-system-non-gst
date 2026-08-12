@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-4">Create Parts Quotation</h4>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.quotations.store') }}" id="quotationForm">
                @csrf
                <input type="hidden" name="type" value="parts">
                
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
                    <div class="col-md-6">
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
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title text-primary mb-0">Select Parts & Items</h5>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#selectPartsModal">
                            <i class="bx bx-search-alt me-1"></i> Browse & Add Parts (Modal)
                        </button>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle" id="partsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 45%;">Spare Part <span class="text-danger">*</span></th>
                                <th style="width: 20%;">Rate</th>
                                <th style="width: 15%;">Qty</th>
                                <th style="width: 15%;">Total Amount</th>
                                <th style="width: 5%;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="partsTableBody">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4 empty-msg">
                                    <i class="bx bx-package me-1 fs-4"></i> No parts selected yet. Click <strong>"Browse & Add Parts (Modal)"</strong> above to select items.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#selectPartsModal">
                        <i class="bx bx-search-alt me-1"></i> Browse & Add Parts (Modal)
                    </button>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 offset-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-3 text-secondary">Summary & Calculations</h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td>Subtotal Amount:</td>
                                        <td class="text-end fw-bold">₹<span id="summary_taxable">0.00</span></td>
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

                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label">Remarks / Notes</label>
                        <textarea name="remarks" class="form-control" rows="3">{{ old('remarks') }}</textarea>
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
    const taxRegimeSelect = document.getElementById('tax_regime');
    const addRowBtn = document.getElementById('addRowBtn');
    const tableBody = document.getElementById('partsTableBody');
    
    // Store all spare parts details for easy lookup in JavaScript
    const sparePartsList = @json($spareParts);

    let rowIndex = 0;

    // Customer Selection Change
    $(customerSelect).on('change', function() {
        const option = this.options ? this.options[this.selectedIndex] : null;
        if (option && option.value) {
            const nameEl = document.getElementById('customer_name');
            const mobEl = document.getElementById('customer_mobile');
            const addrEl = document.getElementById('customer_address');
            const gstinEl = document.getElementById('customer_gstin');
            const panEl = document.getElementById('customer_pan');

            if (nameEl) nameEl.value = option.getAttribute('data-name') || '';
            if (mobEl) mobEl.value = option.getAttribute('data-mobile') || '';
            if (addrEl) addrEl.value = option.getAttribute('data-address') || '';
            if (gstinEl) gstinEl.value = option.getAttribute('data-gstin') || '';
            if (panEl) panEl.value = option.getAttribute('data-pan') || '';
        } else {
            const nameEl = document.getElementById('customer_name');
            const mobEl = document.getElementById('customer_mobile');
            const addrEl = document.getElementById('customer_address');
            const gstinEl = document.getElementById('customer_gstin');
            const panEl = document.getElementById('customer_pan');

            if (nameEl) nameEl.value = '';
            if (mobEl) mobEl.value = '';
            if (addrEl) addrEl.value = '';
            if (gstinEl) gstinEl.value = '';
            if (panEl) panEl.value = '';
        }
    });

    // Tax Regime Change
    if (taxRegimeSelect) {
        taxRegimeSelect.addEventListener('change', function() {
            const regime = this.value;
            if (regime === 'cgst_sgst') {
                document.querySelectorAll('.cgst-summary, .sgst-summary').forEach(el => el.classList.remove('d-none'));
                const igstEl = document.querySelector('.igst-summary');
                if (igstEl) igstEl.classList.add('d-none');
            } else {
                document.querySelectorAll('.cgst-summary, .sgst-summary').forEach(el => el.classList.add('d-none'));
                const igstEl = document.querySelector('.igst-summary');
                if (igstEl) igstEl.classList.remove('d-none');
            }
            calculateTotals();
        });
    }

    // Add Row button click
    if (addRowBtn) {
        addRowBtn.addEventListener('click', function() {
            if (typeof addNewRow === 'function') addNewRow();
        });
    }

    const addRowBtnSecondary = document.getElementById('addRowBtnSecondary');
    if (addRowBtnSecondary) {
        addRowBtnSecondary.addEventListener('click', function() {
            addNewRow();
        });
    }

    function addPartRowFromModal(partId, partNo, partName, rateVal, qtyVal) {
        var emptyMsg = tableBody.querySelector('.empty-msg');
        if (emptyMsg) {
            emptyMsg.closest('tr').remove();
        }

        var existingRow = tableBody.querySelector('tr[data-part-id="' + partId + '"]');
        if (existingRow) {
            var existingQtyInput = existingRow.querySelector('.quantity-input');
            var existingRateInput = existingRow.querySelector('.rate-input');
            var currentQty = parseInt(existingQtyInput.value) || 0;
            existingQtyInput.value = currentQty + qtyVal;
            existingRateInput.value = rateVal.toFixed(2);
            calculateRowTotal(existingRow);
            return;
        }

        const tr = document.createElement('tr');
        tr.setAttribute('data-part-id', partId);

        tr.innerHTML = `
            <td>
                <div class="fw-bold text-primary font-monospace fs-6">${partNo} - ${partName}</div>
                <input type="hidden" name="items[${rowIndex}][spare_part_id]" value="${partId}">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][rate]" class="form-control rate-input fw-semibold" value="${rateVal.toFixed(2)}" required>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control quantity-input text-center fw-bold" value="${qtyVal}" required min="1">
            </td>
            <td>
                <span class="row-total fw-bold text-end d-block">₹0.00</span>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn" title="Remove">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        `;

        tableBody.appendChild(tr);
        rowIndex++;

        const rateInput = tr.querySelector('.rate-input');
        const qtyInput = tr.querySelector('.quantity-input');
        const removeBtn = tr.querySelector('.remove-row-btn');

        [rateInput, qtyInput].forEach(el => {
            el.addEventListener('input', () => calculateRowTotal(tr));
            el.addEventListener('change', () => calculateRowTotal(tr));
        });

        removeBtn.addEventListener('click', function() {
            tr.remove();
            calculateTotals();
            checkEmptyTable();
        });

        calculateRowTotal(tr);
    }

    function checkEmptyTable() {
        const rows = tableBody.querySelectorAll('tr');
        if (rows.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-4 empty-msg">
                        <i class="bx bx-package me-1 fs-4"></i> No parts selected yet. Click <strong>"Browse & Add Parts (Modal)"</strong> above to select items.
                    </td>
                </tr>
            `;
        }
    }

    function calculateRowTotal(row) {
        const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
        const qty = parseInt(row.querySelector('.quantity-input').value) || 0;
        const rowTotalSpan = row.querySelector('.row-total');

        const amount = rate * qty;
        rowTotalSpan.innerText = '₹' + amount.toFixed(2);
        calculateTotals();
    }

    function calculateTotals() {
        let total_taxable = 0;
        const rows = tableBody.querySelectorAll('tr');

        rows.forEach(row => {
            const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
            const qty = parseInt(row.querySelector('.quantity-input').value) || 0;
            total_taxable += (rate * qty);
        });

        const grandTotalRounded = Math.round(total_taxable);
        const roundOff = grandTotalRounded - total_taxable;

        document.getElementById('summary_taxable').innerText = total_taxable.toFixed(2);
        document.getElementById('summary_round_off').innerText = roundOff.toFixed(2);
        document.getElementById('summary_grand_total').innerText = grandTotalRounded.toFixed(2);
    }

    // AJAX Quick Add Customer Form Handler
    var quickAddForm = document.getElementById('quickAddCustomerForm');
    var modalErrorAlert = document.getElementById('modalErrorAlert');
    var saveCustomerBtn = document.getElementById('btnSaveCustomer');
    
    if (quickAddForm) {
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
}

    // Modal Live Search
    function filterModalParts() {
        var searchInput = document.getElementById('modalPartSearch');
        if (!searchInput) return;
        var query = searchInput.value.toLowerCase().trim();
        var queryWords = query.split(/\s+/).filter(Boolean);
        var rows = document.querySelectorAll('#modalPartsList .modal-part-row');

        rows.forEach(function(row) {
            if (queryWords.length === 0) {
                row.classList.remove('d-none');
                row.style.setProperty('display', '', '');
                return;
            }

            var name = (row.getAttribute('data-name') || '').toLowerCase();
            var partno = (row.getAttribute('data-partno') || '').toLowerCase();
            var hsn = (row.getAttribute('data-hsn') || '').toLowerCase();
            var textContent = (row.textContent || '').toLowerCase();
            var combinedText = name + ' ' + partno + ' ' + hsn + ' ' + textContent;

            var matches = queryWords.every(function(word) {
                return combinedText.indexOf(word) !== -1;
            });

            if (matches) {
                row.classList.remove('d-none');
                row.style.setProperty('display', '', '');
            } else {
                row.classList.add('d-none');
                row.style.setProperty('display', 'none', 'important');
            }
        });
        updateSelectedCount();
    }

    $(document).on('input keyup search change clear', '#modalPartSearch', filterModalParts);

    $('#selectPartsModal').on('shown.bs.modal', function () {
        var searchInput = document.getElementById('modalPartSearch');
        if (searchInput) {
            searchInput.focus();
            filterModalParts();
        }
    });

    // Check All Checkbox Handler
    var checkAllParts = document.getElementById('checkAllParts');
    if (checkAllParts) {
        checkAllParts.addEventListener('change', function() {
            var isChecked = this.checked;
            var rows = document.querySelectorAll('#modalPartsList .modal-part-row');
            rows.forEach(function(row) {
                var isHidden = row.classList.contains('d-none') || row.style.display === 'none';
                if (!isHidden) {
                    var cb = row.querySelector('.part-checkbox');
                    var qtyInput = row.querySelector('.modal-qty-input');
                    if (cb && !cb.disabled) {
                        cb.checked = isChecked;
                        if (qtyInput && isChecked && (parseInt(qtyInput.value) || 0) <= 0) {
                            qtyInput.value = 1;
                        }
                    }
                }
            });
            updateSelectedCount();
        });
    }

    // Individual Row Checkbox Change Handler
    $(document).on('change', '#modalPartsList .part-checkbox', function() {
        var row = this.closest('.modal-part-row');
        var qtyInput = row.querySelector('.modal-qty-input');
        if (this.checked) {
            if (qtyInput && (parseInt(qtyInput.value) || 0) <= 0) {
                qtyInput.value = 1;
            }
        }
        updateSelectedCount();
    });

    // Individual Qty Input Change Handler
    $(document).on('input change keyup', '#modalPartsList .modal-qty-input', function() {
        var row = this.closest('.modal-part-row');
        var cb = row.querySelector('.part-checkbox');
        var val = parseInt(this.value || 0) || 0;
        if (cb) {
            cb.checked = (val > 0);
        }
        updateSelectedCount();
    });

    // Update Selected Count & Select All Checkbox Status
    function updateSelectedCount() {
        var count = 0;
        var totalVisible = 0;
        var checkedVisible = 0;

        document.querySelectorAll('#modalPartsList .modal-part-row').forEach(function(row) {
            var isHidden = row.classList.contains('d-none') || row.style.display === 'none';
            var cb = row.querySelector('.part-checkbox');

            if (cb && cb.checked) {
                count++;
                row.classList.add('table-primary');
            } else {
                row.classList.remove('table-primary');
            }

            if (!isHidden) {
                totalVisible++;
                if (cb && cb.checked) {
                    checkedVisible++;
                }
            }
        });

        var textEl = document.getElementById('selectedCountText');
        if (textEl) {
            textEl.textContent = count + ' item(s) selected';
        }

        var checkAll = document.getElementById('checkAllParts');
        if (checkAll) {
            checkAll.checked = (totalVisible > 0 && checkedVisible === totalVisible);
        }
    }

    // Transfer Selected Items from Modal to Quotations Table
    var btnAddSelectedParts = document.getElementById('btnAddSelectedParts');
    if (btnAddSelectedParts) {
        btnAddSelectedParts.addEventListener('click', function() {
            var rows = document.querySelectorAll('#modalPartsList .modal-part-row');
            var addedCount = 0;

            rows.forEach(function(modalRow) {
                var cb = modalRow.querySelector('.part-checkbox');
                var qtyInput = modalRow.querySelector('.modal-qty-input');
                var qtyVal = parseInt(qtyInput ? qtyInput.value : 0) || 0;
                var isChecked = cb && cb.checked;

                if (isChecked) {
                    if (qtyVal <= 0) qtyVal = 1;
                    var partId = modalRow.getAttribute('data-id');
                    var partNo = modalRow.getAttribute('data-partno');
                    var partName = modalRow.getAttribute('data-name');
                    var rateVal = parseFloat(modalRow.querySelector('.modal-rate-input').value) || 0;

                    addPartRowFromModal(partId, partNo, partName, rateVal, qtyVal);
                    if (qtyInput) qtyInput.value = 1;
                    if (cb) cb.checked = false;
                    modalRow.classList.remove('table-primary');
                    addedCount++;
                }
            });

            if (addedCount === 0) {
                alert('Please select at least one item using checkbox or enter a quantity greater than 0.');
                return;
            }

            var checkAll = document.getElementById('checkAllParts');
            if (checkAll) checkAll.checked = false;

            updateSelectedCount();

            // Close modal
            var modalEl = document.getElementById('selectPartsModal');
            var modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
            }
        });
    }
});
</script>

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
            <div class="modal-header py-3" style="background: linear-gradient(135deg, #1e293b, #0f172a) !important;">
                <h5 class="modal-title fw-bold" style="color: #ffffff !important;"><i class="bx bx-package me-2 fs-4" style="color: #818cf8 !important;"></i>Select Spare Parts</h5>
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
                            <i class="bx bx-info-circle me-1"></i> Select items using checkbox or enter Qty (> 0), then click Add
                        </span>
                    </div>
                </div>

                <div class="table-responsive rounded border" style="max-height: 480px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" id="modalPartsTable">
                        <thead style="position: sticky; top: 0; z-index: 100; background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                            <tr class="text-uppercase small fw-bold text-secondary">
                                <th style="width: 45px;" class="text-center bg-light">
                                    <input type="checkbox" id="checkAllParts" class="form-check-input" title="Select All Visible">
                                </th>
                                <th style="width: 160px;" class="bg-light">Part No.</th>
                                <th class="bg-light">Part Name</th>
                                <th style="width: 130px;" class="text-center bg-light">Stock Status</th>
                                <th style="width: 130px;" class="bg-light">Rate (₹)</th>
                                <th style="width: 110px;" class="text-center bg-light">Qty</th>
                            </tr>
                        </thead>
                        <tbody id="modalPartsList" class="bg-white">
                            @foreach($spareParts as $p)
                            <tr class="modal-part-row" data-id="{{ $p->id }}" data-name="{{ e($p->name) }}" data-partno="{{ e($p->part_no ?? '') }}" data-hsn="{{ e($p->hsn_sac_code ?? '') }}" data-price="{{ $p->selling_price }}" data-stock="{{ $p->qty_available }}">
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
                                    <input type="number" class="form-control form-control-sm modal-qty-input text-center fw-bold" value="1" min="0" style="min-width: 85px;">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between py-3">
                <span class="fw-bold text-secondary" id="selectedCountText">0 items selected (Qty > 0)</span>
                <div>
                    <button type="button" class="btn btn-outline-secondary me-2 px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary px-4 fw-bold" id="btnAddSelectedParts">
                        <i class="bx bx-plus me-1 fs-5"></i> Add Selected Items to Quotation
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

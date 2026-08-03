@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin / Purchase Orders /</span> Edit
    </h4>
    <div class="card">
        <div class="card-header border-bottom py-3">
            <h5 class="mb-0 fw-bold text-primary"><i class="bx bx-edit me-2"></i>Edit Purchase Order: {{ $purchaseOrder->order_number }}</h5>
        </div>
        <div class="card-body mt-3">
            <form method="POST" action="{{ route('admin.purchase-orders.update', $purchaseOrder) }}" id="poForm">
                @csrf 
                @method('PUT')
                
                <h5 class="card-title text-primary mb-3">Order Details</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                            <option value="">-- Select Supplier --</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Order Date <span class="text-danger">*</span></label>
                        <input type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', $purchaseOrder->order_date->format('Y-m-d')) }}" required>
                        @error('order_date') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expected Date</label>
                        <input type="date" name="expected_date" class="form-control @error('expected_date') is-invalid @enderror" value="{{ old('expected_date', $purchaseOrder->expected_date?->format('Y-m-d')) }}">
                        @error('expected_date') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Notes / Remarks</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="Optional purchase order notes...">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                        @error('notes') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title text-primary mb-0">Order Items</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#selectPartsModal">
                        <i class="bx bx-search-alt me-1"></i> Browse & Add Parts (Modal)
                    </button>
                </div>

                @error('items') <div class="alert alert-danger py-2 small mb-3">{{ $message }}</div> @enderror

                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead>
                            <tr class="table-dark">
                                <th style="width: 45%;">Part Name / Number <span class="text-danger">*</span></th>
                                <th style="width: 15%; text-align: center;">Qty <span class="text-danger">*</span></th>
                                <th style="width: 20%;">Purchase Unit Price (₹) <span class="text-danger">*</span></th>
                                <th style="width: 15%;">Total Amount</th>
                                <th style="width: 5%; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer">
                            @foreach($purchaseOrder->items as $i => $item)
                            <tr class="item-row" data-part-id="{{ $item->spare_part_id }}">
                                <td>
                                    <div class="fw-bold text-primary font-monospace fs-6">{{ $item->sparePart->part_no ?? '' }} - {{ $item->sparePart->name ?? '' }}</div>
                                    <input type="hidden" name="items[{{ $i }}][spare_part_id]" value="{{ $item->spare_part_id }}">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $i }}][quantity]" class="form-control qty text-center fw-bold" min="1" value="{{ $item->quantity }}" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[{{ $i }}][unit_price]" class="form-control unit-price fw-semibold" min="0" value="{{ number_format($item->unit_price, 2, '.', '') }}" required>
                                </td>
                                <td class="bg-light">
                                    <input type="text" class="form-control line-total bg-transparent border-0 fw-bold text-end" readonly value="{{ number_format($item->total_price, 2, '.', '') }}">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Remove"><i class="bx bx-trash"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mb-4">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#selectPartsModal">
                        <i class="bx bx-search-alt me-1"></i> Browse & Add Parts (Modal)
                    </button>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 offset-md-6">
                        <div class="card bg-light border">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold text-dark mb-0">Grand Total:</h5>
                                    <h4 class="fw-bold text-primary mb-0">₹<span id="grandTotal">{{ number_format($purchaseOrder->total_amount, 2, '.', '') }}</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-label-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="bx bx-check me-1"></i> Update Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Select Parts Modal -->
<div class="modal fade" id="selectPartsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1100px;">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title text-white fw-bold"><i class="bx bx-package me-2 fs-4"></i>Select Spare Parts for Purchase</h5>
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
                            <i class="bx bx-info-circle me-1"></i> Select items, adjust Qty & Price, then click Add
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
                                <th style="width: 130px;" class="text-center bg-light">Current Stock</th>
                                <th style="width: 140px;" class="bg-light">Purchase Price (₹)</th>
                                <th style="width: 100px;" class="text-center bg-light">Qty</th>
                            </tr>
                        </thead>
                        <tbody id="modalPartsList" class="bg-white">
                            @foreach($spareParts as $p)
                            <tr class="modal-part-row" data-id="{{ $p->id }}" data-name="{{ e($p->name) }}" data-partno="{{ e($p->part_no ?? '') }}" data-hsn="{{ e($p->hsn_sac_code ?? '') }}" data-price="{{ $p->purchase_price }}">
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
                                    <span class="badge bg-label-info px-3 py-2 fw-bold">{{ $p->qty_available }} in stock</span>
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm modal-rate-input fw-semibold" value="{{ number_format($p->purchase_price, 2, '.', '') }}" min="0" style="min-width: 100px;">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm modal-qty-input text-center fw-bold" value="1" min="1" style="min-width: 85px;">
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
                        <i class="bx bx-plus me-1 fs-5"></i> Add Selected Items to Order
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
    var itemsContainer = document.getElementById('itemsContainer');
    var itemIndex = {{ count($purchaseOrder->items) }};

    function addPartRowFromModal(partId, partNo, partName, priceVal, qtyVal) {
        var emptyMsg = itemsContainer.querySelector('.empty-msg');
        if (emptyMsg) {
            emptyMsg.closest('tr').remove();
        }

        var existingRow = itemsContainer.querySelector('tr[data-part-id="' + partId + '"]');
        if (existingRow) {
            var existingQtyInput = existingRow.querySelector('.qty');
            var existingPriceInput = existingRow.querySelector('.unit-price');
            var currentQty = parseInt(existingQtyInput.value) || 0;
            existingQtyInput.value = currentQty + qtyVal;
            existingPriceInput.value = priceVal.toFixed(2);
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
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control qty text-center fw-bold" min="1" value="${qtyVal}" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${itemIndex}][unit_price]" class="form-control unit-price fw-semibold" min="0" value="${priceVal.toFixed(2)}" required>
            </td>
            <td class="bg-light">
                <input type="text" class="form-control line-total bg-transparent border-0 fw-bold text-end" readonly value="0.00">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Remove"><i class="bx bx-trash"></i></button>
            </td>
        `;
        itemsContainer.appendChild(row);
        itemIndex++;

        bindRowEvents(row);
        calculateRow(row);
    }

    // Remove row
    itemsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
            var row = e.target.closest('.item-row');
            if (row) {
                row.remove();
                calcTotal();
                checkEmptyContainer();
            }
        }
    });

    function checkEmptyContainer() {
        var rows = itemsContainer.querySelectorAll('.item-row');
        if (rows.length === 0) {
            itemsContainer.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-4 empty-msg">
                        <i class="bx bx-package me-1 fs-4"></i> No parts selected yet. Click <strong>"Browse & Add Parts (Modal)"</strong> above to select items.
                    </td>
                </tr>
            `;
        }
    }

    function bindRowEvents(row) {
        var qtyInput = row.querySelector('.qty');
        var priceInput = row.querySelector('.unit-price');

        [qtyInput, priceInput].forEach(function(input) {
            if (input) {
                input.addEventListener('input', function() {
                    calculateRow(row);
                });
            }
        });
    }

    function calculateRow(row) {
        var qtyInput = row.querySelector('.qty');
        var priceInput = row.querySelector('.unit-price');
        var lineTotal = row.querySelector('.line-total');

        var qty = parseInt(qtyInput.value) || 0;
        var price = parseFloat(priceInput.value) || 0;
        var net = qty * price;

        lineTotal.value = net.toFixed(2);
        calcTotal();
    }

    function calcTotal() {
        var total = 0;
        var rows = itemsContainer.querySelectorAll('.item-row');
        rows.forEach(function(row) {
            var qtyInput = row.querySelector('.qty');
            var priceInput = row.querySelector('.unit-price');
            var qty = parseInt(qtyInput.value) || 0;
            var price = parseFloat(priceInput.value) || 0;
            total += (qty * price);
        });
        document.getElementById('grandTotal').textContent = total.toFixed(2);
    }

    // Initialize existing rows
    var existingRows = itemsContainer.querySelectorAll('.item-row');
    existingRows.forEach(function(row) {
        bindRowEvents(row);
        calculateRow(row);
    });

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
    }

    $(document).on('input keyup search change clear', '#modalPartSearch', filterModalParts);

    $('#selectPartsModal').on('shown.bs.modal', function () {
        var searchInput = document.getElementById('modalPartSearch');
        if (searchInput) {
            searchInput.focus();
            filterModalParts();
        }
    });

    // Modal Check All Checkbox
    var checkAllParts = document.getElementById('checkAllParts');
    if (checkAllParts) {
        checkAllParts.addEventListener('change', function() {
            var isChecked = this.checked;
            var rows = document.querySelectorAll('#modalPartsList .modal-part-row');
            rows.forEach(function(row) {
                var isHidden = row.classList.contains('d-none') || row.style.display === 'none' || window.getComputedStyle(row).display === 'none';
                if (!isHidden) {
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

    // Transfer Selected Items from Modal to Items Table
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
                var priceVal = parseFloat(modalRow.querySelector('.modal-rate-input').value) || 0;
                var qtyVal = parseInt(modalRow.querySelector('.modal-qty-input').value) || 1;

                addPartRowFromModal(partId, partNo, partName, priceVal, qtyVal);

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

    var poForm = document.getElementById('poForm');
    if (poForm) {
        poForm.addEventListener('submit', function(e) {
            var rows = itemsContainer.querySelectorAll('.item-row');
            if (rows.length === 0) {
                alert('Please add at least one item using the "Browse & Add Parts (Modal)" button.');
                e.preventDefault();
            }
        });
    }
});
</script>
@endsection

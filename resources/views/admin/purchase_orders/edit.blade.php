@extends('admin.layouts.app')
@section('style')
<style>
.item-row { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px; }
</style>
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin / Purchase Orders /</span> Edit
    </h4>
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Edit Purchase Order: {{ $purchaseOrder->order_number }}</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.purchase-orders.update', $purchaseOrder) }}" id="poForm">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Order Date</label>
                    <input type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', $purchaseOrder->order_date->format('Y-m-d')) }}">
                    @error('order_date') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Expected Date</label>
                    <input type="date" name="expected_date" class="form-control @error('expected_date') is-invalid @enderror" value="{{ old('expected_date', $purchaseOrder->expected_date?->format('Y-m-d')) }}">
                    @error('expected_date') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                    @error('notes') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <hr>
                <h5>Order Items</h5>
                @error('items') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                <div id="itemsContainer">
                    @foreach($purchaseOrder->items as $i => $item)
                    <div class="item-row row">
                        <div class="col-md-5">
                            <select name="items[{{ $i }}][spare_part_id]" class="form-control" required>
                                <option value="">Select Part</option>
                                @foreach($spareParts as $part)
                                <option value="{{ $part->id }}" data-price="{{ $part->selling_price }}" {{ $item->spare_part_id == $part->id ? 'selected' : '' }}>{{ $part->part_no }} - {{ $part->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="items[{{ $i }}][quantity]" class="form-control qty" min="1" value="{{ $item->quantity }}" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" name="items[{{ $i }}][unit_price]" class="form-control unit-price" min="0" value="{{ $item->unit_price }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control line-total" readonly value="{{ $item->total_price }}">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-danger remove-item">X</button>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-sm btn-secondary mt-2" id="addItem">+ Add Item</button>

                <hr>
                <div class="text-end">
                    <h5>Total: <span id="grandTotal">{{ $purchaseOrder->total_amount }}</span></h5>
                </div>
                <button type="submit" class="btn btn-primary">Update Order</button>
                <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
$(document).ready(function() {
    var itemIndex = {{ count($purchaseOrder->items) }};
    $('#addItem').click(function() {
        var html = '<div class="item-row row">' +
            '<div class="col-md-5"><select name="items[' + itemIndex + '][spare_part_id]" class="form-control"><option value="">Select Part</option>@foreach($spareParts as $part)<option value="{{ $part->id }}" data-price="{{ $part->selling_price }}">{{ $part->part_no }} - {{ $part->name }}</option>@endforeach</select></div>' +
            '<div class="col-md-2"><input type="number" name="items[' + itemIndex + '][quantity]" class="form-control qty" min="1" value="1"></div>' +
            '<div class="col-md-2"><input type="number" step="0.01" name="items[' + itemIndex + '][unit_price]" class="form-control unit-price" min="0" value="0"></div>' +
            '<div class="col-md-2"><input type="text" class="form-control line-total" readonly value="0.00"></div>' +
            '<div class="col-md-1"><button type="button" class="btn btn-sm btn-danger remove-item">X</button></div>' +
        '</div>';
        $('#itemsContainer').append(html);
        initSelect2($('#itemsContainer').find('.item-row').last().find('select'));
        itemIndex++;
    });
    $(document).on('click', '.remove-item', function() {
        if ($('.item-row').length > 1) $(this).closest('.item-row').remove();
        calcTotal();
    });
    $(document).on('change keyup', '.qty, .unit-price', function() {
        var row = $(this).closest('.item-row');
        var qty = parseFloat(row.find('.qty').val()) || 0;
        var price = parseFloat(row.find('.unit-price').val()) || 0;
        row.find('.line-total').val((qty * price).toFixed(2));
        calcTotal();
    });
    $(document).on('change', 'select[name$="[spare_part_id]"]', function() {
        var row = $(this).closest('.item-row');
        var selected = $(this).find(':selected');
        var price = selected.data('price') || 0;
        row.find('.unit-price').val(price);
        row.find('.qty').trigger('keyup');
    });
    function calcTotal() {
        var total = 0;
        $('.line-total').each(function() { total += parseFloat($(this).val()) || 0; });
        $('#grandTotal').text(total.toFixed(2));
    }
    calcTotal();
});
document.getElementById('poForm').addEventListener('submit', function(e) {
    var valid = true;
    var items = document.querySelectorAll('.item-row');
    if (items.length === 0) {
        alert('Please add at least one item.');
        e.preventDefault();
        return;
    }
    items.forEach(function(row) {
        var partSelect = row.querySelector('select[name*="spare_part_id"]');
        var qtyInput = row.querySelector('input[name*="quantity"]');
        if (partSelect && !partSelect.value) {
            valid = false;
            partSelect.classList.add('is-invalid');
        } else if (partSelect) {
            partSelect.classList.remove('is-invalid');
        }
        if (qtyInput && (parseInt(qtyInput.value) || 0) < 1) {
            valid = false;
            qtyInput.classList.add('is-invalid');
        } else if (qtyInput) {
            qtyInput.classList.remove('is-invalid');
        }
    });
    if (!valid) {
        e.preventDefault();
        alert('Please fill in all required fields for each item.');
    }
});
</script>
@endsection

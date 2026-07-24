@extends('admin.layouts.app')
@section('style')
<style>
.vehicle-rows .vehicle-row { border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; margin-bottom: 12px; background: #fafafa; }
.validation-icon { width: 20px; display: inline-block; }
.validation-icon .spinner { display: none; width: 14px; height: 14px; border: 2px solid #e0e0e0; border-top-color: #696cff; border-radius: 50%; animation: spin 0.6s linear infinite; }
.validation-icon .check { display: none; color: #28a745; font-weight: bold; }
.validation-icon .error { display: none; color: #dc3545; font-weight: bold; }
.validation-icon.checking .spinner { display: inline-block; }
.validation-icon.checking .check,
.validation-icon.checking .error { display: none; }
.validation-icon.valid .check { display: inline-block; }
.validation-icon.valid .spinner,
.validation-icon.valid .error { display: none; }
.validation-icon.invalid .error { display: inline-block; }
.validation-icon.invalid .spinner,
.validation-icon.invalid .check { display: none; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-4">Receive Vehicles - {{ $vehiclePurchaseOrder->po_number }}</h4>
    <div class="card"><div class="card-body">
        <p><strong>Supplier:</strong> {{ $vehiclePurchaseOrder->supplier->name ?? '-' }}</p>
        <p><strong>Order Date:</strong> {{ $vehiclePurchaseOrder->order_date->format('d-m-Y') }}</p>
        <hr>
        <datalist id="colorOptions">
            @foreach($colorOptions ?? [] as $colOpt)
                <option value="{{ $colOpt }}">
            @endforeach
        </datalist>
        <form method="POST" action="{{ route('admin.vehicle-purchase-orders.receive-store', $vehiclePurchaseOrder) }}" id="receiveForm">
            @csrf
            <div id="deleted-vehicles-container"></div>
            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif
            @foreach($vehiclePurchaseOrder->items as $i => $item)
                @php $remaining = $item->quantity - $item->received_quantity; @endphp
                @if($remaining > 0)
                <div class="card mb-3" id="po-item-{{ $item->id }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $item->vehicle_description }}</strong>
                            @if($item->color_name) <span class="badge bg-secondary">{{ $item->color_name }}</span> @endif
                            @if($item->mfg_year) <span class="badge bg-info">{{ $item->mfg_year }}</span> @endif
                        </div>
                        <div>
                            <span class="text-muted">Ordered: {{ $item->quantity }}</span> |
                            <span class="text-muted">Received: {{ $item->received_quantity }}</span> |
                            <span class="text-success fw-bold">Remaining: {{ $remaining }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                        
                        @if(isset($receivedVehicles) && $receivedVehicles->isNotEmpty())
                            @php
                                $itemVehicles = $receivedVehicles->filter(function($v) use ($item) {
                                    return $v->vehicle_description === $item->vehicle_description
                                        && $v->color_name === $item->color_name
                                        && $v->mfg_year === $item->mfg_year;
                                });
                            @endphp
                            @if($itemVehicles->isNotEmpty())
                                <div class="mb-4 p-3 bg-light rounded border border-light-subtle">
                                    <div class="small fw-semibold text-muted mb-3"><i class="bx bx-edit me-1"></i> Edit Previously Received Vehicles ({{ $itemVehicles->count() }}):</div>
                                    <div class="edit-vehicle-rows">
                                        @foreach($itemVehicles as $rev)
                                            <div class="vehicle-row p-3 mb-3 border rounded bg-white">
                                                <input type="hidden" name="edit_vehicles[{{ $rev->id }}][id]" value="{{ $rev->id }}">
                                                <div class="row g-2">
                                                    <div class="col-md-3">
                                                        <label class="form-label small text-muted">Chassis Number *</label>
                                                        <input type="text" name="edit_vehicles[{{ $rev->id }}][chassis_number]" class="form-control bg-white" required maxlength="255" data-field="chassis_number" data-id="{{ $rev->id }}" value="{{ old("edit_vehicles.{$rev->id}.chassis_number", $rev->chassis_number) }}" style="{{ $errors->has("edit_vehicles.{$rev->id}.chassis_number") ? 'border: 2px solid #dc3545;' : '' }}">
                                                        <div class="validation-message small text-danger mt-1">
                                                            @error("edit_vehicles.{$rev->id}.chassis_number")
                                                                {{ $message }}
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small text-muted">Motor Number *</label>
                                                        <input type="text" name="edit_vehicles[{{ $rev->id }}][motor_number]" class="form-control bg-white" required maxlength="255" value="{{ old("edit_vehicles.{$rev->id}.motor_number", $rev->motor_number) }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small text-muted">Color</label>
                                                        <input type="text" name="edit_vehicles[{{ $rev->id }}][color_name]" list="colorOptions" class="form-control bg-white" maxlength="255" placeholder="Select/Type Color" value="{{ old("edit_vehicles.{$rev->id}.color_name", $rev->color_name ?? $item->color_name) }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small text-muted">Battery Number</label>
                                                        <input type="text" name="edit_vehicles[{{ $rev->id }}][battery_number]" class="form-control bg-white" maxlength="255" value="{{ old("edit_vehicles.{$rev->id}.battery_number", $rev->battery_number) }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small text-muted">Charger Number</label>
                                                        <input type="text" name="edit_vehicles[{{ $rev->id }}][charger_number]" class="form-control bg-white" maxlength="255" value="{{ old("edit_vehicles.{$rev->id}.charger_number", $rev->charger_number) }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small text-muted">Controller Number</label>
                                                        <input type="text" name="edit_vehicles[{{ $rev->id }}][controller_number]" class="form-control bg-white" maxlength="255" value="{{ old("edit_vehicles.{$rev->id}.controller_number", $rev->controller_number) }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small text-muted">Convertor Number</label>
                                                        <input type="text" name="edit_vehicles[{{ $rev->id }}][convertor_number]" class="form-control bg-white" maxlength="255" value="{{ old("edit_vehicles.{$rev->id}.convertor_number", $rev->convertor_number) }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small text-muted">Manual Number</label>
                                                        <input type="text" name="edit_vehicles[{{ $rev->id }}][manual_number]" class="form-control bg-white" maxlength="255" value="{{ old("edit_vehicles.{$rev->id}.manual_number", $rev->manual_number) }}">
                                                    </div>
                                                    <div class="col-md-12 d-flex justify-content-end mt-2">
                                                        <button type="button" class="btn btn-outline-danger btn-remove-received" data-id="{{ $rev->id }}"><i class="bx bx-trash me-1"></i> Remove</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif

                        <div class="vehicle-rows" id="vehicles-{{ $item->id }}">
                            @php
                                $oldVehicles = old("items.{$i}.vehicles");
                                $vehiclesToRender = is_array($oldVehicles) ? $oldVehicles : [['chassis_number' => '', 'motor_number' => '', 'color_name' => $item->color_name ?? '', 'battery_number' => '', 'charger_number' => '', 'controller_number' => '', 'convertor_number' => '', 'manual_number' => '']];
                            @endphp
                            @foreach($vehiclesToRender as $vIdx => $vVal)
                            <div class="vehicle-row p-3 mb-3 border rounded bg-white">
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label class="form-label small">Chassis Number *</label>
                                        <input type="text" name="items[{{ $i }}][vehicles][{{ $vIdx }}][chassis_number]" class="form-control" required maxlength="255" data-field="chassis_number" value="{{ old("items.{$i}.vehicles.{$vIdx}.chassis_number", $vVal['chassis_number'] ?? '') }}" style="{{ $errors->has("items.{$i}.vehicles.{$vIdx}.chassis_number") ? 'border: 2px solid #dc3545;' : '' }}">
                                        <div class="validation-message small text-danger mt-1">
                                            @error("items.{$i}.vehicles.{$vIdx}.chassis_number")
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Motor Number *</label>
                                        <input type="text" name="items[{{ $i }}][vehicles][{{ $vIdx }}][motor_number]" class="form-control" required maxlength="255" value="{{ old("items.{$i}.vehicles.{$vIdx}.motor_number", $vVal['motor_number'] ?? '') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Color</label>
                                        <input type="text" name="items[{{ $i }}][vehicles][{{ $vIdx }}][color_name]" list="colorOptions" class="form-control" maxlength="255" placeholder="Select/Type Color" value="{{ old("items.{$i}.vehicles.{$vIdx}.color_name", $vVal['color_name'] ?? $item->color_name) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Battery Number</label>
                                        <input type="text" name="items[{{ $i }}][vehicles][{{ $vIdx }}][battery_number]" class="form-control" maxlength="255" value="{{ old("items.{$i}.vehicles.{$vIdx}.battery_number", $vVal['battery_number'] ?? '') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Charger Number</label>
                                        <input type="text" name="items[{{ $i }}][vehicles][{{ $vIdx }}][charger_number]" class="form-control" maxlength="255" value="{{ old("items.{$i}.vehicles.{$vIdx}.charger_number", $vVal['charger_number'] ?? '') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Controller Number</label>
                                        <input type="text" name="items[{{ $i }}][vehicles][{{ $vIdx }}][controller_number]" class="form-control" maxlength="255" value="{{ old("items.{$i}.vehicles.{$vIdx}.controller_number", $vVal['controller_number'] ?? '') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Convertor Number</label>
                                        <input type="text" name="items[{{ $i }}][vehicles][{{ $vIdx }}][convertor_number]" class="form-control" maxlength="255" value="{{ old("items.{$i}.vehicles.{$vIdx}.convertor_number", $vVal['convertor_number'] ?? '') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Manual Number</label>
                                        <input type="text" name="items[{{ $i }}][vehicles][{{ $vIdx }}][manual_number]" class="form-control" maxlength="255" value="{{ old("items.{$i}.vehicles.{$vIdx}.manual_number", $vVal['manual_number'] ?? '') }}">
                                    </div>
                                    <div class="col-md-12 d-flex justify-content-end mt-2 remove-btn-col">
                                        @if($vIdx > 0)
                                            <button type="button" class="btn btn-outline-danger btn-remove"><i class="bx bx-trash me-1"></i> Remove</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2 add-vehicle-btn" data-item="{{ $item->id }}" data-remaining="{{ $remaining }}" data-max="{{ $remaining }}">
                            <i class="bx bx-plus"></i> Add Vehicle
                        </button>
                    </div>
                </div>
                @endif
            @endforeach
            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><i class="bx bx-check"></i> Receive Vehicles</button>
                <a href="{{ route('admin.vehicle-purchase-orders.show', $vehiclePurchaseOrder) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div></div>
</div>
@endsection
@section('script')
<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function checkUnique(input) {
    var field = input.dataset.field;
    var value = input.value.trim();
    var lowerVal = value.toLowerCase();
    var msgBox = input.parentNode.querySelector('.validation-message') || input.parentNode.appendChild(document.createElement('div'));
    msgBox.className = 'validation-message small text-danger mt-1';

    if (!value) {
        msgBox.textContent = '';
        input.style.border = '';
        clearDuplicateErrors(field);
        return;
    }

    // Unique within form check
    var inputsOfSameField = document.querySelectorAll('input[data-field="' + field + '"]');
    var duplicateFound = false;
    inputsOfSameField.forEach(function(otherInput) {
        if (otherInput !== input && otherInput.value.trim().toLowerCase() === lowerVal) {
            duplicateFound = true;
            otherInput.style.border = '2px solid #dc3545';
            var otherMsg = otherInput.parentNode.querySelector('.validation-message') || otherInput.parentNode.appendChild(document.createElement('div'));
            otherMsg.textContent = 'Duplicate chassis number in this form.';
            otherMsg.className = 'validation-message small text-danger mt-1';
        }
    });

    if (duplicateFound) {
        input.style.border = '2px solid #dc3545';
        msgBox.textContent = 'Duplicate chassis number in this form.';
        return;
    }

    // Unique in DB check
    input.style.border = '1px solid #aaa';
    msgBox.textContent = 'Checking...';
    msgBox.className = 'validation-message small text-muted mt-1';

    checkUniqueDB(input, value);
}

function clearDuplicateErrors(field) {
    revalidateFieldType(field);
}

function revalidateFieldType(field) {
    var inputs = document.querySelectorAll('input[data-field="' + field + '"]');
    var values = {};
    inputs.forEach(function(inp) {
        var val = inp.value.trim().toLowerCase();
        if (val) {
            values[val] = (values[val] || 0) + 1;
        }
    });

    inputs.forEach(function(inp) {
        var val = inp.value.trim().toLowerCase();
        var msgBox = inp.parentNode.querySelector('.validation-message');
        if (msgBox && msgBox.textContent.indexOf('Duplicate') === 0) {
            if (!val || values[val] <= 1) {
                msgBox.textContent = '';
                msgBox.className = 'validation-message small mt-1';
                inp.style.border = '';
                if (val) {
                    checkUniqueDB(inp, inp.value.trim());
                }
            }
        }
    });
}

function checkUniqueDB(input, value) {
    var field = input.dataset.field;
    var msgBox = input.parentNode.querySelector('.validation-message') || input.parentNode.appendChild(document.createElement('div'));
    var ignoreId = input.dataset.id || '';
    
    var formData = new FormData();
    formData.append('field', field);
    formData.append('value', value);
    if (ignoreId) {
        formData.append('ignore_id', ignoreId);
    }

    fetch('{{ route("admin.vehicle-inventories.check-unique") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (input.value.trim() !== value) return; // value changed
        if (data.valid) {
            input.style.border = '2px solid #28a745';
            msgBox.textContent = '';
            msgBox.className = 'validation-message small mt-1';
            revalidateFieldType(field);
        } else {
            input.style.border = '2px solid #dc3545';
            msgBox.textContent = data.message;
            msgBox.className = 'validation-message small text-danger mt-1';
        }
    })
    .catch(function() {
        if (input.value.trim() === value) {
            input.style.border = '';
            msgBox.textContent = '';
            msgBox.className = 'validation-message small mt-1';
        }
    });
}

document.addEventListener('input', function(e) {
    if (e.target.matches('input[data-field]')) {
        checkUnique(e.target);
    }
});

document.querySelectorAll('.add-vehicle-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var container = document.getElementById('vehicles-' + this.dataset.item);
        var rows = container.querySelectorAll('.vehicle-row');
        var remaining = parseInt(this.dataset.remaining);
        if (rows.length >= remaining) {
            alert('Cannot add more vehicles. Remaining quantity is ' + remaining + '.');
            return;
        }
        var newIndex = rows.length;
        var newRow = rows[0].cloneNode(true);
        newRow.querySelectorAll('input').forEach(function(input) {
            var name = input.name;
            // e.g. items[0][vehicles][0][chassis_number]
            name = name.replace(/\[vehicles\]\[\d+\]/, '[vehicles][' + newIndex + ']');
            input.name = name;
            if (name.indexOf('color_name') !== -1) {
                var firstColorInp = rows[0].querySelector('input[name*="[color_name]"]');
                if (firstColorInp) input.value = firstColorInp.value;
            } else {
                input.value = '';
            }
            input.style.border = '';
            var msg = input.parentNode.querySelector('.validation-message');
            if (msg) { msg.textContent = ''; msg.className = 'validation-message small mt-1'; }
        });
        
        var actionDiv = newRow.querySelector('.remove-btn-col');
        if (actionDiv) {
            actionDiv.innerHTML = '<button type="button" class="btn btn-outline-danger btn-remove"><i class="bx bx-trash me-1"></i> Remove</button>';
        }
        container.appendChild(newRow);
    });
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-remove')) {
        var row = e.target.closest('.vehicle-row');
        var container = row.parentNode;
        row.remove();
        
        var rows = container.querySelectorAll('.vehicle-row');
        rows.forEach(function(r, index) {
            r.querySelectorAll('input').forEach(function(input) {
                var name = input.name;
                name = name.replace(/\[vehicles\]\[\d+\]/, '[vehicles][' + index + ']');
                input.name = name;
            });
            var actionDiv = r.querySelector('.remove-btn-col');
            if (actionDiv) {
                if (index === 0) {
                    actionDiv.innerHTML = '';
                } else if (!actionDiv.querySelector('.btn-remove')) {
                    actionDiv.innerHTML = '<button type="button" class="btn btn-outline-danger btn-remove"><i class="bx bx-trash me-1"></i> Remove</button>';
                }
            }
        });
        
        revalidateFieldType('chassis_number');
    }
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-remove-received')) {
        if (!confirm('Are you sure you want to remove this vehicle from inventory?')) {
            return;
        }
        var btn = e.target.closest('.btn-remove-received');
        var id = btn.dataset.id;
        var row = btn.closest('.vehicle-row');
        
        var container = document.getElementById('deleted-vehicles-container');
        var hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'delete_vehicles[]';
        hiddenInput.value = id;
        container.appendChild(hiddenInput);
        
        row.remove();
        revalidateFieldType('chassis_number');
    }
});

document.getElementById('receiveForm').addEventListener('submit', function(e) {
    var inputs = this.querySelectorAll('input[required]');
    var errors = [];
    
    inputs.forEach(function(input) {
        var val = input.value.trim();
        if (!val) {
            var label = input.name.indexOf('chassis_number') !== -1 ? 'Chassis number' : 'Motor number';
            errors.push(label + ' is required for all vehicles.');
        }
        if (input.style.borderColor === 'rgb(220, 53, 69)' || input.style.borderColor === '#dc3545') {
            errors.push('Please fix invalid values before submitting.');
        }
    });
    
    if (errors.length > 0) {
        e.preventDefault();
        alert(errors[0]);
    }
});
</script>
@endsection

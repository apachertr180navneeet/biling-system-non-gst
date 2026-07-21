@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Vehicle Inventory
    </h4>

    @if(!empty($lowStockVariants))
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
            <i class="bx bx-error fs-4 me-2"></i> Low Stock Vehicle Variant Alerts
        </h6>
        <ul class="mb-0 ps-3">
            @foreach($lowStockVariants as $variantName => $info)
            <li>
                <strong>{{ $variantName }}</strong>: Only <strong>{{ $info['available'] }}</strong> available (Min Threshold: {{ $info['min_stock'] }})
            </li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Search filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.vehicle-inventories.index') }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search by Vehicle, Chassis No, Motor No or Battery No" value="{{ $search ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select no-select2">
                            <option value="">-- All Statuses --</option>
                            <option value="available" {{ ($statusFilter ?? '') == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="sold" {{ ($statusFilter ?? '') == 'sold' ? 'selected' : '' }}>Sold</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-search"></i> Search</button>
                            <a href="{{ route('admin.vehicle-inventories.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i> Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Vehicles</h5>
            <div>
                <a href="{{ route('admin.vehicle-inventories.export', ['search' => request('search'), 'status' => request('status')]) }}" class="btn btn-outline-success btn-sm me-2"><i class="bx bx-file-export"></i> Export</a>
                <a href="{{ route('admin.vehicle-purchase-orders.index') }}" class="btn btn-sm btn-primary">Vehicle POs</a>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Vehicle</th>
                        <th>Chassis No</th>
                        <th>Motor No</th>
                        <th>Battery No</th>
                        <th>Charger No</th>
                        <th>Controller No</th>
                        <th>Convertor No</th>
                        <th>Manual No</th>
                        <th>Purchase Price</th>
                        <th>Status</th>
                        <th>PO Ref</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventories as $i)
                    @php
                        $variantInfo = $lowStockVariants[$i->vehicle_description] ?? null;
                    @endphp
                    <tr class="@if($i->status == 'available' && $variantInfo) table-warning @endif">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $i->vehicle_description }}</strong>
                            @if($i->status == 'available' && $variantInfo)
                            <br><span class="badge bg-danger">Low Stock ({{ $variantInfo['available'] }}/{{ $variantInfo['min_stock'] }})</span>
                            @endif
                        </td>
                        <td>{{ $i->chassis_number ?? '-' }}</td>
                        <td>{{ $i->motor_number ?? '-' }}</td>
                        <td>{{ $i->battery_number ?? '-' }}</td>
                        <td>{{ $i->charger_number ?? '-' }}</td>
                        <td>{{ $i->controller_number ?? '-' }}</td>
                        <td>{{ $i->convertor_number ?? '-' }}</td>
                        <td>{{ $i->manual_number ?? '-' }}</td>
                        <td>{{ number_format($i->purchase_price, 2) }}</td>
                        <td>
                            @if($i->status == 'available')
                            <span class="badge bg-success">{{ $i->status }}</span>
                            @else
                            <span class="badge bg-secondary">{{ $i->status }}</span>
                            @endif
                        </td>
                        <td>
                            @if($i->purchaseOrder)
                            <a href="{{ route('admin.vehicle-purchase-orders.show', $i->purchaseOrder) }}">{{ $i->purchaseOrder->po_number }}</a>
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.vehicle-inventories.toggle-status-sold', $i->id) }}" method="POST" class="d-inline">
                                @csrf
                                @if($i->status == 'available')
                                <button type="submit" class="btn btn-xs btn-outline-danger" onclick="return confirm('Mark this vehicle as Sold?')">Mark Sold</button>
                                @else
                                <button type="submit" class="btn btn-xs btn-outline-success" onclick="return confirm('Mark this vehicle as Available?')">Mark Available</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="13" class="text-center">No vehicles in inventory.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $inventories->links() }}</div>
    </div>
</div>
@endsection

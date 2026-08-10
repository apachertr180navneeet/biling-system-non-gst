@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Rate List (Master Price List)</h4>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bx bx-printer me-1"></i> Print Rate List</button>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.rate-list') }}" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search by name or part number...">
                </div>
                <div class="col-md-4">
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ ($type ?? 'all') === 'all' ? 'selected' : '' }}>-- All Items (Vehicles & Parts) --</option>
                        <option value="vehicle" {{ ($type ?? '') === 'vehicle' ? 'selected' : '' }}>Vehicles Master Only</option>
                        <option value="part" {{ ($type ?? '') === 'part' ? 'selected' : '' }}>Spare Parts Only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Vehicles Rate List -->
    @if(count($vehicles) > 0)
    <div class="card shadow-sm mb-4">
        <div class="card-header border-bottom bg-light">
            <h5 class="card-title mb-0 text-primary"><i class="bx bx-car me-2"></i> Vehicle Masters Rate List</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Variant Name</th>
                        <th>Color</th>
                        <th>Fuel Type</th>
                        <th>Battery Type / Make</th>
                        <th class="text-end">Ex-Showroom Price / Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicles as $idx => $v)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="fw-bold">{{ $v->variant_name }}</td>
                        <td>{{ $v->color_name ?? '-' }}</td>
                        <td><span class="badge bg-label-info">{{ $v->fuel_type }}</span></td>
                        <td>{{ $v->battery_type ?? 'N/A' }} / {{ $v->battery_make ?? 'N/A' }}</td>
                        <td class="text-end fw-bold text-primary fs-6">₹{{ number_format($v->ex_showroom_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Spare Parts Rate List -->
    @if(count($parts) > 0)
    <div class="card shadow-sm">
        <div class="card-header border-bottom bg-light">
            <h5 class="card-title mb-0 text-success"><i class="bx bx-wrench me-2"></i> Spare Parts Rate List</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Part Number</th>
                        <th>Part Name</th>
                        <th class="text-end">Purchase Rate</th>
                        <th class="text-end">Selling Rate / Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parts as $idx => $p)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="fw-bold">{{ $p->part_no ?? '-' }}</td>
                        <td class="fw-semibold">{{ $p->name }}</td>
                        <td class="text-end text-muted">₹{{ number_format($p->purchase_price ?? 0, 2) }}</td>
                        <td class="text-end fw-bold text-success fs-6">₹{{ number_format($p->selling_price ?? 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

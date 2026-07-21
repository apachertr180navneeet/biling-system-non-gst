@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Spare Part Inventory
    </h4>

    <!-- Search filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.spare-part-stocks.index') }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search by Part No or Part Name" value="{{ $search ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select no-select2">
                            <option value="">-- All Stock Statuses --</option>
                            <option value="low_stock" {{ ($statusFilter ?? '') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out_of_stock" {{ ($statusFilter ?? '') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                            <option value="available" {{ ($statusFilter ?? '') == 'available' ? 'selected' : '' }}>Available</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-search"></i> Search</button>
                            <a href="{{ route('admin.spare-part-stocks.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i> Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Stock Levels</h5>
            <div>
                <a href="{{ route('admin.spare-part-stocks.export', ['search' => request('search'), 'status' => request('status')]) }}" class="btn btn-outline-success btn-sm me-2"><i class="bx bx-file-export"></i> Export</a>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#adjustStockModal">Adjust Stock</button>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Part No.</th>
                        <th>Part Name</th>
                        <th>Qty</th>
                        <th>Min Stock</th>
                        <th>Purchase Price</th>
                        <th>Status</th>
                        <th>PO Ref</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $s)
                    @php
                        $effMin = ($s->sparePart && $s->sparePart->min_stock > 0) ? $s->sparePart->min_stock : $s->min_quantity;
                        $isOut = $s->quantity < 1;
                        $isLow = !$isOut && ($effMin > 0 && $s->quantity <= $effMin);
                    @endphp
                    <tr class="@if($isOut) table-secondary @elseif($isLow) table-danger @endif">
                        <td>{{ $loop->iteration }}</td>
                        <td><code>{{ $s->sparePart->part_no ?? '-' }}</code></td>
                        <td>{{ $s->sparePart->name ?? '-' }}</td>
                        <td><strong>{{ $s->quantity }}</strong></td>
                        <td><span class="badge bg-label-info">{{ $effMin }}</span></td>
                        <td>{{ number_format($s->purchase_price, 2) }}</td>
                        <td>
                            @if($isOut)
                            <span class="badge bg-secondary">Out of Stock</span>
                            @elseif($isLow)
                            <span class="badge bg-danger"><i class="bx bx-error me-1"></i>Low Stock</span>
                            @else
                            <span class="badge bg-success">Available</span>
                            @endif
                        </td>
                        <td>
                            @if($s->purchaseOrder)
                            <a href="{{ route('admin.purchase-orders.show', $s->purchaseOrder) }}">{{ $s->purchaseOrder->order_number }}</a>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center">No stock records. Receive parts via Purchase Orders.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $stocks->links() }}</div>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.spare-part-stocks.adjust') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Part Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="spare_part_id" class="form-label">Select Part</label>
                        <select name="spare_part_id" id="spare_part_id" class="form-select" required>
                            <option value="">-- Select Spare Part --</option>
                            @foreach($spareParts as $p)
                            <option value="{{ $p->id }}">{{ $p->part_no }} - {{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="adjustment_type" class="form-label">Adjustment Type</label>
                        <select name="adjustment_type" id="adjustment_type" class="form-select no-select2" required>
                            <option value="in">Stock In (+)</option>
                            <option value="out">Stock Out (-)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes / Reference</label>
                        <input type="text" name="notes" id="notes" class="form-control" placeholder="e.g. Sold to customer, Manual count adjustment">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

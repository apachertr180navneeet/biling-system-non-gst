@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Reports /</span> Spare Part Stock Ledger
    </h4>

    <!-- Search Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.reports.part-ledger') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <label for="search" class="form-label">Part Name or Part Number</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search by name or number..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <label for="transaction_type" class="form-label">Transaction Type</label>
                    <select name="transaction_type" id="transaction_type" class="form-select no-select2">
                        <option value="">-- All Transactions --</option>
                        <option value="in" {{ request('transaction_type') == 'in' ? 'selected' : '' }}>Stock In</option>
                        <option value="out" {{ request('transaction_type') == 'out' ? 'selected' : '' }}>Stock Out</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Search</button>
                    <a href="{{ route('admin.reports.part-ledger') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Balance Table -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Part Stock Balance Summary</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr class="table-light">
                        <th>Part No.</th>
                        <th>Part Name</th>
                        <th>Unit</th>
                        <th class="text-center">Total Stock In</th>
                        <th class="text-center">Total Stock Out</th>
                        <th class="text-center">Remaining Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summaries as $s)
                    <tr>
                        <td><code>{{ $s->part_no }}</code></td>
                        <td><strong>{{ $s->name }}</strong></td>
                        <td>{{ $s->unit ?? 'pcs' }}</td>
                        <td class="text-center text-success font-weight-bold">+{{ $s->total_in }}</td>
                        <td class="text-center text-danger font-weight-bold">-{{ $s->total_out }}</td>
                        <td class="text-center">
                            @php
                                $isLow = $s->min_stock > 0 && $s->remaining <= $s->min_stock;
                            @endphp
                            <span class="badge bg-{{ $s->remaining < 1 ? 'secondary' : ($isLow ? 'danger' : 'success') }} fs-6">
                                {{ $s->remaining }}
                            </span>
                            @if($isLow && $s->remaining >= 1)
                            <span class="badge bg-danger ms-1">Low Stock (Min: {{ $s->min_stock }})</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center">No part stock summaries available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Ledger Log -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Chronological Transaction History</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Part No.</th>
                        <th>Part Name</th>
                        <th>Type</th>
                        <th class="text-center">Quantity</th>
                        <th>Reference / Document</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledger as $item)
                    <tr>
                        <td>{{ $item->created_at ? $item->created_at->format('Y-m-d H:i') : '-' }}</td>
                        <td><code>{{ $item->sparePart->part_no ?? '-' }}</code></td>
                        <td>{{ $item->sparePart->name ?? '-' }}</td>
                        <td>
                            @if($item->transaction_type == 'in')
                            <span class="badge bg-label-success">Stock In</span>
                            @else
                            <span class="badge bg-label-danger">Stock Out</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <strong>
                                @if($item->transaction_type == 'in')
                                +{{ $item->quantity }}
                                @else
                                -{{ $item->quantity }}
                                @endif
                            </strong>
                        </td>
                        <td><code>{{ $item->reference_no ?? '-' }}</code></td>
                        <td><span class="text-muted">{{ $item->notes ?? '-' }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center">No transaction records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $ledger->links() }}
        </div>
    </div>
</div>
@endsection

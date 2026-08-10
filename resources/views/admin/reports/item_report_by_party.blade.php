@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Item Report By Party</h4>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bx bx-printer me-1"></i> Print Report</button>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.item-report-by-party') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Select Party (Customer / Supplier)</label>
                    <select name="party_id" class="form-select" onchange="this.form.submit()">
                        @foreach($partyList as $p)
                            <option value="{{ $p['id'] }}" {{ $selectedParty === $p['id'] ? 'selected' : '' }}>
                                {{ $p['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Date Filter</label>
                    <select name="date_filter" class="form-select" onchange="this.form.submit()">
                        <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_30_days" {{ $dateFilter === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Financial Year</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                Items for Party: <strong class="text-primary">{{ $selectedPartyData['name'] ?? 'Selected Party' }}</strong> 
                ({{ $selectedPartyData['type'] ?? '' }})
            </h5>
            <span class="badge bg-label-info">{{ count($itemsData) }} Item Transaction(s)</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Doc / Ref No</th>
                        <th>Transaction</th>
                        <th>Item Type</th>
                        <th>Item Description</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itemsData as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['date'] }}</td>
                        <td class="fw-bold">{{ $row['doc_no'] }}</td>
                        <td>
                            @if($row['transaction_type'] === 'Sale')
                                <span class="badge bg-label-success"><i class="bx bx-up-arrow-alt me-1"></i> Sale</span>
                            @else
                                <span class="badge bg-label-primary"><i class="bx bx-down-arrow-alt me-1"></i> Purchase</span>
                            @endif
                        </td>
                        <td><span class="badge bg-label-info">{{ $row['item_type'] }}</span></td>
                        <td class="fw-semibold">{{ $row['item_name'] }}</td>
                        <td class="text-center fw-bold">{{ $row['qty'] }}</td>
                        <td class="text-end">₹{{ number_format($row['rate'], 2) }}</td>
                        <td class="text-end fw-bold text-primary">₹{{ number_format($row['amount'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No item transactions found for the selected party in this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

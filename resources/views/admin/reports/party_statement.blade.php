@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Party Statement (Ledger)</h4>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bx bx-printer me-1"></i> Print Statement</button>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.party-statement') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Select Party</label>
                    <select name="party_id" class="form-select" onchange="this.form.submit()">
                        @foreach($partyList as $p)
                            <option value="{{ $p['id'] }}" {{ $partySelect === $p['id'] ? 'selected' : '' }}>
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
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> View Statement</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statement Table Card -->
    <div class="card shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                Ledger Statement for: <strong class="text-primary">{{ $partyData['name'] ?? 'Party' }}</strong> 
                ({{ $partyData['type'] ?? '' }})
            </h5>
            <span class="badge bg-label-info">{{ count($ledgerEntries) }} Entry(ies)</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Doc / Ref No</th>
                        <th>Type</th>
                        <th>Payment Mode</th>
                        <th class="text-end">Billed / Debit</th>
                        <th class="text-end">Paid / Credit</th>
                        <th class="text-end">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledgerEntries as $entry)
                    <tr>
                        <td>{{ $entry['display_date'] }}</td>
                        <td class="fw-bold">{{ $entry['doc_no'] }}</td>
                        <td><span class="badge bg-label-secondary">{{ $entry['type'] }}</span></td>
                        <td>{{ $entry['mode'] ?? '-' }}</td>
                        <td class="text-end text-primary fw-semibold">
                            {{ $entry['debit'] > 0 ? '₹' . number_format($entry['debit'], 2) : '-' }}
                        </td>
                        <td class="text-end text-success fw-semibold">
                            {{ $entry['credit'] > 0 ? '₹' . number_format($entry['credit'], 2) : '-' }}
                        </td>
                        <td class="text-end fw-bold {{ $entry['running_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                            ₹{{ number_format($entry['running_balance'], 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No transactions recorded for this party in the selected date range.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

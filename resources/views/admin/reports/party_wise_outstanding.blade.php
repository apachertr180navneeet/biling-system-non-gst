@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Party Wise Outstanding Report</h4>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bx bx-printer me-1"></i> Print Report</button>
    </div>

    <!-- Outstanding Table Card -->
    <div class="card shadow-sm">
        <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0">Complete Party Outstanding Summary</h5>
            <div class="d-flex align-items-center gap-2">
                <form method="GET" action="{{ route('admin.reports.party-wise-outstanding') }}" class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search party name or phone..." value="{{ $search }}">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    @if($search)
                    <a href="{{ route('admin.reports.party-wise-outstanding') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                    @endif
                </form>
                <span class="badge bg-label-primary ms-2">{{ count($partyOutstandings) }} Party(ies)</span>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Party Type</th>
                        <th>Party Name</th>
                        <th>Phone Number</th>
                        <th class="text-end">Total Billed</th>
                        <th class="text-end">Total Paid / Deposited</th>
                        <th class="text-end text-danger">Outstanding Balance</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totBilled = 0; $totPaid = 0; $totBal = 0; @endphp
                    @forelse($partyOutstandings as $idx => $row)
                    @php
                        $totBilled += $row['total_billed'];
                        $totPaid += $row['total_paid'];
                        $totBal += $row['outstanding'];
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <span class="badge {{ $row['type'] === 'Customer' ? 'bg-label-primary' : 'bg-label-info' }}">
                                {{ $row['type'] }}
                            </span>
                        </td>
                        <td class="fw-bold">{{ $row['name'] }}</td>
                        <td>{{ $row['phone'] }}</td>
                        <td class="text-end">₹{{ number_format($row['total_billed'], 2) }}</td>
                        <td class="text-end text-success fw-semibold">₹{{ number_format($row['total_paid'], 2) }}</td>
                        <td class="text-end fw-bold {{ $row['outstanding'] > 0 ? 'text-danger' : 'text-success' }}">
                            ₹{{ number_format($row['outstanding'], 2) }}
                        </td>
                        <td class="text-center">
                            @if(isset($row['view_url']) && $row['view_url'] !== '#')
                            <a href="{{ $row['view_url'] }}" class="btn btn-xs btn-outline-primary">
                                <i class="bx bx-history me-1"></i> View Ledger
                            </a>
                            @else
                            <a href="{{ route('admin.reports.party-statement', ['party_id' => 'supplier_' . $row['id']]) }}" class="btn btn-xs btn-outline-primary">
                                <i class="bx bx-file me-1"></i> Statement
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No party balances recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="4" class="fw-bold">TOTAL OUTSTANDING</th>
                        <th class="text-end fw-bold">₹{{ number_format($totBilled, 2) }}</th>
                        <th class="text-end fw-bold text-success">₹{{ number_format($totPaid, 2) }}</th>
                        <th class="text-end fw-bold text-danger fs-6">₹{{ number_format($totBal, 2) }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

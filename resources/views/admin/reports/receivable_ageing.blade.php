@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Receivable Ageing Report</h4>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bx bx-printer me-1"></i> Print Report</button>
    </div>

    <!-- Ageing Table Card -->
    <div class="card shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Accounts Receivable Ageing Breakdown</h5>
            <span class="badge bg-danger">{{ count($ageingList) }} Pending Invoice(s)</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Doc Type</th>
                        <th>Doc No</th>
                        <th>Date</th>
                        <th>Customer Name</th>
                        <th class="text-end">Total Billed</th>
                        <th class="text-end">Received</th>
                        <th class="text-end text-danger">Balance</th>
                        <th class="text-center">Age (Days)</th>
                        <th class="text-end">0 - 30 Days</th>
                        <th class="text-end">31 - 60 Days</th>
                        <th class="text-end">61 - 90 Days</th>
                        <th class="text-end text-danger">> 90 Days</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totBilled = 0; $totReceived = 0; $totBal = 0;
                        $tot0_30 = 0; $tot31_60 = 0; $tot61_90 = 0; $tot90Plus = 0;
                    @endphp
                    @forelse($ageingList as $row)
                    @php
                        $totBilled += $row['total_amount'];
                        $totReceived += $row['received'];
                        $totBal += $row['balance'];
                        $tot0_30 += $row['b_0_30'];
                        $tot31_60 += $row['b_31_60'];
                        $tot61_90 += $row['b_61_90'];
                        $tot90Plus += $row['b_90_plus'];
                    @endphp
                    <tr>
                        <td><span class="badge {{ $row['type'] === 'Vehicle Invoice' ? 'bg-label-primary' : 'bg-label-info' }}">{{ $row['type'] }}</span></td>
                        <td class="fw-bold">{{ $row['doc_no'] }}</td>
                        <td>{{ $row['date'] }}</td>
                        <td class="fw-semibold">{{ $row['party_name'] }}</td>
                        <td class="text-end">₹{{ number_format($row['total_amount'], 2) }}</td>
                        <td class="text-end text-success">₹{{ number_format($row['received'], 2) }}</td>
                        <td class="text-end fw-bold text-danger">₹{{ number_format($row['balance'], 2) }}</td>
                        <td class="text-center fw-bold">{{ $row['days'] }}</td>
                        <td class="text-end fw-semibold">{{ $row['b_0_30'] > 0 ? '₹' . number_format($row['b_0_30'], 2) : '-' }}</td>
                        <td class="text-end fw-semibold text-warning">{{ $row['b_31_60'] > 0 ? '₹' . number_format($row['b_31_60'], 2) : '-' }}</td>
                        <td class="text-end fw-semibold text-warning">{{ $row['b_61_90'] > 0 ? '₹' . number_format($row['b_61_90'], 2) : '-' }}</td>
                        <td class="text-end fw-bold text-danger">{{ $row['b_90_plus'] > 0 ? '₹' . number_format($row['b_90_plus'], 2) : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-4 text-success fw-semibold">No pending receivable balances found.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="4" class="fw-bold">TOTAL RECEIVABLES</th>
                        <th class="text-end fw-bold">₹{{ number_format($totBilled, 2) }}</th>
                        <th class="text-end fw-bold text-success">₹{{ number_format($totReceived, 2) }}</th>
                        <th class="text-end fw-bold text-danger fs-6">₹{{ number_format($totBal, 2) }}</th>
                        <th></th>
                        <th class="text-end fw-bold">₹{{ number_format($tot0_30, 2) }}</th>
                        <th class="text-end fw-bold text-warning">₹{{ number_format($tot31_60, 2) }}</th>
                        <th class="text-end fw-bold text-warning">₹{{ number_format($tot61_90, 2) }}</th>
                        <th class="text-end fw-bold text-danger">₹{{ number_format($tot90Plus, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

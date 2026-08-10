@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Customer Ledger Statement</h4>
            <div class="text-muted">
                <strong>{{ $customer->name }}</strong> 
                @if($customer->company_name) ({{ $customer->company_name }}) @endif
                | Mobile: {{ $customer->phone ?? 'N/A' }} 
                | Email: {{ $customer->email ?? 'N/A' }}
            </div>
        </div>
        <div>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Customers
            </a>
            <button onclick="window.print()" class="btn btn-primary ms-2">
                <i class="bx bx-printer me-1"></i> Print Statement
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-label-primary">
                <div class="card-body">
                    <span class="fw-semibold d-block text-muted mb-1">Total Invoiced Amount</span>
                    <h3 class="card-title text-primary mb-0">₹{{ number_format($summary['total_billed'], 2) }}</h3>
                    <small class="text-muted">{{ $summary['total_invoices'] }} Invoice(s)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-label-success">
                <div class="card-body">
                    <span class="fw-semibold d-block text-muted mb-1">Total Paid / Deposited</span>
                    <h3 class="card-title text-success mb-0">₹{{ number_format($summary['total_paid'], 2) }}</h3>
                    <small class="text-muted">Received to Date</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm {{ $summary['outstanding_balance'] > 0 ? 'bg-label-danger' : 'bg-label-info' }}">
                <div class="card-body">
                    <span class="fw-semibold d-block text-muted mb-1">Current Outstanding Balance</span>
                    <h3 class="card-title {{ $summary['outstanding_balance'] > 0 ? 'text-danger' : 'text-info' }} mb-0">
                        ₹{{ number_format($summary['outstanding_balance'], 2) }}
                    </h3>
                    <small class="text-muted">{{ $summary['outstanding_balance'] > 0 ? 'Pending Payment' : 'Fully Cleared' }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="card shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Complete Ledger History</h5>
            <span class="badge bg-label-primary">Up to Today ({{ date('d-m-Y') }})</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Doc / Ref No</th>
                        <th>Payment Mode</th>
                        <th class="text-end">Billed (Debit)</th>
                        <th class="text-end">Paid (Credit)</th>
                        <th class="text-end">Running Balance</th>
                        <th>Notes / Details</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $item)
                    <tr>
                        <td>{{ $item['display_date'] }}</td>
                        <td>
                            @if($item['type'] === 'Vehicle Invoice')
                                <span class="badge bg-label-primary"><i class="bx bx-car me-1"></i> Vehicle Invoice</span>
                            @elseif($item['type'] === 'Part Invoice')
                                <span class="badge bg-label-info"><i class="bx bx-wrench me-1"></i> Part Invoice</span>
                            @elseif($item['type'] === 'Payment Rollback')
                                <span class="badge bg-label-warning"><i class="bx bx-undo me-1"></i> Rollback</span>
                            @else
                                <span class="badge bg-label-success"><i class="bx bx-check me-1"></i> Payment Received</span>
                            @endif
                        </td>
                        <td class="fw-bold">{{ $item['doc_no'] }}</td>
                        <td>{{ $item['payment_mode'] ?? '-' }}</td>
                        <td class="text-end text-primary fw-semibold">
                            {{ $item['debit'] > 0 ? '₹' . number_format($item['debit'], 2) : '-' }}
                        </td>
                        <td class="text-end text-success fw-semibold">
                            {{ $item['credit'] > 0 ? '₹' . number_format($item['credit'], 2) : '-' }}
                        </td>
                        <td class="text-end fw-bold {{ $item['running_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                            ₹{{ number_format($item['running_balance'], 2) }}
                        </td>
                        <td><small class="text-muted">{{ $item['notes'] }}</small></td>
                        <td class="text-center">
                            @if(isset($item['view_url']) && $item['view_url'] !== '#')
                            <a href="{{ $item['view_url'] }}" class="btn btn-xs btn-outline-primary" title="View Document">
                                <i class="bx bx-show"></i> View
                            </a>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No ledger transactions found for this customer.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

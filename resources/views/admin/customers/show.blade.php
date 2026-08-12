@extends('admin.layouts.app')
@section('style')
<style>
.stat-card-premium {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #ffffff;
    transition: all 0.25s ease-in-out;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
}
.stat-card-premium:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
}
.stat-icon-box {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
}
.bg-icon-billed { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
.bg-icon-paid { background: rgba(16, 185, 129, 0.12); color: #059669; }
.bg-icon-outstanding { background: rgba(239, 68, 68, 0.12); color: #dc2626; }
.bg-icon-cleared { background: rgba(6, 182, 212, 0.12); color: #0891b2; }

.info-card-box {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
}

.info-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 3px;
}
.info-value {
    font-size: 0.95rem;
    font-weight: 600;
    color: #0f172a;
}

.table-custom thead th {
    background-color: #f8fafc;
    color: #475569;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 700;
    padding: 14px 16px;
    border-bottom: 2px solid #e2e8f0;
}
.table-custom tbody tr {
    transition: background-color 0.2s ease;
}
.table-custom tbody tr:hover {
    background-color: #f1f5f9;
}
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-light">Admin / Customers /</span> {{ $customer->name }}
            </h4>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-label-primary">{{ ucfirst($customer->type) }}</span>
                <span class="badge {{ $customer->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">
                    <i class="bx bx-radio-circle-marked me-1"></i>{{ $customer->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.reports.party-statement', ['party_id' => 'customer_' . $customer->id]) }}" class="btn btn-warning shadow-sm fw-semibold">
                <i class="bx bx-receipt me-1"></i> Party Statement Report
            </a>
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary shadow-sm fw-semibold">
                <i class="bx bx-edit me-1"></i> Edit Customer
            </a>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card-premium h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Total Invoiced Amount</span>
                        <h3 class="fw-bold text-dark mb-1">₹{{ number_format($summary['total_billed'] ?? 0, 2) }}</h3>
                        <span class="badge bg-label-primary rounded-pill">
                            <i class="bx bx-file me-1"></i>{{ $summary['total_invoices'] ?? 0 }} Invoice(s)
                        </span>
                    </div>
                    <div class="stat-icon-box bg-icon-billed">
                        <i class="bx bx-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card-premium h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Total Paid / Deposited</span>
                        <h3 class="fw-bold text-success mb-1">₹{{ number_format($summary['total_paid'] ?? 0, 2) }}</h3>
                        <span class="badge bg-label-success rounded-pill">
                            <i class="bx bx-check-double me-1"></i>Received to Date
                        </span>
                    </div>
                    <div class="stat-icon-box bg-icon-paid">
                        <i class="bx bx-wallet"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card-premium h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Current Outstanding Balance</span>
                        <h3 class="fw-bold {{ ($summary['outstanding_balance'] ?? 0) > 0 ? 'text-danger' : 'text-info' }} mb-1">
                            ₹{{ number_format($summary['outstanding_balance'] ?? 0, 2) }}
                        </h3>
                        <span class="badge {{ ($summary['outstanding_balance'] ?? 0) > 0 ? 'bg-label-danger' : 'bg-label-info' }} rounded-pill">
                            <i class="bx {{ ($summary['outstanding_balance'] ?? 0) > 0 ? 'bx-error-circle' : 'bx-badge-check' }} me-1"></i>
                            {{ ($summary['outstanding_balance'] ?? 0) > 0 ? 'Pending Payment' : 'Fully Cleared' }}
                        </span>
                    </div>
                    <div class="stat-icon-box {{ ($summary['outstanding_balance'] ?? 0) > 0 ? 'bg-icon-outstanding' : 'bg-icon-cleared' }}">
                        <i class="bx {{ ($summary['outstanding_balance'] ?? 0) > 0 ? 'bx-trending-up' : 'bx-check-circle' }}"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Profile Info Box -->
    <div class="card info-card-box mb-4">
        <div class="card-header bg-transparent border-bottom p-3 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-user me-2 text-primary"></i> Customer Profile Information</h5>
            <span class="badge bg-label-secondary">ID: #{{ $customer->id }}</span>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="info-label">Full Name</div>
                    <div class="info-value text-dark fw-bold fs-6">{{ $customer->name }}</div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-label">Company Name</div>
                    <div class="info-value text-dark">{{ $customer->company_name ?? '-' }}</div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value text-dark"><i class="bx bx-phone me-1 text-primary"></i>{{ $customer->phone ?? '-' }}</div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-label">Email Address</div>
                    <div class="info-value text-dark"><i class="bx bx-envelope me-1 text-info"></i>{{ $customer->email ?? '-' }}</div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-label">State</div>
                    <div class="info-value text-dark">{{ $customer->state ?? '-' }}</div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-label">PAN Number</div>
                    <div class="info-value text-dark">{{ $customer->pan_no ?? '-' }}</div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-label">Aadhaar Number</div>
                    <div class="info-value text-dark">{{ $customer->aadhaar_no ?? '-' }}</div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-label">Address</div>
                    <div class="info-value text-dark">{{ $customer->address ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Ledger / All Transactions Card -->
    <div class="card info-card-box shadow-sm">
        <div class="card-header bg-transparent border-bottom p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bx bx-history me-2 text-primary"></i> Customer Ledger & All Transactions
                </h5>
                <small class="text-muted">Chronological statement of all invoices and payment deposits</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-label-primary px-3 py-2 rounded-pill fs-7">
                    <i class="bx bx-list-check me-1"></i> {{ count($history ?? []) }} Entry(ies)
                </span>
                <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-printer me-1"></i> Print
                </button>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction Type</th>
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
                    @forelse($history ?? [] as $item)
                    <tr>
                        <td class="fw-semibold text-secondary">{{ $item['display_date'] }}</td>
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
                        <td class="fw-bold text-dark">{{ $item['doc_no'] }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $item['payment_mode'] ?? '-' }}</span>
                        </td>
                        <td class="text-end text-primary fw-bold">
                            {{ $item['debit'] > 0 ? '₹' . number_format($item['debit'], 2) : '-' }}
                        </td>
                        <td class="text-end text-success fw-bold">
                            {{ $item['credit'] > 0 ? '₹' . number_format($item['credit'], 2) : '-' }}
                        </td>
                        <td class="text-end fw-bold {{ $item['running_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                            ₹{{ number_format($item['running_balance'], 2) }}
                        </td>
                        <td><small class="text-muted">{{ $item['notes'] }}</small></td>
                        <td class="text-center">
                            @if(isset($item['view_url']) && $item['view_url'] !== '#')
                            <a href="{{ $item['view_url'] }}" class="btn btn-xs btn-outline-primary" title="View Document">
                                <i class="bx bx-show me-1"></i> View
                            </a>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bx bx-folder-open fs-1 d-block mb-2 text-secondary"></i>
                            No ledger transactions recorded for this customer yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@section('script')
@endsection

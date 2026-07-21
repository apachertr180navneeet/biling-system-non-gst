@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">
            Party Report By Item
            <button type="button" class="btn btn-sm btn-outline-warning ms-2" id="favoriteBtn" onclick="toggleFavorite()" title="Favorite this report">
                <i class="bx bx-star" id="favoriteIcon"></i>
            </button>
        </h4>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#emailModal">
                <i class="bx bx-envelope me-1"></i> Email Excel
            </button>
            <a href="{{ route('admin.reports.party-report-by-item.export-excel', request()->query()) }}" class="btn btn-outline-success btn-sm">
                <i class="bx bx-download me-1"></i> Download Excel
            </a>
            <a href="{{ route('admin.reports.party-report-by-item.print-pdf', request()->query()) }}" target="_blank" class="btn btn-outline-danger btn-sm">
                <i class="bx bxs-file-pdf me-1"></i> Print PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.party-report-by-item') }}" id="reportFilterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Search / Select Item</label>
                        <select name="item_id" id="item_id_select" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Select Item / Vehicle --</option>
                            @foreach($itemList as $item)
                                <option value="{{ $item['id'] }}" {{ $selectedItem === $item['id'] ? 'selected' : '' }}>
                                    {{ $item['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Date Filter</label>
                        <select name="date_filter" id="date_filter_select" class="form-select no-select2" onchange="toggleCustomDates()">
                            <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="last_7_days" {{ $dateFilter === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="last_15_days" {{ $dateFilter === 'last_15_days' ? 'selected' : '' }}>Last 15 Days</option>
                            <option value="last_30_days" {{ $dateFilter === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="this_week" {{ $dateFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="previous_week" {{ $dateFilter === 'previous_week' ? 'selected' : '' }}>Previous Week</option>
                            <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="previous_month" {{ $dateFilter === 'previous_month' ? 'selected' : '' }}>Previous Month</option>
                            <option value="this_quarter" {{ $dateFilter === 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                            <option value="previous_quarter" {{ $dateFilter === 'previous_quarter' ? 'selected' : '' }}>Previous Quarter</option>
                            <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
                            <option value="previous_year" {{ $dateFilter === 'previous_year' ? 'selected' : '' }}>Previous Year</option>
                            <option value="current_financial_year" {{ $dateFilter === 'current_financial_year' ? 'selected' : '' }}>Current Financial Year</option>
                            <option value="previous_financial_year" {{ $dateFilter === 'previous_financial_year' ? 'selected' : '' }}>Previous Financial Year</option>
                            <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bx bx-filter-alt me-1"></i> Apply Filter
                            </button>
                            <a href="{{ route('admin.reports.party-report-by-item') }}" class="btn btn-outline-secondary">
                                Reset
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Custom Date Inputs -->
                <div class="row g-3 mt-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="customDateRow">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">From Date</label>
                        <input type="date" name="custom_from" class="form-control" value="{{ $customFrom }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">To Date</label>
                        <input type="date" name="custom_to" class="form-control" value="{{ $customTo }}">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Selected Item Banner -->
    <div class="card mb-4 border-start border-primary border-4">
        <div class="card-body py-3 d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted text-uppercase small fw-bold">Selected Item:</span>
                <h5 class="mb-0 text-primary fw-bold mt-1">
                    {{ $selectedItemData['name'] ?? 'No Item Selected' }}
                </h5>
            </div>
            @if($fromDate && $toDate)
                <div class="text-end">
                    <span class="badge bg-label-primary px-3 py-2 fs-6">
                        <i class="bx bx-calendar me-1"></i> {{ date('d/m/Y', strtotime($fromDate)) }} - {{ date('d/m/Y', strtotime($toDate)) }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- Report Table -->
    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Party Name</th>
                        <th class="text-center">Sales Qty</th>
                        <th class="text-end">Sales Amount</th>
                        <th class="text-center">Purchase Qty</th>
                        <th class="text-end">Purchase Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalSalesQty = 0;
                        $totalSalesAmt = 0;
                        $totalPurchaseQty = 0;
                        $totalPurchaseAmt = 0;
                    @endphp

                    @forelse($partyData as $row)
                        @php
                            $totalSalesQty += $row['sales_qty'];
                            $totalSalesAmt += $row['sales_amount'];
                            $totalPurchaseQty += $row['purchase_qty'];
                            $totalPurchaseAmt += $row['purchase_amount'];
                        @endphp
                        <tr>
                            <td class="fw-bold text-dark">{{ $row['party_name'] }}</td>
                            <td class="text-center">{{ $row['sales_qty'] > 0 ? $row['sales_qty'] : '-' }}</td>
                            <td class="text-end fw-semibold {{ $row['sales_amount'] > 0 ? 'text-success' : '' }}">
                                {{ $row['sales_amount'] > 0 ? '₹' . number_format($row['sales_amount'], 2) : '-' }}
                            </td>
                            <td class="text-center">{{ $row['purchase_qty'] > 0 ? $row['purchase_qty'] : '-' }}</td>
                            <td class="text-end fw-semibold {{ $row['purchase_amount'] > 0 ? 'text-danger' : '' }}">
                                {{ $row['purchase_amount'] > 0 ? '₹' . number_format($row['purchase_amount'], 2) : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bx bx-info-circle fs-3 d-block mb-1"></i>
                                No transactions found for the selected item and date range.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($partyData) > 0)
                    <tfoot class="table-light border-top border-2">
                        <tr class="fw-bold fs-6">
                            <td>Total</td>
                            <td class="text-center">{{ $totalSalesQty }}</td>
                            <td class="text-end text-success">₹{{ number_format($totalSalesAmt, 2) }}</td>
                            <td class="text-center">{{ $totalPurchaseQty }}</td>
                            <td class="text-end text-danger">₹{{ number_format($totalPurchaseAmt, 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.reports.party-report-by-item.email-excel') }}">
                @csrf
                <input type="hidden" name="item_id" value="{{ $selectedItem }}">
                <input type="hidden" name="date_filter" value="{{ $dateFilter }}">
                <input type="hidden" name="custom_from" value="{{ $customFrom }}">
                <input type="hidden" name="custom_to" value="{{ $customTo }}">

                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-envelope me-1"></i> Email Excel Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Recipient Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-paper-plane me-1"></i> Send Email</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleCustomDates() {
    const filter = document.getElementById('date_filter_select').value;
    const customRow = document.getElementById('customDateRow');
    if (filter === 'custom') {
        customRow.classList.remove('d-none');
    } else {
        customRow.classList.add('d-none');
    }
}

function toggleFavorite() {
    const icon = document.getElementById('favoriteIcon');
    const isFav = icon.classList.contains('bxs-star');
    if (isFav) {
        icon.classList.remove('bxs-star', 'text-warning');
        icon.classList.add('bx-star');
        localStorage.setItem('fav_party_report', '0');
    } else {
        icon.classList.remove('bx-star');
        icon.classList.add('bxs-star', 'text-warning');
        localStorage.setItem('fav_party_report', '1');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('fav_party_report') === '1') {
        const icon = document.getElementById('favoriteIcon');
        if (icon) {
            icon.classList.remove('bx-star');
            icon.classList.add('bxs-star', 'text-warning');
        }
    }
});
</script>
@endsection

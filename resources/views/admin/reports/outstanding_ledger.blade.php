@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Reports /</span> Outstanding Sale & Purchase Ledger
    </h4>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <!-- Sales Outstanding Card -->
        <div class="col-md-6 col-lg-6 mb-3">
            <div class="card h-100 {{ $tab === 'sales' ? 'border-primary border-2' : '' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar bg-light-primary rounded p-2">
                            <i class="bx bx-trending-up text-primary fs-3"></i>
                        </div>
                        <span class="badge bg-label-primary rounded-pill">Sales</span>
                    </div>
                    <h4 class="card-title mb-1 text-primary">₹{{ number_format($totalOutstandingSales, 2) }}</h4>
                    <p class="text-muted mb-2">Total Outstanding Receivables</p>
                    <div class="d-flex justify-content-between text-xs border-top pt-2">
                        <span>Vehicles: <strong>₹{{ number_format($totalOutstandingSalesVehicle, 2) }}</strong></span>
                        <span>Parts: <strong>₹{{ number_format($totalOutstandingSalesParts, 2) }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchases Outstanding Card -->
        <div class="col-md-6 col-lg-6 mb-3">
            <div class="card h-100 {{ $tab === 'purchases' ? 'border-danger border-2' : '' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar bg-light-danger rounded p-2">
                            <i class="bx bx-trending-down text-danger fs-3"></i>
                        </div>
                        <span class="badge bg-label-danger rounded-pill">Purchases</span>
                    </div>
                    <h4 class="card-title mb-1 text-danger">₹{{ number_format($totalOutstandingPurchases, 2) }}</h4>
                    <p class="text-muted mb-2">Total Outstanding Payables</p>
                    <div class="d-flex justify-content-between text-xs border-top pt-2">
                        <span>Vehicles: <strong>₹{{ number_format($totalOutstandingPurchasesVehicle, 2) }}</strong></span>
                        <span>Parts: <strong>₹{{ number_format($totalOutstandingPurchasesParts, 2) }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="nav-align-top mb-4">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a href="{{ route('admin.reports.outstanding-ledger', ['tab' => 'sales', 'type' => $type, 'search' => $search, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" 
                   class="nav-link {{ $tab === 'sales' ? 'active' : '' }}" role="tab">
                    <i class="tf-icons bx bx-up-arrow-circle me-1"></i> Outstanding Sales
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.reports.outstanding-ledger', ['tab' => 'purchases', 'type' => $type, 'search' => $search, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" 
                   class="nav-link {{ $tab === 'purchases' ? 'active' : '' }}" role="tab">
                    <i class="tf-icons bx bx-down-arrow-circle me-1"></i> Outstanding Purchases
                </a>
            </li>
        </ul>
        
        <div class="tab-content border-top-0 rounded-bottom">
            <!-- Filter Form -->
            <div class="card mb-4 shadow-none border-0">
                <div class="card-body p-0 pb-3">
                    <form action="{{ route('admin.reports.outstanding-ledger') }}" method="GET" class="row g-3">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search Party / Doc No.</label>
                            <input type="text" name="search" id="search" class="form-control" placeholder="Name, number, mobile..." value="{{ $search }}">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="type" class="form-label">Category Type</label>
                            <select name="type" id="type" class="form-select">
                                <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All (Vehicles & Parts)</option>
                                <option value="vehicle" {{ $type === 'vehicle' ? 'selected' : '' }}>Vehicles Only</option>
                                <option value="part" {{ $type === 'part' ? 'selected' : '' }}>Parts Only</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="from_date" class="form-label">From Date</label>
                            <input type="date" name="from_date" id="from_date" class="form-control" value="{{ $fromDate }}">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="to_date" class="form-label">To Date</label>
                            <input type="date" name="to_date" id="to_date" class="form-control" value="{{ $toDate }}">
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2 w-100">Search</button>
                            <a href="{{ route('admin.reports.outstanding-ledger', ['tab' => $tab]) }}" class="btn btn-outline-secondary w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Ledger Table -->
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr class="table-light">
                            <th>Date</th>
                            <th>Doc Number</th>
                            <th>Category</th>
                            <th>{{ $tab === 'sales' ? 'Customer' : 'Supplier' }}</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">{{ $tab === 'sales' ? 'Received Amount' : 'Paid Amount' }}</th>
                            <th class="text-end text-danger">Outstanding Balance</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ledger as $item)
                        <tr>
                            <td>
                                @if(is_string($item->doc_date))
                                    {{ \Carbon\Carbon::parse($item->doc_date)->format('Y-m-d') }}
                                @else
                                    {{ $item->doc_date ? $item->doc_date->format('Y-m-d') : '-' }}
                                @endif
                            </td>
                            <td>
                                <strong>{{ $item->doc_number }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-label-{{ $item->sub_type === 'vehicle' ? 'info' : 'warning' }}">
                                    {{ ucfirst($item->sub_type) }}
                                </span>
                            </td>
                            <td>
                                {{ $item->party_name ?? 'N/A' }}
                            </td>
                            <td class="text-end">
                                ₹{{ number_format($item->total_amount, 2) }}
                            </td>
                            <td class="text-end text-success">
                                ₹{{ number_format($item->received_amount, 2) }}
                            </td>
                            <td class="text-end text-danger fw-bold">
                                ₹{{ number_format($item->balance, 2) }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    @if($tab === 'sales')
                                        @if($item->sub_type === 'vehicle')
                                            <a href="{{ route('admin.vehicle-sales-invoices.show', $item->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            @if($item->balance > 0)
                                                <button class="btn btn-sm btn-success receive-payment-btn" data-url="{{ route('admin.vehicle-sales-invoices.receive-payment', $item->id) }}" data-balance="{{ $item->balance }}" data-title="Receive Payment" title="Receive Payment">
                                                    <i class="bx bx-wallet"></i>
                                                </button>
                                            @endif
                                        @else
                                            <a href="{{ route('admin.part-sales-invoices.show', $item->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            @if($item->balance > 0)
                                                <button class="btn btn-sm btn-success receive-payment-btn" data-url="{{ route('admin.part-sales-invoices.receive-payment', $item->id) }}" data-balance="{{ $item->balance }}" data-title="Receive Payment" title="Receive Payment">
                                                    <i class="bx bx-wallet"></i>
                                                </button>
                                            @endif
                                        @endif
                                    @else
                                        @if($item->sub_type === 'vehicle')
                                            <a href="{{ route('admin.vehicle-purchase-orders.show', $item->id) }}" class="btn btn-sm btn-outline-danger" title="View">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            @if($item->balance > 0)
                                                <button class="btn btn-sm btn-danger receive-payment-btn" data-url="{{ route('admin.vehicle-purchase-orders.receive-payment', $item->id) }}" data-balance="{{ $item->balance }}" data-title="Pay Amount" title="Pay Amount">
                                                    <i class="bx bx-wallet"></i>
                                                </button>
                                            @endif
                                        @else
                                            <a href="{{ route('admin.purchase-orders.show', $item->id) }}" class="btn btn-sm btn-outline-danger" title="View">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            @if($item->balance > 0)
                                                <button class="btn btn-sm btn-danger receive-payment-btn" data-url="{{ route('admin.purchase-orders.receive-payment', $item->id) }}" data-balance="{{ $item->balance }}" data-title="Pay Amount" title="Pay Amount">
                                                    <i class="bx bx-wallet"></i>
                                                </button>
                                            @endif
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bx bx-info-circle fs-3 d-block mb-2"></i>
                                No outstanding records found matching your filters.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($ledger && method_exists($ledger, 'links'))
            <div class="d-flex justify-content-end mt-4">
                {{ $ledger->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(function(){
    $('.receive-payment-btn').click(function(){
        var url = $(this).data('url');
        var balance = $(this).data('balance');
        var titleText = $(this).data('title') || 'Receive Payment';
        var promptText = titleText === 'Receive Payment' 
            ? 'Enter the amount received. Outstanding Balance: ₹' + balance 
            : 'Enter the amount paid. Outstanding Balance: ₹' + balance;
        
        Swal.fire({
            title: titleText,
            text: promptText,
            input: 'number',
            inputAttributes: {
                min: 0.01,
                max: balance,
                step: 0.01
            },
            showCancelButton: true,
            confirmButtonText: 'Submit',
            showLoaderOnConfirm: true,
            preConfirm: (amount) => {
                if (!amount || amount <= 0) {
                    Swal.showValidationMessage('Please enter a valid amount');
                    return false;
                }
                if (parseFloat(amount) > parseFloat(balance)) {
                    Swal.showValidationMessage('Amount cannot exceed the balance of ₹' + balance);
                    return false;
                }
                return $.post(url, {
                    _token: '{{ csrf_token() }}',
                    amount: amount
                }).done(function(r) {
                    if (!r.success) {
                        Swal.showValidationMessage(r.message);
                    }
                    return r;
                }).fail(function() {
                    Swal.showValidationMessage('Request failed');
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value.success) {
                Swal.fire('Success', titleText === 'Receive Payment' ? 'Payment received successfully!' : 'Payment processed successfully!', 'success').then(() => {
                    location.reload();
                });
            }
        });
    });
});
</script>
@endsection

@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin / Part Sales Invoices /</span> Outstanding
    </h4>

    <!-- Search filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.part-sales-invoices.outstanding') }}">
                <div class="row g-3">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" placeholder="Search by Invoice No, Customer Name or Mobile" value="{{ $search ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-search"></i> Search</button>
                            <a href="{{ route('admin.part-sales-invoices.outstanding') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i> Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Outstanding Part Sales Invoices</h5>
            <div>
                <a href="{{ route('admin.part-sales-invoices.outstanding.export', ['search' => request('search')]) }}" class="btn btn-outline-success me-2"><i class="bx bx-file-export"></i> Export</a>
                <a href="{{ route('admin.part-sales-invoices.index') }}" class="btn btn-outline-secondary">All Invoices</a>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Received</th>
                        <th>Balance</th>
                        <th>Payment Mode</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><a href="{{ route('admin.part-sales-invoices.show', $inv) }}" class="fw-bold">{{ $inv->invoice_number }}</a></td>
                        <td>{{ $inv->invoice_date->format('d-m-Y') }}</td>
                        <td>
                            {{ $inv->customer_name }}
                            @if($inv->customer_mobile) <br><small class="text-muted">{{ $inv->customer_mobile }}</small> @endif
                        </td>
                        <td>{{ $inv->items->count() }}</td>
                        <td>{{ number_format($inv->total_amount, 2) }}</td>
                        <td>{{ number_format($inv->received_amount, 2) }}</td>
                        <td><span class="badge bg-danger">{{ number_format($inv->balance, 2) }}</span></td>
                        <td>{{ $inv->payment_mode ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.part-sales-invoices.show', $inv) }}" class="btn btn-sm btn-info" title="Print"><i class="bx bx-printer"></i></a>
                            @if($inv->balance > 0)
                            <button class="btn btn-sm btn-success receive-payment-btn" data-url="{{ route('admin.part-sales-invoices.receive-payment', $inv) }}" data-balance="{{ $inv->balance }}" title="Receive Payment"><i class="bx bx-wallet"></i></button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted">No outstanding part sales invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $invoices->links() }}</div>
    </div>
</div>
@endsection

@section('script')
<script>
$(function(){
    $('.receive-payment-btn').click(function(){
        var url = $(this).data('url');
        var balance = $(this).data('balance');
        
        Swal.fire({
            title: 'Receive Payment',
            text: 'Enter the amount received. Outstanding Balance: ₹' + balance,
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
                Swal.fire('Success', 'Payment received successfully!', 'success').then(() => {
                    location.reload();
                });
            }
        });
    });
});
</script>
@endsection

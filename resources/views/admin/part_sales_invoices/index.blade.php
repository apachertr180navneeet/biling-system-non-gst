@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Part Sales Invoices</h4>
        <div>
            <a href="{{ route('admin.part-sales-invoices.outstanding') }}" class="btn btn-warning me-2"><i class="bx bx-list-check"></i> Outstanding</a>
            <a href="{{ route('admin.part-sales-invoices.export', ['search' => request('search')]) }}" class="btn btn-outline-success me-2"><i class="bx bx-file-export"></i> Export</a>
            <a href="{{ route('admin.part-sales-invoices.create') }}" class="btn btn-primary"><i class="bx bx-plus"></i> New Part Sales Invoice</a>
        </div>
    </div>

    <!-- Search filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.part-sales-invoices.index') }}">
                <div class="row g-3">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" placeholder="Search by Invoice No, Customer Name or Mobile" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-search"></i> Search</button>
                            <a href="{{ route('admin.part-sales-invoices.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i> Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>GSTIN</th>
                        <th>Items Count</th>
                        <th>Taxable Amount</th>
                        <th>GST (CGST+SGST)</th>
                        <th>Grand Total</th>
                        <th>Payment Mode</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td><a href="{{ route('admin.part-sales-invoices.show', $inv) }}" class="fw-bold">{{ $inv->invoice_number }}</a></td>
                        <td>{{ $inv->invoice_date->format('d-m-Y') }}</td>
                        <td>
                            {{ $inv->customer_name }}
                            @if($inv->customer_mobile) <br><small class="text-muted">{{ $inv->customer_mobile }}</small> @endif
                        </td>
                        <td>{{ $inv->customer_gstin ?? '-' }}</td>
                        <td>{{ $inv->items->count() }}</td>
                        <td>{{ number_format($inv->taxable_amount, 2) }}</td>
                        <td>{{ number_format($inv->cgst_amount + $inv->sgst_amount, 2) }}</td>
                        <td><strong>{{ number_format($inv->total_amount, 2) }}</strong></td>
                        <td>{{ $inv->payment_mode }}</td>
                        <td>
                            <a href="{{ route('admin.part-sales-invoices.show', $inv) }}" class="btn btn-sm btn-info" title="View / Print"><i class="bx bx-printer"></i></a>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $inv->id }}" data-url="{{ route('admin.part-sales-invoices.destroy', $inv) }}" title="Delete"><i class="bx bx-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted">No parts sales invoices recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $invoices->links() }}</div>
    </div>
</div>

<form id="deleteForm" method="POST">@csrf</form>
@endsection

@section('script')
<script>
$(function(){
    $('.delete-btn').click(function(){
        var form=$('#deleteForm'), url=$(this).data('url');
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the parts sales invoice and restore the parts stock in inventory.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#8592a3',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.attr('action', url);
                $.post(url, form.serialize() + '&_method=DELETE').done(function(r){
                    if(r.success) location.reload();
                }).fail(function(){
                    Swal.fire('Error', 'Something went wrong!', 'error');
                });
            }
        });
    });
});
</script>
@endsection

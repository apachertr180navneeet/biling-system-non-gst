@extends('admin.layouts.app')
@section('style')
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Purchase Orders
    </h4>

    <!-- Search filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.purchase-orders.index') }}">
                <div class="row g-3">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" placeholder="Search by Order No or Supplier Name" value="{{ $search ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-search"></i> Search</button>
                            <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i> Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">All Purchase Orders</h5>
            <div>
                <a href="{{ route('admin.purchase-orders.outstanding') }}" class="btn btn-warning me-2"><i class="bx bx-list-check"></i> Outstanding</a>
                <a href="{{ route('admin.purchase-orders.export', ['search' => request('search')]) }}" class="btn btn-outline-success me-2"><i class="bx bx-file-export"></i> Export</a>
                <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary">Add New</a>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order No.</th>
                        <th>Supplier</th>
                        <th>Order Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Received</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->supplier->name ?? '-' }}</td>
                        <td>{{ $order->order_date->format('d-m-Y') }}</td>
                        <td>{{ $order->items->count() }}</td>
                        <td>{{ number_format($order->total_amount, 2) }}</td>
                        <td>{{ number_format($order->received_amount, 2) }}</td>
                        <td><span class="badge bg-danger">{{ number_format($order->balance, 2) }}</span></td>
                        <td>
                            @if($order->status == 'pending')
                            <span class="badge bg-warning">Pending</span>
                            @elseif($order->status == 'partial')
                            <span class="badge bg-info">Partial</span>
                            @elseif($order->status == 'received')
                            <span class="badge bg-success">Received</span>
                            @else
                            <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <label class="switch switch-success">
                                <input type="checkbox" class="toggle-status" data-url="{{ route('admin.purchase-orders.toggle-status', $order) }}" {{ $order->is_active ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>
                            <a href="{{ route('admin.purchase-orders.show', $order) }}" class="btn btn-sm btn-info" title="View"><i class="bx bx-show"></i></a>
                            @if($order->status == 'pending')
                            <a href="{{ route('admin.purchase-orders.edit', $order) }}" class="btn btn-sm btn-primary" title="Edit"><i class="bx bx-edit"></i></a>
                            @endif
                            <a href="{{ route('admin.purchase-orders.pdf', $order) }}" class="btn btn-sm btn-danger" target="_blank" title="Download PDF"><i class="bx bxs-file-pdf"></i></a>
                            <button type="button" class="btn btn-sm btn-dark" onclick="directPrintPdf('{{ route('admin.purchase-orders.pdf', $order) }}')" title="Direct Print PDF"><i class="bx bx-printer"></i></button>
                            <a href="{{ route('admin.purchase-orders.whatsapp', $order) }}" class="btn btn-sm btn-success" target="_blank" title="Send WhatsApp"><i class="bx bxl-whatsapp"></i></a>
                            <button class="btn btn-sm btn-danger btn-delete" data-url="{{ route('admin.purchase-orders.destroy', $order) }}" title="Delete"><i class="bx bx-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center">No purchase orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
@section('script')
<script>
$(function(){
    $('.btn-delete').click(function() {
        var url = $(this).data('url');
        var btn = $(this);
        Swal.fire({ title: 'Are you sure?', text: 'This will be soft deleted.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete!' }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({ url: url, type: 'POST', data: { _token: '{{ csrf_token() }}', _method: 'DELETE' }, success: function(resp) {
                    if (resp.success) { btn.closest('tr').remove(); setFlash('success', resp.message); }
                }});
            }
        });
    });

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

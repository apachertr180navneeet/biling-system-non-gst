@extends('admin.layouts.app')
@section('style')
<style>
.status-card { transition: transform 0.2s; }
.status-card:hover { transform: translateY(-2px); }
</style>
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin / Vehicle Purchase Orders /</span> Outstanding
    </h4>

    <!-- Search filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.vehicle-purchase-orders.outstanding') }}">
                <div class="row g-3">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" placeholder="Search by PO No or Supplier Name" value="{{ $search ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-search"></i> Search</button>
                            <a href="{{ route('admin.vehicle-purchase-orders.outstanding') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i> Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Outstanding Vehicle Purchase Orders</h5>
            <div>
                <a href="{{ route('admin.vehicle-purchase-orders.outstanding.export', ['search' => request('search')]) }}" class="btn btn-outline-success me-2"><i class="bx bx-file-export"></i> Export</a>
                <a href="{{ route('admin.vehicle-purchase-orders.index') }}" class="btn btn-outline-secondary">All Orders</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="text-nowrap">PO No.</th>
                        <th>Supplier</th>
                        <th class="text-nowrap">Order Date</th>
                        <th>Vehicle</th>
                        <th>Outstanding Amt</th>
                        <th>PO Received</th>
                        <th>PO Balance</th>
                        <th>Status</th>
                        <th class="text-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $outstandingItems = $order->items->filter(fn($item) => $item->quantity - $item->received_quantity > 0);
                        @endphp
                        @if($outstandingItems->count() > 0)
                            @foreach($outstandingItems as $item)
                            <tr>
                                <td>{{ $loop->parent->iteration }}</td>
                                <td class="text-nowrap">{{ $order->po_number }}</td>
                                <td>{{ $order->supplier->name ?? '-' }}</td>
                                <td class="text-nowrap">{{ $order->order_date->format('d-m-Y') }}</td>
                                <td>{{ $item->vehicle_description }}</td>
                                <td>{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                <td>{{ number_format($order->received_amount, 2) }}</td>
                                <td><span class="badge bg-danger">{{ number_format($order->balance, 2) }}</span></td>
                                <td>
                                    @if($order->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                    @elseif($order->status == 'partial')
                                    <span class="badge bg-info">Partial</span>
                                    @else
                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.vehicle-purchase-orders.receive', $order) }}" class="btn btn-sm btn-primary">Receive</a>
                                    @if($order->balance > 0)
                                    <button class="btn btn-sm btn-success receive-payment-btn" data-url="{{ route('admin.vehicle-purchase-orders.receive-payment', $order) }}" data-balance="{{ $order->balance }}" title="Receive Payment"><i class="bx bx-wallet"></i></button>
                                    @endif
                                    <a href="{{ route('admin.vehicle-purchase-orders.show', $order) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                            </tr>
                            @endforeach
                        @elseif($order->balance > 0)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="text-nowrap">{{ $order->po_number }}</td>
                                <td>{{ $order->supplier->name ?? '-' }}</td>
                                <td class="text-nowrap">{{ $order->order_date->format('d-m-Y') }}</td>
                                <td>- (All Vehicles Received)</td>
                                <td>{{ number_format($order->total_amount, 2) }}</td>
                                <td>{{ number_format($order->received_amount, 2) }}</td>
                                <td><span class="badge bg-danger">{{ number_format($order->balance, 2) }}</span></td>
                                <td>
                                    <span class="badge bg-success">Received</span>
                                </td>
                                <td class="text-nowrap">
                                    @if($order->balance > 0)
                                    <button class="btn btn-sm btn-success receive-payment-btn" data-url="{{ route('admin.vehicle-purchase-orders.receive-payment', $order) }}" data-balance="{{ $order->balance }}" title="Receive Payment"><i class="bx bx-wallet"></i></button>
                                    @endif
                                    <a href="{{ route('admin.vehicle-purchase-orders.show', $order) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                            </tr>
                        @endif
                    @empty
                    <tr><td colspan="13" class="text-center">No outstanding vehicle purchase orders found.</td></tr>
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

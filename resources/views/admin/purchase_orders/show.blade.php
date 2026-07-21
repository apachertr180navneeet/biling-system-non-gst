@extends('admin.layouts.app')
@section('style')
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin / Purchase Orders /</span> {{ $purchaseOrder->order_number }}
    </h4>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Purchase Order Details</h5>
            <div>
                @if($purchaseOrder->status != 'received')
                <a href="{{ route('admin.purchase-orders.receive', $purchaseOrder) }}" class="btn btn-sm btn-primary"><i class="bx bx-import"></i> Receive Items</a>
                @endif
                <a href="{{ route('admin.purchase-orders.pdf', $purchaseOrder) }}" class="btn btn-sm btn-danger" target="_blank"><i class="bx bxs-file-pdf"></i> Download PDF</a>
                <a href="{{ route('admin.purchase-orders.whatsapp', $purchaseOrder) }}" class="btn btn-sm btn-success" target="_blank"><i class="bx bxl-whatsapp"></i> Send to WhatsApp</a>
                <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <strong>Order Number:</strong> {{ $purchaseOrder->order_number }}<br>
                    <strong>Order Date:</strong> {{ $purchaseOrder->order_date->format('d-m-Y') }}<br>
                    <strong>Expected Date:</strong> {{ $purchaseOrder->expected_date?->format('d-m-Y') ?? '-' }}<br>
                    <strong>Status:</strong>
                    @if($purchaseOrder->status == 'pending') <span class="badge bg-warning">Pending</span>
                    @elseif($purchaseOrder->status == 'partial') <span class="badge bg-info">Partial</span>
                    @elseif($purchaseOrder->status == 'received') <span class="badge bg-success">Received</span>
                    @else <span class="badge bg-secondary">{{ ucfirst($purchaseOrder->status) }}</span>
                    @endif
                </div>
                <div class="col-md-6">
                    <strong>Supplier:</strong> {{ $purchaseOrder->supplier->name ?? '-' }}<br>
                    <strong>Created By:</strong> {{ $purchaseOrder->createdBy->full_name ?? 'System' }}<br>
                    <strong>Total Amount:</strong> {{ number_format($purchaseOrder->total_amount, 2) }}<br>
                    <strong>Received Amount:</strong> {{ number_format($purchaseOrder->received_amount, 2) }}<br>
                    <strong>Balance:</strong> <span class="badge bg-danger">{{ number_format($purchaseOrder->balance, 2) }}</span><br>
                    <strong>Notes:</strong> {{ $purchaseOrder->notes ?? '-' }}
                </div>
            </div>
            <h5>Items</h5>
            <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr><th>#</th><th>Part</th><th>Qty</th><th>Unit Price</th><th>Total</th><th>Received</th></tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->sparePart->part_no ?? '-' }} - {{ $item->sparePart->name ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->total_price, 2) }}</td>
                        <td>{{ $item->received_quantity }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><th colspan="4" class="text-end">Total:</th><th>{{ number_format($purchaseOrder->total_amount, 2) }}</th><th></th></tr>
                </tfoot>
            </table>
            </div>
        </div>
    </div>
</div>
@endsection

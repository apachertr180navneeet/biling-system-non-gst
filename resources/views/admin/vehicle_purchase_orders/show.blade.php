@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin / Vehicle Purchase Orders /</span> {{ $vehiclePurchaseOrder->po_number }}
    </h4>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">PO Details</h5>
            <div>
                @if($vehiclePurchaseOrder->status != 'received')
                <a href="{{ route('admin.vehicle-purchase-orders.receive', $vehiclePurchaseOrder) }}" class="btn btn-sm btn-primary"><i class="bx bx-import"></i> Receive Items</a>
                @endif
                <button type="button" onclick="directPrintPdf('{{ route('admin.vehicle-purchase-orders.pdf', $vehiclePurchaseOrder) }}')" class="btn btn-sm btn-dark me-1"><i class="bx bx-printer"></i> Print PDF</button>
                <a href="{{ route('admin.vehicle-purchase-orders.pdf', $vehiclePurchaseOrder) }}" class="btn btn-sm btn-danger me-1" target="_blank"><i class="bx bxs-file-pdf"></i> Download PDF</a>
                <a href="{{ route('admin.vehicle-purchase-orders.whatsapp', $vehiclePurchaseOrder) }}" class="btn btn-sm btn-success me-1" target="_blank"><i class="bx bxl-whatsapp"></i> Send to WhatsApp</a>
                <a href="{{ route('admin.vehicle-purchase-orders.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <strong>PO Number:</strong> {{ $vehiclePurchaseOrder->po_number }}<br>
                    <strong>Order Date:</strong> {{ $vehiclePurchaseOrder->order_date->format('d-m-Y') }}<br>
                    <strong>Expected Date:</strong> {{ $vehiclePurchaseOrder->expected_date?->format('d-m-Y') ?? '-' }}<br>
                    <strong>Status:</strong>
                    @if($vehiclePurchaseOrder->status == 'pending') <span class="badge bg-warning">Pending</span>
                    @elseif($vehiclePurchaseOrder->status == 'partial') <span class="badge bg-info">Partial</span>
                    @elseif($vehiclePurchaseOrder->status == 'received') <span class="badge bg-success">Received</span>
                    @else <span class="badge bg-secondary">{{ ucfirst($vehiclePurchaseOrder->status) }}</span>
                    @endif
                </div>
                <div class="col-md-6">
                    <strong>Supplier:</strong> {{ $vehiclePurchaseOrder->supplier->name ?? '-' }}<br>
                    <strong>Total Amount:</strong> {{ number_format($vehiclePurchaseOrder->total_amount, 2) }}<br>
                    <strong>Received Amount:</strong> {{ number_format($vehiclePurchaseOrder->received_amount, 2) }}<br>
                    <strong>Balance:</strong> <span class="badge bg-danger">{{ number_format($vehiclePurchaseOrder->balance, 2) }}</span><br>
                    <strong>Notes:</strong> {{ $vehiclePurchaseOrder->notes ?? '-' }}
                </div>
            </div>
            <h5>Items</h5>
            <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr><th>#</th><th>Vehicle</th><th>Qty</th><th>Unit Price</th><th>Total</th><th>Received</th></tr>
                </thead>
                <tbody>
                    @foreach($vehiclePurchaseOrder->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            {{ $item->vehicle_description }}
                            @if($item->color_name) <span class="badge bg-secondary ms-1">{{ $item->color_name }}</span> @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->total_price, 2) }}</td>
                        <td>{{ $item->received_quantity }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><th colspan="4" class="text-end">Total:</th><th>{{ number_format($vehiclePurchaseOrder->total_amount, 2) }}</th><th></th></tr>
                </tfoot>
            </table>
            </div>
        </div>
    </div>
</div>
@endsection


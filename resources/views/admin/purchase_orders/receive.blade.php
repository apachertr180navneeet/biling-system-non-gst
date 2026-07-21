@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-4">Receive Items - {{ $purchaseOrder->order_number }}</h4>
        <div class="card"><div class="card-body">
        <p><strong>Supplier:</strong> {{ $purchaseOrder->supplier->name ?? '-' }}</p>
        <p><strong>Order Date:</strong> {{ $purchaseOrder->order_date->format('d-m-Y') }}</p>
        <hr>
        <form method="POST" action="{{ route('admin.purchase-orders.receive-store', $purchaseOrder) }}">
            @csrf
            <table class="table table-bordered">
                <thead><tr><th>#</th><th>Part</th><th>Ordered Qty</th><th>Previously Received</th><th>Receive Qty</th></tr></thead>
                <tbody>
                    @foreach($purchaseOrder->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->sparePart->name ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->received_quantity }}</td>
                        <td>
                            <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                            <input type="number" name="items[{{ $i }}][received_qty]" class="form-control" value="{{ $item->quantity - $item->received_quantity }}" min="0" max="{{ $item->quantity }}">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><i class="bx bx-check"></i> Receive Items</button>
                <a href="{{ route('admin.purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div></div>
</div>
@endsection

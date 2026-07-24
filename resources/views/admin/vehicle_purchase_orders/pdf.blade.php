<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vehicle Purchase Order - {{ $vehiclePurchaseOrder->po_number }}</title>
    <style>
        @page {
            margin: 40px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            font-size: 11px;
            line-height: 1.5;
        }
        #watermark {
            position: fixed;
            top: 30%;
            left: 15%;
            width: 70%;
            text-align: center;
            opacity: 0.06;
            z-index: -1000;
        }
        #watermark img {
            width: 100%;
            height: auto;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .subtitle {
            font-size: 10px;
            color: #777;
            margin-top: 2px;
        }
        .po-title {
            font-size: 22px;
            font-weight: bold;
            text-align: right;
            color: #2c3e50;
            text-transform: uppercase;
        }
        .po-meta {
            text-align: right;
            font-size: 11px;
            color: #555;
            margin-top: 5px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .info-col {
            width: 50%;
            vertical-align: top;
        }
        .info-card {
            border: 1px solid #eaeaea;
            background-color: #fafafa;
            padding: 12px;
            border-radius: 6px;
            margin-right: 10px;
        }
        .info-card-right {
            border: 1px solid #eaeaea;
            background-color: #fafafa;
            padding: 12px;
            border-radius: 6px;
            margin-left: 10px;
        }
        .info-title {
            font-size: 11px;
            font-weight: bold;
            color: #696cff;
            text-transform: uppercase;
            margin-bottom: 6px;
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 3px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #696cff;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            font-size: 11px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eaeaea;
            font-size: 11px;
        }
        .items-table tr:nth-child(even) td {
            background-color: #fcfcfc;
        }
        .text-right {
            text-align: right;
        }
        .total-row td {
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #696cff;
            border-bottom: 2px solid #696cff;
            background-color: #fafafa !important;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #eaeaea;
            padding-top: 15px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .bg-warning { background-color: #ffab00; color: #fff; }
        .bg-info { background-color: #03c3ec; color: #fff; }
        .bg-success { background-color: #71dd37; color: #fff; }
        .bg-secondary { background-color: #8592a3; color: #fff; }
    </style>
</head>
<body>

    <div id="watermark">
        <img src="{{ public_path('assets/admin/img/logo.jpg') }}" alt="Watermark Logo">
    </div>

    <table class="header-table">
        <tr>
            <td class="info-col">
                <img src="{{ public_path('assets/admin/img/logo.jpg') }}" style="height: 60px; margin-bottom: 5px;">
                <div class="subtitle">Premium Billing & Inventory Management System</div>
            </td>
            <td>
                <div class="po-title">Vehicle Purchase Order</div>
                <div class="po-meta">
                    <strong>PO Number:</strong> {{ $vehiclePurchaseOrder->po_number }}<br>
                    <strong>Date:</strong> {{ $vehiclePurchaseOrder->order_date->format('d-m-Y') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="info-col">
                <div class="info-card">
                    <div class="info-title">Supplier Details</div>
                    <strong>Name:</strong> {{ $vehiclePurchaseOrder->supplier->name ?? '-' }}<br>
                    @if(!empty($vehiclePurchaseOrder->supplier->phone))
                        <strong>Phone:</strong> {{ $vehiclePurchaseOrder->supplier->phone }}<br>
                    @endif
                    @if(!empty($vehiclePurchaseOrder->supplier->email))
                        <strong>Email:</strong> {{ $vehiclePurchaseOrder->supplier->email }}<br>
                    @endif
                    @if(!empty($vehiclePurchaseOrder->supplier->address))
                        <strong>Address:</strong> {{ $vehiclePurchaseOrder->supplier->address }}<br>
                    @endif
                </div>
            </td>
            <td class="info-col">
                <div class="info-card-right">
                    <div class="info-title">Order Information</div>
                    <strong>Expected Date:</strong> {{ $vehiclePurchaseOrder->expected_date?->format('d-m-Y') ?? '-' }}<br>
                    <strong>Status:</strong>
                    @if($vehiclePurchaseOrder->status == 'pending') <span class="badge bg-warning">Pending</span>
                    @elseif($vehiclePurchaseOrder->status == 'partial') <span class="badge bg-info">Partial</span>
                    @elseif($vehiclePurchaseOrder->status == 'received') <span class="badge bg-success">Received</span>
                    @else <span class="badge bg-secondary">{{ ucfirst($vehiclePurchaseOrder->status) }}</span>
                    @endif
                    <br>
                    @if(!empty($vehiclePurchaseOrder->notes))
                        <strong>Notes:</strong> {{ $vehiclePurchaseOrder->notes }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">Vehicle</th>
                <th style="width: 12%; text-align: right;">Qty</th>
                <th style="width: 18%; text-align: right;">Unit Price</th>
                <th style="width: 20%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehiclePurchaseOrder->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $item->vehicle_description }}</strong>
                    @if($item->color_name) <br><small style="color: #666;">Color: {{ $item->color_name }}</small> @endif
                </td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3"></td>
                <td class="text-right">Grand Total:</td>
                <td class="text-right">{{ number_format($vehiclePurchaseOrder->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Thank you for your business! If you have any questions, please contact us.
    </div>

</body>
</html>

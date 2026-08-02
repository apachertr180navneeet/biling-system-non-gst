<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vehicle Sales Invoice - {{ $vehicleSalesInvoice->invoice_number }}</title>
    <style>
        @page {
            margin: 25px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1e293b;
            font-size: 10px;
            line-height: 1.4;
        }
        #watermark {
            position: fixed;
            top: 25%;
            left: 10%;
            width: 80%;
            text-align: center;
            opacity: 0.05;
            z-index: -1000;
        }
        #watermark img {
            width: 100%;
            height: auto;
        }
        .company-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2px solid #059669;
            padding-bottom: 6px;
        }
        .company-title {
            font-size: 18px;
            font-weight: bold;
            color: #14532d;
            margin-bottom: 3px;
        }
        .company-info {
            font-size: 9px;
            color: #475569;
            line-height: 1.3;
        }
        .logo-td {
            text-align: right;
            vertical-align: top;
        }
        .invoice-title {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
            text-transform: uppercase;
            text-align: right;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .meta-table td {
            padding: 5px 8px;
            font-size: 9.5px;
        }
        .meta-label {
            color: #64748b;
            font-weight: bold;
        }
        .meta-value {
            color: #0f172a;
            font-weight: bold;
        }
        .billing-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .billing-box {
            vertical-align: top;
            border: 1px solid #e2e8f0;
            padding: 8px;
            background-color: #ffffff;
            border-left: 4px solid #10b981;
        }
        .billing-title {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 3px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 2px;
        }
        .billing-name {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 3px;
        }
        .billing-desc {
            font-size: 9px;
            color: #475569;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            font-size: 9.5px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9.5px;
            vertical-align: top;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .item-name {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            display: block;
        }
        .warranty-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 5px 8px;
            margin-top: 5px;
            font-size: 8.5px;
        }
        .warranty-title {
            font-weight: bold;
            color: #14532d;
            text-transform: uppercase;
        }
        .specs-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .specs-table td {
            padding: 3px 6px;
            font-size: 8.5px;
            color: #334155;
            border: 1px solid #f1f5f9;
        }
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .bottom-col {
            vertical-align: top;
        }
        .terms-box {
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            padding: 8px;
        }
        .terms-title {
            font-size: 9px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .terms-list {
            font-size: 8.5px;
            color: #475569;
            margin: 0;
            padding-left: 14px;
            line-height: 1.3;
        }
        .summary-card {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            padding: 8px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 2px 0;
            font-size: 9.5px;
        }
        .summary-label {
            color: #065f46;
        }
        .summary-value {
            text-align: right;
            color: #065f46;
            font-weight: bold;
        }
        .summary-total-row td {
            border-top: 1.5px solid #059669;
            padding-top: 4px;
            font-size: 11px;
            font-weight: bold;
            color: #047857;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        .sig-box {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
        }
        .sig-line {
            border-top: 1px dashed #94a3b8;
            width: 60%;
            margin: 0 auto 5px auto;
        }
        .sig-text {
            font-size: 9px;
            font-weight: bold;
            color: #475569;
        }
    </style>
</head>
<body>

    <div id="watermark">
        <img src="{{ public_path('assets/admin/img/logo.jpg') }}" alt="Watermark Logo">
    </div>

    <table class="company-header">
        <tr>
            <td>
                <div class="company-title">SHREE KRISHNA AUTO GREEN</div>
                <div class="company-info">
                    NEAR MAHAMANDIR CIRCLE, MANDORE ROAD, JODHPUR (RAJASTHAN)<br>
                    Contact: 7586899148, 9829028792
                </div>
            </td>
            <td class="logo-td">
                <img src="{{ public_path('assets/admin/img/logo.jpg') }}" style="height: 55px; margin-bottom: 4px;"><br>
                <div class="invoice-title">Vehicle Sales Invoice</div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td style="width: 25%;">
                <span class="meta-label">Invoice#:</span> <span class="meta-value">{{ $vehicleSalesInvoice->invoice_number }}</span>
            </td>
            <td style="width: 25%;">
                <span class="meta-label">Date:</span> <span class="meta-value">{{ $vehicleSalesInvoice->invoice_date->format('d M Y') }}</span>
            </td>
            <td style="width: 25%;">
                <span class="meta-label">Payment:</span> <span class="meta-value">{{ $vehicleSalesInvoice->payment_mode ?? '-' }}</span>
            </td>
            <td style="width: 25%; text-align: right;">
                @if($vehicleSalesInvoice->payment_mode === 'Finance' && $vehicleSalesInvoice->finance_name)
                    <span class="meta-label">Finance:</span> <span class="meta-value">{{ $vehicleSalesInvoice->finance_name }}</span>
                @else
                    <span class="meta-label">Date of Sale:</span> <span class="meta-value">{{ $vehicleSalesInvoice->invoice_date->format('d M Y') }}</span>
                @endif
            </td>
        </tr>
    </table>

    <table class="billing-table">
        <tr>
            <td class="billing-box" style="width: 60%;">
                <div class="billing-title">BILL TO</div>
                <div class="billing-name">{{ $vehicleSalesInvoice->customer_name }}</div>
                <div class="billing-desc">
                    {{ $vehicleSalesInvoice->customer_address ?? '-' }}<br>
                    <strong>Mobile:</strong> {{ $vehicleSalesInvoice->customer_mobile ?? '-' }} | 
                    <strong>Residence Tel:</strong> {{ $vehicleSalesInvoice->customer_residence_phone ?? '-' }}
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td class="billing-box" style="width: 36%; border-left-color: #059669;">
                <div class="billing-title">CUSTOMER INFO</div>
                <div class="billing-desc">
                    <strong>Age:</strong> {{ $vehicleSalesInvoice->customer_age ?? '-' }}<br>
                    <strong>Occupation:</strong> {{ $vehicleSalesInvoice->customer_occupation ?? 'BUSINESS' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 59%;">Item & Description</th>
                <th style="width: 8%; text-align: center;">Qty</th>
                <th style="width: 14%; text-align: right;">Rate</th>
                <th style="width: 14%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center" style="font-weight: bold;">1</td>
                <td>
                    <span class="item-name">{{ strtoupper($vehicleSalesInvoice->vehicleInventory->vehicle_description) }} ({{ strtoupper($battery_type) }})</span>

                    @if($vehicleSalesInvoice->warranty_notes)
                    <div class="warranty-box">
                        <span class="warranty-title">WARRANTY DETAILS:</span> {{ $vehicleSalesInvoice->warranty_notes }}
                    </div>
                    @endif

                    <table class="specs-table">
                        <tr>
                            <td><b>Model:</b> {{ $vehicleSalesInvoice->vehicleInventory->vehicle_description }}</td>
                            <td><b>Colour:</b> {{ $color_name }}</td>
                            <td><b>Chassis No:</b> <strong style="color: #059669;">{{ $vehicleSalesInvoice->vehicleInventory->chassis_number }}</strong></td>
                        </tr>
                        <tr>
                            <td><b>Battery No:</b> {{ $vehicleSalesInvoice->vehicleInventory->battery_number ?? '-' }}</td>
                            <td><b>Charger No:</b> {{ $vehicleSalesInvoice->vehicleInventory->charger_number ?? '-' }}</td>
                            <td><b>Controller No:</b> {{ $vehicleSalesInvoice->vehicleInventory->controller_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><b>Convertor No:</b> {{ $vehicleSalesInvoice->vehicleInventory->convertor_number ?? '-' }}</td>
                            <td><b>Manual No:</b> {{ $vehicleSalesInvoice->vehicleInventory->manual_number ?? '-' }}</td>
                            <td><b>Motor No:</b> {{ $vehicleSalesInvoice->vehicleInventory->motor_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><b>Battery Type:</b> {{ $battery_type }}</td>
                            <td><b>Battery Make:</b> {{ $battery_make }}</td>
                            <td></td>
                        </tr>
                    </table>
                </td>
                <td class="text-center" style="font-weight: bold;">1</td>
                <td class="text-right" style="font-weight: bold;">{{ number_format($vehicleSalesInvoice->rate, 2) }}</td>
                <td class="text-right" style="font-weight: bold;">{{ number_format($vehicleSalesInvoice->rate, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="bottom-table">
        <tr>
            <td class="bottom-col" style="width: 58%;">
                <div class="terms-box">
                    <div class="terms-title">TERMS & CONDITIONS</div>
                    <ol class="terms-list">
                        <li>Received vehicle, tool kit, charger, jack, stepny and Battery in good and running condition.</li>
                        <li>Our responsibility ceases upon delivery & claim for loss/ shortage etc. will not be entertained thereafter.</li>
                        <li>Goods Once sold will not be taken back or exchanged under any circumstances.</li>
                        <li>Warranty as per Company's policy given in owner's manual. 12Month motor and Controller.</li>
                        <li>Subject to JODHPUR Jurisdiction only.</li>
                        <li>Getting any work done on the vehicle outside of our authorized office workshop will void the entire warranty.</li>
                    </ol>
                    <div style="font-size: 9px; font-weight: bold; color: #059669; margin-top: 8px; font-style: italic;">Thanks for shopping with us.</div>
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td class="bottom-col" style="width: 38%;">
                <div class="summary-card">
                    <table class="summary-table">
                        <tr>
                            <td class="summary-label">Sub Total</td>
                            <td class="summary-value">{{ number_format($vehicleSalesInvoice->sub_total, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Others</td>
                            <td class="summary-value">0.00</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Total</td>
                            <td class="summary-value">{{ number_format($vehicleSalesInvoice->total, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Less :- NEMMP 2020</td>
                            <td class="summary-value">{{ number_format($vehicleSalesInvoice->nemmp_incentive, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Discount</td>
                            <td class="summary-value">{{ number_format($vehicleSalesInvoice->discount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">TOTAL DISC. (-)</td>
                            <td class="summary-value">{{ number_format($vehicleSalesInvoice->nemmp_incentive + $vehicleSalesInvoice->discount, 2) }}</td>
                        </tr>
                        <tr class="summary-total-row">
                            <td class="summary-label" style="font-weight: bold;">G. Total</td>
                            <td class="summary-value">{{ number_format($vehicleSalesInvoice->grand_total, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="signature-table">
        <tr>
            <td class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-text">CUSTOMER SIGNATURE</div>
            </td>
            <td class="sig-box">
                <div class="sig-text" style="margin-bottom: 20px;">For <strong>SHREE KRISHNA AUTO GREEN</strong></div>
                <div class="sig-line" style="width: 50%;"></div>
                <div class="sig-text">Prop.</div>
            </td>
        </tr>
    </table>

</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vehicle Sales Invoice - {{ $vehicleSalesInvoice->invoice_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 9.5px;
            line-height: 1.4;
        }
        #watermark {
            position: fixed;
            top: 25%;
            left: 10%;
            width: 80%;
            text-align: center;
            opacity: 0.04;
            z-index: -1000;
        }
        #watermark img {
            width: 100%;
            height: auto;
        }
        .company-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border-bottom: 2px solid #059669;
            padding-bottom: 5px;
        }
        .company-title {
            font-size: 17px;
            font-weight: bold;
            color: #14532d;
            margin-bottom: 2px;
            letter-spacing: -0.3px;
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
            font-size: 16px;
            font-weight: bold;
            color: #059669;
            text-transform: uppercase;
            text-align: right;
            letter-spacing: 0.5px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .meta-table td {
            padding: 4px 8px;
            font-size: 9px;
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
            margin-bottom: 8px;
        }
        .billing-box {
            vertical-align: top;
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            background-color: #ffffff;
            border-left: 4px solid #10b981;
        }
        .billing-title {
            font-size: 8.5px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 2px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 2px;
        }
        .billing-name {
            font-size: 10.5px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .billing-desc {
            font-size: 8.5px;
            color: #475569;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 1px solid #cbd5e1;
        }
        .items-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 5px 8px;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9.5px;
            vertical-align: top;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .item-name-row {
            margin-bottom: 6px;
        }
        .item-name {
            font-size: 11px;
            font-weight: bold;
            color: #000000;
            text-transform: uppercase;
        }
        .item-make {
            font-size: 9.5px;
            font-weight: 600;
            color: #64748b;
            margin-left: 8px;
            text-transform: uppercase;
        }
        .warranty-box {
            background-color: #f8fafc;
            border-left: 3px solid #64748b;
            padding: 4px 8px;
            margin-top: 4px;
            margin-bottom: 6px;
            font-size: 8.5px;
            color: #334155;
        }
        .warranty-title {
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            margin-bottom: 2px;
            font-size: 8.5px;
        }
        .specs-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .specs-table td {
            padding: 2px 4px;
            font-size: 8.5px;
            color: #1e293b;
            width: 50%;
            vertical-align: top;
        }
        .specs-label {
            font-weight: bold;
            color: #334155;
            display: inline-block;
            width: 95px;
        }
        .specs-value {
            color: #0f172a;
        }
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .bottom-col {
            vertical-align: top;
        }
        .terms-box {
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            padding: 6px 8px;
        }
        .terms-title {
            font-size: 9px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .terms-list {
            font-size: 8px;
            color: #475569;
            margin: 0;
            padding-left: 14px;
            line-height: 1.35;
        }
        .terms-list li {
            margin-bottom: 2px;
        }
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 2px 0;
            font-size: 9px;
        }
        .summary-label {
            color: #475569;
        }
        .summary-value {
            text-align: right;
            color: #0f172a;
            font-weight: bold;
        }
        .summary-total-row td {
            border-top: 1.5px solid #0f172a;
            padding-top: 3px;
            font-size: 10.5px;
            font-weight: bold;
            color: #0f172a;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .sig-box {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
        }
        .sig-line {
            border-top: 1px dashed #94a3b8;
            width: 60%;
            margin: 0 auto 4px auto;
        }
        .sig-text {
            font-size: 9px;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
                    Near Mahamandir Circle, Main Mandore Road, Jodhpur (Raj.)<br>
                    Office No.: 9602029148 | E-mail ID: vijay.deora429@gmail.com
                </div>
            </td>
            <td class="logo-td">
                <img src="{{ public_path('assets/admin/img/logo.jpg') }}" style="height: 50px; margin-bottom: 2px;"><br>
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
                <th style="width: 59%;">ITEM & DESCRIPTION</th>
                <th style="width: 8%; text-align: center;">Qty</th>
                <th style="width: 14%; text-align: right;">Rate</th>
                <th style="width: 14%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center" style="font-weight: bold;">1</td>
                <td>
                    <div class="item-name-row">
                        <span class="item-name">{{ strtoupper($vehicleSalesInvoice->vehicleInventory->vehicle_description) }}</span>
                        <span class="item-make">{{ strtoupper($battery_make) }}</span>
                    </div>

                    <div class="warranty-box">
                        <div class="warranty-title">WARRANTY DETAILS</div>
                        @if($vehicleSalesInvoice->warranty_notes)
                            {!! nl2br(e($vehicleSalesInvoice->warranty_notes)) !!}
                        @else
                            MOTOR / CONTROLLER WARRANTY - 1 YEAR<br>
                            BATTERY WARRANTY - 3 YEAR<br>
                            CHARGER WARRANTY - 2 YEAR
                        @endif
                    </div>

                    <table class="specs-table">
                        <tr>
                            <td><span class="specs-label">Model:</span> <span class="specs-value">{{ $vehicleSalesInvoice->vehicleInventory->vehicle_description }}</span></td>
                            <td><span class="specs-label">Colour:</span> <span class="specs-value">{{ $color_name }}</span></td>
                        </tr>
                        <tr>
                            <td><span class="specs-label">Chassis No:</span> <span class="specs-value"><strong>{{ $vehicleSalesInvoice->vehicleInventory->chassis_number }}</strong></span></td>
                            <td><span class="specs-label">Battery No:</span> <span class="specs-value">{{ $vehicleSalesInvoice->vehicleInventory->battery_number ?? '-' }}</span></td>
                        </tr>
                        <tr>
                            <td><span class="specs-label">Charger No:</span> <span class="specs-value">{{ $vehicleSalesInvoice->vehicleInventory->charger_number ?? '-' }}</span></td>
                            <td><span class="specs-label">Controller No:</span> <span class="specs-value">{{ $vehicleSalesInvoice->vehicleInventory->controller_number ?? '-' }}</span></td>
                        </tr>
                        <tr>
                            <td><span class="specs-label">Convertor No:</span> <span class="specs-value">{{ $vehicleSalesInvoice->vehicleInventory->convertor_number ?? '-' }}</span></td>
                            <td><span class="specs-label">Manual No:</span> <span class="specs-value">{{ $vehicleSalesInvoice->vehicleInventory->manual_number ?? '-' }}</span></td>
                        </tr>
                        <tr>
                            <td><span class="specs-label">Motor No:</span> <span class="specs-value">{{ $vehicleSalesInvoice->vehicleInventory->motor_number ?? '-' }}</span></td>
                            <td><span class="specs-label">Battery Type:</span> <span class="specs-value">{{ $battery_type }}</span></td>
                        </tr>
                        <tr>
                            <td><span class="specs-label">Battery Make:</span> <span class="specs-value">{{ $battery_make }}</span></td>
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
                        <li>Received vehicle, tool kit, charger, jack, stepny and battery in good and running condition.</li>
                        <li>Our responsibility ceases upon delivery & claim for loss/ shortage etc. will not be entertained thereafter.</li>
                        <li>Goods Once Sold will Not Be Taken Back or exchanged under any circumstances.</li>
                        <li>Subject to JODHPUR Jurisdiction only.</li>
                        <li>Getting any work done on the vehicle outside of our authorized office/workshop will void the entire warranty.</li>
                    </ol>
                    <div style="font-size: 8.5px; font-weight: bold; color: #475569; margin-top: 6px; font-style: italic;">Thanks for shopping with us.</div>
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
            <td class="sig-box" style="width: 100%; text-align: center;" colspan="2">
                <div class="sig-text" style="margin-top: 15px;">CUSTOMER SIGNATURE</div>
            </td>
        </tr>
    </table>

</body>
</html>


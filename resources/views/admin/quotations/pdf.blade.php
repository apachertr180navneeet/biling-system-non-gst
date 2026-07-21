<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation - {{ $quotation->quotation_number }}</title>
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
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .subtitle {
            font-size: 9px;
            color: #777;
            margin-top: 2px;
        }
        .po-title {
            font-size: 20px;
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
            margin-bottom: 20px;
        }
        .info-col {
            width: 50%;
            vertical-align: top;
        }
        .info-card {
            border: 1px solid #eaeaea;
            background-color: #fafafa;
            padding: 10px;
            border-radius: 5px;
            margin-right: 5px;
        }
        .info-card-right {
            border: 1px solid #eaeaea;
            background-color: #fafafa;
            padding: 10px;
            border-radius: 5px;
            margin-left: 5px;
        }
        .info-title {
            font-size: 10px;
            font-weight: bold;
            color: #696cff;
            text-transform: uppercase;
            margin-bottom: 4px;
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 2px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #696cff;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            font-size: 10px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #eaeaea;
            font-size: 10px;
        }
        .items-table tr:nth-child(even) td {
            background-color: #fcfcfc;
        }
        .text-right {
            text-align: right;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .totals-table td {
            padding: 4px 8px;
            font-size: 10px;
        }
        .total-row td {
            font-weight: bold;
            font-size: 11px;
            border-top: 2px solid #696cff;
            border-bottom: 2px solid #696cff;
            background-color: #fafafa !important;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #eaeaea;
            padding-top: 10px;
            text-align: center;
            font-size: 9px;
            color: #999;
        }
    </style>
</head>
<body>

    <div id="watermark">
        <img src="{{ public_path('assets/admin/img/logo.jpg') }}" alt="Watermark Logo">
    </div>

    <table class="header-table">
        <tr>
            <td class="info-col">
                <img src="{{ public_path('assets/admin/img/logo.jpg') }}" style="height: 60px; margin-bottom: 3px;">
                <div class="subtitle">Premium Billing & Inventory Management System</div>
            </td>
            <td>
                <div class="po-title">Quotation</div>
                <div class="po-meta">
                    <strong>Quotation Number:</strong> {{ $quotation->quotation_number }}<br>
                    <strong>Date:</strong> {{ $quotation->quotation_date->format('d-m-Y') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="info-col">
                <div class="info-card">
                    <div class="info-title">Company details</div>
                    <strong>SHREE KRISHNA AUTO GREEN</strong><br>
                    Plot No. 12, Auto Green Zone,<br>
                    Jaipur Road, Rajasthan<br>
                    Mobile: +91 9999999999<br>
                    Email: info@shreekrishnaautogreen.com
                </div>
            </td>
            <td class="info-col">
                <div class="info-card-right">
                    <div class="info-title">Quotation For</div>
                    <strong>Name:</strong> {{ $quotation->customer_name }}<br>
                    @if($quotation->customer_mobile)<strong>Mobile:</strong> {{ $quotation->customer_mobile }}<br>@endif
                    @if($quotation->customer_address)<strong>Address:</strong> {{ $quotation->customer_address }}<br>@endif
                    @if($quotation->customer_gstin)<strong>GSTIN:</strong> {{ $quotation->customer_gstin }}<br>@endif
                    @if($quotation->customer_pan)<strong>PAN:</strong> {{ $quotation->customer_pan }}<br>@endif
                    <strong>Place of Supply:</strong> {{ $quotation->place_of_supply }}
                </div>
            </td>
        </tr>
    </table>

    @if($quotation->type === 'vehicle')
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Vehicle Specifications / Variant</th>
                    <th class="text-right" style="width: 15%;">Ex-Showroom</th>
                    <th class="text-right" style="width: 10%;">Discount</th>
                    <th class="text-right" style="width: 10%;">Incentive</th>
                    <th class="text-right" style="width: 20%;">Taxable Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $quotation->vehicleMaster->variant_name ?? '-' }}</strong><br>
                        Color: {{ $quotation->vehicleMaster->color_name ?? '-' }} | Fuel: {{ $quotation->vehicleMaster->fuel_type ?? '-' }}<br>
                        Battery Make: {{ $quotation->vehicleMaster->battery_make ?? '-' }}<br>
                        <strong style="color: #000;">ON ROAD PRICE INCLUDING GST, RTO, INSURANCE</strong>
                    </td>
                    <td class="text-right">₹{{ number_format($quotation->rate, 2) }}</td>
                    <td class="text-right">-₹{{ number_format($quotation->discount, 2) }}</td>
                    <td class="text-right">-₹{{ number_format($quotation->nemmp_incentive, 2) }}</td>
                    <td class="text-right">₹{{ number_format($quotation->taxable_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 40%;">Part Details</th>
                    <th class="text-right" style="width: 15%;">Rate</th>
                    <th style="width: 10%;">GST %</th>
                    <th class="text-right" style="width: 10%;">Qty</th>
                    <th class="text-right" style="width: 20%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->sparePart->name ?? '-' }}</strong><br>
                        <small style="color: #666;">Part No: {{ $item->sparePart->part_no ?? '-' }}</small>
                    </td>
                    <td class="text-right">₹{{ number_format($item->rate, 2) }}</td>
                    <td>{{ $item->tax_percentage }}%</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">₹{{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table style="width: 100%;">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                @if($quotation->type === 'vehicle')
                    <div style="font-size: 8.5px; border: 1px solid #ccc; padding: 6px; border-radius: 4px; background: #fafafa; margin-right: 10px; margin-bottom: 8px;">
                        <strong style="font-size: 9.5px; color: #111; display: block; margin-bottom: 4px; border-bottom: 1px solid #ddd; padding-bottom: 2px;">TECHNICAL SPECIFICATION -:</strong>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr><td style="width: 48%; padding: 1px 0;"><strong>Model/ Maker's:</strong></td><td>:- {{ $quotation->model_maker_name ?? 'E- PASSENGER/ARZOO/PASSANGER' }}</td></tr>
                            <tr><td style="padding: 1px 0;"><strong>GROSS WEIGHT:</strong></td><td>:- {{ $quotation->gross_weight ?? '60 KG' }}</td></tr>
                            <tr><td style="padding: 1px 0;"><strong>CHARGING TIME:</strong></td><td>:- {{ $quotation->charging_time ?? '3-4 HR' }}</td></tr>
                            <tr><td style="padding: 1px 0;"><strong>PERFORMANCE:</strong></td><td>:- {{ $quotation->performance ?? 'HIGH SPEED 25 KM/HR' }}</td></tr>
                            <tr><td style="padding: 1px 0;"><strong>CHARGER OUTPUT:</strong></td><td>:- {{ $quotation->charger_output ?? 'DC 51V 105 AH (1 LITHIUM BATTERY)' }}</td></tr>
                            <tr><td style="padding: 1px 0;"><strong>MOTOR OUTPUT:</strong></td><td>:- {{ $quotation->motor_output ?? '1200 W' }}</td></tr>
                            <tr><td style="padding: 1px 0;"><strong>SEATING CAPACITY:</strong></td><td>:- {{ $quotation->seating_capacity ?? '5' }}</td></tr>
                            <tr><td style="padding: 1px 0;"><strong>TYPE OF BREAK:</strong></td><td>:- {{ $quotation->type_of_break ?? 'DRUM BREAK' }}</td></tr>
                            <tr><td style="padding: 1px 0;"><strong>ROOT TOP ABS HARD ROOF:</strong></td><td>;- {{ $quotation->roof_top_abs ?? 'YES' }}</td></tr>
                            <tr><td style="padding: 1px 0;"><strong>FRONT FIBER WIND SHIELD:</strong></td><td>;- {{ $quotation->front_fiber_wind_shield ?? 'YES' }}</td></tr>
                            <tr><td style="padding: 1px 0;"><strong>METER:</strong></td><td>:- {{ $quotation->meter_type ?? 'DIGITAL' }}</td></tr>
                            <tr><td style="padding: 1px 0;"><strong>ON ROAD PRICE:</strong></td><td>:- {{ number_format($quotation->total_amount, 0, '', '') }}/- INCLUDING GST, RTO, INSURANCE</td></tr>
                            <tr><td style="padding: 1px 0;"><strong>ACCESSORIES:</strong></td><td>:- STEPNY, JACK, TOOL KIT,STERIO, SIDE MIRROR</td></tr>
                        </table>
                    </div>

                    <div style="font-size: 8px; border: 1px solid #ccc; padding: 6px; border-radius: 4px; background: #fafafa; margin-right: 10px; margin-bottom: 8px;">
                        <strong style="font-size: 9px; color: #111; display: block; margin-bottom: 3px;">TERMS AND CONDITION –</strong>
                        THERE IS 12 MONTH WARRANTY FOR MOTOR, CONTROLLER<br>
                        THERE IS 36 MONTH WARRANTY FOR BATTERY, CHARGER<br>
                        THERE IS NO WARRANTY OR GURANTEE FOR THE WORK CARRIED OUT AND PARTS REPLACED OTHER THAN FOR THE PARTS LIKE BATTERIES, CHARGER, CONTROLLER, MOTOR.
                    </div>

                    <div style="font-size: 8.5px; border: 1px solid #ccc; padding: 6px; border-radius: 4px; background: #fafafa; margin-right: 10px;">
                        <strong style="font-size: 9px; color: #111; display: block; margin-bottom: 3px;">BANK DETAILS :-</strong>
                        <strong>AC HOLDER :-</strong> SHREE KRISHNA AUTO GREEN<br>
                        <strong>BRANCH :-</strong> STADIUM, SHOPPING CENTRE<br>
                        <strong>AC NUMBER :-</strong> 65261516842<br>
                        <strong>IFSC :-</strong> SBIN0050696
                    </div>
                @elseif($quotation->remarks)
                    <div style="font-size: 9px; color: #555; border: 1px solid #eee; padding: 8px; border-radius: 4px; background: #fafafa; margin-right: 15px;">
                        <strong>Remarks / Terms:</strong><br>
                        {!! nl2br(e($quotation->remarks)) !!}
                    </div>
                @endif
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table class="totals-table">
                    <tr>
                        <td>Taxable Amount:</td>
                        <td class="text-right">₹{{ number_format($quotation->taxable_amount, 2) }}</td>
                    </tr>
                    @if($quotation->tax_regime === 'cgst_sgst')
                        <tr>
                            <td>CGST Amount:</td>
                            <td class="text-right">₹{{ number_format($quotation->cgst_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>SGST Amount:</td>
                            <td class="text-right">₹{{ number_format($quotation->sgst_amount, 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td>IGST Amount:</td>
                            <td class="text-right">₹{{ number_format($quotation->igst_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Round Off:</td>
                        <td class="text-right">₹{{ number_format($quotation->round_off, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>Grand Total:</strong></td>
                        <td class="text-right"><strong>₹{{ number_format($quotation->total_amount, 2) }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="margin-top: 50px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; text-align: left;">
                    <div style="margin-top: 40px; border-top: 1px solid #ccc; width: 150px; text-align: center; padding-top: 5px;">
                        Customer Signature
                    </div>
                </td>
                <td style="width: 50%; text-align: right;">
                    <div style="margin-top: 40px; border-top: 1px solid #ccc; width: 180px; float: right; text-align: center; padding-top: 5px;">
                        Authorized Signatory<br>
                        <strong>SHREE KRISHNA AUTO GREEN</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        This is a computer-generated quotation and does not require a physical signature.
    </div>

</body>
</html>

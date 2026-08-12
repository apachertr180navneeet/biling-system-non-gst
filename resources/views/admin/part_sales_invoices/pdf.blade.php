<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Parts Sales Invoice - {{ $partSalesInvoice->invoice_number }}</title>
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
            margin-bottom: 15px;
            border-bottom: 2px solid #059669;
            padding-bottom: 8px;
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
            margin-bottom: 12px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .meta-table td {
            padding: 6px 10px;
            font-size: 10px;
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
            margin-bottom: 15px;
        }
        .billing-box {
            width: 48%;
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
            margin-bottom: 4px;
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
            font-size: 9.5px;
            color: #475569;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
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
        }
        .items-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .item-name {
            font-weight: bold;
            color: #0f172a;
            display: block;
        }
        .item-desc {
            font-size: 8.5px;
            color: #64748b;
        }
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .bottom-col {
            vertical-align: top;
        }
        .bank-card {
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 8px;
            margin-bottom: 8px;
        }
        .bank-title {
            font-size: 9px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .words-box {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            font-size: 9px;
            background-color: #ffffff;
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
            padding: 3px 0;
            font-size: 10px;
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
            padding-top: 5px;
            font-size: 12px;
            font-weight: bold;
            color: #047857;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
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

    @php
    if (!function_exists('getIndianRupeesInWords')) {
        function getIndianRupeesInWords($number) {
            $decimal = round($number - ($no = floor($number)), 2) * 100;
            $hundred = null;
            $digits_length = strlen($no);
            $i = 0;
            $str = array();
            $words = array(
                0 => '', 1 => 'One', 2 => 'Two',
                3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
                7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
                10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
                13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
                16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
                19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
                40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
                70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
            );
            $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
            while( $i < $digits_length ) {
                $divider = ($i == 2) ? 10 : 100;
                $number = floor($no % $divider);
                $no = floor($no / $divider);
                $i += $divider == 10 ? 1 : 2;
                if ($number) {
                    $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                    $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                    $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural. $hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.$hundred;
                } else $str[] = null;
            }
            $Rupees = implode('', array_reverse($str));
            $paise = ($decimal > 0) ? " and " . ($words[floor($decimal / 10) * 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
            return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise . ' Only';
        }
    }
    @endphp

    <div id="watermark">
        <img src="{{ public_path('assets/admin/img/logo.jpg') }}" alt="Watermark Logo">
    </div>

    <table class="company-header">
        <tr>
            <td>
                <div class="company-title">SHREE KRISHNA AUTO GREEN</div>
                <div class="company-info">
                    NH 65 NEAR ROADWAYS BUS STAND JODHPUR, JODHPUR, Rajasthan, 342001<br>
                    PAN Number: ANQPD4555N | Mobile: 7568899148 | Email: vijay.deora429@gmail.com
                </div>
            </td>
            <td class="logo-td">
                <img src="{{ public_path('assets/admin/img/logo.jpg') }}" style="height: 55px; margin-bottom: 4px;"><br>
                <div class="invoice-title">Parts Sales Invoice</div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <span class="meta-label">Invoice No:</span> <span class="meta-value">{{ $partSalesInvoice->invoice_number }}</span>
            </td>
            <td style="width: 50%; text-align: right;">
                <span class="meta-label">Invoice Date:</span> <span class="meta-value">{{ $partSalesInvoice->invoice_date->format('d/m/Y') }}{{ $partSalesInvoice->created_at ? ' ' . $partSalesInvoice->created_at->format('h:i A') : '' }}</span>
            </td>
        </tr>
    </table>

    <table class="billing-table">
        <tr>
            <td class="billing-box">
                <div class="billing-title">BILL TO</div>
                <div class="billing-name">{{ $partSalesInvoice->customer_name }}</div>
                <div class="billing-desc">
                    {{ $partSalesInvoice->customer_address ?? '-' }}<br>
                    <strong>Mobile:</strong> {{ $partSalesInvoice->customer_mobile ?? '-' }} | 
                    <strong>PAN:</strong> {{ $partSalesInvoice->customer_pan ?? '-' }}<br>
                    <strong>Place of Supply:</strong> {{ $partSalesInvoice->place_of_supply }}
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td class="billing-box" style="border-left-color: #059669;">
                <div class="billing-title">SHIP TO</div>
                <div class="billing-name">{{ $partSalesInvoice->customer_name }}</div>
                <div class="billing-desc">
                    {{ $partSalesInvoice->customer_address ?? '-' }}<br>
                    <strong>Mobile:</strong> {{ $partSalesInvoice->customer_mobile ?? '-' }} | 
                    <strong>PAN:</strong> {{ $partSalesInvoice->customer_pan ?? '-' }}<br>
                    <strong>Place of Supply:</strong> {{ $partSalesInvoice->place_of_supply }}
                </div>
            </td>
        </tr>
    </table>

    @if(isset($customerLedger))
    <div style="border: 1px solid #93c5fd; background-color: #eff6ff; padding: 6px 10px; margin-bottom: 12px; border-radius: 4px;">
        <div style="font-size: 9.5px; font-weight: bold; color: #1d4ed8; margin-bottom: 4px; text-transform: uppercase;">
            Customer Ledger Summary: <span style="color: #0f172a;">{{ $customerLedger->customer_name }}</span>
        </div>
        <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
            <tr>
                <td style="width: 33%;">
                    <span style="color: #64748b; display: block; font-size: 8px; font-weight: bold;">TOTAL AMOUNT BILLED</span>
                    <strong style="color: #0f172a; font-size: 10px;">₹ {{ number_format($customerLedger->total_billed, 2) }}</strong>
                </td>
                <td style="width: 33%;">
                    <span style="color: #64748b; display: block; font-size: 8px; font-weight: bold;">TOTAL PAID / DEPOSITED</span>
                    <strong style="color: #059669; font-size: 10px;">₹ {{ number_format($customerLedger->total_paid, 2) }}</strong>
                </td>
                <td style="width: 34%;">
                    <span style="color: #64748b; display: block; font-size: 8px; font-weight: bold;">OUTSTANDING BALANCE</span>
                    <strong style="color: #dc2626; font-size: 10px;">₹ {{ number_format($customerLedger->outstanding_balance, 2) }}</strong>
                </td>
            </tr>
        </table>
    </div>
    @endif

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 53%;">Items & Description</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 14%; text-align: right;">Rate</th>
                <th style="width: 18%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($partSalesInvoice->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <span class="item-name">{{ strtoupper($item->sparePart->name ?? 'N/A') }}</span>
                    @if($item->serial_no_warranty_notes)
                    <span class="item-desc">{{ $item->serial_no_warranty_notes }}</span>
                    @endif
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                <td class="text-right" style="font-weight: bold;">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="bottom-table">
        <tr>
            <td class="bottom-col" style="width: 55%;">
                <div class="bank-card">
                    <div class="bank-title">Bank Details</div>
                    <table style="width: 100%; font-size: 9px;">
                        <tr><td style="color: #64748b; width: 30%;">Name:</td><td style="font-weight: bold;">SHREE KRISHNA AUTO GREEN</td></tr>
                        <tr><td style="color: #64748b;">IFSC Code:</td><td style="font-weight: bold;">SBIN0050696</td></tr>
                        <tr><td style="color: #64748b;">Account No:</td><td style="font-weight: bold;">65261516842</td></tr>
                        <tr><td style="color: #64748b;">Bank:</td><td style="font-weight: bold;">State Bank of India, JODHPUR</td></tr>
                    </table>
                </div>

                <div class="words-box">
                    <strong>Total Amount (in words):</strong><br>
                    {{ getIndianRupeesInWords($partSalesInvoice->total_amount) }}
                </div>
            </td>
            <td style="width: 5%;"></td>
            <td class="bottom-col" style="width: 40%;">
                <div class="summary-card">
                    <table class="summary-table">
                        <tr>
                            <td class="summary-label">Subtotal Amount</td>
                            <td class="summary-value">₹ {{ number_format($partSalesInvoice->taxable_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Round Off</td>
                            <td class="summary-value">₹ {{ number_format($partSalesInvoice->round_off, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label" style="font-weight: bold;">Current Invoice Total</td>
                            <td class="summary-value" style="font-weight: bold;">₹ {{ number_format($partSalesInvoice->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Previous Outstanding</td>
                            <td class="summary-value">₹ {{ number_format($partSalesInvoice->previous_balance ?? 0, 2) }}</td>
                        </tr>
                        <tr class="summary-total-row">
                            <td class="summary-total-label" style="font-weight: bold;">Total Bill Amount</td>
                            <td class="summary-value" style="font-weight: bold; font-size: 11px; text-align: right; color: #047857;">₹ {{ number_format($partSalesInvoice->total_amount + ($partSalesInvoice->previous_balance ?? 0), 2) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Received Amount</td>
                            <td class="summary-value" style="color: #059669; font-weight: bold;">₹ {{ number_format($partSalesInvoice->received_amount ?? 0, 2) }}</td>
                        </tr>
                        <tr style="border-top: 1.5px solid #059669;">
                            <td class="summary-label" style="font-weight: bold; color: #dc2626; padding-top: 3px;">Outstanding Balance</td>
                            <td class="summary-value" style="font-weight: bold; font-size: 11px; color: #dc2626; padding-top: 3px;">₹ {{ number_format($partSalesInvoice->current_balance ?? (($partSalesInvoice->previous_balance ?? 0) + $partSalesInvoice->balance), 2) }}</td>
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
                <div class="sig-text" style="margin-bottom: 25px;">AUTHORISED SIGNATORY FOR<br><strong>SHREE KRISHNA AUTO GREEN</strong></div>
                <div class="sig-line" style="width: 50%;"></div>
                <div class="sig-text">Prop.</div>
            </td>
        </tr>
    </table>

</body>
</html>

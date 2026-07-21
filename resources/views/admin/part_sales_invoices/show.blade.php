@extends('admin.layouts.app')
@section('style')
<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

.invoice-wrapper {
    background: #f8fafc;
    padding: 30px 15px;
    display: flex;
    justify-content: center;
}

.invoice-card {
    width: 794px; /* A4 Portrait width */
    max-width: 100%;
    background: #fff;
    box-shadow: 0 15px 35px rgba(22, 101, 52, 0.05);
    border-radius: 12px;
    font-family: 'Outfit', -apple-system, sans-serif;
    color: #1e293b;
    position: relative;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.08;
    z-index: 0;
    pointer-events: none;
    width: 70%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.watermark img {
    width: 100%;
    height: auto;
}

/* Dynamic eco-accent top border */
.invoice-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 6px;
    background: linear-gradient(90deg, #10b981, #059669, #15803d);
}

.invoice-padding {
    padding: 25px;
}

/* Header design */
.company-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 10px;
}

.company-details h2 {
    font-size: 24px;
    font-weight: 800;
    color: #14532d; /* Deep forest green */
    margin: 0 0 4px 0;
    letter-spacing: -0.5px;
}

.company-details p {
    font-size: 12px;
    color: #475569;
    line-height: 1.4;
    margin: 0;
}

.invoice-title-block {
    text-align: right;
}

.invoice-title-block h1 {
    font-size: 32px;
    font-weight: 900;
    color: #059669; /* Emerald green */
    letter-spacing: 2px;
    margin: 0;
    line-height: 1;
}

.brand-badge {
    background-color: #ecfdf5;
    color: #047857;
    font-size: 12px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 99px;
    display: inline-block;
    margin-top: 6px;
    border: 1px solid #a7f3d0;
}

.invoice-meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 15px;
}

.meta-table {
    width: 100%;
}

.meta-table td {
    padding: 3px 0;
    font-size: 13px;
}

.meta-label {
    color: #64748b;
    font-weight: 500;
}

.meta-value {
    color: #0f172a;
    font-weight: 600;
    text-align: right;
}

/* Billing Section */
.billing-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.billing-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 15px;
    background: #fff;
    border-left: 4px solid #10b981; /* Accent green line */
}

.billing-card h3 {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #64748b;
    margin: 0 0 6px 0;
}

.billing-card h4 {
    font-size: 13px;
    font-weight: 700;
    margin: 0 0 4px 0;
    color: #0f172a;
}

.billing-card p {
    font-size: 11px;
    color: #475569;
    line-height: 1.4;
    margin: 0;
}

/* Items Table */
.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.items-table th {
    background-color: #0f172a; /* Slate header */
    color: #ffffff;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 8px 12px;
}

.items-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: top;
    font-size: 12px;
}

.items-table tr:last-child td {
    border-bottom: none;
}

.item-name {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    display: block;
}

.item-desc {
    font-size: 11px;
    color: #64748b;
    display: block;
    margin-top: 2px;
}

/* Bottom elements */
.bottom-section {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
    gap: 15px;
}

.left-panel {
    width: 55%;
}

.bank-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 15px;
    background: #f8fafc;
    margin-bottom: 10px;
}

.bank-card h4 {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #0f172a;
    margin: 0 0 6px 0;
}

.bank-table {
    width: 100%;
    font-size: 11px;
}

.bank-table td {
    padding: 2px 0;
}

.right-panel {
    width: 42%;
}

.summary-card {
    background-color: #ecfdf5; /* Eco light green */
    border: 1px solid #a7f3d0;
    border-radius: 8px;
    padding: 10px 15px;
}

.summary-table {
    width: 100%;
}

.summary-table td {
    padding: 4px 0;
    font-size: 12px;
}

.summary-label {
    color: #065f46;
    font-weight: 500;
}

.summary-value {
    text-align: right;
    color: #065f46;
    font-weight: 600;
}

.summary-total-row td {
    border-top: 2px solid #059669;
    padding-top: 6px;
    margin-top: 4px;
}

.summary-total-label {
    font-size: 14px;
    font-weight: 800;
    color: #047857;
}

.summary-total-value {
    font-size: 15px;
    font-weight: 800;
    color: #047857;
    text-align: right;
}

.words-box {
    margin-top: 15px;
    font-size: 11px;
    color: #1e293b;
    border-top: 1px solid #e2e8f0;
    padding-top: 8px;
}

.signature-row {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
    border-top: 1px solid #e2e8f0;
    padding-top: 15px;
}

.sig-box {
    width: 45%;
    text-align: center;
}

.sig-line {
    border-top: 1px dashed #cbd5e1;
    margin-bottom: 6px;
    width: 75%;
    margin-left: auto;
    margin-right: auto;
}

.sig-text {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
}

.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 5mm;
    }
    body * {
        visibility: hidden;
    }
    .invoice-wrapper, .invoice-wrapper * {
        visibility: visible;
    }
    .invoice-wrapper {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: #fff;
        padding: 0 !important;
        margin: 0 !important;
    }
    .invoice-card {
        box-shadow: none;
        border: none;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        transform: none !important;
    }
    .invoice-card::before {
        display: none;
    }
    .invoice-padding {
        padding: 5px !important;
    }
    .btn-print-group {
        display: none !important;
    }
    .layout-navbar, .layout-menu-toggle, .menu-vertical, .footer {
        display: none !important;
    }
    .content-wrapper {
        padding: 0 !important;
        margin: 0 !important;
    }
}
</style>
@endsection

@section('content')
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

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4 btn-print-group">
        <h4 class="fw-bold mb-0">Parts Sales Invoice Detail</h4>
        <div>
            <button onclick="window.print();" class="btn btn-primary"><i class="bx bx-printer"></i> Print Invoice</button>
            <a href="{{ route('admin.part-sales-invoices.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <div class="invoice-wrapper">
        <div class="invoice-card">
            <!-- Background Watermark -->
            <div class="watermark">
                <img src="{{ asset('assets/admin/img/logo.jpg') }}" alt="Watermark Logo">
            </div>
            
            <div class="invoice-padding">
                
                <!-- Company Header -->
                <div class="company-section">
                    <div class="company-details">
                        <h2>SHREE KRISHNA AUTO GREEN</h2>
                        <p>NH 65 NEAR ROADWAYS BUS STAND JODHPUR, JODHPUR, Rajasthan, 342001</p>
                        <p style="margin-top: 4px; font-weight: 600; color: #047857;">GSTIN : 08ANQPD4555N1ZE</p>
                        <p>PAN Number : ANQPD4555N</p>
                        <p>Email: vijay.deora429@gmail.com | Mobile: 7568899148</p>
                    </div>
                    <div class="logo-block">
                        <img src="{{ asset('assets/admin/img/logo.jpg') }}" alt="Shree Krishna Auto Green" style="max-height: 120px; width: auto;">
                    </div>
                </div>

                <!-- Meta Info Grid -->
                <div class="invoice-meta-grid">
                    <div>
                        <table class="meta-table">
                            <tr>
                                <td class="meta-label" style="width: 40%;">Invoice No.:</td>
                                <td class="meta-value" style="text-align: left; padding-left: 10px;">{{ $partSalesInvoice->invoice_number }}</td>
                            </tr>
                        </table>
                    </div>
                    <div>
                        <table class="meta-table">
                            <tr>
                                <td class="meta-label" style="width: 40%;">Invoice Date:</td>
                                <td class="meta-value" style="text-align: left; padding-left: 10px;">{{ $partSalesInvoice->invoice_date->format('d/m/Y g:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Customer Details Block -->
                <div class="billing-section">
                    <div class="billing-card">
                        <h3>BILL TO</h3>
                        <h4>{{ $partSalesInvoice->customer_name }}</h4>
                        <p>{{ $partSalesInvoice->customer_address ?? '-' }}</p>
                        <p style="margin-top: 6px;"><b>Mobile:</b> {{ $partSalesInvoice->customer_mobile ?? '-' }}</p>
                        <p><b>GSTIN:</b> {{ $partSalesInvoice->customer_gstin ?? '-' }}</p>
                        <p><b>PAN Number:</b> {{ $partSalesInvoice->customer_pan ?? '-' }}</p>
                        <p><b>Place of Supply:</b> {{ $partSalesInvoice->place_of_supply }}</p>
                    </div>
                    <div class="billing-card" style="border-left-color: #059669;">
                        <h3>SHIP TO</h3>
                        <h4>{{ $partSalesInvoice->customer_name }}</h4>
                        <p>{{ $partSalesInvoice->customer_address ?? '-' }}</p>
                        <p style="margin-top: 6px;"><b>Mobile:</b> {{ $partSalesInvoice->customer_mobile ?? '-' }}</p>
                        <p><b>GSTIN:</b> {{ $partSalesInvoice->customer_gstin ?? '-' }}</p>
                        <p><b>PAN Number:</b> {{ $partSalesInvoice->customer_pan ?? '-' }}</p>
                        <p><b>Place of Supply:</b> {{ $partSalesInvoice->place_of_supply }}</p>
                    </div>
                </div>

                <!-- Items Table -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 6%; text-align: center;">#</th>
                            <th style="width: 54%;">Items & Description</th>
                            <th style="width: 10%; text-align: center;">Qty</th>
                            <th style="width: 14%; text-align: right;">Rate</th>
                            <th style="width: 16%; text-align: right;">Tax (GST)</th>
                            <th style="width: 14%; text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($partSalesInvoice->items as $index => $item)
                        <tr>
                            <td class="text-center" style="font-weight: 600;">{{ $index + 1 }}</td>
                            <td>
                                <span class="item-name">{{ strtoupper($item->sparePart->name) }}</span>
                                @if($item->serial_no_warranty_notes)
                                <span class="item-desc">{{ $item->serial_no_warranty_notes }}</span>
                                @endif
                            </td>
                            <td class="text-center" style="font-weight: 600;">{{ $item->quantity }} {{ strtoupper($item->sparePart->unit) }}</td>
                            <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                            <td class="text-right">
                                {{ number_format($item->tax_amount, 2) }}
                                <br><small class="text-muted">({{ floatval($item->tax_percentage) }}%)</small>
                            </td>
                            <td class="text-right" style="font-weight: 600;">{{ number_format($item->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Bottom Section -->
                <div class="bottom-section">
                    <div class="left-panel">
                        <div class="bank-card">
                            <h4>BANK DETAILS</h4>
                            <table class="bank-table">
                                <tr>
                                    <td style="color: #64748b; width: 35%;">Name:</td>
                                    <td style="font-weight: 600; color: #0f172a;">SHREE KRISHNA AUTO GREEN</td>
                                </tr>
                                <tr>
                                    <td style="color: #64748b;">IFSC Code:</td>
                                    <td style="font-weight: 600; color: #0f172a;">SBIN0050696</td>
                                </tr>
                                <tr>
                                    <td style="color: #64748b;">Account No:</td>
                                    <td style="font-weight: 600; color: #0f172a;">65261516842</td>
                                </tr>
                                <tr>
                                    <td style="color: #64748b;">Bank:</td>
                                    <td style="font-weight: 600; color: #0f172a;">State Bank of India, JODHPUR</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="words-box">
                            <strong>Total Amount (in words)</strong>
                            <div>{{ getIndianRupeesInWords($partSalesInvoice->total_amount) }}</div>
                        </div>
                    </div>

                    <div class="right-panel">
                        <div class="summary-card">
                            <table class="summary-table">
                                <tr>
                                    <td class="summary-label">Taxable Amount</td>
                                    <td class="summary-value">₹ {{ number_format($partSalesInvoice->taxable_amount, 2) }}</td>
                                </tr>
                                @if(($partSalesInvoice->tax_regime ?? 'cgst_sgst') === 'igst')
                                <tr>
                                    <td class="summary-label">IGST Amount</td>
                                    <td class="summary-value">₹ {{ number_format($partSalesInvoice->igst_amount ?? 0, 2) }}</td>
                                </tr>
                                @else
                                <tr>
                                    <td class="summary-label">CGST Amount</td>
                                    <td class="summary-value">₹ {{ number_format($partSalesInvoice->cgst_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="summary-label">SGST Amount</td>
                                    <td class="summary-value">₹ {{ number_format($partSalesInvoice->sgst_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="summary-label">Round Off</td>
                                    <td class="summary-value">₹ {{ number_format($partSalesInvoice->round_off, 2) }}</td>
                                </tr>
                                <tr class="summary-total-row">
                                    <td class="summary-total-label">Total Amount</td>
                                    <td class="summary-total-value">₹ {{ number_format($partSalesInvoice->total_amount, 0) }}</td>
                                </tr>


                            </table>
                        </div>
                    </div>
                </div>

                <!-- Signature Section -->
                <div class="signature-row">
                    <div class="sig-box">
                        <div class="sig-line"></div>
                        <div class="sig-text">CUSTOMER SIGNATURE</div>
                    </div>
                    <div class="sig-box">
                        <div class="sig-text" style="margin-bottom: 25px;">AUTHORISED SIGNATORY FOR<br><strong>SHREE KRISHNA AUTO GREEN</strong></div>
                        <div class="sig-line" style="width: 50%;"></div>
                        <div class="sig-text">Prop.</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

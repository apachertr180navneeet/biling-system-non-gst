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
    padding: 20px;
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
    font-size: 26px;
    font-weight: 800;
    color: #14532d; /* Deep forest green */
    margin: 0 0 6px 0;
    letter-spacing: -0.5px;
}

.company-details p {
    font-size: 13px;
    color: #475569;
    line-height: 1.5;
    margin: 0;
}

.invoice-title-block {
    text-align: right;
}

.invoice-title-block h1 {
    font-size: 40px;
    font-weight: 900;
    color: #059669; /* Emerald green */
    letter-spacing: 2px;
    margin: 0;
    line-height: 1;
}

.brand-badge {
    background-color: #ecfdf5;
    color: #047857;
    font-size: 14px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 99px;
    display: inline-block;
    margin-top: 8px;
    border: 1px solid #a7f3d0;
}

.invoice-meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
}

.meta-box {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 18px;
}

.meta-table {
    width: 100%;
}

.meta-table td {
    padding: 4px 0;
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
    grid-template-columns: 1.2fr 0.8fr;
    gap: 12px;
    margin-bottom: 12px;
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
    margin: 0 0 8px 0;
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

.info-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 15px;
    background: #f8fafc;
}

.info-card h3 {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #64748b;
    margin: 0 0 8px 0;
}

.info-table {
    width: 100%;
}

.info-table td {
    padding: 3px 0;
    font-size: 11px;
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
    background-color: #0f172a; /* Sophisticated slate/black header */
    color: #ffffff;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 16px;
}

.items-table td {
    padding: 15px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: top;
    font-size: 13px;
}

.items-table tr:last-child td {
    border-bottom: none;
}

.item-name {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
}

.item-badge {
    background-color: #d1fae5;
    color: #065f46;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    margin-left: 8px;
    display: inline-block;
    border: 1px solid #a7f3d0;
}

.warranty-box {
    background-color: #f0fdf4;
    border-left: 4px solid #10b981;
    padding: 6px 10px;
    border-radius: 6px;
    margin-top: 6px;
    font-size: 11px;
}

.warranty-box strong {
    color: #14532d;
    display: block;
    margin-bottom: 4px;
    letter-spacing: 0.5px;
}

.warranty-box div {
    color: #15803d;
    line-height: 1.5;
}

.specs-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px 12px;
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 12px;
    margin-top: 8px;
}

.specs-cell {
    font-size: 12px;
    color: #475569;
}

.specs-cell strong {
    color: #0f172a;
    font-weight: 600;
}

/* Bottom elements */
.bottom-section {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
}

.terms-box {
    width: 58%;
}

.terms-box h4 {
    font-size: 12px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 8px 0;
    letter-spacing: 0.5px;
}

.terms-box ol {
    margin: 0;
    padding-left: 18px;
    font-size: 11px;
    color: #64748b;
    line-height: 1.5;
}

.terms-box ol li {
    margin-bottom: 4px;
}

.summary-card {
    width: 38%;
    background-color: #ecfdf5; /* Eco light green */
    border: 1px solid #a7f3d0;
    border-radius: 8px;
    padding: 12px 18px;
}

.summary-table {
    width: 100%;
}

.summary-table td {
    padding: 5px 0;
    font-size: 13px;
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
    padding-top: 8px;
    margin-top: 4px;
}

.summary-total-label {
    font-size: 15px;
    font-weight: 800;
    color: #047857;
}

.summary-total-value {
    font-size: 16px;
    font-weight: 800;
    color: #047857;
    text-align: right;
}

.signature-row {
    display: flex;
    justify-content: space-between;
    margin-top: 25px;
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
    font-size: 12px;
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
        transform-origin: top left;
    }
    .invoice-card::before {
        display: none;
    }
    .invoice-padding {
        padding: 5px !important;
    }
    .company-section {
        margin-bottom: 8px !important;
        padding-bottom: 6px !important;
    }
    .invoice-meta-grid {
        margin-bottom: 8px !important;
        gap: 8px !important;
    }
    .meta-box {
        padding: 6px 12px !important;
    }
    .billing-section {
        margin-bottom: 8px !important;
        gap: 8px !important;
    }
    .billing-card, .info-card {
        padding: 8px 12px !important;
    }
    .items-table {
        margin-bottom: 8px !important;
    }
    .items-table th, .items-table td {
        padding: 6px 10px !important;
    }
    .warranty-box {
        margin-top: 4px !important;
        padding: 4px 8px !important;
        font-size: 10.5px !important;
    }
    .specs-grid {
        margin-top: 4px !important;
        padding: 4px 8px !important;
        gap: 3px 8px !important;
    }
    .bottom-section {
        margin-top: 8px !important;
    }
    .summary-card {
        padding: 6px 10px !important;
    }
    .summary-table td {
        padding: 2px 0 !important;
        font-size: 12px !important;
    }
    .signature-row {
        margin-top: 10px !important;
        padding-top: 8px !important;
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
    .specs-grid {
        background-color: #f8fafc !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .summary-card {
        background-color: #ecfdf5 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .warranty-box {
        background-color: #f0fdf4 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4 btn-print-group">
        <h4 class="fw-bold mb-0">Invoice Detail</h4>
        <div>
            <button onclick="window.print();" class="btn btn-primary"><i class="bx bx-printer"></i> Print Invoice</button>
            <a href="{{ route('admin.vehicle-sales-invoices.index') }}" class="btn btn-secondary">Back to List</a>
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
                        <p>NEAR MAHAMANDIR CIRCLE, MANDORE ROAD</p>
                        <p>JODHPUR (RAJASTHAN)</p>
                        <p style="margin-top: 4px; font-weight: 600; color: #047857;">GSTIN : 08ANQPD4555N1ZE</p>
                        <p>Contact : 7586899148, 9829028792</p>
                    </div>
                    <div class="logo-block">
                        <img src="{{ asset('assets/admin/img/logo.jpg') }}" alt="Shree Krishna Auto Green" style="max-height: 120px; width: auto;">
                    </div>
                </div>

                <!-- Meta Info Grid -->
                <div class="invoice-meta-grid">
                    <div class="meta-box">
                        <table class="meta-table">
                            <tr>
                                <td class="meta-label">Invoice#</td>
                                <td class="meta-value">INV-{{ str_pad($vehicleSalesInvoice->id, 6, '0', STR_PAD_LEFT) }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Invoice Date</td>
                                <td class="meta-value">{{ $vehicleSalesInvoice->invoice_date->format('d M Y') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="meta-box">
                        <table class="meta-table">
                            <tr>
                                <td class="meta-label">Payment Mode</td>
                                <td class="meta-value">{{ $vehicleSalesInvoice->payment_mode ?? '-' }}</td>
                            </tr>
                            @if($vehicleSalesInvoice->payment_mode === 'Finance' && $vehicleSalesInvoice->finance_name)
                            <tr>
                                <td class="meta-label">Finance Name</td>
                                <td class="meta-value">{{ $vehicleSalesInvoice->finance_name }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="meta-label">Date of Sale</td>
                                <td class="meta-value">{{ $vehicleSalesInvoice->invoice_date->format('d M Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Customer Details Block -->
                <div class="billing-section">
                    <div class="billing-card">
                        <h3>Bill To</h3>
                        <h4>{{ $vehicleSalesInvoice->customer_name }}</h4>
                        <p>{{ $vehicleSalesInvoice->customer_address ?? '-' }}</p>
                        <p style="margin-top: 6px;"><b>Mobile:</b> {{ $vehicleSalesInvoice->customer_mobile ?? '-' }}</p>
                        <p><b>Residence Tel:</b> {{ $vehicleSalesInvoice->customer_residence_phone ?? '-' }}</p>
                    </div>
                    <div class="info-card">
                        <h3>Customer Info</h3>
                        <table class="info-table">
                            <tr>
                                <td style="color: #64748b;">Age:</td>
                                <td style="font-weight: 600; color: #0f172a; text-align: right;">{{ $vehicleSalesInvoice->customer_age ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="color: #64748b; padding-top: 6px;">Occupation:</td>
                                <td style="font-weight: 600; color: #0f172a; text-align: right; padding-top: 6px;">{{ $vehicleSalesInvoice->customer_occupation ?? 'BUSINESS' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Items Table -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 6%; text-align: center;">#</th>
                            <th style="width: 58%;">Item & Description</th>
                            <th style="width: 8%; text-align: center;">Qty</th>
                            <th style="width: 14%; text-align: right;">Rate</th>
                            <th style="width: 14%; text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center" style="font-weight: 600;">1</td>
                            <td>
                                <span class="item-name">{{ strtoupper($vehicleSalesInvoice->vehicleInventory->vehicle_description) }}</span>
                                <span class="item-badge">{{ strtoupper($battery_type) }}</span>

                                <div class="warranty-box">
                                    <strong>WARRANTY DETAILS</strong>
                                    <div>{!! nl2br(e($vehicleSalesInvoice->warranty_notes)) !!}</div>
                                </div>

                                <!-- Restructured Borderless Specifications Grid -->
                                <div class="specs-grid">
                                    <div class="specs-cell"><b>Model:</b> {{ $vehicleSalesInvoice->vehicleInventory->vehicle_description }}</div>
                                    <div class="specs-cell"><b>Colour:</b> {{ $color_name }}</div>
                                    <div class="specs-cell"><b>Chassis No:</b> <strong style="color: #059669;">{{ $vehicleSalesInvoice->vehicleInventory->chassis_number }}</strong></div>
                                    
                                    <div class="specs-cell"><b>Battery No:</b> {{ $vehicleSalesInvoice->vehicleInventory->battery_number ?? '-' }}</div>
                                    <div class="specs-cell"><b>Charger No:</b> {{ $vehicleSalesInvoice->vehicleInventory->charger_number ?? '-' }}</div>
                                    <div class="specs-cell"><b>Controller No:</b> {{ $vehicleSalesInvoice->vehicleInventory->controller_number ?? '-' }}</div>
                                    
                                    <div class="specs-cell"><b>Convertor No:</b> {{ $vehicleSalesInvoice->vehicleInventory->convertor_number ?? '-' }}</div>
                                    <div class="specs-cell"><b>Manual No:</b> {{ $vehicleSalesInvoice->vehicleInventory->manual_number ?? '-' }}</div>
                                    <div class="specs-cell"><b>Motor No:</b> {{ $vehicleSalesInvoice->vehicleInventory->motor_number ?? '-' }}</div>
                                    
                                    <div class="specs-cell"><b>Battery Type:</b> {{ $battery_type }}</div>
                                    <div class="specs-cell"><b>Battery Make:</b> {{ $battery_make }}</div>
                                    <div class="specs-cell"></div>
                                </div>
                            </td>
                            <td class="text-center" style="font-weight: 600;">1</td>
                            <td class="text-right" style="font-weight: 600;">{{ number_format($vehicleSalesInvoice->rate, 2) }}</td>
                            <td class="text-right" style="font-weight: 600;">{{ number_format($vehicleSalesInvoice->rate, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Bottom Section -->
                <div class="bottom-section">
                    <div class="terms-box">
                        <h4>TERMS & CONDITIONS</h4>
                        <ol>
                            <li>Received vehicle, tool kit, charger, jack, stepny and Battery in good and running condition.</li>
                            <li>Our responsibility ceases upon delivery & claim for loss/ shortage etc. will not be entertained thereafter.</li>
                            <li>Goods Once sold will not be taken back or exchanged under any circumstances.</li>
                            <li>Warranty as per Company's policy given in owner's manual. 12Month motor and Controller.</li>
                            <li>Subject to JODHPUR Jurisdiction only.</li>
                            <li>Getting any work done on the vehicle outside of our authorized office workshop will void the entire warranty.</li>
                        </ol>
                        <p style="font-size: 12px; font-weight: 600; color: #059669; margin-top: 15px; font-style: italic;">Thanks for shopping with us.</p>
                    </div>

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
                            @if(($vehicleSalesInvoice->tax_regime ?? 'cgst_sgst') === 'igst')
                            <tr>
                                <td class="summary-label">IGST @ 5%</td>
                                <td class="summary-value">{{ number_format($vehicleSalesInvoice->igst_amount ?? 0, 2) }}</td>
                            </tr>
                            @else
                            <tr>
                                <td class="summary-label">SGST @ 2.5%</td>
                                <td class="summary-value">{{ number_format($vehicleSalesInvoice->sgst_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="summary-label">CGST @ 2.5%</td>
                                <td class="summary-value">{{ number_format($vehicleSalesInvoice->cgst_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="summary-label">Total</td>
                                <td class="summary-value">{{ number_format($vehicleSalesInvoice->total, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="summary-label" style="font-size: 11px;">Less :- NEMMP 2020</td>
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
                                <td class="summary-total-label">G. Total</td>
                                <td class="summary-total-value">{{ number_format($vehicleSalesInvoice->grand_total, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Signature Section -->
                <div class="signature-row">
                    <div class="sig-box">
                        <div class="sig-line"></div>
                        <div class="sig-text">CUSTOMER SIGNATURE</div>
                    </div>
                    <div class="sig-box">
                        <div class="sig-text" style="margin-bottom: 25px;">For SHREE KRISHNA AUTO GREEN</div>
                        <div class="sig-line" style="width: 50%;"></div>
                        <div class="sig-text">Prop.</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

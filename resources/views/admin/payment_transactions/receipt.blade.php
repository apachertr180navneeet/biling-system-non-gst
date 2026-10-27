<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - REC-PAY-{{ $paymentTransaction->id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/fonts/boxicons.css') }}" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }
        body {
            background-color: #f1f5f9;
            color: #1e293b;
            padding: 30px 15px;
        }
        .receipt-card {
            max-width: 720px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            position: relative;
        }
        .receipt-accent-top {
            height: 6px;
            background: linear-gradient(90deg, #10b981, #059669, #047857);
        }
        .receipt-content {
            padding: 30px 35px;
        }
        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .company-info h2 {
            font-size: 20px;
            font-weight: 800;
            color: #065f46;
            margin-bottom: 4px;
            letter-spacing: -0.3px;
        }
        .company-info p {
            font-size: 12px;
            color: #64748b;
            line-height: 1.4;
        }
        .logo-box img {
            max-height: 70px;
            width: auto;
        }
        .badge-receipt {
            display: inline-block;
            background: #ecfdf5;
            color: #047857;
            font-weight: 700;
            font-size: 12px;
            padding: 5px 12px;
            border-radius: 20px;
            border: 1px solid #a7f3d0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .meta-item {
            font-size: 13px;
        }
        .meta-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .meta-value {
            color: #1e293b;
            font-weight: 700;
        }
        .amount-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .amount-table th {
            background: #065f46;
            color: #ffffff;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 10px 14px;
            text-align: left;
        }
        .amount-table th.text-right, .amount-table td.text-right {
            text-align: right;
        }
        .amount-table td {
            padding: 12px 14px;
            font-size: 13px;
            border-bottom: 1px solid #e2e8f0;
        }
        .amount-table tr.total-row td {
            background: #f0fdf4;
            font-weight: 800;
            font-size: 15px;
            color: #047857;
            border-top: 2px solid #10b981;
        }
        .amount-in-words {
            background: #f8fafc;
            border-left: 4px solid #10b981;
            padding: 10px 15px;
            font-size: 12px;
            margin-bottom: 20px;
            border-radius: 0 6px 6px 0;
        }
        .notes-box {
            background: #fefce8;
            border: 1px solid #fef08a;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 30px;
        }
        .notes-box-title {
            font-size: 11px;
            font-weight: 700;
            color: #854d0e;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .notes-box-content {
            font-size: 13px;
            color: #713f12;
            line-height: 1.4;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            padding-top: 30px;
            border-top: 1px dashed #cbd5e1;
            margin-top: 20px;
        }
        .sig-col {
            text-align: center;
            width: 40%;
        }
        .sig-line {
            border-top: 1px solid #64748b;
            margin-bottom: 6px;
        }
        .sig-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
        }
        .btn-bar {
            max-width: 720px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
        }
        .btn-print {
            background: #065f46;
            color: white;
        }
        .btn-print:hover {
            background: #047857;
        }
        .btn-back {
            background: #e2e8f0;
            color: #334155;
        }
        .btn-back:hover {
            background: #cbd5e1;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .btn-bar {
                display: none !important;
            }
            .receipt-card {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>

    <div class="btn-bar">
        <a href="javascript:window.close();" class="btn btn-back">
            <i class="bx bx-arrow-back"></i> Close
        </a>
        <button onclick="window.print()" class="btn btn-print">
            <i class="bx bx-printer"></i> Print Receipt
        </button>
    </div>

    <div class="receipt-card">
        <div class="receipt-accent-top"></div>
        <div class="receipt-content">
            
            <div class="receipt-header">
                <div class="company-info">
                    <h2>SHREE KRISHNA AUTO GREEN</h2>
                    <p>Near Mahamandir Circle, Mandore Road, Jodhpur (Rajasthan)</p>
                    <p>Phone: 7586899148, 9829028792</p>
                </div>
                <div class="logo-box">
                    <img src="{{ asset('assets/admin/img/logo.jpg') }}" alt="Logo">
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span class="badge-receipt">
                    <i class="bx bx-check-circle"></i> Official Payment Receipt
                </span>
                <span style="font-size: 12px; font-weight: 700; color: #64748b;">
                    Receipt No: <strong style="color: #065f46;">REC-PAY-{{ str_pad($paymentTransaction->id, 5, '0', STR_PAD_LEFT) }}</strong>
                </span>
            </div>

            <div class="meta-grid">
                <div class="meta-item">
                    <div class="meta-label">Party / Customer Name</div>
                    <div class="meta-value">{{ $paymentTransaction->party_name ?? ($paymentTransaction->customer->name ?? ($paymentTransaction->supplier->name ?? 'Walk-in Party')) }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Payment Date & Time</div>
                    <div class="meta-value">{{ $paymentTransaction->created_at ? $paymentTransaction->created_at->format('d-m-Y h:i A') : $paymentTransaction->payment_date->format('d-m-Y') }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Invoice / Reference No</div>
                    <div class="meta-value">{{ $billNumber }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Payment Mode</div>
                    <div class="meta-value">{{ $paymentTransaction->payment_mode ?? 'Cash' }}</div>
                </div>
            </div>

            <table class="amount-table">
                <thead>
                    <tr>
                        <th>Particulars</th>
                        <th class="text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>Amount Paid</strong>
                            <div style="font-size: 11px; color: #64748b;">Net funds received via {{ $paymentTransaction->payment_mode ?? 'Cash' }}</div>
                        </td>
                        <td class="text-right fw-bold" style="color: #059669; font-size: 14px;">
                            ₹{{ number_format($paymentTransaction->amount, 2) }}
                        </td>
                    </tr>
                    @if(($paymentTransaction->discount ?? 0) > 0)
                    <tr>
                        <td>
                            <strong>Payment Settlement Discount</strong>
                            <div style="font-size: 11px; color: #64748b;">Discount granted on this payment settlement</div>
                        </td>
                        <td class="text-right fw-bold" style="color: #d97706; font-size: 14px;">
                            ₹{{ number_format($paymentTransaction->discount, 2) }}
                        </td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td>Total Settled / Credited to Account</td>
                        <td class="text-right">
                            ₹{{ number_format($paymentTransaction->amount + ($paymentTransaction->discount ?? 0), 2) }}
                        </td>
                    </tr>
                    @if(isset($bill->balance))
                    <tr>
                        <td style="color: #dc2626; font-weight: 600;">Remaining Balance on Invoice</td>
                        <td class="text-right" style="color: #dc2626; font-weight: 700;">
                            ₹{{ number_format($bill->balance, 2) }}
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>

            @if(function_exists('getIndianRupeesInWords'))
            <div class="amount-in-words">
                <strong>Amount Paid in Words:</strong> {{ getIndianRupeesInWords($paymentTransaction->amount) }}
            </div>
            @endif

            @if(!empty($paymentTransaction->notes))
            <div class="notes-box">
                <div class="notes-box-title"><i class="bx bx-note"></i> Payment Remarks / Notes:</div>
                <div class="notes-box-content">{{ $paymentTransaction->notes }}</div>
            </div>
            @endif

            <div class="signature-section">
                <div class="sig-col">
                    <div style="height: 40px;"></div>
                    <div class="sig-line"></div>
                    <div class="sig-title">Customer / Payer Signature</div>
                </div>
                <div class="sig-col">
                    <div style="height: 40px; font-size: 11px; color: #64748b; font-style: italic;">
                        Received by: {{ $paymentTransaction->creator->name ?? 'Authorized Staff' }}
                    </div>
                    <div class="sig-line"></div>
                    <div class="sig-title">Authorised Signatory</div>
                </div>
            </div>

        </div>
    </div>

    @if(request()->has('print'))
    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
    @endif
</body>
</html>

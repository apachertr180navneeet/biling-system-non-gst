<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Party Report By Item</title>
    <style>
        @page {
            margin: 30px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            font-size: 11px;
            line-height: 1.5;
        }
        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
        }
        .meta-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .report-table th {
            background-color: #2c3e50;
            color: #ffffff;
            font-weight: bold;
            padding: 8px 10px;
            font-size: 10px;
            text-align: left;
        }
        .report-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e9ecef;
            font-size: 10px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #777;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td>
                <div class="title">SHREE KRISHNA AUTO GREEN</div>
                <div style="font-size: 12px; font-weight: bold; color: #555; margin-top: 3px;">Party Report By Item</div>
            </td>
            <td class="text-right">
                <div style="font-size: 10px; color: #777;">Generated on: {{ date('d/m/Y h:i A') }}</div>
            </td>
        </tr>
    </table>

    <div class="meta-box">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%;">
                    <strong>Selected Item:</strong> {{ $selectedItemData['name'] ?? 'N/A' }}
                </td>
                <td style="width: 40%; text-align: right;">
                    <strong>Date Range:</strong> 
                    @if($fromDate && $toDate)
                        {{ date('d/m/Y', strtotime($fromDate)) }} - {{ date('d/m/Y', strtotime($toDate)) }}
                    @else
                        {{ ucfirst(str_replace('_', ' ', $dateFilter)) }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th>Party Name</th>
                <th class="text-center">Sales Qty</th>
                <th class="text-right">Sales Amount</th>
                <th class="text-center">Purchase Qty</th>
                <th class="text-right">Purchase Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalSalesQty = 0;
                $totalSalesAmt = 0;
                $totalPurchaseQty = 0;
                $totalPurchaseAmt = 0;
            @endphp

            @forelse($partyData as $row)
                @php
                    $totalSalesQty += $row['sales_qty'];
                    $totalSalesAmt += $row['sales_amount'];
                    $totalPurchaseQty += $row['purchase_qty'];
                    $totalPurchaseAmt += $row['purchase_amount'];
                @endphp
                <tr>
                    <td><strong>{{ $row['party_name'] }}</strong></td>
                    <td class="text-center">{{ $row['sales_qty'] > 0 ? $row['sales_qty'] : '-' }}</td>
                    <td class="text-right">{{ $row['sales_amount'] > 0 ? '₹' . number_format($row['sales_amount'], 2) : '-' }}</td>
                    <td class="text-center">{{ $row['purchase_qty'] > 0 ? $row['purchase_qty'] : '-' }}</td>
                    <td class="text-right">{{ $row['purchase_amount'] > 0 ? '₹' . number_format($row['purchase_amount'], 2) : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px; color: #777;">
                        No transactions found for the selected item.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($partyData) > 0)
            <tfoot>
                <tr style="background-color: #f1f3f5; font-weight: bold;">
                    <td>Total</td>
                    <td class="text-center">{{ $totalSalesQty }}</td>
                    <td class="text-right">₹{{ number_format($totalSalesAmt, 2) }}</td>
                    <td class="text-center">{{ $totalPurchaseQty }}</td>
                    <td class="text-right">₹{{ number_format($totalPurchaseAmt, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        This is a computer-generated report.
    </div>

</body>
</html>

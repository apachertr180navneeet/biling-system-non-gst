<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - {{ $salarySlip->slip_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .slip-box {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #222;
            padding: 25px;
        }
        .header-table, .info-table, .salary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111;
        }
        .slip-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
            color: #444;
        }
        .border-bottom {
            border-bottom: 2px solid #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }
        .info-table td {
            padding: 5px 8px;
            font-size: 13px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            width: 20%;
        }
        .salary-table {
            border: 1px solid #333;
            margin-top: 15px;
        }
        .salary-table th {
            background-color: #f2f2f2;
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
            font-size: 13px;
        }
        .salary-table td {
            border: 1px solid #333;
            padding: 8px;
            font-size: 13px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .net-box {
            background-color: #f8f9fa;
            border: 2px solid #333;
            padding: 12px;
            margin-top: 15px;
            font-size: 14px;
        }
        .signatures {
            margin-top: 60px;
            width: 100%;
        }
        .signatures td {
            text-align: center;
            vertical-align: bottom;
            font-size: 12px;
        }
        .no-print {
            max-width: 800px;
            margin: 0 auto 15px auto;
            text-align: right;
        }
        .btn-print {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .slip-box { border: 1px solid #000; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>
    </div>

    <div class="slip-box">
        <table class="header-table border-bottom">
            <tr>
                <td>
                    <div class="company-name">{{ config('app.name') }}</div>
                    <div class="slip-title">PAYSLIP FOR {{ strtoupper(date('F Y', mktime(0, 0, 0, $salarySlip->month, 1, $salarySlip->year))) }}</div>
                </td>
                <td class="text-right">
                    <div style="font-size: 15px; font-weight: bold;">SLIP NO: {{ $salarySlip->slip_number }}</div>
                    <div style="margin-top: 4px;">Status: <strong>{{ strtoupper($salarySlip->payment_status) }}</strong></div>
                    <div>Payment Date: {{ $salarySlip->payment_date ? $salarySlip->payment_date->format('d/m/Y') : '-' }}</div>
                </td>
            </tr>
        </table>

        <!-- Employee Information -->
        <table class="info-table">
            <tr>
                <td class="info-label">Employee Code:</td>
                <td><strong>{{ $salarySlip->employee ? $salarySlip->employee->employee_code : 'N/A' }}</strong></td>
                <td class="info-label">Payment Mode:</td>
                <td>{{ $salarySlip->payment_mode ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Employee Name:</td>
                <td><strong>{{ $salarySlip->employee ? $salarySlip->employee->full_name : 'N/A' }}</strong></td>
                <td class="info-label">Bank Name:</td>
                <td>{{ $salarySlip->employee ? $salarySlip->employee->bank_name : '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Designation:</td>
                <td>{{ $salarySlip->employee ? $salarySlip->employee->designation : '-' }}</td>
                <td class="info-label">Account Number:</td>
                <td>{{ $salarySlip->employee ? $salarySlip->employee->account_number : '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Department:</td>
                <td>{{ $salarySlip->employee ? $salarySlip->employee->department : '-' }}</td>
                <td class="info-label">IFSC Code:</td>
                <td>{{ $salarySlip->employee ? $salarySlip->employee->ifsc_code : '-' }}</td>
            </tr>
        </table>

        <!-- Attendance Summary Box -->
        <table class="salary-table" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th class="text-center">Total Days</th>
                    <th class="text-center">Present Days</th>
                    <th class="text-center">Absent Days</th>
                    <th class="text-center">Half Days</th>
                    <th class="text-center">Paid Leaves</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">{{ $salarySlip->total_days }}</td>
                    <td class="text-center" style="font-weight: bold; color: green;">{{ $salarySlip->present_days }}</td>
                    <td class="text-center" style="font-weight: bold; color: red;">{{ $salarySlip->absent_days }}</td>
                    <td class="text-center">{{ $salarySlip->half_days }}</td>
                    <td class="text-center">{{ $salarySlip->paid_leaves }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Earnings and Deductions Table -->
        <table class="salary-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Earnings Details</th>
                    <th style="width: 50%;">Deductions Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 0;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="border: none;">Basic Salary / Rate:</td>
                                <td class="text-right" style="border: none;">₹{{ number_format($salarySlip->basic_salary, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="border: none;">Earned Base Salary:</td>
                                <td class="text-right" style="border: none; font-weight: bold;">₹{{ number_format($salarySlip->earned_salary, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="border: none;">Allowances / Bonus:</td>
                                <td class="text-right" style="border: none;">₹{{ number_format($salarySlip->allowances, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="padding: 0; vertical-align: top;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="border: none;">Other Deductions / Penalties:</td>
                                <td class="text-right" style="border: none; color: red;">₹{{ number_format($salarySlip->deductions, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="border: none;">Advance Recovery Deduction:</td>
                                <td class="text-right" style="border: none; color: red;">₹{{ number_format($salarySlip->advance_deduction ?? 0, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr style="font-weight: bold; background-color: #f9f9f9;">
                    <td>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="border: none;">Total Gross Earnings:</td>
                                <td class="text-right" style="border: none;">₹{{ number_format($salarySlip->earned_salary + $salarySlip->allowances, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="border: none;">Total Deductions:</td>
                                <td class="text-right" style="border: none; color: red;">₹{{ number_format($salarySlip->deductions + ($salarySlip->advance_deduction ?? 0), 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Net Salary Box -->
        <div class="net-box">
            <table style="width: 100%;">
                <tr>
                    <td>
                        <strong>NET SALARY PAYABLE: ₹{{ number_format($salarySlip->net_salary, 2) }}</strong><br>
                        <small>In Words: {{ \App\Http\Controllers\Admin\SalarySlipController::numberToWords($salarySlip->net_salary) }} Only</small>
                    </td>
                    <td class="text-right" style="font-size: 20px; font-weight: bold;">
                        ₹{{ number_format($salarySlip->net_salary, 2) }}
                    </td>
                </tr>
            </table>
        </div>

        @if($salarySlip->remarks)
        <div style="margin-top: 10px; font-size: 12px;">
            <strong>Notes:</strong> {{ $salarySlip->remarks }}
        </div>
        @endif

        <table class="signatures">
            <tr>
                <td style="width: 50%;">
                    <div>___________________________</div>
                    <div style="margin-top: 5px;">Employee Signature</div>
                </td>
                <td style="width: 50%;">
                    <div>___________________________</div>
                    <div style="margin-top: 5px;">Authorized Signatory</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>

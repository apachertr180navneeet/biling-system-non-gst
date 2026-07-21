@extends('admin.layouts.app')

@section('style')
<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}
@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
.dashboard-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    text-decoration: none !important;
    transition: all 0.25s ease-in-out;
    border: 1.5px solid #e2e8f0;
}
.dashboard-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
}
.dashboard-card-info {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.dashboard-card-value {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1a2530;
    margin: 0;
    line-height: 1.25;
}
.dashboard-card-label {
    font-size: 0.95rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}
.dashboard-card-chevron {
    font-size: 1.5rem;
    color: #94a3b8;
    transition: transform 0.2s ease;
}
.dashboard-card:hover .dashboard-card-chevron {
    transform: translateX(4px);
}

/* Card Themes matching the screenshot precisely */
.card-collect {
    background-color: #f4fbf7;
    border-color: #def2e6;
}
.card-collect .dashboard-card-label {
    color: #2e7d32;
}
.card-collect:hover {
    border-color: #c8ebd7;
}

.card-pay {
    background-color: #fff6f6;
    border-color: #fcdcdc;
}
.card-pay .dashboard-card-label {
    color: #c62828;
}
.card-pay:hover {
    border-color: #fbc2c2;
}

.card-stock {
    background-color: #f4f8fd;
    border-color: #dce7f6;
}
.card-stock .dashboard-card-label {
    color: #78909c;
}
.card-stock:hover {
    border-color: #c5d9f1;
}

.card-sale {
    background-color: #f4f8fd;
    border-color: #dce7f6;
}
.card-sale .dashboard-card-label {
    color: #78909c;
}
.card-sale:hover {
    border-color: #c5d9f1;
}
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Dashboard
    </h4>
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-4 p-md-5 text-center">
                    <h2 class="text-primary mb-2">Welcome, {{ Auth::user()->full_name }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid mb-4">
        <!-- To Collect -->
        <a href="{{ route('admin.reports.outstanding-ledger', ['tab' => 'sales']) }}" class="dashboard-card card-collect">
            <div class="dashboard-card-info">
                <h3 class="dashboard-card-value">₹ {{ number_format($toCollect, 2) }}</h3>
                <span class="dashboard-card-label">
                    To Collect 
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <polyline points="19 12 12 19 5 12"></polyline>
                    </svg>
                </span>
            </div>
            <i class="bx bx-chevron-right dashboard-card-chevron"></i>
        </a>

        <!-- To Pay -->
        <a href="{{ route('admin.reports.outstanding-ledger', ['tab' => 'purchases']) }}" class="dashboard-card card-pay">
            <div class="dashboard-card-info">
                <h3 class="dashboard-card-value">₹ {{ number_format($toPay, 2) }}</h3>
                <span class="dashboard-card-label">
                    To Pay 
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="19" x2="12" y2="5"></line>
                        <polyline points="5 12 12 5 19 12"></polyline>
                    </svg>
                </span>
            </div>
            <i class="bx bx-chevron-right dashboard-card-chevron"></i>
        </a>

        <!-- Parts Stock -->
        <a href="{{ route('admin.reports.part-ledger') }}" class="dashboard-card card-stock">
            <div class="dashboard-card-info">
                <h3 class="dashboard-card-value">Parts Stock</h3>
                <span class="dashboard-card-label">Value of items: {{ number_format($stockCountParts) }} items</span>
                @if(($lowStockCount ?? 0) > 0)
                <span class="badge bg-danger mt-1"><i class="bx bx-error me-1"></i>{{ $lowStockCount }} Part(s) Low Stock</span>
                @endif
            </div>
            <i class="bx bx-chevron-right dashboard-card-chevron"></i>
        </a>

        <!-- Vehicle Stock -->
        <a href="{{ route('admin.reports.vehicle-ledger') }}" class="dashboard-card card-sale">
            <div class="dashboard-card-info">
                <h3 class="dashboard-card-value">Vehicle Stock</h3>
                <span class="dashboard-card-label">Value of items: {{ number_format($stockCountVehicles) }} items</span>
                @if(($lowStockVehicleCount ?? 0) > 0)
                <span class="badge bg-danger mt-1"><i class="bx bx-error me-1"></i>{{ $lowStockVehicleCount }} Variant(s) Low Stock</span>
                @endif
            </div>
            <i class="bx bx-chevron-right dashboard-card-chevron"></i>
        </a>
    </div>

    <!-- Sales Report Graph & Latest Transactions -->
    <div class="row mb-4">
        <!-- Sales Report Graph -->
        <div class="col-lg-7 mb-4 mb-lg-0">
            <div class="card h-100 shadow-sm border">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2 fw-semibold"><i class="bx bx-bar-chart-alt-2 text-primary me-2"></i>Sales Report Graph</h5>
                        <small class="text-muted">Monthly sales revenue overview</small>
                    </div>
                </div>
                <div class="card-body px-3 py-2">
                    <div id="salesReportChart" style="min-height: 310px;"></div>
                </div>
            </div>
        </div>

        <!-- Latest Transactions -->
        <div class="col-lg-5">
            <div class="card h-100 shadow-sm border">
                <div class="card-header d-flex align-items-center justify-content-between pb-2 border-bottom">
                    <h5 class="card-title m-0 fw-semibold">Latest Transactions</h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="font-size: 0.75rem; letter-spacing: 0.5px;" class="text-uppercase text-muted">DATE</th>
                                <th style="font-size: 0.75rem; letter-spacing: 0.5px;" class="text-uppercase text-muted">TYPE</th>
                                <th style="font-size: 0.75rem; letter-spacing: 0.5px;" class="text-uppercase text-muted">TXN NO</th>
                                <th style="font-size: 0.75rem; letter-spacing: 0.5px;" class="text-uppercase text-muted">PARTY NAME</th>
                                <th style="font-size: 0.75rem; letter-spacing: 0.5px;" class="text-uppercase text-muted text-end">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($latestTransactions as $txn)
                            <tr>
                                <td class="text-body" style="font-size: 0.85rem;">{{ $txn['date'] }}</td>
                                <td><span class="badge bg-label-primary px-2 py-1" style="font-size: 0.75rem;">{{ $txn['type'] }}</span></td>
                                <td class="fw-semibold" style="font-size: 0.85rem;">
                                    <a href="{{ $txn['url'] }}" class="text-body">{{ $txn['txn_no'] }}</a>
                                </td>
                                <td class="text-uppercase" style="font-size: 0.85rem;">{{ $txn['party_name'] }}</td>
                                <td class="fw-bold text-end" style="font-size: 0.85rem;">₹ {{ number_format($txn['amount']) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No transactions found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-center border-top bg-light py-2 mt-auto">
                    <a href="{{ route('admin.part-sales-invoices.index') }}" class="text-primary fw-semibold small text-decoration-none">
                        See All Transactions <i class="bx bx-right-arrow-alt align-middle"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const salesChartEl = document.querySelector('#salesReportChart');
    if (salesChartEl) {
        const salesChartOptions = {
            series: [
                {
                    name: 'Vehicle Sales',
                    data: @json($salesChartData['vehicle_sales'])
                },
                {
                    name: 'Part Sales',
                    data: @json($salesChartData['part_sales'])
                }
            ],
            chart: {
                height: 310,
                type: 'area',
                toolbar: { show: false },
                fontFamily: 'Public Sans, sans-serif'
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            colors: ['#696cff', '#03c3ec'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [0, 95, 100]
                }
            },
            xaxis: {
                categories: @json($salesChartData['categories']),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#a1acb8', fontSize: '12px' }
                }
            },
            yaxis: {
                labels: {
                    style: { colors: '#a1acb8', fontSize: '12px' },
                    formatter: function (val) {
                        return '₹' + Math.round(val).toLocaleString('en-IN');
                    }
                }
            },
            grid: {
                borderColor: '#eceef1',
                strokeDashArray: 4,
                padding: { top: 0, right: 10, bottom: 0, left: 10 }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return '₹ ' + Math.round(val).toLocaleString('en-IN');
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            }
        };

        const chart = new ApexCharts(salesChartEl, salesChartOptions);
        chart.render();
    }
});
</script>
@endsection


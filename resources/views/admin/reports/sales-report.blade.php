@extends('admin.layouts.master_layout')

@section('pageTitle', 'Sales Report')

@section('content')
<div class="row">
    <!-- Date Range Filter -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                            value="{{ now()->subDays(30)->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                            value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-success w-100" id="exportBtn">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sales Stats Cards -->
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Revenue</h6>
                        <h3 class="mb-0" id="stat_total_revenue">${{ number_format($salesData['total_revenue'] ?? 0, 2) }}</h3>
                    </div>
                    <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Transactions</h6>
                        <h3 class="mb-0" id="stat_total_transactions">{{ $salesData['total_transactions'] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-exchange-alt fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Avg Transaction</h6>
                        <h3 class="mb-0" id="stat_avg_transaction">${{ number_format($salesData['average_transaction'] ?? 0, 2) }}</h3>
                    </div>
                    <i class="fas fa-chart-bar fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Fees</h6>
                        <h3 class="mb-0" id="stat_total_fees">${{ number_format($salesData['total_fees'] ?? 0, 2) }}</h3>
                    </div>
                    <i class="fas fa-percentage fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="col-lg-8 mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Revenue Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Transaction Status Breakdown -->
    <div class="col-lg-4 mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Transaction Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Invoice Stats -->
    <div class="col-12 mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Invoice Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2">
                        <h4 class="text-primary" id="invoice_total">{{ $invoiceData['total'] ?? 0 }}</h4>
                        <p class="text-muted mb-0">Total Invoices</p>
                    </div>
                    <div class="col-md-2">
                        <h4 class="text-warning" id="invoice_pending">{{ $invoiceData['pending'] ?? 0 }}</h4>
                        <p class="text-muted mb-0">Pending</p>
                    </div>
                    <div class="col-md-2">
                        <h4 class="text-success" id="invoice_paid">{{ $invoiceData['paid'] ?? 0 }}</h4>
                        <p class="text-muted mb-0">Paid</p>
                    </div>
                    <div class="col-md-2">
                        <h4 class="text-danger" id="invoice_overdue">{{ $invoiceData['overdue'] ?? 0 }}</h4>
                        <p class="text-muted mb-0">Overdue</p>
                    </div>
                    <div class="col-md-2">
                        <h4 class="text-info" id="invoice_total_amount">${{ number_format($invoiceData['total_amount'] ?? 0, 2) }}</h4>
                        <p class="text-muted mb-0">Total Amount</p>
                    </div>
                    <div class="col-md-2">
                        <h4 class="text-success" id="invoice_paid_amount">${{ number_format($invoiceData['paid_amount'] ?? 0, 2) }}</h4>
                        <p class="text-muted mb-0">Collected</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Properties -->
    <div class="col-lg-6 mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Top Properties by Views</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Property</th>
                                <th>Views</th>
                                <th>Inquiries</th>
                            </tr>
                        </thead>
                        <tbody id="topPropertiesTable">
                            @forelse($topProperties ?? [] as $index => $property)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ Str::limit($property->title ?? 'N/A', 30) }}</td>
                                <td>{{ number_format($property->views_count ?? 0) }}</td>
                                <td>{{ $property->inquiries_count ?? 0 }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="col-lg-6 mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Transactions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="recentTransactionsTable">
                            @forelse($recentTransactions ?? [] as $transaction)
                            <tr>
                                <td><code>{{ Str::limit($transaction->transaction_id, 12) }}</code></td>
                                <td>${{ number_format($transaction->amount, 2) }}</td>
                                <td>
                                    @if($transaction->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($transaction->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @else
                                        <span class="badge bg-danger">{{ ucfirst($transaction->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No transactions found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods Breakdown -->
    <div class="col-12 mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Methods Performance</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($paymentMethodStats ?? [] as $method)
                    <div class="col-md-3 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-{{ $method->icon ?? 'credit-card' }} fa-2x text-primary mb-2"></i>
                                <h6>{{ $method->name ?? 'Unknown' }}</h6>
                                <p class="mb-0"><strong>{{ $method->transactions_count ?? 0 }}</strong> transactions</p>
                                <p class="text-success mb-0">${{ number_format($method->transactions_sum_amount ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center text-muted">
                        No payment method data available
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <div>Loading...</div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    .loading-spinner {
        color: white;
        font-size: 24px;
        text-align: center;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
    const toastr = {
        success: (msg) => Toast.fire({ icon: 'success', title: msg }),
        error: (msg) => Toast.fire({ icon: 'error', title: msg }),
        info: (msg) => Toast.fire({ icon: 'info', title: msg })
    };

    // Chart data from controller
    let chartData = @json($chartData ?? ['labels' => [], 'revenue' => [], 'transactions' => []]);
    let statusData = @json($statusBreakdown ?? ['completed' => 0, 'pending' => 0, 'failed' => 0]);

    // Revenue Chart
    let revenueCtx = document.getElementById('revenueChart').getContext('2d');
    let revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Revenue ($)',
                data: chartData.revenue,
                borderColor: 'rgb(40, 167, 69)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.3
            }, {
                label: 'Transactions',
                data: chartData.transactions,
                borderColor: 'rgb(0, 123, 255)',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.3,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    title: { display: true, text: 'Revenue ($)' }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: 'Transactions' }
                }
            }
        }
    });

    // Status Pie Chart
    let statusCtx = document.getElementById('statusChart').getContext('2d');
    let statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending', 'Failed'],
            datasets: [{
                data: [statusData.completed, statusData.pending, statusData.failed],
                backgroundColor: [
                    'rgb(40, 167, 69)',
                    'rgb(255, 193, 7)',
                    'rgb(220, 53, 69)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Filter form submit
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        loadReportData();
    });

    function loadReportData() {
        let startDate = $('#start_date').val();
        let endDate = $('#end_date').val();

        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: '{{ route("admin.reports.sales.data") }}',
            type: 'GET',
            data: { start_date: startDate, end_date: endDate },
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    updateStats(response.data);
                    updateCharts(response.data);
                    toastr.success('Report updated!');
                }
            },
            error: function() {
                $('#loadingOverlay').hide();
                toastr.error('Failed to load report data.');
            }
        });
    }

    function updateStats(data) {
        $('#stat_total_revenue').text('$' + parseFloat(data.salesData.total_revenue).toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#stat_total_transactions').text(data.salesData.total_transactions);
        $('#stat_avg_transaction').text('$' + parseFloat(data.salesData.average_transaction).toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#stat_total_fees').text('$' + parseFloat(data.salesData.total_fees).toLocaleString('en-US', {minimumFractionDigits: 2}));

        // Invoice stats
        $('#invoice_total').text(data.invoiceData.total);
        $('#invoice_pending').text(data.invoiceData.pending);
        $('#invoice_paid').text(data.invoiceData.paid);
        $('#invoice_overdue').text(data.invoiceData.overdue);
        $('#invoice_total_amount').text('$' + parseFloat(data.invoiceData.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#invoice_paid_amount').text('$' + parseFloat(data.invoiceData.paid_amount).toLocaleString('en-US', {minimumFractionDigits: 2}));
    }

    function updateCharts(data) {
        revenueChart.data.labels = data.chartData.labels;
        revenueChart.data.datasets[0].data = data.chartData.revenue;
        revenueChart.data.datasets[1].data = data.chartData.transactions;
        revenueChart.update();

        statusChart.data.datasets[0].data = [
            data.statusBreakdown.completed,
            data.statusBreakdown.pending,
            data.statusBreakdown.failed
        ];
        statusChart.update();
    }

    // Export
    $('#exportBtn').on('click', function() {
        let startDate = $('#start_date').val();
        let endDate = $('#end_date').val();
        window.location.href = '{{ route("admin.reports.sales.export") }}?start_date=' + startDate + '&end_date=' + endDate;
    });
});
</script>
@endpush

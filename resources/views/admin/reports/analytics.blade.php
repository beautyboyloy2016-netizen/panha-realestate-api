@extends('admin.layouts.master_layout')

@section('pageTitle', 'Analytics Dashboard')

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
                        <button type="button" class="btn btn-info w-100" id="refreshBtn">
                            <i class="fas fa-sync-alt"></i> Refresh
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

    <!-- User Analytics -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-users"></i> User Analytics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 text-center border-end">
                        <h3 class="text-primary" id="user_total">{{ $userAnalytics['total'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Total Users</p>
                    </div>
                    <div class="col-md-2 text-center border-end">
                        <h3 class="text-success" id="user_active">{{ $userAnalytics['active'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Active</p>
                    </div>
                    <div class="col-md-2 text-center border-end">
                        <h3 class="text-info" id="user_new_this_month">{{ $userAnalytics['new_this_month'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">New This Month</p>
                    </div>
                    <div class="col-md-2 text-center border-end">
                        <h3 class="text-warning" id="user_with_properties">{{ $userAnalytics['with_properties'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">With Properties</p>
                    </div>
                    <div class="col-md-2 text-center border-end">
                        <h3 class="text-danger" id="user_verified">{{ $userAnalytics['verified'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Verified</p>
                    </div>
                    <div class="col-md-2 text-center">
                        <h3 class="text-secondary" id="user_growth">{{ $userAnalytics['growth_rate'] ?? 0 }}%</h3>
                        <p class="text-muted mb-0">Growth Rate</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Registration Chart -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">User Registration Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="userRegistrationChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- User Roles Distribution -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Users by Role</h5>
            </div>
            <div class="card-body">
                <canvas id="userRolesChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Property Analytics -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-building"></i> Property Analytics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 text-center border-end">
                        <h3 class="text-success" id="property_total">{{ $propertyAnalytics['total'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Total Properties</p>
                    </div>
                    <div class="col-md-2 text-center border-end">
                        <h3 class="text-primary" id="property_available">{{ $propertyAnalytics['available'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Available</p>
                    </div>
                    <div class="col-md-2 text-center border-end">
                        <h3 class="text-info" id="property_for_sale">{{ $propertyAnalytics['for_sale'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">For Sale</p>
                    </div>
                    <div class="col-md-2 text-center border-end">
                        <h3 class="text-warning" id="property_for_rent">{{ $propertyAnalytics['for_rent'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">For Rent</p>
                    </div>
                    <div class="col-md-2 text-center border-end">
                        <h3 class="text-danger" id="property_featured">{{ $propertyAnalytics['featured'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Featured</p>
                    </div>
                    <div class="col-md-2 text-center">
                        <h3 class="text-dark" id="property_total_value">${{ number_format(($propertyAnalytics['total_value'] ?? 0) / 1000000, 1) }}M</h3>
                        <p class="text-muted mb-0">Total Value</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Types Chart -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Properties by Type</h5>
            </div>
            <div class="card-body">
                <canvas id="propertyTypesChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Property Locations Chart -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Properties by City</h5>
            </div>
            <div class="card-body">
                <canvas id="propertyCitiesChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Views Analytics -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-eye"></i> Property Views Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="viewsChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Views Stats -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Views Summary</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <h2 class="text-info" id="views_total">{{ number_format($viewsAnalytics['total_views'] ?? 0) }}</h2>
                    <p class="text-muted">Total Views</p>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-success" id="views_today">{{ $viewsAnalytics['today'] ?? 0 }}</h4>
                        <p class="text-muted mb-0">Today</p>
                    </div>
                    <div class="col-6">
                        <h4 class="text-primary" id="views_this_week">{{ $viewsAnalytics['this_week'] ?? 0 }}</h4>
                        <p class="text-muted mb-0">This Week</p>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-warning" id="views_this_month">{{ $viewsAnalytics['this_month'] ?? 0 }}</h4>
                        <p class="text-muted mb-0">This Month</p>
                    </div>
                    <div class="col-6">
                        <h4 class="text-secondary" id="views_average">{{ $viewsAnalytics['average_per_property'] ?? 0 }}</h4>
                        <p class="text-muted mb-0">Avg/Property</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inquiry Analytics -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-envelope"></i> Inquiry Analytics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 text-center border-end">
                        <h3 class="text-warning" id="inquiry_total">{{ $inquiryAnalytics['total'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Total Inquiries</p>
                    </div>
                    <div class="col-md-2 text-center border-end">
                        <h3 class="text-info" id="inquiry_pending">{{ $inquiryAnalytics['pending'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Pending</p>
                    </div>
                    <div class="col-md-2 text-center border-end">
                        <h3 class="text-primary" id="inquiry_in_progress">{{ $inquiryAnalytics['in_progress'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">In Progress</p>
                    </div>
                    <div class="col-md-2 text-center border-end">
                        <h3 class="text-success" id="inquiry_resolved">{{ $inquiryAnalytics['resolved'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Resolved</p>
                    </div>
                    <div class="col-md-2 text-center border-end">
                        <h3 class="text-success" id="inquiry_this_month">{{ $inquiryAnalytics['this_month'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">This Month</p>
                    </div>
                    <div class="col-md-2 text-center">
                        <h3 class="text-secondary" id="inquiry_response_rate">{{ number_format($inquiryAnalytics['response_rate'] ?? 0, 1) }}%</h3>
                        <p class="text-muted mb-0">Response Rate</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inquiry Trend Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Inquiry Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="inquiryTrendChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Inquiry Status Distribution -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Inquiry Status</h5>
            </div>
            <div class="card-body">
                <canvas id="inquiryStatusChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Performing Properties -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Most Viewed Properties</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Property</th>
                                <th>Type</th>
                                <th>Views</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topViewedProperties ?? [] as $index => $property)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ Str::limit($property->title ?? 'N/A', 25) }}</td>
                                <td><span class="badge bg-info">{{ $property->property_type ?? 'N/A' }}</span></td>
                                <td><strong>{{ number_format($property->views_count ?? 0) }}</strong></td>
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

    <!-- Most Inquired Properties -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Most Inquired Properties</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Property</th>
                                <th>Price</th>
                                <th>Inquiries</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topInquiredProperties ?? [] as $index => $property)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ Str::limit($property->title ?? 'N/A', 25) }}</td>
                                <td>${{ number_format($property->price ?? 0) }}</td>
                                <td><strong>{{ $property->inquiries_count ?? 0 }}</strong></td>
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
    let chartData = @json($chartData ?? []);

    // User Registration Chart
    let userRegCtx = document.getElementById('userRegistrationChart').getContext('2d');
    let userRegChart = new Chart(userRegCtx, {
        type: 'line',
        data: {
            labels: chartData.userRegistrations?.labels ?? [],
            datasets: [{
                label: 'New Users',
                data: chartData.userRegistrations?.data ?? [],
                borderColor: 'rgb(0, 123, 255)',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    // User Roles Chart
    let userRolesCtx = document.getElementById('userRolesChart').getContext('2d');
    let userRolesChart = new Chart(userRolesCtx, {
        type: 'pie',
        data: {
            labels: chartData.userRoles?.labels ?? [],
            datasets: [{
                data: chartData.userRoles?.data ?? [],
                backgroundColor: [
                    'rgb(0, 123, 255)',
                    'rgb(40, 167, 69)',
                    'rgb(255, 193, 7)',
                    'rgb(220, 53, 69)',
                    'rgb(108, 117, 125)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Property Types Chart
    let propTypesCtx = document.getElementById('propertyTypesChart').getContext('2d');
    let propTypesChart = new Chart(propTypesCtx, {
        type: 'bar',
        data: {
            labels: chartData.propertyTypes?.labels ?? [],
            datasets: [{
                label: 'Properties',
                data: chartData.propertyTypes?.data ?? [],
                backgroundColor: 'rgb(40, 167, 69)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Property Cities Chart
    let propCitiesCtx = document.getElementById('propertyCitiesChart').getContext('2d');
    let propCitiesChart = new Chart(propCitiesCtx, {
        type: 'doughnut',
        data: {
            labels: chartData.propertyCities?.labels ?? [],
            datasets: [{
                data: chartData.propertyCities?.data ?? [],
                backgroundColor: [
                    'rgb(0, 123, 255)',
                    'rgb(40, 167, 69)',
                    'rgb(255, 193, 7)',
                    'rgb(220, 53, 69)',
                    'rgb(23, 162, 184)',
                    'rgb(108, 117, 125)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Views Chart
    let viewsCtx = document.getElementById('viewsChart').getContext('2d');
    let viewsChart = new Chart(viewsCtx, {
        type: 'line',
        data: {
            labels: chartData.views?.labels ?? [],
            datasets: [{
                label: 'Property Views',
                data: chartData.views?.data ?? [],
                borderColor: 'rgb(23, 162, 184)',
                backgroundColor: 'rgba(23, 162, 184, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Inquiry Trend Chart
    let inquiryTrendCtx = document.getElementById('inquiryTrendChart').getContext('2d');
    let inquiryTrendChart = new Chart(inquiryTrendCtx, {
        type: 'bar',
        data: {
            labels: chartData.inquiryTrend?.labels ?? [],
            datasets: [{
                label: 'Inquiries',
                data: chartData.inquiryTrend?.data ?? [],
                backgroundColor: 'rgb(255, 193, 7)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Inquiry Status Chart
    let inquiryStatusCtx = document.getElementById('inquiryStatusChart').getContext('2d');
    let inquiryStatusChart = new Chart(inquiryStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'In Progress', 'Resolved', 'Closed'],
            datasets: [{
                data: chartData.inquiryStatus ?? [0, 0, 0, 0],
                backgroundColor: [
                    'rgb(255, 193, 7)',
                    'rgb(0, 123, 255)',
                    'rgb(40, 167, 69)',
                    'rgb(108, 117, 125)'
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
        loadAnalyticsData();
    });

    $('#refreshBtn').on('click', function() {
        loadAnalyticsData();
    });

    function loadAnalyticsData() {
        let startDate = $('#start_date').val();
        let endDate = $('#end_date').val();

        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: '{{ route("admin.reports.analytics.data") }}',
            type: 'GET',
            data: { start_date: startDate, end_date: endDate },
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    updateAnalytics(response.data);
                    toastr.success('Analytics updated!');
                }
            },
            error: function() {
                $('#loadingOverlay').hide();
                toastr.error('Failed to load analytics data.');
            }
        });
    }

    function updateAnalytics(data) {
        // Update user analytics
        if (data.userAnalytics) {
            $('#user_total').text(data.userAnalytics.total);
            $('#user_active').text(data.userAnalytics.active);
            $('#user_new_this_month').text(data.userAnalytics.new_this_month);
            $('#user_with_properties').text(data.userAnalytics.with_properties);
            $('#user_verified').text(data.userAnalytics.verified);
            $('#user_growth').text(data.userAnalytics.growth_rate + '%');
        }

        // Update property analytics
        if (data.propertyAnalytics) {
            $('#property_total').text(data.propertyAnalytics.total);
            $('#property_available').text(data.propertyAnalytics.available);
            $('#property_for_sale').text(data.propertyAnalytics.for_sale);
            $('#property_for_rent').text(data.propertyAnalytics.for_rent);
            $('#property_featured').text(data.propertyAnalytics.featured);
            $('#property_total_value').text('$' + (data.propertyAnalytics.total_value / 1000000).toFixed(1) + 'M');
        }

        // Update views analytics
        if (data.viewsAnalytics) {
            $('#views_total').text(parseInt(data.viewsAnalytics.total_views).toLocaleString());
            $('#views_today').text(data.viewsAnalytics.today);
            $('#views_this_week').text(data.viewsAnalytics.this_week);
            $('#views_this_month').text(data.viewsAnalytics.this_month);
            $('#views_average').text(data.viewsAnalytics.average_per_property);
        }

        // Update inquiry analytics
        if (data.inquiryAnalytics) {
            $('#inquiry_total').text(data.inquiryAnalytics.total);
            $('#inquiry_pending').text(data.inquiryAnalytics.pending);
            $('#inquiry_in_progress').text(data.inquiryAnalytics.in_progress);
            $('#inquiry_resolved').text(data.inquiryAnalytics.resolved);
            $('#inquiry_this_month').text(data.inquiryAnalytics.this_month);
            $('#inquiry_response_rate').text(parseFloat(data.inquiryAnalytics.response_rate).toFixed(1) + '%');
        }

        // Update charts if data provided
        if (data.chartData) {
            if (data.chartData.userRegistrations) {
                userRegChart.data.labels = data.chartData.userRegistrations.labels;
                userRegChart.data.datasets[0].data = data.chartData.userRegistrations.data;
                userRegChart.update();
            }
            if (data.chartData.views) {
                viewsChart.data.labels = data.chartData.views.labels;
                viewsChart.data.datasets[0].data = data.chartData.views.data;
                viewsChart.update();
            }
            if (data.chartData.inquiryTrend) {
                inquiryTrendChart.data.labels = data.chartData.inquiryTrend.labels;
                inquiryTrendChart.data.datasets[0].data = data.chartData.inquiryTrend.data;
                inquiryTrendChart.update();
            }
        }
    }
});
</script>
@endpush

@extends('admin.layouts.master_layout')

@section('pageTitle', 'Reports')

@section('content')
<div class="row">
    <!-- Stats Overview -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Total Revenue</h6>
                                <h3 class="mb-0">${{ number_format($stats['total_revenue'], 2) }}</h3>
                            </div>
                            <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                        </div>
                        <small>Monthly: ${{ number_format($stats['monthly_revenue'], 2) }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Properties</h6>
                                <h3 class="mb-0">{{ $stats['total_properties'] }}</h3>
                            </div>
                            <i class="fas fa-building fa-2x opacity-50"></i>
                        </div>
                        <small>Active: {{ $stats['active_properties'] }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Users</h6>
                                <h3 class="mb-0">{{ $stats['total_users'] }}</h3>
                            </div>
                            <i class="fas fa-users fa-2x opacity-50"></i>
                        </div>
                        <small>New this month: {{ $stats['new_users_this_month'] }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Inquiries</h6>
                                <h3 class="mb-0">{{ $stats['total_inquiries'] }}</h3>
                            </div>
                            <i class="fas fa-envelope fa-2x opacity-50"></i>
                        </div>
                        <small>Pending: {{ $stats['pending_inquiries'] }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Reports</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <a href="{{ route('admin.reports.sales') }}" class="card bg-light text-decoration-none h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
                                <h5>Sales Report</h5>
                                <p class="text-muted mb-0">Revenue, transactions, and invoice analytics</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('admin.reports.analytics') }}" class="card bg-light text-decoration-none h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-pie fa-3x text-info mb-3"></i>
                                <h5>Analytics</h5>
                                <p class="text-muted mb-0">User, property, and engagement metrics</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="#" class="card bg-light text-decoration-none h-100" data-bs-toggle="modal" data-bs-target="#itemModal">
                            <div class="card-body text-center">
                                <i class="fas fa-plus-circle fa-3x text-primary mb-3"></i>
                                <h5>Create Custom Report</h5>
                                <p class="text-muted mb-0">Build and save custom reports</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Saved Reports Table -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Saved Reports</h4>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal">
                        <i class="fas fa-plus"></i> Create Report
                    </button>
                    <button type="button" class="btn btn-info" id="refreshTableBtn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="dataTable">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Name</th>
                                <th width="12%">Type</th>
                                <th width="12%">Creator</th>
                                <th width="12%">Schedule</th>
                                <th width="10%">Status</th>
                                <th width="12%">Last Run</th>
                                <th width="12%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="itemForm" method="POST">
            @csrf
            <input type="hidden" id="itemId" name="item_id">
            <input type="hidden" id="formMethod" name="_method" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Create Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Report Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="type" name="type" required>
                                    <option value="sales">Sales</option>
                                    <option value="analytics">Analytics</option>
                                    <option value="property">Property</option>
                                    <option value="user">User</option>
                                    <option value="transaction">Transaction</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Schedule (Optional)</label>
                                <select class="form-control" id="schedule" name="schedule">
                                    <option value="">Manual Only</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email Recipients</label>
                                <input type="text" class="form-control" id="email_recipients" name="email_recipients" placeholder="email1@example.com, email2@example.com">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_public" name="is_public" value="1">
                                <label class="form-check-label" for="is_public">Public Report</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Save Report
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <div>Processing...</div>
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
        warning: (msg) => Toast.fire({ icon: 'warning', title: msg }),
        info: (msg) => Toast.fire({ icon: 'info', title: msg })
    };

    // Initialize DataTable
    let table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.reports.index") }}',
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name_display', name: 'name' },
            { data: 'type_badge', name: 'type' },
            { data: 'creator_name', orderable: false },
            { data: 'schedule_display', name: 'schedule' },
            { data: 'status', name: 'is_active' },
            { data: 'last_run', orderable: false },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        pageLength: 10,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
        }
    });

    // Refresh
    $('#refreshTableBtn').on('click', function() {
        table.ajax.reload();
        toastr.info('Table refreshed!');
    });

    // Reset modal
    $('#itemModal').on('hidden.bs.modal', function() {
        $('#itemForm')[0].reset();
        $('#itemId').val('');
        $('#formMethod').val('POST');
        $('#modalTitle').text('Create Report');
        $('#is_active').prop('checked', true);
    });

    // Submit form
    $('#itemForm').on('submit', function(e) {
        e.preventDefault();

        let itemId = $('#itemId').val();
        let url = itemId
            ? '{{ route("admin.reports.index") }}/' + itemId
            : '{{ route("admin.reports.store") }}';
        let method = itemId ? 'PUT' : 'POST';

        let formData = $(this).serialize() + '&_method=' + method;

        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    $('#itemModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                $('#loadingOverlay').hide();
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    Object.keys(errors).forEach(key => toastr.error(errors[key][0]));
                } else {
                    toastr.error(xhr.responseJSON?.message || 'An error occurred.');
                }
            }
        });
    });

    // Edit
    $(document).on('click', '.edit-item', function() {
        let id = $(this).data('id');
        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: '{{ route("admin.reports.index") }}/' + id + '/edit',
            type: 'GET',
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    let data = response.data;
                    $('#itemId').val(data.id);
                    $('#formMethod').val('PUT');
                    $('#modalTitle').text('Edit Report');
                    $('#name').val(data.name);
                    $('#type').val(data.type);
                    $('#description').val(data.description);
                    $('#schedule').val(data.schedule);
                    $('#email_recipients').val(data.email_recipients);
                    $('#is_public').prop('checked', data.is_public);
                    $('#is_active').prop('checked', data.is_active);
                    $('#itemModal').modal('show');
                }
            },
            error: function() {
                $('#loadingOverlay').hide();
                toastr.error('Failed to load data.');
            }
        });
    });

    // View
    $(document).on('click', '.view-item', function() {
        let id = $(this).data('id');
        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: '{{ route("admin.reports.index") }}/' + id,
            type: 'GET',
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    let data = response.data;
                    let html = `
                        <h5>${data.name}</h5>
                        <p>${data.description || 'No description'}</p>
                        <hr>
                        <p><strong>Type:</strong> ${data.type}</p>
                        <p><strong>Schedule:</strong> ${data.schedule || 'Manual'}</p>
                        <p><strong>Created by:</strong> ${data.creator ? data.creator.name : 'System'}</p>
                        <p><strong>Last Run:</strong> ${data.last_generated_at || 'Never'}</p>
                    `;
                    $('#viewModalContent').html(html);
                    $('#viewModal').modal('show');
                }
            },
            error: function() {
                $('#loadingOverlay').hide();
                toastr.error('Failed to load data.');
            }
        });
    });

    // Run report
    $(document).on('click', '.run-report', function() {
        let id = $(this).data('id');
        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: '{{ route("admin.reports.index") }}/' + id + '/run',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    table.ajax.reload();
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                $('#loadingOverlay').hide();
                toastr.error(xhr.responseJSON?.message || 'Failed to run report.');
            }
        });
    });

    // Delete
    $(document).on('click', '.delete-item', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This report will be deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loadingOverlay').css('display', 'flex');

                $.ajax({
                    url: '{{ route("admin.reports.index") }}/' + id,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(response) {
                        $('#loadingOverlay').hide();
                        if (response.success) {
                            table.ajax.reload();
                            toastr.success(response.message);
                        }
                    },
                    error: function(xhr) {
                        $('#loadingOverlay').hide();
                        toastr.error(xhr.responseJSON?.message || 'Failed to delete.');
                    }
                });
            }
        });
    });
});
</script>
@endpush

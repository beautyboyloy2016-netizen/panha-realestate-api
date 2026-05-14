@extends('admin.layouts.master_layout')

@section('pageTitle', 'Payment Methods')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Payment Methods</h4>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal">
                        <i class="fas fa-plus"></i> Add Payment Method
                    </button>
                    <button type="button" class="btn btn-info" id="refreshTableBtn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="statusFilter">Status</label>
                        <select class="form-control" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="typeFilter">Type</label>
                        <select class="form-control" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                            <option value="crypto">Crypto</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>&nbsp;</label>
                        <div class="d-flex">
                            <button type="button" class="btn btn-secondary" id="clearFiltersBtn">
                                <i class="fas fa-times"></i> Clear Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- DataTable -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="dataTable">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="5%">Icon</th>
                                <th width="20%">Name</th>
                                <th width="12%">Type</th>
                                <th width="15%">Processing Fee</th>
                                <th width="10%">Status</th>
                                <th width="10%">Transactions</th>
                                <th width="15%">Actions</th>
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
                    <h5 class="modal-title" id="modalTitle">Add Payment Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" placeholder="e.g., credit_card" required>
                                <small class="text-muted">Unique identifier (lowercase, underscores)</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="type" name="type" required>
                                    <option value="online">Online</option>
                                    <option value="offline">Offline</option>
                                    <option value="crypto">Cryptocurrency</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Icon (Font Awesome)</label>
                                <input type="text" class="form-control" id="icon" name="icon" placeholder="e.g., fas fa-credit-card">
                                <small class="text-muted">Font Awesome class</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Processing Fee</label>
                                <input type="number" class="form-control" id="processing_fee" name="processing_fee" step="0.01" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Fee Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="processing_fee_type" name="processing_fee_type" required>
                                    <option value="fixed">Fixed Amount</option>
                                    <option value="percentage">Percentage</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" min="0" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Description of this payment method..."></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Save
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
                <h5 class="modal-title">Payment Method Details</h5>
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
    // Toastr-like notifications using SweetAlert2 Toast
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
            url: '{{ route("admin.payment-methods.index") }}',
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'icon_display', orderable: false, searchable: false },
            { data: 'name_display', name: 'name' },
            { data: 'type_badge', name: 'type_badge' },
            { data: 'fee_display', name: 'processing_fee' },
            { data: 'status', name: 'status' },
            { data: 'transactions_count', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[2, 'asc']],
        pageLength: 10,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
        }
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        table.column(5).search($(this).val()).draw();
    });

    // Type filter
    $('#typeFilter').on('change', function() {
        table.column(3).search($(this).val()).draw();
    });

    // Clear filters
    $('#clearFiltersBtn').on('click', function() {
        $('#statusFilter').val('');
        $('#typeFilter').val('');
        table.search('').columns().search('').draw();
    });

    // Refresh table
    $('#refreshTableBtn').on('click', function() {
        table.ajax.reload();
        toastr.info('Table refreshed!');
    });

    // Reset modal on close
    $('#itemModal').on('hidden.bs.modal', function() {
        resetForm();
    });

    function resetForm() {
        $('#itemForm')[0].reset();
        $('#itemId').val('');
        $('#formMethod').val('POST');
        $('#modalTitle').text('Add Payment Method');
        $('#is_active').prop('checked', true);
    }

    // Auto-generate code from name
    $('#name').on('blur', function() {
        if (!$('#code').val()) {
            let code = $(this).val().toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '_')
                .replace(/-+/g, '_');
            $('#code').val(code);
        }
    });

    // Submit form
    $('#itemForm').on('submit', function(e) {
        e.preventDefault();

        let itemId = $('#itemId').val();
        let url = itemId
            ? '{{ route("admin.payment-methods.index") }}/' + itemId
            : '{{ route("admin.payment-methods.store") }}';
        let method = itemId ? 'PUT' : 'POST';

        let formData = $(this).serialize() + '&_method=' + method;

        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
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
                    Object.keys(errors).forEach(function(key) {
                        toastr.error(errors[key][0]);
                    });
                } else {
                    toastr.error(xhr.responseJSON?.message || 'An error occurred. Please try again.');
                }
            }
        });
    });

    // Edit item
    $(document).on('click', '.edit-item', function() {
        let id = $(this).data('id');
        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: '{{ route("admin.payment-methods.index") }}/' + id + '/edit',
            type: 'GET',
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    let data = response.data;
                    $('#itemId').val(data.id);
                    $('#formMethod').val('PUT');
                    $('#modalTitle').text('Edit Payment Method');
                    $('#name').val(data.name);
                    $('#code').val(data.code);
                    $('#type').val(data.type);
                    $('#icon').val(data.icon);
                    $('#processing_fee').val(data.processing_fee);
                    $('#processing_fee_type').val(data.processing_fee_type);
                    $('#sort_order').val(data.sort_order);
                    $('#description').val(data.description);
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

    // View item
    $(document).on('click', '.view-item', function() {
        let id = $(this).data('id');
        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: '{{ route("admin.payment-methods.index") }}/' + id,
            type: 'GET',
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    let data = response.data;
                    let status = data.is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';

                    let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="${data.icon || 'fas fa-credit-card'}"></i> ${data.name}</h5>
                                <p class="text-muted">${data.description || 'No description'}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                ${status}
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Code:</strong><br>
                                <code>${data.code}</code>
                            </div>
                            <div class="col-md-4">
                                <strong>Type:</strong><br>
                                ${data.type.charAt(0).toUpperCase() + data.type.slice(1)}
                            </div>
                            <div class="col-md-4">
                                <strong>Processing Fee:</strong><br>
                                ${data.processing_fee} ${data.processing_fee_type === 'percentage' ? '%' : 'USD'}
                            </div>
                        </div>
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

    // Delete item
    $(document).on('click', '.delete-item', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This payment method will be deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loadingOverlay').css('display', 'flex');

                $.ajax({
                    url: '{{ route("admin.payment-methods.index") }}/' + id,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
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

@extends('admin.layouts.master_layout')

@section('pageTitle', 'Invoices')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Invoices</h4>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal">
                        <i class="fas fa-plus"></i> Create Invoice
                    </button>
                    <button type="button" class="btn btn-info" id="refreshTableBtn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body py-2">
                                <h6 class="mb-0">Total Invoices</h6>
                                <h3 class="mb-0" id="statTotal">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body py-2">
                                <h6 class="mb-0">Paid</h6>
                                <h3 class="mb-0" id="statPaid">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body py-2">
                                <h6 class="mb-0">Pending</h6>
                                <h3 class="mb-0" id="statPending">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body py-2">
                                <h6 class="mb-0">Overdue</h6>
                                <h3 class="mb-0" id="statOverdue">0</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="statusFilter">Status</label>
                        <select class="form-control" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-9">
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
                                <th width="15%">Invoice #</th>
                                <th width="20%">Customer</th>
                                <th width="12%">Amount</th>
                                <th width="10%">Status</th>
                                <th width="12%">Due Date</th>
                                <th width="10%">Paid</th>
                                <th width="16%">Actions</th>
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
    <div class="modal-dialog modal-xl">
        <form id="itemForm" method="POST">
            @csrf
            <input type="hidden" id="itemId" name="item_id">
            <input type="hidden" id="formMethod" name="_method" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Create Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">Customer Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Select User (Optional)</label>
                                        <select class="form-control" id="user_id" name="user_id">
                                            <option value="">Guest Customer</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" data-email="{{ $user->email }}" data-name="{{ $user->name }}">{{ $user->name }} ({{ $user->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Customer Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Customer Phone</label>
                                        <input type="text" class="form-control" id="customer_phone" name="customer_phone">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Customer Address</label>
                                        <textarea class="form-control" id="customer_address" name="customer_address" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Details -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0">Invoice Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="issue_date" name="issue_date" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Due Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="due_date" name="due_date" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Currency <span class="text-danger">*</span></label>
                                                <select class="form-control" id="currency" name="currency" required>
                                                    <option value="USD">USD</option>
                                                    <option value="EUR">EUR</option>
                                                    <option value="KHR">KHR</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                                <select class="form-control" id="status" name="status" required>
                                                    <option value="draft">Draft</option>
                                                    <option value="sent">Sent</option>
                                                    <option value="paid">Paid</option>
                                                    <option value="cancelled">Cancelled</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Subtotal <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="subtotal" name="subtotal" step="0.01" min="0" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Tax Rate (%)</label>
                                                <input type="number" class="form-control" id="tax_rate" name="tax_rate" step="0.01" min="0" max="100" value="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Discount</label>
                                                <input type="number" class="form-control" id="discount_amount" name="discount_amount" step="0.01" min="0" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Total</label>
                                                <input type="text" class="form-control" id="total_display" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Notes visible to customer..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Terms & Conditions</label>
                                <textarea class="form-control" id="terms" name="terms" rows="3" placeholder="Payment terms..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Save Invoice
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
                <h5 class="modal-title">Invoice Details</h5>
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
    #itemModal {
        overflow-y: auto !important;
    }
    #itemModal .modal-dialog {
        margin: 1.75rem auto;
        max-height: none;
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

    // Set default dates
    const today = new Date().toISOString().split('T')[0];
    const dueDate = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    $('#issue_date').val(today);
    $('#due_date').val(dueDate);

    // Calculate total on input change
    function calculateTotal() {
        let subtotal = parseFloat($('#subtotal').val()) || 0;
        let taxRate = parseFloat($('#tax_rate').val()) || 0;
        let discount = parseFloat($('#discount_amount').val()) || 0;
        let tax = subtotal * (taxRate / 100);
        let total = subtotal + tax - discount;
        $('#total_display').val($('#currency').val() + ' ' + total.toFixed(2));
    }

    $('#subtotal, #tax_rate, #discount_amount, #currency').on('input change', calculateTotal);

    // Auto-fill customer info from user selection
    $('#user_id').on('change', function() {
        let selected = $(this).find('option:selected');
        if (selected.val()) {
            $('#customer_name').val(selected.data('name'));
            $('#customer_email').val(selected.data('email'));
        }
    });

    // Initialize DataTable
    let table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.invoices.index") }}',
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'invoice_number_display', name: 'invoice_number' },
            { data: 'customer', name: 'customer_name' },
            { data: 'amount', name: 'total_amount' },
            { data: 'status', name: 'status' },
            { data: 'due_date_display', name: 'due_date' },
            { data: 'paid_amount', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
        }
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        table.column(4).search($(this).val()).draw();
    });

    // Clear filters
    $('#clearFiltersBtn').on('click', function() {
        $('#statusFilter').val('');
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
        $('#modalTitle').text('Create Invoice');
        $('#issue_date').val(today);
        $('#due_date').val(dueDate);
        $('#total_display').val('');
    }

    // Submit form
    $('#itemForm').on('submit', function(e) {
        e.preventDefault();

        let itemId = $('#itemId').val();
        let url = itemId
            ? '{{ route("admin.invoices.index") }}/' + itemId
            : '{{ route("admin.invoices.store") }}';
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
            url: '{{ route("admin.invoices.index") }}/' + id + '/edit',
            type: 'GET',
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    let data = response.data;
                    $('#itemId').val(data.id);
                    $('#formMethod').val('PUT');
                    $('#modalTitle').text('Edit Invoice');
                    $('#user_id').val(data.user_id);
                    $('#customer_name').val(data.customer_name);
                    $('#customer_email').val(data.customer_email);
                    $('#customer_phone').val(data.customer_phone);
                    $('#customer_address').val(data.customer_address);
                    $('#issue_date').val(data.issue_date);
                    $('#due_date').val(data.due_date);
                    $('#currency').val(data.currency);
                    $('#status').val(data.status);
                    $('#subtotal').val(data.subtotal);
                    $('#tax_rate').val(data.tax_rate);
                    $('#discount_amount').val(data.discount_amount);
                    $('#notes').val(data.notes);
                    $('#terms').val(data.terms);
                    calculateTotal();

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
            url: '{{ route("admin.invoices.index") }}/' + id,
            type: 'GET',
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    let data = response.data;
                    let statusBadge = {
                        'draft': '<span class="badge bg-secondary">Draft</span>',
                        'sent': '<span class="badge bg-info">Sent</span>',
                        'paid': '<span class="badge bg-success">Paid</span>',
                        'overdue': '<span class="badge bg-danger">Overdue</span>',
                        'cancelled': '<span class="badge bg-dark">Cancelled</span>'
                    };

                    let html = `
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h4>${data.invoice_number}</h4>
                                ${statusBadge[data.status] || statusBadge['draft']}
                            </div>
                            <div class="col-md-6 text-end">
                                <h3 class="text-primary">${data.currency} ${parseFloat(data.total_amount).toFixed(2)}</h3>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Bill To:</h6>
                                <strong>${data.customer_name}</strong><br>
                                ${data.customer_email}<br>
                                ${data.customer_phone || ''}<br>
                                ${data.customer_address || ''}
                            </div>
                            <div class="col-md-6 text-end">
                                <p><strong>Issue Date:</strong> ${data.issue_date}</p>
                                <p><strong>Due Date:</strong> ${data.due_date}</p>
                                ${data.paid_date ? '<p><strong>Paid Date:</strong> ' + data.paid_date + '</p>' : ''}
                            </div>
                        </div>
                        <hr>
                        <table class="table">
                            <tr><td>Subtotal</td><td class="text-end">${data.currency} ${parseFloat(data.subtotal).toFixed(2)}</td></tr>
                            <tr><td>Tax (${data.tax_rate}%)</td><td class="text-end">${data.currency} ${parseFloat(data.tax_amount).toFixed(2)}</td></tr>
                            <tr><td>Discount</td><td class="text-end">-${data.currency} ${parseFloat(data.discount_amount).toFixed(2)}</td></tr>
                            <tr class="fw-bold"><td>Total</td><td class="text-end">${data.currency} ${parseFloat(data.total_amount).toFixed(2)}</td></tr>
                        </table>
                        ${data.notes ? '<hr><p><strong>Notes:</strong> ' + data.notes + '</p>' : ''}
                        ${data.terms ? '<p><strong>Terms:</strong> ' + data.terms + '</p>' : ''}
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

    // Mark as paid
    $(document).on('click', '.mark-paid', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Mark as Paid?',
            text: "This will mark the invoice as paid.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, mark as paid!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loadingOverlay').css('display', 'flex');

                $.ajax({
                    url: '{{ route("admin.invoices.index") }}/' + id + '/mark-paid',
                    type: 'POST',
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
                        toastr.error(xhr.responseJSON?.message || 'Failed to update.');
                    }
                });
            }
        });
    });

    // Delete item
    $(document).on('click', '.delete-item', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This invoice will be deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loadingOverlay').css('display', 'flex');

                $.ajax({
                    url: '{{ route("admin.invoices.index") }}/' + id,
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

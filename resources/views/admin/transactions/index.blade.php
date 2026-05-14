@extends('admin.layouts.master_layout')

@section('pageTitle', 'Transactions')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Transactions</h4>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal">
                        <i class="fas fa-plus"></i> Record Transaction
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
                        <div class="card bg-success text-white">
                            <div class="card-body py-2">
                                <h6 class="mb-0"><i class="fas fa-check-circle"></i> Completed</h6>
                                <h3 class="mb-0" id="statCompleted">$0.00</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body py-2">
                                <h6 class="mb-0"><i class="fas fa-clock"></i> Pending</h6>
                                <h3 class="mb-0" id="statPending">$0.00</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body py-2">
                                <h6 class="mb-0"><i class="fas fa-undo"></i> Refunds</h6>
                                <h3 class="mb-0" id="statRefunds">$0.00</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body py-2">
                                <h6 class="mb-0"><i class="fas fa-calendar-day"></i> Today</h6>
                                <h3 class="mb-0" id="statToday">$0.00</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label for="statusFilter">Status</label>
                        <select class="form-control" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="typeFilter">Type</label>
                        <select class="form-control" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="payment">Payment</option>
                            <option value="refund">Refund</option>
                            <option value="deposit">Deposit</option>
                            <option value="withdrawal">Withdrawal</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="paymentMethodFilter">Payment Method</label>
                        <select class="form-control" id="paymentMethodFilter">
                            <option value="">All Methods</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
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
                                <th width="18%">Transaction ID</th>
                                <th width="15%">User</th>
                                <th width="8%">Type</th>
                                <th width="12%">Amount</th>
                                <th width="6%">Fee</th>
                                <th width="12%">Method</th>
                                <th width="8%">Invoice</th>
                                <th width="8%">Status</th>
                                <th width="10%">Actions</th>
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
                    <h5 class="modal-title" id="modalTitle">Record Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">User</label>
                                <select class="form-control" id="user_id" name="user_id">
                                    <option value="">Guest</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select class="form-control" id="payment_method_id" name="payment_method_id">
                                    <option value="">Select Method</option>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method->id }}" data-fee="{{ $method->processing_fee }}" data-fee-type="{{ $method->processing_fee_type }}">
                                            {{ $method->name }} ({{ $method->formatted_fee }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="type" name="type" required>
                                    <option value="payment">Payment</option>
                                    <option value="refund">Refund</option>
                                    <option value="deposit">Deposit</option>
                                    <option value="withdrawal">Withdrawal</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Currency <span class="text-danger">*</span></label>
                                <select class="form-control" id="currency" name="currency" required>
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="KHR">KHR</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Fee</label>
                                <input type="number" class="form-control" id="fee" name="fee" step="0.01" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Net Amount</label>
                                <input type="text" class="form-control" id="net_amount_display" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="completed">Completed</option>
                                    <option value="failed">Failed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description" placeholder="Transaction description...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Internal)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Internal notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Save Transaction
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
                <h5 class="modal-title">Transaction Details</h5>
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

    // Calculate net amount and fee
    function calculateNetAmount() {
        let amount = parseFloat($('#amount').val()) || 0;
        let fee = parseFloat($('#fee').val()) || 0;
        let net = amount - fee;
        $('#net_amount_display').val('$' + net.toFixed(2));
    }

    $('#amount, #fee').on('input', calculateNetAmount);

    // Auto-calculate fee based on payment method
    $('#payment_method_id').on('change', function() {
        let selected = $(this).find('option:selected');
        let fee = parseFloat(selected.data('fee')) || 0;
        let feeType = selected.data('fee-type');
        let amount = parseFloat($('#amount').val()) || 0;

        if (feeType === 'percentage') {
            fee = amount * (fee / 100);
        }

        $('#fee').val(fee.toFixed(2));
        calculateNetAmount();
    });

    // Initialize DataTable
    let table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.transactions.index") }}',
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'transaction_id_display', name: 'transaction_id' },
            { data: 'user_display', name: 'user_id' },
            { data: 'type_badge', name: 'type_badge' },
            { data: 'amount_display', name: 'amount' },
            { data: 'fee_display', name: 'fee' },
            { data: 'payment_method_display', name: 'payment_method_id' },
            { data: 'invoice_display', name: 'invoice_id' },
            { data: 'status', name: 'status' },
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
        table.column(8).search($(this).val()).draw();
    });

    // Type filter
    $('#typeFilter').on('change', function() {
        table.column(3).search($(this).val()).draw();
    });

    // Clear filters
    $('#clearFiltersBtn').on('click', function() {
        $('#statusFilter').val('');
        $('#typeFilter').val('');
        $('#paymentMethodFilter').val('');
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
        $('#modalTitle').text('Record Transaction');
        $('#net_amount_display').val('');
    }

    // Submit form
    $('#itemForm').on('submit', function(e) {
        e.preventDefault();

        let itemId = $('#itemId').val();
        let url = itemId
            ? '{{ route("admin.transactions.index") }}/' + itemId
            : '{{ route("admin.transactions.store") }}';
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

    // View item
    $(document).on('click', '.view-item', function() {
        let id = $(this).data('id');
        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: '{{ route("admin.transactions.index") }}/' + id,
            type: 'GET',
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    let data = response.data;
                    let statusBadge = {
                        'pending': '<span class="badge bg-warning">Pending</span>',
                        'processing': '<span class="badge bg-info">Processing</span>',
                        'completed': '<span class="badge bg-success">Completed</span>',
                        'failed': '<span class="badge bg-danger">Failed</span>',
                        'refunded': '<span class="badge bg-secondary">Refunded</span>',
                        'cancelled': '<span class="badge bg-dark">Cancelled</span>'
                    };
                    let typeBadge = {
                        'payment': '<span class="badge bg-primary">Payment</span>',
                        'refund': '<span class="badge bg-warning">Refund</span>',
                        'deposit': '<span class="badge bg-info">Deposit</span>',
                        'withdrawal': '<span class="badge bg-secondary">Withdrawal</span>'
                    };

                    let html = `
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <h5><code>${data.transaction_id}</code></h5>
                                ${statusBadge[data.status] || ''} ${typeBadge[data.type] || ''}
                            </div>
                            <div class="col-md-4 text-end">
                                <h3 class="${data.type === 'refund' ? 'text-danger' : 'text-success'}">
                                    ${data.type === 'refund' ? '-' : '+'}${data.currency} ${parseFloat(data.amount).toFixed(2)}
                                </h3>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>User:</strong> ${data.user ? data.user.name + ' (' + data.user.email + ')' : 'Guest'}</p>
                                <p><strong>Payment Method:</strong> ${data.payment_method ? data.payment_method.name : 'N/A'}</p>
                                <p><strong>Invoice:</strong> ${data.invoice ? data.invoice.invoice_number : 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Amount:</strong> ${data.currency} ${parseFloat(data.amount).toFixed(2)}</p>
                                <p><strong>Fee:</strong> ${data.currency} ${parseFloat(data.fee).toFixed(2)}</p>
                                <p><strong>Net Amount:</strong> ${data.currency} ${parseFloat(data.net_amount).toFixed(2)}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Created:</strong> ${data.created_at}</p>
                                <p><strong>Processed:</strong> ${data.processed_at || 'Not processed'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>IP Address:</strong> ${data.ip_address || 'N/A'}</p>
                                ${data.gateway_transaction_id ? '<p><strong>Gateway ID:</strong> ' + data.gateway_transaction_id + '</p>' : ''}
                            </div>
                        </div>
                        ${data.description ? '<hr><p><strong>Description:</strong> ' + data.description + '</p>' : ''}
                        ${data.notes ? '<p><strong>Notes:</strong> ' + data.notes + '</p>' : ''}
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

    // Approve transaction
    $(document).on('click', '.approve-item', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Approve Transaction?',
            text: "This will mark the transaction as completed.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loadingOverlay').css('display', 'flex');

                $.ajax({
                    url: '{{ route("admin.transactions.index") }}/' + id + '/approve',
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
                        toastr.error(xhr.responseJSON?.message || 'Failed to approve.');
                    }
                });
            }
        });
    });

    // Reject transaction
    $(document).on('click', '.reject-item', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Reject Transaction?',
            text: "Please provide a reason:",
            input: 'text',
            inputPlaceholder: 'Reason for rejection...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Reject'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loadingOverlay').css('display', 'flex');

                $.ajax({
                    url: '{{ route("admin.transactions.index") }}/' + id + '/reject',
                    type: 'POST',
                    data: { reason: result.value },
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
                        toastr.error(xhr.responseJSON?.message || 'Failed to reject.');
                    }
                });
            }
        });
    });

    // Refund transaction
    $(document).on('click', '.refund-item', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Create Refund?',
            text: "This will create a refund for this transaction.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, refund it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loadingOverlay').css('display', 'flex');

                $.ajax({
                    url: '{{ route("admin.transactions.index") }}/' + id + '/refund',
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
                        toastr.error(xhr.responseJSON?.message || 'Failed to refund.');
                    }
                });
            }
        });
    });
});
</script>
@endpush

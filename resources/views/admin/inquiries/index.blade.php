@extends('admin.layouts.master_layout')

@section('pageTitle', __('admin.inquiries.title'))

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.inquiries.title') }}</h4>
                    <div class="card-tools">
                        <a href="{{ route('admin.inquiries.export') }}" class="btn btn-success">
                            <i class="fas fa-download"></i> {{ __('admin.inquiries.export_csv') }}
                        </a>
                        <button type="button" class="btn btn-info" id="refreshTableBtn">
                            <i class="fas fa-sync-alt"></i> {{ __('admin.refresh') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="inquiriesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Contact</th>
                                    <th>Property</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via Ajax DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reply Modal -->
    <div class="modal fade" id="replyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reply to Inquiry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="replyForm">
                    @csrf
                    <input type="hidden" id="inquiryId" name="inquiry_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Reply Message</label>
                            <textarea class="form-control" name="reply_message" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Send Reply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const table = $('#inquiriesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.inquiries.index") }}',
            type: 'GET',
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'contact', name: 'contact' },
            { data: 'property', name: 'property' },
            { data: 'message', name: 'message', orderable: false, searchable: false },
            { data: 'status', name: 'status' },
            { data: 'user', name: 'user' },
            { data: 'date', name: 'date' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        responsive: true,
        pageLength: 25,
    });

    $('#refreshTableBtn').click(function() {
        table.ajax.reload();
    });

    // Reply to inquiry
    $(document).on('click', '.reply-inquiry', function() {
        const inquiryId = $(this).data('id');
        $('#inquiryId').val(inquiryId);
        $('#replyModal').modal('show');
    });

    $('#replyForm').submit(function(e) {
        e.preventDefault();
        const inquiryId = $('#inquiryId').val();

        $.ajax({
            url: '/admin/inquiries/' + inquiryId + '/reply',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#replyModal').modal('hide');
                    Swal.fire('Success!', response.message, 'success');
                    table.ajax.reload();
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', 'Failed to send reply', 'error');
            }
        });
    });

    // Delete inquiry
    $(document).on('click', '.delete-inquiry', function() {
        const inquiryId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/inquiries/' + inquiryId,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            table.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Failed to delete inquiry', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush

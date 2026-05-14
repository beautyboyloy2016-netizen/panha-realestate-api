@extends('admin.layouts.master_layout')

@section('pageTitle', 'Inquiry Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Inquiry Details</h4>
                <div class="card-tools">
                    <a href="{{ route('admin.inquiries.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#replyModal">
                        <i class="fas fa-reply"></i> Reply to Inquiry
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Contact Information -->
                    <div class="col-md-6">
                        <h5>Contact Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="150">ID:</th>
                                <td>{{ $inquiry->id }}</td>
                            </tr>
                            <tr>
                                <th>Name:</th>
                                <td><strong>{{ $inquiry->name }}</strong></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td>
                                    @if($inquiry->phone)
                                        <a href="tel:{{ $inquiry->phone }}">{{ $inquiry->phone }}</a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @php
                                        $badges = [
                                            'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
                                            'contacted' => '<span class="badge bg-info">Contacted</span>',
                                            'closed' => '<span class="badge bg-success">Closed</span>',
                                        ];
                                    @endphp
                                    {!! $badges[$inquiry->status] ?? '<span class="badge bg-secondary">Unknown</span>' !!}
                                </td>
                            </tr>
                            <tr>
                                <th>User Account:</th>
                                <td>
                                    @if($inquiry->user)
                                        <strong>{{ $inquiry->user->full_name }}</strong><br>
                                        <small class="text-muted">Registered User</small>
                                    @else
                                        <span class="text-muted">Guest Inquiry</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Property & Dates -->
                    <div class="col-md-6">
                        <h5>Property & Dates</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="150">Property:</th>
                                <td>
                                    @if($inquiry->property)
                                        <a href="{{ route('admin.properties.show', $inquiry->property->id) }}" class="text-primary">
                                            <strong>{{ $inquiry->property->title }}</strong>
                                        </a><br>
                                        <small class="text-muted">
                                            {{ $inquiry->property->city }}, {{ $inquiry->property->district }}
                                        </small><br>
                                        <small class="text-muted">
                                            {{ $inquiry->property->listing_type }} - ${{ number_format($inquiry->property->price) }}
                                        </small>
                                    @else
                                        <span class="text-danger">Property Deleted</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Submitted:</th>
                                <td>
                                    {{ $inquiry->created_at->format('Y-m-d H:i:s') }}<br>
                                    <small class="text-muted">{{ $inquiry->created_at->diffForHumans() }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Last Updated:</th>
                                <td>
                                    {{ $inquiry->updated_at->format('Y-m-d H:i:s') }}<br>
                                    <small class="text-muted">{{ $inquiry->updated_at->diffForHumans() }}</small>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Message -->
                    <div class="col-md-12 mt-3">
                        <h5>Message</h5>
                        <div class="card">
                            <div class="card-body">
                                {!! nl2br(e($inquiry->message)) !!}
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="col-md-12 mt-3">
                        <h5>Actions</h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-info" onclick="updateStatus('pending')">
                                <i class="fas fa-clock"></i> Mark as Pending
                            </button>
                            <button type="button" class="btn btn-primary" onclick="updateStatus('contacted')">
                                <i class="fas fa-check"></i> Mark as Contacted
                            </button>
                            <button type="button" class="btn btn-success" onclick="updateStatus('closed')">
                                <i class="fas fa-check-double"></i> Mark as Closed
                            </button>
                        </div>
                    </div>
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
            <form id="replyForm" action="{{ route('admin.inquiries.reply', $inquiry->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reply Message</label>
                        <textarea class="form-control" name="reply_message" rows="5" required></textarea>
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle"></i>
                            This reply will be sent to <strong>{{ $inquiry->email }}</strong>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateStatus(status) {
    Swal.fire({
        title: 'Update Status?',
        text: `Change inquiry status to ${status}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, update it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.inquiries.update", $inquiry->id) }}',
                type: 'PATCH',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Updated!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Failed to update status', 'error');
                }
            });
        }
    });
}

$('#replyForm').submit(function(e) {
    e.preventDefault();

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                $('#replyModal').modal('hide');
                Swal.fire('Success!', response.message, 'success').then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            Swal.fire('Error!', 'Failed to send reply', 'error');
        }
    });
});
</script>
@endpush

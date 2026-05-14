@extends('admin.layouts.master_layout')

@section('pageTitle', 'Serviced Apartments')

@push('styles')
<style>
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
    }
    .stats-card h2 {
        font-size: 2.5rem;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.properties.index') }}">Properties</a></li>
                <li class="breadcrumb-item active">Serviced Apartments</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="stats-card">
            <h5><i class="fas fa-building me-2"></i>Serviced Apartments</h5>
            <h2 id="totalCount">0</h2>
            <small>Apartments available for rent</small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">About This Section</h5>
                <p class="text-muted mb-0">
                    Serviced apartments are fully furnished rental apartments available for short-term or long-term stays.
                    This section displays all properties with <span class="badge bg-info">Apartment</span> type and
                    <span class="badge bg-success">For Rent</span> listing type that are currently available.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="fas fa-concierge-bell me-2"></i>Serviced Apartments List
                </h4>
                <div>
                    <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to All Properties
                    </a>
                    <button type="button" class="btn btn-info" id="refreshTableBtn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="servicedApartmentsTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">ID</th>
                                <th width="10%">Image</th>
                                <th width="25%">Title & Location</th>
                                <th width="12%">Price/Month</th>
                                <th width="15%">Details</th>
                                <th width="13%">Status</th>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#servicedApartmentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.properties.serviced-apartments") }}',
            dataSrc: function(json) {
                $('#totalCount').text(json.recordsTotal || 0);
                return json.data;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'title', name: 'title' },
            { data: 'price', name: 'price' },
            { data: 'details', name: 'details', orderable: false },
            { data: 'status', name: 'status', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        responsive: true
    });

    $('#refreshTableBtn').click(function() {
        table.ajax.reload();
    });
});
</script>
@endpush

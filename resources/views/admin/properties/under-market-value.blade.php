@extends('admin.layouts.master_layout')

@section('pageTitle', 'Under Market Value Properties')

@push('styles')
<style>
    .stats-card {
        background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
    }
    .stats-card h2 {
        font-size: 2.5rem;
        font-weight: 700;
    }
    .deal-badge {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
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
                <li class="breadcrumb-item active">Under Market Value</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="stats-card">
            <h5><i class="fas fa-tags me-2"></i>Special Deals</h5>
            <h2 id="totalCount">0</h2>
            <small>Featured properties under market value</small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-fire text-danger me-2"></i>Hot Deals</h5>
                <p class="text-muted mb-0">
                    Properties under market value are <span class="badge bg-warning text-dark">Featured</span>
                    listings priced competitively. These properties are highlighted on the homepage to attract buyers
                    looking for great deals. Sorted by price from lowest to highest.
                </p>
                <div class="mt-2">
                    <small class="text-info">
                        <i class="fas fa-info-circle me-1"></i>
                        To add properties here, mark them as "Featured" in the property edit form.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="fas fa-percentage me-2 text-danger"></i>Under Market Value Properties
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
                    <table class="table table-striped table-bordered" id="underMarketValueTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">ID</th>
                                <th width="8%">Image</th>
                                <th width="20%">Title</th>
                                <th width="10%">Listing</th>
                                <th width="15%">Price</th>
                                <th width="17%">Location</th>
                                <th width="15%">Details</th>
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
    var table = $('#underMarketValueTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.properties.under-market-value") }}',
            dataSrc: function(json) {
                $('#totalCount').text(json.recordsTotal || 0);
                return json.data;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'title', name: 'title' },
            { data: 'listing', name: 'listing', orderable: false },
            { data: 'price', name: 'price' },
            { data: 'location', name: 'location' },
            { data: 'details', name: 'details', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[4, 'asc']], // Sort by price ascending (lowest first)
        responsive: true
    });

    $('#refreshTableBtn').click(function() {
        table.ajax.reload();
    });
});
</script>
@endpush

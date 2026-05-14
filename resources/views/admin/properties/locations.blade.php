@extends('admin.layouts.master_layout')

@section('pageTitle', 'Property Locations')

@push('styles')
<style>
    .location-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .location-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .location-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
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
                <li class="breadcrumb-item active">Locations</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1"><i class="fas fa-map-marked-alt me-2"></i>Property Locations Overview</h4>
                        <p class="mb-0 opacity-75">Properties distributed across {{ count($locations) }} cities</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.properties.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left"></i> Back to All Properties
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="stats-grid">
    @forelse($locations as $location)
    <div class="card location-card" onclick="window.location='{{ route('admin.properties.index') }}?city={{ urlencode($location->city) }}'">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="location-icon bg-primary-subtle text-primary me-3">
                    <i class="fas fa-city"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="mb-1">{{ $location->city }}</h5>
                    <div class="d-flex gap-3">
                        <small class="text-muted">
                            <i class="fas fa-building me-1"></i>{{ $location->total_count }} Total
                        </small>
                        <small class="text-success">
                            <i class="fas fa-check-circle me-1"></i>{{ $location->available_count }} Available
                        </small>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-muted"></i>
            </div>
            <hr class="my-3">
            <div class="row text-center">
                <div class="col-6">
                    <div class="border-end">
                        <h5 class="mb-0 text-primary">{{ $location->for_sale_count }}</h5>
                        <small class="text-muted">For Sale</small>
                    </div>
                </div>
                <div class="col-6">
                    <div>
                        <h5 class="mb-0 text-info">{{ $location->for_rent_count }}</h5>
                        <small class="text-muted">For Rent</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No properties found. Start by adding some properties to see location statistics.
        </div>
    </div>
    @endforelse
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Location Statistics Table</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>City</th>
                                <th class="text-center">Total Properties</th>
                                <th class="text-center">Available</th>
                                <th class="text-center">For Sale</th>
                                <th class="text-center">For Rent</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($locations as $location)
                            <tr>
                                <td>
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                    <strong>{{ $location->city }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary fs-6">{{ $location->total_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success fs-6">{{ $location->available_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-6">{{ $location->for_sale_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info fs-6">{{ $location->for_rent_count }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.properties.index') }}?city={{ urlencode($location->city) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>View Properties
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th>Total</th>
                                <th class="text-center">{{ $locations->sum('total_count') }}</th>
                                <th class="text-center">{{ $locations->sum('available_count') }}</th>
                                <th class="text-center">{{ $locations->sum('for_sale_count') }}</th>
                                <th class="text-center">{{ $locations->sum('for_rent_count') }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

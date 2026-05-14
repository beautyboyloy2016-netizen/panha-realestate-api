@extends('admin.layouts.master_layout')

@section('pageTitle', 'Property Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Property Details</h4>
                <div class="card-tools">
                    <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.properties.edit', $property->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Property
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Property Images -->
                    <div class="col-md-12 mb-4">
                        <h5>Property Images</h5>
                        <div class="row">
                            @if($property->images && $property->images->count() > 0)
                                @foreach($property->images as $image)
                                    <div class="col-md-3 mb-3">
                                        <img src="{{ $image->url }}" alt="{{ $property->title }}" class="img-fluid rounded" style="width:100%; height:200px; object-fit:cover;">
                                        @if($image->is_primary)
                                            <span class="badge bg-primary mt-1">Primary</span>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="col-12">
                                    <p class="text-muted">No images available</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <h5>Basic Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="150">ID:</th>
                                <td>{{ $property->id }}</td>
                            </tr>
                            <tr>
                                <th>Title:</th>
                                <td><strong>{{ $property->title }}</strong></td>
                            </tr>
                            <tr>
                                <th>Listing Type:</th>
                                <td>
                                    <span class="badge {{ $property->listing_type === 'For Sale' ? 'bg-primary' : 'bg-info' }}">
                                        {{ $property->listing_type }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Property Type:</th>
                                <td>{{ $property->property_type }}</td>
                            </tr>
                            <tr>
                                <th>Price:</th>
                                <td><strong class="text-success">${{ number_format($property->price) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Area:</th>
                                <td>{{ $property->area }} {{ $property->area_unit }}</td>
                            </tr>
                            <tr>
                                <th>Bedrooms:</th>
                                <td><i class="fas fa-bed"></i> {{ $property->bedrooms }}</td>
                            </tr>
                            <tr>
                                <th>Bathrooms:</th>
                                <td><i class="fas fa-bath"></i> {{ $property->bathrooms }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($property->is_featured)
                                        <span class="badge bg-warning text-dark">Featured</span>
                                    @endif
                                    <span class="badge bg-success">Available</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Views:</th>
                                <td><span class="badge bg-secondary">{{ $property->views }} views</span></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Location & Owner -->
                    <div class="col-md-6">
                        <h5>Location & Owner</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="150">City:</th>
                                <td>{{ $property->city }}</td>
                            </tr>
                            <tr>
                                <th>District:</th>
                                <td>{{ $property->district ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Address:</th>
                                <td>{{ $property->location ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Latitude:</th>
                                <td>{{ $property->latitude ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Longitude:</th>
                                <td>{{ $property->longitude ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Owner:</th>
                                <td>
                                    @if($property->user)
                                        <strong>{{ $property->user->full_name }}</strong><br>
                                        <small class="text-muted">{{ $property->user->email }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created At:</th>
                                <td>{{ $property->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Updated At:</th>
                                <td>{{ $property->updated_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Description -->
                    <div class="col-md-12 mt-3">
                        <h5>Description</h5>
                        <div class="card">
                            <div class="card-body">
                                {!! nl2br(e($property->description)) !!}
                            </div>
                        </div>
                    </div>

                    <!-- Features -->
                    @if($property->features)
                    <div class="col-md-12 mt-3">
                        <h5>Features</h5>
                        <div class="card">
                            <div class="card-body">
                                @php
                                    $features = is_string($property->features) ? json_decode($property->features, true) : $property->features;
                                @endphp
                                @if(is_array($features) && count($features) > 0)
                                    <div class="row">
                                        @foreach($features as $feature)
                                            <div class="col-md-3 mb-2">
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-check"></i> {{ $feature }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted">No features available</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

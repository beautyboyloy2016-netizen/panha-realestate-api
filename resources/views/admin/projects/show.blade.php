@extends('admin.layouts.master_layout')

@section('pageTitle', 'Project Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Project Details</h4>
                <div class="card-tools">
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.projects.edit', $project->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Project
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Project Image -->
                    @if($project->image_url)
                    <div class="col-md-12 mb-4">
                        <h5>Project Image</h5>
                        <img src="{{ $project->image_url }}" alt="{{ $project->name }}" class="img-fluid rounded" style="max-height: 400px; object-fit: cover;">
                    </div>
                    @endif

                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <h5>Basic Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="150">ID:</th>
                                <td>{{ $project->id }}</td>
                            </tr>
                            <tr>
                                <th>Name:</th>
                                <td><strong>{{ $project->name }}</strong></td>
                            </tr>
                            <tr>
                                <th>Developer:</th>
                                <td>{{ $project->developer }}</td>
                            </tr>
                            <tr>
                                <th>Location:</th>
                                <td>{{ $project->location }}</td>
                            </tr>
                            <tr>
                                <th>Units:</th>
                                <td><span class="badge bg-info">{{ $project->units }} units</span></td>
                            </tr>
                            <tr>
                                <th>Price From:</th>
                                <td><strong class="text-success">{{ $project->price_from }}</strong></td>
                            </tr>
                            <tr>
                                <th>Completion:</th>
                                <td><span class="badge bg-secondary">{{ $project->completion }}</span></td>
                            </tr>
                            <tr>
                                <th>Rental Yield:</th>
                                <td>
                                    @if($project->rental_yield)
                                        <span class="badge bg-success">{{ $project->rental_yield }}%</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Status & Dates -->
                    <div class="col-md-6">
                        <h5>Status & Dates</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="150">Featured:</th>
                                <td>
                                    @if($project->featured)
                                        <span class="badge bg-warning text-dark">Featured</span>
                                    @else
                                        <span class="badge bg-secondary">Standard</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created At:</th>
                                <td>{{ $project->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Updated At:</th>
                                <td>{{ $project->updated_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Description -->
                    @if($project->description)
                    <div class="col-md-12 mt-3">
                        <h5>Description</h5>
                        <div class="card">
                            <div class="card-body">
                                {!! nl2br(e($project->description)) !!}
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

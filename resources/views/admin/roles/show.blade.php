@extends('admin.layouts.master_layout')

@section('pageTitle', 'Role Details')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Role Details: {{ $role->title }}</h4>
        <div class="card-tools">
          <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
          </a>
          <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <table class="table table-bordered">
              <tbody>
                <tr>
                  <th width="30%">ID</th>
                  <td>{{ $role->id }}</td>
                </tr>
                <tr>
                  <th>Title</th>
                  <td>{{ $role->title }}</td>
                </tr>
                <tr>
                  <th>Status</th>
                  <td>
                    @if($role->status)
                      <span class="badge bg-success">Active</span>
                    @else
                      <span class="badge bg-danger">Inactive</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <th>Created At</th>
                  <td>{{ $role->created_at->format('Y-m-d H:i:s') }}</td>
                </tr>
                <tr>
                  <th>Updated At</th>
                  <td>{{ $role->updated_at->format('Y-m-d H:i:s') }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="col-md-6">
            @if(isset($role->permissions) && $role->permissions->count() > 0)
            <div class="card">
              <div class="card-header">
                <h5>Assigned Permissions ({{ $role->permissions->count() }})</h5>
              </div>
              <div class="card-body">
                @foreach($role->permissions->groupBy('group') as $group => $groupPermissions)
                <div class="mb-3">
                  <h6 class="text-primary">{{ $group }}</h6>
                  <div class="ms-3">
                    @foreach($groupPermissions as $permission)
                    <span class="badge bg-info text-white me-1 mb-1">{{ $permission->title }}</span>
                    @endforeach
                  </div>
                </div>
                @endforeach
              </div>
            </div>
            @else
            <div class="alert alert-info">
              <i class="fas fa-info-circle"></i>
              No permissions assigned to this role.
            </div>
            @endif

            @if(isset($role->users) && $role->users->count() > 0)
            <div class="card mt-3">
              <div class="card-header">
                <h5>Users with this Role ({{ $role->users->count() }})</h5>
              </div>
              <div class="card-body">
                <div class="row">
                  @foreach($role->users as $user)
                  <div class="col-md-6 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="avatar-sm me-2">
                        <img src="{{ $user->avatar ?? '/assets/backend/images/avatar.png' }}"
                             alt="{{ $user->first_name }}" class="rounded-circle" width="30">
                      </div>
                      <div>
                        <strong>{{ $user->first_name }} {{ $user->last_name }}</strong><br>
                        <small class="text-muted">{{ $user->email }}</small>
                      </div>
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

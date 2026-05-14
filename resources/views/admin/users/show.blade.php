@extends('admin.layouts.master_layout')

@section('pageTitle', 'User Details')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">User Details</h4>
        <div class="card-tools">
          <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
          </a>
          <a href="{{ route('admin.users.edit', $user->id ?? 0) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit User
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <table class="table table-borderless">
              <tr>
                <th width="150">ID:</th>
                <td>{{ $user->id ?? 'N/A' }}</td>
              </tr>
              <tr>
                <th>First Name:</th>
                <td>{{ $user->first_name ?? 'N/A' }}</td>
              </tr>
              <tr>
                <th>Last Name:</th>
                <td>{{ $user->last_name ?? 'N/A' }}</td>
              </tr>
              <tr>
                <th>Full Name:</th>
                <td><strong>{{ ($user->first_name ?? '') . ' ' . ($user->last_name ?? '') }}</strong></td>
              </tr>
              <tr>
                <th>Username:</th>
                <td>{{ $user->username ?? 'N/A' }}</td>
              </tr>
              <tr>
                <th>Email:</th>
                <td>
                  <a href="mailto:{{ $user->email ?? '' }}">{{ $user->email ?? 'N/A' }}</a>
                  @if(isset($user->email_verified_at) && $user->email_verified_at)
                    <span class="badge bg-success ms-2">Verified</span>
                  @else
                    <span class="badge bg-warning ms-2">Unverified</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th>Phone:</th>
                <td>
                  @if($user->phone ?? null)
                    <a href="tel:{{ $user->phone }}">{{ $user->phone }}</a>
                  @else
                    N/A
                  @endif
                </td>
              </tr>
              <tr>
                <th>Date of Birth:</th>
                <td>{{ $user->date_of_birth ?? 'N/A' }}</td>
              </tr>
            </table>
          </div>

          <div class="col-md-6">
            <table class="table table-borderless">
              <tr>
                <th width="150">Status:</th>
                <td>
                  @if(($user->status ?? 'active') === 'active')
                    <span class="badge bg-success">Active</span>
                  @else
                    <span class="badge bg-danger">Inactive</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th>Roles:</th>
                <td>
                  @if(isset($user->roles) && $user->roles->count() > 0)
                    @foreach($user->roles as $role)
                      <span class="badge bg-info text-white me-1">{{ $role->name }}</span>
                    @endforeach
                  @else
                    <span class="text-muted">No roles assigned</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th>Created At:</th>
                <td>{{ $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
              </tr>
              <tr>
                <th>Updated At:</th>
                <td>{{ $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
              </tr>
              <tr>
                <th>Last Login:</th>
                <td>{{ $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never' }}</td>
              </tr>
              <tr>
                <th>Login Count:</th>
                <td>{{ $user->login_count ?? 0 }} times</td>
              </tr>
            </table>
          </div>
        </div>

        @if(isset($user->vendor) && $user->vendor)
        <div class="row mt-4">
          <div class="col-12">
            <h5>Vendor Information</h5>
            <div class="card border-left-primary">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <strong>Store Name:</strong> {{ $user->vendor->store_name ?? 'N/A' }}<br>
                    <strong>Description:</strong> {{ $user->vendor->description ?? 'N/A' }}<br>
                    <strong>Status:</strong>
                    @if(($user->vendor->status ?? 'pending') === 'active')
                      <span class="badge bg-success">Active</span>
                    @elseif(($user->vendor->status ?? 'pending') === 'pending')
                      <span class="badge bg-warning">Pending</span>
                    @else
                      <span class="badge bg-danger">Inactive</span>
                    @endif
                  </div>
                  <div class="col-md-6">
                    <strong>Commission Rate:</strong> {{ $user->vendor->commission_rate ?? 0 }}%<br>
                    <strong>Total Earnings:</strong> ${{ number_format($user->vendor->total_earnings ?? 0, 2) }}<br>
                    <strong>Available Balance:</strong> ${{ number_format($user->vendor->available_balance ?? 0, 2) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        @endif

        <div class="row mt-4">
          <div class="col-12">
            <div class="btn-group">
              <a href="{{ route('admin.users.edit', $user->id ?? 0) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit User
              </a>
              <form action="{{ route('admin.users.destroy', $user->id ?? 0) }}" method="POST" class="d-inline ms-2">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                  <i class="fas fa-trash"></i> Delete User
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

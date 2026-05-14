@extends('admin.layouts.master_layout')

@section('pageTitle', 'Create New Role')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Create New Role</h4>
        <div class="card-tools">
          <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
          </a>
        </div>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.roles.store') }}" method="POST">
          @csrf

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="title">Role Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('title') is-invalid @enderror"
                       id="title" name="title" value="{{ old('title') }}" required>
                @error('title')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                  <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                  <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          @if(isset($permissions) && $permissions->count() > 0)
          <div class="row">
            <div class="col-12">
              <h5>Assign Permissions</h5>
              <div class="row">
                @foreach($permissions->groupBy('group') as $group => $groupPermissions)
                <div class="col-md-4 mb-3">
                  <div class="card">
                    <div class="card-header">
                      <h6 class="mb-0">{{ $group }}</h6>
                      <div class="form-check">
                        <input class="form-check-input group-checkbox" type="checkbox" data-group="{{ $group }}">
                        <label class="form-check-label">Select All</label>
                      </div>
                    </div>
                    <div class="card-body">
                      @foreach($groupPermissions as $permission)
                      <div class="form-check">
                        <input class="form-check-input permission-checkbox"
                               type="checkbox"
                               name="permissions[]"
                               value="{{ $permission->id }}"
                               id="permission_{{ $permission->id }}"
                               data-group="{{ $group }}"
                               {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                          {{ $permission->title }}
                        </label>
                      </div>
                      @endforeach
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
          </div>
          @endif

          <div class="form-group">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Create Role
            </button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary ms-2">
              <i class="fas fa-times"></i> Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
  // Group checkbox functionality
  $('.group-checkbox').on('change', function() {
    const group = $(this).data('group');
    const isChecked = $(this).prop('checked');
    $(`.permission-checkbox[data-group="${group}"]`).prop('checked', isChecked);
  });

  // Update group checkbox when individual permissions change
  $('.permission-checkbox').on('change', function() {
    const group = $(this).data('group');
    const totalPermissions = $(`.permission-checkbox[data-group="${group}"]`).length;
    const checkedPermissions = $(`.permission-checkbox[data-group="${group}"]:checked`).length;

    if (checkedPermissions === totalPermissions) {
      $(`.group-checkbox[data-group="${group}"]`).prop('checked', true);
    } else {
      $(`.group-checkbox[data-group="${group}"]`).prop('checked', false);
    }
  });
});
</script>
@endpush

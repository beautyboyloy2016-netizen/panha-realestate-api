@extends('admin.layouts.master_layout')

@section('pageTitle', __('admin.permissions.title'))

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ __('admin.permissions.title') }}</h4>
          <div class="card-tools">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPermissionModal">
              <i class="fas fa-plus"></i> {{ __('admin.permissions.create') }}
            </button>
            <button type="button" class="btn btn-info" id="refreshTableBtn">
              <i class="fas fa-sync-alt"></i> {{ __('admin.refresh') }}
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-3">
              <div class="form-group">
                <label for="statusFilter">{{ __('admin.permissions.filter_status') }}</label>
                <select class="form-control" id="statusFilter">
                  <option value="">{{ __('admin.users.all_status') }}</option>
                  <option value="Active">{{ __('common.active') }}</option>
                  <option value="Inactive">{{ __('common.inactive') }}</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label for="groupFilter">{{ __('admin.permissions.filter_group') }}</label>
                <select class="form-control" id="groupFilter">
                  <option value="">{{ __('admin.permissions.all_groups') }}</option>
                  @if (isset($groups))
                    @foreach ($groups as $group)
                      <option value="{{ $group }}">{{ ucwords(str_replace('_', ' ', $group)) }}</option>
                    @endforeach
                  @endif
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>&nbsp;</label>
                <div class="d-flex">
                  <button type="button" class="btn btn-secondary me-2" id="clearFiltersBtn">
                    <i class="fas fa-times"></i> {{ __('admin.clear_filters') }}
                  </button>
                  <button type="button" class="btn btn-success" id="exportPermissionsBtn">
                    <i class="fas fa-download"></i> {{ __('admin.permissions.export_permissions') }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-striped table-bordered" id="permissionsTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Group</th>
                  <th>Title</th>
                  <th>Status</th>
                  <th>Created At</th>
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

  <!-- Loading Overlay -->
  <div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
      <i class="fas fa-spinner fa-spin"></i>
      <div>Processing...</div>
    </div>
  </div>
@endsection

@push('styles')
  {{-- <link href="{{ assetUrl() }}assets/backend/lib/datatables/css/dataTables.bootstrap5.min.css" rel="stylesheet"> --}}
  <style>
    .table-responsive {
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    .dataTables_wrapper .dataTables_filter input {
      border-radius: 20px;
      border: 1px solid #ddd;
      padding: 8px 15px;
    }

    .dataTables_wrapper .dataTables_length select {
      border-radius: 6px;
      border: 1px solid #ddd;
      padding: 5px 10px;
    }

    #permissionsTable th {
      background: #f8f9fa;
      font-weight: 600;
      border-bottom: 2px solid #dee2e6;
    }

    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }

    .loading-spinner {
      color: white;
      font-size: 24px;
    }

    .modal-lg {
      max-width: 800px;
    }
  </style>
@endpush

<!-- Permission Modals -->
<div class="modal fade" id="createPermissionModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <form id="createPermissionForm" method="POST">
      @csrf
      <input type="hidden" id="permissionId" name="permission_id">
      <input type="hidden" id="formMethod" name="_method" value="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Create New Permission</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="createTitle">Permission Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="createTitle" name="title" required
                  placeholder="e.g., user_create">
                <div class="invalid-feedback"></div>
                <small class="form-text text-muted">Use snake_case format (e.g., user_create, product_edit)</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="createGroup">Permission Group</label>
                <input type="text" class="form-control" id="createGroup" name="group"
                  placeholder="e.g., user_management">
                <div class="invalid-feedback"></div>
                <small class="form-text text-muted">Groups help organize related permissions</small>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="createStatus">Status</label>
                <select class="form-control" id="createStatus" name="status">
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Tip:</strong> Permission names should follow the format <code>resource_action</code>
                (e.g., <code>user_create</code>, <code>product_edit</code>, <code>order_delete</code>).
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="createPermissionBtn">
            <i class="fas fa-save" id="buttonIcon"></i> <span id="buttonText">Create Permission</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="viewPermissionModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Permission Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="permissionDetailsContent">
        <!-- Permission details will be loaded here -->
      </div>
    </div>
  </div>
</div>

@push('scripts')
  {{-- <script src="{{ assetUrl() }}assets/backend/lib/datatables/js/jquery.dataTables.min.js"></script>
  <script src="{{ assetUrl() }}assets/backend/lib/datatables/js/dataTables.bootstrap5.min.js"></script> --}}
  <script>
    $(document).ready(function() {
      // Initialize server-side DataTable
      const table = $('#permissionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '{{ route('admin.permissions.index') }}',
          type: 'GET'
        },
        columns: [{
            data: 'id',
            name: 'id'
          },
          {
            data: 'group',
            name: 'group'
          },
          {
            data: 'title',
            name: 'title'
          },
          {
            data: 'status',
            name: 'status'
          },
          {
            data: 'created_at',
            name: 'created_at'
          },
          {
            data: 'actions',
            name: 'actions',
            orderable: false,
            searchable: false
          }
        ],
        order: [
          [0, 'desc']
        ],
        pageLength: 25,
        responsive: true,
        language: {
          processing: '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>'
        }
      });

      // Function to reset modal to create mode
      function resetModalToCreateMode() {
        // Reset modal title and button
        $('#modalTitle').text('Create New Permission');
        $('#buttonText').text('Create Permission');
        $('#buttonIcon').removeClass('fa-edit').addClass('fa-save');
        $('#createPermissionBtn').removeClass('btn-success').addClass('btn-primary');

        // Reset form method and permission ID
        $('#formMethod').val('POST');
        $('#permissionId').val('');
      }

      // Handle modal close - reset forms
      $('.modal').on('hidden.bs.modal', function() {
        // Reset form
        const form = $(this).find('form')[0];
        if (form) {
          form.reset();
        }

        // Clear validation states
        $(this).find('.form-control').removeClass('is-invalid');
        $(this).find('.invalid-feedback').text('');

        // Reset modal to create mode
        if ($(this).attr('id') === 'createPermissionModal') {
          resetModalToCreateMode();
        }
      });

      // Reset modal when create button is clicked
      $('button[data-bs-target="#createPermissionModal"]').on('click', function() {
        resetModalToCreateMode();
      });

      // Status filter
      $('#statusFilter').on('change', function() {
        const status = $(this).val();
        table.column(3).search(status).draw(); // Status is column index 3
      });

      // Group filter
      $('#groupFilter').on('change', function() {
        const group = $(this).val();
        table.column(1).search(group).draw(); // Group is column index 1
      });

      // Clear filters
      $('#clearFiltersBtn').on('click', function() {
        $('#statusFilter').val('');
        $('#groupFilter').val('');
        table.search('').columns().search('').draw();
        showAlert('All filters cleared', 'info');
      });

      // Export permissions
      $('#exportPermissionsBtn').on('click', function() {
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Exporting...').prop('disabled', true);

        // Simulate export process
        setTimeout(function() {
          $btn.html(originalText).prop('disabled', false);
          showAlert('Export functionality would be implemented here', 'warning');
        }, 2000);
      });

      // Refresh table
      $('#refreshTableBtn').on('click', function() {
        table.ajax.reload();
        showAlert('Table refreshed successfully!', 'info');
      });

      // Create/Update Permission
      $('#createPermissionBtn').on('click', function() {
        const $btn = $(this);
        const originalText = $btn.html();
        const isEdit = $('#permissionId').val() !== '';
        const buttonLoadingText = isEdit ? '<i class="fas fa-spinner fa-spin"></i> Updating...' :
          '<i class="fas fa-spinner fa-spin"></i> Creating...';

        $btn.html(buttonLoadingText).prop('disabled', true);

        // Clear previous validation errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData($('#createPermissionForm')[0]);
        const url = isEdit ? '{{ route('admin.permissions.update', ':id') }}'.replace(':id', $('#permissionId')
            .val()) :
          '{{ route('admin.permissions.store') }}';
        const method = 'POST';

        $.ajax({
          url: url,
          type: method,
          data: formData,
          processData: false,
          contentType: false,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          success: function(response) {
            if (response.success) {
              $('#createPermissionModal').modal('hide');
              table.ajax.reload();

              // Use SweetAlert2 for success notification
              Swal.fire({
                icon: 'success',
                title: response.title || 'Success',
                text: response.message,
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
              });
            }
          },
          error: function(xhr) {
            if (xhr.status === 422) {
              const errors = xhr.responseJSON?.errors || {};
              Object.keys(errors).forEach(key => {
                const field = $(`[name="${key}"]`);
                field.addClass('is-invalid');
                field.next('.invalid-feedback').text(errors[key][0]);
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: `An error occurred while ${isEdit ? 'updating' : 'creating'} the permission.`,
                position: 'top-end',
                toast: true,
                timer: 5000,
                timerProgressBar: true,
                showConfirmButton: false
              });
            }
          },
          complete: function() {
            $btn.html(originalText).prop('disabled', false);
          }
        });
      });

      // View Permission
      $(document).on('click', '.view-permission', function() {
        const permissionId = $(this).data('id');

        $.ajax({
          url: '{{ route('admin.permissions.show', ':id') }}'.replace(':id', permissionId),
          type: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          success: function(response) {
            if (response.success) {
              const permission = response.permission;

              $('#permissionDetailsContent').html(`
                <div class="row">
                  <div class="col-md-6">
                    <strong>ID:</strong> ${permission.id}<br>
                    <strong>Title:</strong> <code>${permission.title}</code><br>
                    <strong>Group:</strong> ${permission.group ? '<span class="badge bg-secondary">' + permission.group + '</span>' : '<span class="text-muted">No Group</span>'}<br>
                    <strong>Status:</strong> ${permission.status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'}<br>
                  </div>
                  <div class="col-md-6">
                    <strong>Created:</strong> ${new Date(permission.created_at).toLocaleString()}<br>
                    <strong>Updated:</strong> ${new Date(permission.updated_at).toLocaleString()}<br>
                  </div>
                </div>
              `);
              $('#viewPermissionModal').modal('show');
            }
          },
          error: function(xhr) {
            alert('Error loading permission details');
          }
        });
      });

      // Edit Permission
      $(document).on('click', '.edit-permission', function() {
        const permissionId = $(this).data('id');

        $.ajax({
          url: '{{ route('admin.permissions.edit', ':id') }}'.replace(':id', permissionId),
          type: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          success: function(response) {
            if (response.success) {
              const permission = response.permission;

              // Set modal mode to edit
              $('#modalTitle').text('Edit Permission');
              $('#buttonText').text('Update Permission');
              $('#buttonIcon').removeClass('fa-save').addClass('fa-edit');
              $('#createPermissionBtn').removeClass('btn-primary').addClass('btn-success');

              // Set form method for update
              $('#formMethod').val('PUT');
              $('#permissionId').val(permission.id);

              // Fill form fields
              $('#createTitle').val(permission.title);
              $('#createGroup').val(permission.group);
              $('#createStatus').val(permission.status ? '1' : '0');

              $('#createPermissionModal').modal('show');
            }
          },
          error: function(xhr) {
            alert('Error loading permission data');
          }
        });
      });

      // Delete Permission
      $(document).on('click', '.delete-permission', function() {
        const permissionId = $(this).data('id');
        const permissionTitle = $(this).closest('tr').find('td:eq(2)').text().trim();

        Swal.fire({
          title: 'Are you sure?',
          text: `You are about to delete permission: ${permissionTitle}. This action cannot be undone!`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, delete it!',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: '{{ route('admin.permissions.destroy', ':id') }}'.replace(':id', permissionId),
              type: 'DELETE',
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {
                if (response.success) {
                  table.ajax.reload();
                  Swal.fire({
                    icon: 'success',
                    title: response.title || 'Success',
                    text: response.message,
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                  });
                }
              },
              error: function(xhr) {
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: 'An error occurred while deleting the permission.',
                  position: 'top-end',
                  toast: true,
                  timer: 5000,
                  timerProgressBar: true,
                  showConfirmButton: false
                });
              }
            });
          }
        });
      });

      // Helper function to show alerts using SweetAlert2
      function showAlert(message, type = 'info') {
        const iconMap = {
          'success': 'success',
          'danger': 'error',
          'warning': 'warning',
          'info': 'info'
        };

        const titleMap = {
          'success': 'Success!',
          'danger': 'Error!',
          'warning': 'Warning!',
          'info': 'Information'
        };

        Swal.fire({
          icon: iconMap[type] || 'info',
          title: titleMap[type] || 'Notification',
          text: message,
          timer: type === 'danger' ? 5000 : 3000,
          timerProgressBar: true,
          showConfirmButton: false,
          position: 'top-end',
          toast: true,
          background: '#fff',
          customClass: {
            popup: 'colored-toast'
          }
        });
      }
    });
  </script>
@endpush

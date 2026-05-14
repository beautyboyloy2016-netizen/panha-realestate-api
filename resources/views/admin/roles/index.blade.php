@extends('admin.layouts.master_layout')

@section('pageTitle', __('admin.roles.title'))

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ __('admin.roles.title') }}</h4>
          <div class="card-tools">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
              <i class="fas fa-plus"></i> {{ __('admin.roles.create') }}
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
                <label for="statusFilter">{{ __('admin.roles.filter_status') }}</label>
                <select class="form-control" id="statusFilter">
                  <option value="">{{ __('admin.users.all_status') }}</option>
                  <option value="Active">{{ __('common.active') }}</option>
                  <option value="Inactive">{{ __('common.inactive') }}</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label for="permissionFilter">{{ __('admin.roles.filter_permissions') }}</label>
                <select class="form-control" id="permissionFilter">
                  <option value="">{{ __('admin.users.all_roles') }}</option>
                  <option value="with_permissions">{{ __('admin.roles.with_permissions') }}</option>
                  <option value="without_permissions">{{ __('admin.roles.without_permissions') }}</option>
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
                  <button type="button" class="btn btn-success" id="exportRolesBtn">
                    <i class="fas fa-download"></i> {{ __('admin.roles.export_roles') }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-striped table-bordered" id="rolesTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Role Title</th>
                  <th>Permissions Count</th>
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

    #rolesTable th {
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

    .modal-xl {
      max-width: 1200px;
    }

    /* Permissions table styling */
    .permissions-table {
      margin-top: 20px;
    }

    .permissions-table th {
      background: #f8f9fa;
      font-weight: 600;
    }

    .permission-group {
      font-weight: 600;
      color: #495057;
    }

    .form-check-input:checked {
      background-color: #007bff;
      border-color: #007bff;
    }

    .permission-checkbox {
      margin: 2px 5px;
    }
  </style>
@endpush

<!-- Role Modals -->
<div class="modal fade" id="createRoleModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <form id="createRoleForm" method="POST">
      @csrf
      <input type="hidden" id="roleId" name="role_id">
      <input type="hidden" id="formMethod" name="_method" value="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Create New Role</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="createTitle">Role Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="createTitle" name="title" required placeholder="e.g., Admin, Manager, Editor">
                <div class="invalid-feedback"></div>
              </div>
            </div>
            <div class="col-md-6">
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
              <div class="form-group">
                <label class="form-control-label">
                  <strong>Permissions</strong>
                  <span class="text-danger">*</span>
                </label>
                <div class="permissions-table">
                  <table class="table table-bordered table-hover">
                    <thead>
                      <tr>
                        <th width="25%">Group</th>
                        <th class="text-center" width="10%">
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                            <label class="form-check-label" for="selectAll">All</label>
                          </div>
                        </th>
                        <th>Permissions</th>
                      </tr>
                    </thead>
                    <tbody id="permissionsTableBody">
                      @if(isset($permissions))
                        @foreach($permissions as $group => $groupPermissions)
                          <tr>
                            <td class="permission-group">{{ ucwords(str_replace('_', ' ', $group)) }}</td>
                            <td class="text-center">
                              <div class="form-check">
                                <input type="checkbox" class="form-check-input group-checkbox" data-group="{{ $group }}" id="group_{{ $group }}">
                                <label class="form-check-label" for="group_{{ $group }}"></label>
                              </div>
                            </td>
                            <td>
                              @foreach($groupPermissions as $permission)
                                <div class="form-check form-check-inline permission-checkbox">
                                  <input type="checkbox" class="form-check-input permission-item"
                                         name="permissions[]"
                                         value="{{ $permission->id }}"
                                         id="permission_{{ $permission->id }}"
                                         data-group="{{ $group }}">
                                  <label class="form-check-label" for="permission_{{ $permission->id }}">
                                    {{ $permission->title }}
                                  </label>
                                </div>
                              @endforeach
                            </td>
                          </tr>
                        @endforeach
                      @else
                        <tr>
                          <td colspan="3" class="text-center text-muted">No permissions found</td>
                        </tr>
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="createRoleBtn">
            <i class="fas fa-save" id="buttonIcon"></i> <span id="buttonText">Create Role</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="viewRoleModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Role Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="roleDetailsContent">
        <!-- Role details will be loaded here -->
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
      const table = $('#rolesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '{{ route('admin.roles.index') }}',
          type: 'GET'
        },
        columns: [{
            data: 'id',
            name: 'id'
          },
          {
            data: 'title',
            name: 'title'
          },
          {
            data: 'permissions_count',
            name: 'permissions_count',
            orderable: true,
            searchable: false
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

      // Permission checkbox functionality
      function initializePermissionCheckboxes() {
        // Select/Deselect All functionality
        $('#selectAll').on('change', function() {
          const isChecked = $(this).is(':checked');
          $('.permission-item, .group-checkbox').prop('checked', isChecked);
        });

        // Group checkbox functionality
        $('.group-checkbox').on('change', function() {
          const group = $(this).data('group');
          const isChecked = $(this).is(':checked');
          $(`.permission-item[data-group="${group}"]`).prop('checked', isChecked);
          updateSelectAllState();
        });

        // Individual permission checkbox functionality
        $('.permission-item').on('change', function() {
          const group = $(this).data('group');
          const groupPermissions = $(`.permission-item[data-group="${group}"]`);
          const checkedGroupPermissions = $(`.permission-item[data-group="${group}"]:checked`);

          // Update group checkbox state
          $(`.group-checkbox[data-group="${group}"]`).prop('checked',
            groupPermissions.length === checkedGroupPermissions.length
          );

          updateSelectAllState();
        });

        function updateSelectAllState() {
          const totalPermissions = $('.permission-item').length;
          const checkedPermissions = $('.permission-item:checked').length;
          $('#selectAll').prop('checked', totalPermissions === checkedPermissions);
        }
      }

      // Initialize permission checkboxes on modal shown
      $('#createRoleModal').on('shown.bs.modal', function() {
        initializePermissionCheckboxes();
      });

      // Function to reset modal to create mode
      function resetModalToCreateMode() {
        // Reset modal title and button
        $('#modalTitle').text('Create New Role');
        $('#buttonText').text('Create Role');
        $('#buttonIcon').removeClass('fa-edit').addClass('fa-save');
        $('#createRoleBtn').removeClass('btn-success').addClass('btn-primary');

        // Reset form method and role ID
        $('#formMethod').val('POST');
        $('#roleId').val('');

        // Clear all checkboxes
        $('.permission-item, .group-checkbox, #selectAll').prop('checked', false);
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
        if ($(this).attr('id') === 'createRoleModal') {
          resetModalToCreateMode();
        }
      });

      // Reset modal when create button is clicked
      $('button[data-bs-target="#createRoleModal"]').on('click', function() {
        resetModalToCreateMode();
      });

      // Status filter
      $('#statusFilter').on('change', function() {
        const status = $(this).val();
        table.column(3).search(status).draw();
      });

      // Permission filter
      $('#permissionFilter').on('change', function() {
        const filter = $(this).val();
        // This would need backend implementation for proper filtering
        showAlert('Permission filtering would be implemented in backend', 'info');
      });

      // Clear filters
      $('#clearFiltersBtn').on('click', function() {
        $('#statusFilter').val('');
        $('#permissionFilter').val('');
        table.search('').columns().search('').draw();
        showAlert('All filters cleared', 'info');
      });

      // Export roles
      $('#exportRolesBtn').on('click', function() {
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Exporting...').prop('disabled', true);

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

      // Create/Update Role
      $('#createRoleBtn').on('click', function() {
        const $btn = $(this);
        const originalText = $btn.html();
        const isEdit = $('#roleId').val() !== '';
        const buttonLoadingText = isEdit ? '<i class="fas fa-spinner fa-spin"></i> Updating...' :
          '<i class="fas fa-spinner fa-spin"></i> Creating...';

        $btn.html(buttonLoadingText).prop('disabled', true);

        // Clear previous validation errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData($('#createRoleForm')[0]);
        const url = isEdit ? '{{ route('admin.roles.update', ':id') }}'.replace(':id', $('#roleId').val()) :
          '{{ route('admin.roles.store') }}';

        $.ajax({
          url: url,
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          success: function(response) {
            if (response.success) {
              $('#createRoleModal').modal('hide');
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
                text: `An error occurred while ${isEdit ? 'updating' : 'creating'} the role.`,
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

      // View Role
      $(document).on('click', '.view-role', function() {
        const roleId = $(this).data('id');

        $.ajax({
          url: '{{ route('admin.roles.show', ':id') }}'.replace(':id', roleId),
          type: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          success: function(response) {
            if (response.success) {
              const role = response.role;
              let permissions = role.permissions.map(permission =>
                `<span class="badge bg-info text-white">${permission.title}</span>`
              ).join(' ') || '<span class="text-muted">No Permissions</span>';

              $('#roleDetailsContent').html(`
                <div class="row">
                  <div class="col-md-6">
                    <strong>ID:</strong> ${role.id}<br>
                    <strong>Title:</strong> <strong>${role.title}</strong><br>
                    <strong>Status:</strong> ${role.status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'}<br>
                    <strong>Created:</strong> ${new Date(role.created_at).toLocaleString()}<br>
                  </div>
                  <div class="col-md-6">
                    <strong>Updated:</strong> ${new Date(role.updated_at).toLocaleString()}<br>
                    <strong>Permissions Count:</strong> ${role.permissions.length}<br>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col-md-12">
                    <strong>Permissions:</strong><br>
                    <div class="mt-2">${permissions}</div>
                  </div>
                </div>
              `);
              $('#viewRoleModal').modal('show');
            }
          },
          error: function(xhr) {
            alert('Error loading role details');
          }
        });
      });

      // Edit Role
      $(document).on('click', '.edit-role', function() {
        const roleId = $(this).data('id');

        $.ajax({
          url: '{{ route('admin.roles.edit', ':id') }}'.replace(':id', roleId),
          type: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          success: function(response) {
            if (response.success) {
              const role = response.role;

              // Set modal mode to edit
              $('#modalTitle').text('Edit Role');
              $('#buttonText').text('Update Role');
              $('#buttonIcon').removeClass('fa-save').addClass('fa-edit');
              $('#createRoleBtn').removeClass('btn-primary').addClass('btn-success');

              // Set form method for update
              $('#formMethod').val('PUT');
              $('#roleId').val(role.id);

              // Fill form fields
              $('#createTitle').val(role.title);
              $('#createStatus').val(role.status ? '1' : '0');

              // Clear all checkboxes first
              $('.permission-item, .group-checkbox, #selectAll').prop('checked', false);

              // Check the role's permissions
              const rolePermissionIds = role.permissions.map(permission => permission.id.toString());
              rolePermissionIds.forEach(permissionId => {
                $(`#permission_${permissionId}`).prop('checked', true);
              });

              // Update group checkboxes based on selected permissions
              $('.group-checkbox').each(function() {
                const group = $(this).data('group');
                const groupPermissions = $(`.permission-item[data-group="${group}"]`);
                const checkedGroupPermissions = $(`.permission-item[data-group="${group}"]:checked`);

                $(this).prop('checked', groupPermissions.length === checkedGroupPermissions.length);
              });

              // Update select all checkbox
              const totalPermissions = $('.permission-item').length;
              const checkedPermissions = $('.permission-item:checked').length;
              $('#selectAll').prop('checked', totalPermissions === checkedPermissions);

              $('#createRoleModal').modal('show');
            }
          },
          error: function(xhr) {
            alert('Error loading role data');
          }
        });
      });

      // Delete Role
      $(document).on('click', '.delete-role', function() {
        const roleId = $(this).data('id');
        const roleTitle = $(this).closest('tr').find('td:eq(1)').text().trim();

        Swal.fire({
          title: 'Are you sure?',
          text: `You are about to delete role: ${roleTitle}. This action cannot be undone!`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, delete it!',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: '{{ route('admin.roles.destroy', ':id') }}'.replace(':id', roleId),
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
                const errorMessage = xhr.responseJSON?.message || 'An error occurred while deleting the role.';
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: errorMessage,
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

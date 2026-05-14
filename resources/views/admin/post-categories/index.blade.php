@extends('admin.layouts.master_layout')

@section('pageTitle', 'Post Categories')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Post Categories</h4>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                    <button type="button" class="btn btn-info" id="refreshTableBtn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="statusFilter">Status</label>
                        <select class="form-control" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label>&nbsp;</label>
                        <div class="d-flex">
                            <button type="button" class="btn btn-secondary" id="clearFiltersBtn">
                                <i class="fas fa-times"></i> Clear Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- DataTable -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="categoriesTable">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="30%">Name</th>
                                <th width="20%">Slug</th>
                                <th width="15%">Posts</th>
                                <th width="10%">Order</th>
                                <th width="10%">Status</th>
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

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="categoryForm" method="POST">
            @csrf
            <input type="hidden" id="categoryId" name="category_id">
            <input type="hidden" id="formMethod" name="_method" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" placeholder="Auto-generated">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Category</label>
                            <select class="form-control" id="parent_id" name="parent_id">
                                <option value="">None (Top Level)</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="0" min="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Icon (FontAwesome class)</label>
                            <div class="input-group">
                                <span class="input-group-text" id="iconPreview"><i class="fas fa-folder"></i></span>
                                <input type="text" class="form-control" id="icon" name="icon" placeholder="fa-folder">
                            </div>
                            <small class="text-muted">Example: fa-newspaper, fa-chart-line</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Color</label>
                            <input type="color" class="form-control form-control-color w-100" id="color" name="color" value="#3498db">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Category description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Save Category
                    </button>
                </div>
            </div>
        </form>
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
<style>
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
        text-align: center;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    let table = $('#categoriesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.post-categories.index") }}',
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name' },
            { data: 'slug' },
            { data: 'posts_count' },
            { data: 'sort_order' },
            { data: 'status' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[4, 'asc']],
        pageLength: 10,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
        }
    });

    // Update icon preview
    $('#icon').on('input', function() {
        let iconClass = $(this).val() || 'fa-folder';
        $('#iconPreview i').attr('class', 'fas ' + iconClass);
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        table.column(5).search($(this).val()).draw();
    });

    // Clear filters
    $('#clearFiltersBtn').on('click', function() {
        $('#statusFilter').val('');
        table.search('').columns().search('').draw();
    });

    // Refresh table
    $('#refreshTableBtn').on('click', function() {
        table.ajax.reload();
        toastr.info('Table refreshed!');
    });

    // Reset modal on close
    $('#categoryModal').on('hidden.bs.modal', function() {
        resetForm();
    });

    function resetForm() {
        $('#categoryForm')[0].reset();
        $('#categoryId').val('');
        $('#formMethod').val('POST');
        $('#modalTitle').text('Add New Category');
        $('#color').val('#3498db');
        $('#iconPreview i').attr('class', 'fas fa-folder');
    }

    // Submit form
    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();

        let categoryId = $('#categoryId').val();
        let url = categoryId
            ? '{{ route("admin.post-categories.index") }}/' + categoryId
            : '{{ route("admin.post-categories.store") }}';
        let method = categoryId ? 'PUT' : 'POST';

        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize() + '&_method=' + method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    $('#categoryModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                $('#loadingOverlay').hide();
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    Object.keys(errors).forEach(function(key) {
                        toastr.error(errors[key][0]);
                    });
                } else {
                    toastr.error(xhr.responseJSON?.message || 'An error occurred.');
                }
            }
        });
    });

    // Edit category
    $(document).on('click', '.edit-category', function() {
        let id = $(this).data('id');
        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: '{{ route("admin.post-categories.index") }}/' + id + '/edit',
            type: 'GET',
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    let category = response.category;
                    $('#categoryId').val(category.id);
                    $('#formMethod').val('PUT');
                    $('#modalTitle').text('Edit Category');
                    $('#name').val(category.name);
                    $('#slug').val(category.slug);
                    $('#parent_id').val(category.parent_id);
                    $('#description').val(category.description);
                    $('#icon').val(category.icon);
                    $('#color').val(category.color || '#3498db');
                    $('#sort_order').val(category.sort_order);
                    $('#is_active').val(category.is_active ? '1' : '0');

                    if (category.icon) {
                        $('#iconPreview i').attr('class', 'fas ' + category.icon);
                    }

                    $('#categoryModal').modal('show');
                }
            },
            error: function() {
                $('#loadingOverlay').hide();
                toastr.error('Failed to load category data.');
            }
        });
    });

    // Delete category
    $(document).on('click', '.delete-category', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This category will be deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loadingOverlay').css('display', 'flex');

                $.ajax({
                    url: '{{ route("admin.post-categories.index") }}/' + id,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#loadingOverlay').hide();
                        if (response.success) {
                            table.ajax.reload();
                            toastr.success(response.message);
                        }
                    },
                    error: function(xhr) {
                        $('#loadingOverlay').hide();
                        toastr.error(xhr.responseJSON?.message || 'Failed to delete category.');
                    }
                });
            }
        });
    });

    // Auto-generate slug
    $('#name').on('blur', function() {
        if (!$('#slug').val()) {
            let slug = $(this).val().toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
            $('#slug').val(slug);
        }
    });
});
</script>
@endpush

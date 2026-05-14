@extends('admin.layouts.master_layout')

@section('pageTitle', 'Blog Posts')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Blog Posts</h4>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#postModal">
                        <i class="fas fa-plus"></i> Add New Post
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
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="categoryFilter">Category</label>
                        <select class="form-control" id="categoryFilter">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->name }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
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
                    <table class="table table-striped table-bordered" id="postsTable">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="5%">Image</th>
                                <th width="25%">Title</th>
                                <th width="12%">Category</th>
                                <th width="15%">Tags</th>
                                <th width="10%">Author</th>
                                <th width="8%">Status</th>
                                <th width="8%">Views</th>
                                <th width="12%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Post Modal -->
<div class="modal fade" id="postModal" tabindex="-1">
    <div class="modal-dialog modal-xl"></div>
        <form id="postForm" method="POST">
            @csrf
            <input type="hidden" id="postId" name="post_id">
            <input type="hidden" id="formMethod" name="_method" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" class="form-control" id="slug" name="slug" placeholder="Auto-generated from title">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Excerpt</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" rows="3" placeholder="Brief description of the post..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Content</label>
                                <div id="content-editor" style="min-height: 300px; border: 1px solid #ddd;"></div>
                                <textarea name="content" id="content" style="display: none;"></textarea>
                            </div>
                        </div>
                        <!-- Right Column -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">Publish Settings</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="draft">Draft</option>
                                            <option value="published">Published</option>
                                            <option value="scheduled">Scheduled</option>
                                            <option value="archived">Archived</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Category</label>
                                        <select class="form-control" id="category_id" name="category_id">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tags</label>
                                        <select class="form-control" id="tags" name="tags[]" multiple>
                                            @foreach($tags as $tag)
                                                <option value="{{ $tag->id }}" data-color="{{ $tag->color ?? '#6c757d' }}">{{ $tag->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Publish Date</label>
                                        <input type="text" class="form-control" id="published_at" name="published_at" placeholder="Select date and time..." readonly>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1">
                                            <label class="form-check-label" for="is_featured">Featured Post</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="allow_comments" name="allow_comments" value="1" checked>
                                            <label class="form-check-label" for="allow_comments">Allow Comments</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mt-3">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0">Featured Image</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div id="featuredImageUploader"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Save Post
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Post Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
    .ql-editor {
        min-height: 200px;
    }
    /* Fix modal scrolling */
    #postModal {
        overflow-y: auto !important;
    }
    #postModal .modal-dialog {
        margin: 1.75rem auto;
        max-height: none;
    }
    #postModal .modal-content {
        max-height: none;
    }
    #postModal .modal-body {
        overflow-y: visible;
        max-height: none;
    }
    /* Fix panhaSelect input height */
    .panha-select-container .panha-select-input {
        min-height: 38px;
        height: auto;
        padding: 4px 8px;
    }
    .panha-select-container .panha-select-tags {
        gap: 4px;
        padding: 2px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<link href="{{ assetUrl() }}assets/backend/panhaDatetimePicker/panhaDateTimePicker.css" rel="stylesheet">
<link href="{{ assetUrl() }}assets/backend/phanaSelect-Vanilla/panha-select.css" rel="stylesheet">
<script src="{{ assetUrl() }}assets/backend/phanaSelect-Vanilla/panhaSelectVanilla.js"></script>
<script src="{{ assetUrl() }}assets/backend/panhaDatetimePicker/panhaVanillaDateTimePicker.js"></script>
<script src="{{ assetUrl() }}assets/backend/filemanager/panha-media-manager-lib.js"></script>
<script src="{{ assetUrl() }}assets/backend/filemanager/panha-media-upload-preview.js"></script>

<!-- Media Manager Modal for Featured Image -->
<x-media-manager-modal
    modalId="featuredImageMediaManager"
    title="Select Featured Image"
    :maxFiles="1"
    acceptedTypes="image/*"
/>
<script>
$(document).ready(function() {
    // Toastr-like notifications using SweetAlert2 Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
    const toastr = {
        success: (msg) => Toast.fire({ icon: 'success', title: msg }),
        error: (msg) => Toast.fire({ icon: 'error', title: msg }),
        warning: (msg) => Toast.fire({ icon: 'warning', title: msg }),
        info: (msg) => Toast.fire({ icon: 'info', title: msg })
    };

    // Helper function to safely clear the datetime picker
    function safeClearDatePicker() {
        if (publishedAtPicker && publishedAtPicker.picker) {
            try {
                publishedAtPicker.clearSelection();
            } catch (e) {
                $('#published_at').val('');
            }
        } else {
            $('#published_at').val('');
        }
    }

    // Initialize PanhaDateTimePicker for Publish Date field
    let publishedAtPicker = null;
    if (document.getElementById('published_at')) {
        publishedAtPicker = new PanhaDateTimePicker('#published_at', {
            mode: 'datetime',
            theme: 'light',
            enableTime: true,
            time_24hr: true,
            format: 'YYYY-MM-DD HH:mm',
            allowClear: true,
            showFooter: true,
            closeOnSelect: false,
            onChange: function(selectedDate) {
                // Date is automatically updated in the input
            }
        });
    }

    // Initialize panhaSelect for Tags field
    let tagsSelect = null;
    if (document.getElementById('tags')) {
        tagsSelect = panhaSelect('#tags', {
            placeholder: 'Select tags...',
            searchPlaceholder: 'Search tags...',
            allowClear: true,
            multiple: true,
            width: '100%',
            tags: false,
            closeOnSelect: false,
            templateResult: function(option) {
                const color = option.element?.dataset?.color || '#6c757d';
                return `<span class="badge" style="background-color: ${color}; color: #fff; margin-right: 5px;">●</span> ${option.text}`;
            },
            templateSelection: function(option) {
                const color = option.element?.dataset?.color || '#6c757d';
                return `<span style="background-color: ${color}; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 12px;">${option.text}</span>`;
            }
        });
    }

    // Initialize Featured Image Uploader
    let featuredImageUploader = null;
    if (document.getElementById('featuredImageUploader')) {
        featuredImageUploader = new PanhaMediaUploadPreview('#featuredImageUploader', {
            maxFiles: 1,
            maxFileSize: 5 * 1024 * 1024, // 5MB
            fieldName: 'featured_image_id',
            gridColumns: 1,
            enableDragDrop: false,
            showActionBar: false,
            texts: {
                title: '',
                uploadText: 'Click to select image',
                uploadSubtext: 'Select from media library',
                addMoreText: 'Change',
            },
            theme: {
                primaryColor: '#6c757d',
                primaryHover: '#5a6268',
            }
        });

        // Override upload zone click to open media manager
        setTimeout(() => {
            const uploadZone = document.querySelector('#featuredImageUploader .pmup-upload-zone');
            const fileInput = document.querySelector('#featuredImageUploader .pmup-file-input');

            if (uploadZone && fileInput) {
                fileInput.style.display = 'none';
                fileInput.disabled = true;

                uploadZone.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    window.PanhaMediaManagerModal.open({
                        modalId: 'featuredImageMediaManager',
                        defaultPath: '',
                        onSelect: function(files) {
                            if (files && files.length > 0 && featuredImageUploader) {
                                const file = files[0];
                                featuredImageUploader.loadExistingFiles([{
                                    url: file.url,
                                    name: file.name,
                                    size: file.size || 0,
                                    type: 'image/jpeg',
                                    id: file.id
                                }]);
                            }
                        }
                    });

                    return false;
                }, true);
            }
        }, 200);
    }

    // Initialize Quill editor
    let quill = new Quill('#content-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    // Sync Quill content to hidden textarea
    quill.on('text-change', function() {
        $('#content').val(quill.root.innerHTML);
    });

    // Initialize DataTable
    let table = $('#postsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.posts.index") }}',
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'image', orderable: false, searchable: false },
            { data: 'title' },
            { data: 'category' },
            { data: 'tags', orderable: false },
            { data: 'author' },
            { data: 'status' },
            { data: 'views' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
        }
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        table.column(6).search($(this).val()).draw();
    });

    // Category filter
    $('#categoryFilter').on('change', function() {
        table.column(3).search($(this).val()).draw();
    });

    // Clear filters
    $('#clearFiltersBtn').on('click', function() {
        $('#statusFilter').val('');
        $('#categoryFilter').val('');
        table.search('').columns().search('').draw();
    });

    // Refresh table
    $('#refreshTableBtn').on('click', function() {
        table.ajax.reload();
        toastr.info('Table refreshed!');
    });

    // Reset modal on close
    $('#postModal').on('hidden.bs.modal', function() {
        resetForm();
    });

    function resetForm() {
        $('#postForm')[0].reset();
        $('#postId').val('');
        $('#formMethod').val('POST');
        $('#modalTitle').text('Add New Post');
        quill.root.innerHTML = '';

        // Reset panhaSelect
        if (tagsSelect) {
            tagsSelect.clear();
        }

        // Reset Featured Image Uploader
        if (featuredImageUploader) {
            featuredImageUploader.clear();
        }

        // Reset PanhaDateTimePicker
        safeClearDatePicker();
    }

    // Submit form
    $('#postForm').on('submit', function(e) {
        e.preventDefault();

        let postId = $('#postId').val();
        let url = postId
            ? '{{ route("admin.posts.index") }}/' + postId
            : '{{ route("admin.posts.store") }}';
        let method = postId ? 'PUT' : 'POST';

        // Get form data
        let formData = $(this).serialize() + '&_method=' + method;

        // Add featured image ID from uploader
        if (featuredImageUploader) {
            const files = featuredImageUploader.getFiles();
            if (files.length > 0 && files[0].id) {
                formData += '&featured_image_id=' + files[0].id;
            }
        }

        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    $('#postModal').modal('hide');
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
                    toastr.error('An error occurred. Please try again.');
                }
            }
        });
    });

    // Edit post
    $(document).on('click', '.edit-post', function() {
        let id = $(this).data('id');
        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: '{{ route("admin.posts.index") }}/' + id + '/edit',
            type: 'GET',
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    let post = response.post;
                    $('#postId').val(post.id);
                    $('#formMethod').val('PUT');
                    $('#modalTitle').text('Edit Post');
                    $('#title').val(post.title);
                    $('#slug').val(post.slug);
                    $('#excerpt').val(post.excerpt);
                    $('#status').val(post.status);
                    $('#category_id').val(post.category_id);
                    $('#is_featured').prop('checked', post.is_featured);
                    $('#allow_comments').prop('checked', post.allow_comments);

                    // Set published_at using PanhaDateTimePicker
                    if (post.published_at) {
                        // Format: YYYY-MM-DD HH:mm
                        let dateStr = post.published_at.replace('T', ' ').substring(0, 16);
                        $('#published_at').val(dateStr);
                        // Trigger parseInputDate to update the picker state
                        if (publishedAtPicker) {
                            publishedAtPicker.parseInputDate(dateStr);
                        }
                    } else {
                        safeClearDatePicker();
                    }

                    // Set content
                    quill.root.innerHTML = post.content || '';

                    // Set tags using panhaSelect
                    if (tagsSelect) {
                        tagsSelect.clear();
                        if (post.tag_ids && Array.isArray(post.tag_ids)) {
                            post.tag_ids.forEach(tagId => {
                                tagsSelect.selectOption(tagId.toString());
                            });
                        }
                    }

                    // Set featured image using PanhaMediaUploadPreview
                    if (featuredImageUploader) {
                        featuredImageUploader.clear();
                        if (post.featured_image) {
                            featuredImageUploader.loadExistingFiles([{
                                url: post.featured_image.full_url,
                                name: post.featured_image.original_name || post.featured_image.file_name,
                                size: post.featured_image.file_size || 0,
                                type: post.featured_image.mime_type || 'image/jpeg',
                                id: post.featured_image.id
                            }]);
                        }
                    }

                    $('#postModal').modal('show');
                }
            },
            error: function() {
                $('#loadingOverlay').hide();
                toastr.error('Failed to load post data.');
            }
        });
    });

    // View post
    $(document).on('click', '.view-post', function() {
        let id = $(this).data('id');
        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: '{{ route("admin.posts.index") }}/' + id,
            type: 'GET',
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success) {
                    let post = response.post;
                    let html = `
                        <div class="row">
                            <div class="col-md-4">
                                <img src="${post.primary_image || 'https://via.placeholder.com/300x200?text=No+Image'}" class="img-fluid rounded">
                            </div>
                            <div class="col-md-8">
                                <h4>${post.title}</h4>
                                <p class="text-muted">${post.excerpt || 'No excerpt'}</p>
                                <p><strong>Status:</strong> <span class="badge bg-${post.status === 'published' ? 'success' : 'warning'}">${post.status}</span></p>
                                <p><strong>Category:</strong> ${post.category?.name || 'Uncategorized'}</p>
                                <p><strong>Views:</strong> ${post.views}</p>
                                <p><strong>Published:</strong> ${post.published_at || 'Not published'}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="content">${post.content || 'No content'}</div>
                    `;
                    $('#viewModalContent').html(html);
                    $('#viewModal').modal('show');
                }
            },
            error: function() {
                $('#loadingOverlay').hide();
                toastr.error('Failed to load post data.');
            }
        });
    });

    // Delete post
    $(document).on('click', '.delete-post', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This post will be deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loadingOverlay').css('display', 'flex');

                $.ajax({
                    url: '{{ route("admin.posts.index") }}/' + id,
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
                    error: function() {
                        $('#loadingOverlay').hide();
                        toastr.error('Failed to delete post.');
                    }
                });
            }
        });
    });

    // Auto-generate slug from title
    $('#title').on('blur', function() {
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

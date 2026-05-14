@extends('admin.layouts.master_layout')

@section('pageTitle', 'Add New Post')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Add New Post</h4>
                <div class="card-tools">
                    <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Posts
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form id="postForm" action="{{ route('admin.posts.store') }}" method="POST">
                    @csrf
                    <input type="hidden" id="featured_image_id" name="featured_image_id" value="{{ old('featured_image_id') }}">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                       id="slug" name="slug" value="{{ old('slug') }}" placeholder="Auto-generated from title">
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Excerpt</label>
                                <textarea class="form-control @error('excerpt') is-invalid @enderror"
                                          id="excerpt" name="excerpt" rows="3"
                                          placeholder="Brief description of the post...">{{ old('excerpt') }}</textarea>
                                @error('excerpt')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Content</label>
                                <div id="content-editor" style="min-height: 400px; border: 1px solid #ddd;"></div>
                                <textarea name="content" id="content" style="display: none;">{{ old('content') }}</textarea>
                                @error('content')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Right Column -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-cog"></i> Publish Settings</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                            <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                            <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                            <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Category</label>
                                        <select class="form-control" id="category_id" name="category_id">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tags</label>
                                        <select class="form-control" id="tags" name="tags[]" multiple>
                                            @foreach($tags as $tag)
                                                <option value="{{ $tag->id }}" data-color="{{ $tag->color ?? '#6c757d' }}" {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>
                                                    {{ $tag->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Publish Date</label>
                                        <input type="text" class="form-control" id="published_at"
                                               name="published_at" value="{{ old('published_at') }}" placeholder="Select date and time..." readonly>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="is_featured"
                                                   name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_featured">Featured Post</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="allow_comments"
                                                   name="allow_comments" value="1" {{ old('allow_comments', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="allow_comments">Allow Comments</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mt-3">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-image"></i> Featured Image</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div id="featuredImageUploader"></div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save"></i> Save Post
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<link href="{{ assetUrl() }}assets/backend/phanaSelect-Vanilla/panha-select.css" rel="stylesheet">
<link href="{{ assetUrl() }}assets/backend/panhaDatetimePicker/panhaDateTimePicker.css" rel="stylesheet">
<style>
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

    // Load existing content if any
    let existingContent = $('#content').val();
    if (existingContent) {
        quill.root.innerHTML = existingContent;
    }

    // Sync Quill content to hidden textarea
    quill.on('text-change', function() {
        $('#content').val(quill.root.innerHTML);
    });

    // Sync before form submit
    $('#postForm').on('submit', function() {
        $('#content').val(quill.root.innerHTML);

        // Set featured image ID from uploader
        if (featuredImageUploader) {
            const files = featuredImageUploader.getFiles();
            if (files.length > 0 && files[0].id) {
                $('#featured_image_id').val(files[0].id);
            }
        }
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

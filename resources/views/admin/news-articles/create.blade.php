@extends('admin.layouts.master_layout')

@section('pageTitle', __('admin.news.create'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/backend/PanhaNote-Editor/panha-note-editor.min.css') }}">
<style>
    .pmup-container {
        box-shadow: none !important;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('admin.news.create') }}</h4>
                <div class="card-tools">
                    <a href="{{ route('admin.news-articles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('common.back') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form id="articleForm" action="{{ route('admin.news-articles.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <!-- Left Column: Form Fields -->
                        <div class="col-lg-8">
                            <div class="row">
                                <!-- Title -->
                                <div class="col-md-8 mb-3">
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                           id="title" name="title" value="{{ old('title') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Category -->
                                <div class="col-md-4 mb-3">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('category') is-invalid @enderror"
                                           id="category" name="category" value="{{ old('category') }}"
                                           list="category-list" required>
                                    <datalist id="category-list">
                                        @foreach($categories ?? [] as $cat)
                                            <option value="{{ $cat }}">
                                        @endforeach
                                    </datalist>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Excerpt -->
                                <div class="col-md-12 mb-3">
                                    <label for="excerpt" class="form-label">Excerpt <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('excerpt') is-invalid @enderror"
                                              id="excerpt" name="excerpt" rows="3" required>{{ old('excerpt') }}</textarea>
                                    @error('excerpt')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Published Date -->
                                <div class="col-md-6 mb-3">
                                    <label for="published_at" class="form-label">Publish Date</label>
                                    <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror"
                                           id="published_at" name="published_at" value="{{ old('published_at') }}">
                                    <small class="text-muted">Leave empty to save as draft</small>
                                    @error('published_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Content -->
                                <div class="col-md-12 mb-3">
                                    <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                                    <div id="content-editor" style="min-height: 300px;"></div>
                                    <textarea name="content" id="content" style="display: none;">{{ old('content') }}</textarea>
                                    @error('content')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Image Upload -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-image"></i> Featured Image</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div id="featuredImageUploader"></div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-images"></i> Gallery Images</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div id="galleryImagesUploader"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('common.save') }}
                            </button>
                            <a href="{{ route('admin.news-articles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> {{ __('common.cancel') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/backend/filemanager/panha-media-manager-lib.js') }}"></script>
<script src="{{ asset('assets/backend/filemanager/panha-media-upload-preview.js') }}"></script>
<script src="{{ asset('assets/backend/PanhaNote-Editor/panha-note-editor.bundle.js') }}"></script>

<!-- Media Manager Modal for Featured Image -->
<x-media-manager-modal
    modalId="featuredImageMediaManager"
    title="Select Featured Image"
    :maxFiles="1"
    acceptedTypes="image/*"
/>

<!-- Media Manager Modal for Gallery Images -->
<x-media-manager-modal
    modalId="galleryImagesMediaManager"
    title="Select Gallery Images"
    :maxFiles="10"
    acceptedTypes="image/*"
/>

<script>
let contentEditor = null;

// Initialize Panha Note Editor when ready
document.addEventListener('panhaNoteEditorReady', function() {
    contentEditor = PanhaNoteEditor.init('#content-editor', {
        placeholder: 'Write your article content here...',
        minHeight: '300px',
        toolbar: 'full',
        onChange: function(content) {
            document.getElementById('content').value = content;
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Featured Image Uploader (Single)
    const featuredImageUploader = new PanhaMediaUploadPreview('#featuredImageUploader', {
        maxFiles: 1,
        maxFileSize: 5 * 1024 * 1024,
        fieldName: 'featured_image',
        gridColumns: 1,
        enableDragDrop: false,
        texts: {
            title: '',
            uploadText: 'Click to select from library',
            uploadSubtext: 'Select from media library',
            addMoreText: 'Change',
        },
        theme: {
            primaryColor: '#0d6efd',
            primaryHover: '#0b5ed7',
        }
    });

    // Override Featured Image upload zone click to open media manager
    setTimeout(() => {
        const featuredUploadZone = document.querySelector('#featuredImageUploader .pmup-upload-zone');
        const featuredFileInput = document.querySelector('#featuredImageUploader .pmup-file-input');

        if (featuredUploadZone && featuredFileInput) {
            featuredFileInput.style.display = 'none';
            featuredFileInput.disabled = true;

            featuredUploadZone.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                window.PanhaMediaManagerModal.open({
                    modalId: 'featuredImageMediaManager',
                    defaultPath: '',
                    onSelect: function(files) {
                        if (files && files.length > 0) {
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

    // Initialize Gallery Images Uploader (Multiple)
    const galleryImagesUploader = new PanhaMediaUploadPreview('#galleryImagesUploader', {
        maxFiles: 10,
        maxFileSize: 5 * 1024 * 1024,
        fieldName: 'gallery_images[]',
        gridColumns: 3,
        enableDragDrop: false,
        texts: {
            title: '',
            uploadText: 'Click to select from library',
            uploadSubtext: 'Select up to 10 images',
            addMoreText: 'Add More',
        },
        theme: {
            primaryColor: '#0dcaf0',
            primaryHover: '#31d2f2',
        }
    });

    // Function to attach Media Manager handlers for Gallery Images
    function attachGalleryMediaManagerHandlers() {
        setTimeout(() => {
            const galleryUploadZone = document.querySelector('#galleryImagesUploader .pmup-upload-zone');
            const galleryFileInput = document.querySelector('#galleryImagesUploader .pmup-file-input');
            const addMoreButton = document.querySelector('#galleryImagesUploader .pmup-add-more-btn');

            if (galleryUploadZone && galleryFileInput) {
                galleryFileInput.style.display = 'none';
                galleryFileInput.disabled = true;

                galleryUploadZone.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    window.PanhaMediaManagerModal.open({
                        modalId: 'galleryImagesMediaManager',
                        defaultPath: '',
                        onSelect: function(files) {
                            if (files && files.length > 0) {
                                const existingFiles = files.map(file => ({
                                    url: file.url,
                                    name: file.name,
                                    size: file.size || 0,
                                    type: 'image/jpeg',
                                    id: file.id
                                }));
                                galleryImagesUploader.loadExistingFiles(existingFiles);
                                // Re-attach handlers after loading new files
                                attachGalleryMediaManagerHandlers();
                            }
                        }
                    });

                    return false;
                }, true);
            }

            // Attach handler to "Add More" button if it exists
            if (addMoreButton) {
                addMoreButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    window.PanhaMediaManagerModal.open({
                        modalId: 'galleryImagesMediaManager',
                        defaultPath: '',
                        onSelect: function(files) {
                            if (files && files.length > 0) {
                                const existingFiles = files.map(file => ({
                                    url: file.url,
                                    name: file.name,
                                    size: file.size || 0,
                                    type: 'image/jpeg',
                                    id: file.id
                                }));
                                galleryImagesUploader.loadExistingFiles(existingFiles);
                                // Re-attach handlers after loading new files
                                attachGalleryMediaManagerHandlers();
                            }
                        }
                    });

                    return false;
                }, true);
            }
        }, 300);
    }

    // Initial attachment
    attachGalleryMediaManagerHandlers();

    // Form submission handler
    document.getElementById('articleForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Sync content from editor before submit
        if (contentEditor) {
            document.getElementById('content').value = contentEditor.getContent();
        }

        const formData = new FormData(this);

        // Add featured image ID from Media Manager
        const featuredFiles = featuredImageUploader.getFiles();
        if (featuredFiles.length > 0 && featuredFiles[0].id) {
            formData.append('featured_image_id', featuredFiles[0].id);
        }

        // Add gallery image IDs from Media Manager
        const galleryFiles = galleryImagesUploader.getFiles();
        galleryFiles.forEach(file => {
            if (file.id) {
                formData.append('gallery_image_ids[]', file.id);
            }
        });

        // Submit form via AJAX
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '{{ __("common.success") }}',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = '{{ route("admin.news-articles.index") }}';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("common.error") }}',
                    text: data.message || 'Something went wrong!'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: '{{ __("common.error") }}',
                text: 'Failed to create article!'
            });
        });
    });
});
</script>
@endpush

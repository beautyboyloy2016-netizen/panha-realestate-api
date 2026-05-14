@extends('admin.layouts.master_layout')

@section('pageTitle', __('admin.projects.create'))

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
                <h4 class="card-title">{{ __('admin.projects.create') }}</h4>
                <div class="card-tools">
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('common.back') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form id="projectForm" action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <!-- Left Column: Form Fields -->
                        <div class="col-lg-8">
                            <div class="row">
                                <!-- Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">{{ __('admin.projects.name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Developer -->
                                <div class="col-md-6 mb-3">
                                    <label for="developer" class="form-label">Developer <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('developer') is-invalid @enderror"
                                           id="developer" name="developer" value="{{ old('developer') }}" required>
                                    @error('developer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Location -->
                                <div class="col-md-6 mb-3">
                                    <label for="location" class="form-label">{{ __('admin.projects.location') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror"
                                           id="location" name="location" value="{{ old('location') }}" required>
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Units -->
                                <div class="col-md-6 mb-3">
                                    <label for="units" class="form-label">{{ __('admin.projects.units') }} <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('units') is-invalid @enderror"
                                           id="units" name="units" value="{{ old('units') }}" min="1" required>
                                    @error('units')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Price From -->
                                <div class="col-md-6 mb-3">
                                    <label for="price_from" class="form-label">{{ __('admin.properties.price') }} From <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('price_from') is-invalid @enderror"
                                           id="price_from" name="price_from" value="{{ old('price_from') }}"
                                           placeholder="$50,000" required>
                                    @error('price_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Completion -->
                                <div class="col-md-6 mb-3">
                                    <label for="completion" class="form-label">{{ __('admin.projects.completion') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('completion') is-invalid @enderror"
                                           id="completion" name="completion" value="{{ old('completion') }}"
                                           placeholder="Q4 2025" required>
                                    @error('completion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Rental Yield -->
                                <div class="col-md-6 mb-3">
                                    <label for="rental_yield" class="form-label">{{ __('admin.projects.yield') }} (%)</label>
                                    <input type="number" class="form-control @error('rental_yield') is-invalid @enderror"
                                           id="rental_yield" name="rental_yield" value="{{ old('rental_yield') }}"
                                           min="0" max="100" step="0.1" placeholder="7.5">
                                    @error('rental_yield')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Featured -->
                                <div class="col-md-6 mb-3 d-flex align-items-center">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="featured" name="featured"
                                               value="1" {{ old('featured') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="featured">
                                            {{ __('admin.properties.featured') }}
                                        </label>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="col-md-12 mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <div id="description-editor" style="min-height: 150px;"></div>
                                    <textarea name="description" id="description" style="display: none;">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Image Upload -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-image"></i> Project Image</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div id="projectImageUploader"></div>
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
                            <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
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

<!-- Media Manager Modal for Project Image -->
<x-media-manager-modal
    modalId="projectImageMediaManager"
    title="Select Project Image"
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
let descriptionEditor = null;

// Initialize Panha Note Editor when ready
document.addEventListener('panhaNoteEditorReady', function() {
    descriptionEditor = PanhaNoteEditor.init('#description-editor', {
        placeholder: 'Enter project description...',
        minHeight: '150px',
        toolbar: 'full',
        onChange: function(content) {
            document.getElementById('description').value = content;
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Project Image Uploader (Single)
    const projectImageUploader = new PanhaMediaUploadPreview('#projectImageUploader', {
        maxFiles: 1,
        maxFileSize: 5 * 1024 * 1024,
        fieldName: 'project_image',
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

    // Override Project Image upload zone click to open media manager
    setTimeout(() => {
        const projectUploadZone = document.querySelector('#projectImageUploader .pmup-upload-zone');
        const projectFileInput = document.querySelector('#projectImageUploader .pmup-file-input');

        if (projectUploadZone && projectFileInput) {
            projectFileInput.style.display = 'none';
            projectFileInput.disabled = true;

            projectUploadZone.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                window.PanhaMediaManagerModal.open({
                    modalId: 'projectImageMediaManager',
                    defaultPath: '',
                    onSelect: function(files) {
                        if (files && files.length > 0) {
                            const file = files[0];
                            projectImageUploader.loadExistingFiles([{
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
    document.getElementById('projectForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Sync description from editor before submit
        if (descriptionEditor) {
            document.getElementById('description').value = descriptionEditor.getContent();
        }

        const formData = new FormData(this);

        // Add project image ID from Media Manager
        const projectFiles = projectImageUploader.getFiles();
        if (projectFiles.length > 0 && projectFiles[0].id) {
            formData.append('featured_image_id', projectFiles[0].id);
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
                    window.location.href = '{{ route("admin.projects.index") }}';
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
                text: 'Failed to create project!'
            });
        });
    });
});
</script>
@endpush

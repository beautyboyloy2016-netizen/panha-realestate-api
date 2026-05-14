@extends('admin.layouts.master_layout')

@section('pageTitle', 'Edit Property - ' . $property->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/backend/phanaSelect-Vanilla/panha-select.css') }}">
<link rel="stylesheet" href="{{ asset('assets/backend/PanhaNote-Editor/panha-note-editor.min.css') }}">
<style>
    .pmup-container {
        box-shadow: none !important;
    }
    .pmup-gallery-grid {
        gap: 8px;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.properties.index') }}">Properties</a></li>
                <li class="breadcrumb-item active">Edit: {{ Str::limit($property->title, 30) }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="fas fa-edit me-2"></i>Edit Property
                </h4>
                <div>
                    <a href="{{ route('admin.properties.show', $property->id) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form id="propertyForm" action="{{ route('admin.properties.update', $property->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="propertyId" name="property_id" value="{{ $property->id }}">

                    <div class="row">
                        <!-- Left Column: Form Fields (9 columns) -->
                        <div class="col-lg-9">
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                           name="title" id="title" value="{{ old('title', $property->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Property Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('property_type') is-invalid @enderror"
                                            name="property_type" id="property_type" required>
                                        <option value="">Select Type</option>
                                        @foreach($propertyTypes as $type)
                                            <option value="{{ $type->name }}" {{ old('property_type', $property->property_type) == $type->name ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('property_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Listing Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('listing_type') is-invalid @enderror"
                                            name="listing_type" id="listing_type" required>
                                        <option value="">Select Type</option>
                                        <option value="For Sale" {{ old('listing_type', $property->listing_type) == 'For Sale' ? 'selected' : '' }}>For Sale</option>
                                        <option value="For Rent" {{ old('listing_type', $property->listing_type) == 'For Rent' ? 'selected' : '' }}>For Rent</option>
                                    </select>
                                    @error('listing_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Price <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror"
                                           name="price" id="price" step="0.01" value="{{ old('price', $property->price) }}" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Bedrooms <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('bedrooms') is-invalid @enderror"
                                           name="bedrooms" id="bedrooms" min="0" value="{{ old('bedrooms', $property->bedrooms) }}" required>
                                    @error('bedrooms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Bathrooms <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('bathrooms') is-invalid @enderror"
                                           name="bathrooms" id="bathrooms" min="0" value="{{ old('bathrooms', $property->bathrooms) }}" required>
                                    @error('bathrooms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Area <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('area') is-invalid @enderror"
                                           name="area" id="area" step="0.01" value="{{ old('area', $property->area) }}" required>
                                    @error('area')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Unit</label>
                                    <select class="form-control" name="area_unit" id="area_unit">
                                        <option value="sqm" {{ old('area_unit', $property->area_unit) == 'sqm' ? 'selected' : '' }}>sqm</option>
                                        <option value="sqft" {{ old('area_unit', $property->area_unit) == 'sqft' ? 'selected' : '' }}>sqft</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Owner <span class="text-danger">*</span></label>
                                    <select class="form-control @error('user_id') is-invalid @enderror"
                                            name="user_id" id="user_id" required>
                                        <option value="">Select Owner</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id', $property->user_id) == $user->id ? 'selected' : '' }}>
                                                {{ $user->full_name ?? $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Location -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror"
                                           name="city" id="city" value="{{ old('city', $property->city) }}" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">District</label>
                                    <input type="text" class="form-control @error('district') is-invalid @enderror"
                                           name="district" id="district" value="{{ old('district', $property->district) }}">
                                    @error('district')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Location/Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror"
                                           name="location" id="location" value="{{ old('location', $property->location) }}" required>
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Latitude</label>
                                    <input type="number" class="form-control" name="latitude" id="latitude"
                                           step="0.00000001" value="{{ old('latitude', $property->latitude) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Longitude</label>
                                    <input type="number" class="form-control" name="longitude" id="longitude"
                                           step="0.00000001" value="{{ old('longitude', $property->longitude) }}">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Is Featured</label>
                                    <select class="form-control" name="is_featured" id="is_featured">
                                        <option value="0" {{ old('is_featured', $property->is_featured) == 0 ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ old('is_featured', $property->is_featured) == 1 ? 'selected' : '' }}>Yes</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Is Available</label>
                                    <select class="form-control" name="is_available" id="is_available">
                                        <option value="1" {{ old('is_available', $property->is_available) == 1 ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ old('is_available', $property->is_available) == 0 ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>

                                <!-- Features -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Features (Select multiple)</label>
                                    @php
                                        $selectedFeatures = old('features', $property->features ?? []);
                                        if (is_string($selectedFeatures)) {
                                            $selectedFeatures = json_decode($selectedFeatures, true) ?? [];
                                        }
                                    @endphp
                                    <select class="form-control" name="features[]" id="features" multiple>
                                        @foreach($features as $feature)
                                            <option value="{{ $feature->name }}" {{ in_array($feature->name, $selectedFeatures) ? 'selected' : '' }}>
                                                {{ $feature->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Description -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                    <div id="description-editor" style="min-height: 200px;"></div>
                                    <textarea name="description" id="description" style="display: none;">{{ old('description', $property->description) }}</textarea>
                                    @error('description')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Image Uploads (3 columns) -->
                        <div class="col-lg-3">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-image"></i> Featured Image</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div id="featuredImageUploader"></div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-images"></i> Gallery Images</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div id="galleryImagesUploader"></div>
                                </div>
                            </div>

                            <!-- Property Info Card -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Property Info</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><small class="text-muted">ID:</small> <strong>{{ $property->id }}</strong></p>
                                    <p class="mb-1"><small class="text-muted">Views:</small> <span class="badge bg-secondary">{{ $property->views }}</span></p>
                                    <p class="mb-1"><small class="text-muted">Created:</small> {{ $property->created_at->format('M d, Y') }}</p>
                                    <p class="mb-0"><small class="text-muted">Updated:</small> {{ $property->updated_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <i class="fas fa-save"></i> Update Property
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/backend/phanaSelect-Vanilla/panhaSelectVanilla.js') }}"></script>
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
let descriptionEditor = null;
let featuredImageUploader = null;
let galleryImagesUploader = null;

// Store initial description content
const initialDescription = @json(old('description', $property->description));

// Initialize Panha Note Editor when ready
document.addEventListener('panhaNoteEditorReady', function() {
    descriptionEditor = PanhaNoteEditor.init('#description-editor', {
        placeholder: 'Enter property description...',
        minHeight: '200px',
        toolbar: 'full',
        initialContent: initialDescription,
        onChange: function(content) {
            document.getElementById('description').value = content;
        }
    });

    // If the editor doesn't support initialContent option, set it manually
    if (initialDescription && descriptionEditor) {
        setTimeout(() => {
            if (typeof descriptionEditor.setContent === 'function') {
                descriptionEditor.setContent(initialDescription);
            } else if (document.querySelector('#description-editor .pn-editor-content')) {
                document.querySelector('#description-editor .pn-editor-content').innerHTML = initialDescription;
            }
            document.getElementById('description').value = initialDescription;
        }, 100);
    }
});

$(document).ready(function() {
    // Initialize panhaSelectVanilla for Features field
    let featuresSelect = null;
    if (document.getElementById('features')) {
        featuresSelect = panhaSelect('#features', {
            placeholder: 'Select features...',
            searchPlaceholder: 'Search features...',
            allowClear: true,
            multiple: true,
            width: '100%',
            tags: false,
            closeOnSelect: false
        });
    }

    // Initialize Featured Image Uploader (Single Image)
    featuredImageUploader = new PanhaMediaUploadPreview('#featuredImageUploader', {
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

    // Load existing featured image
    @if($property->entityMedia->firstWhere('zone', 'primary_image'))
        @php
            $featuredMedia = $property->entityMedia->firstWhere('zone', 'primary_image');
        @endphp
        @if($featuredMedia && $featuredMedia->media)
            featuredImageUploader.loadExistingFiles([{
                url: '{{ $featuredMedia->media->full_url }}',
                name: '{{ $featuredMedia->media->original_name }}',
                size: {{ $featuredMedia->media->file_size ?? 0 }},
                type: '{{ $featuredMedia->media->mime_type ?? "image/jpeg" }}',
                id: {{ $featuredMedia->media->id }}
            }]);
        @endif
    @endif

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

    // Initialize Gallery Images Uploader (Multiple Images)
    galleryImagesUploader = new PanhaMediaUploadPreview('#galleryImagesUploader', {
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

    // Load existing gallery images
    @if($property->entityMedia->where('zone', 'gallery')->count() > 0)
        @php
            $galleryMedia = $property->entityMedia->where('zone', 'gallery');
        @endphp
        galleryImagesUploader.loadExistingFiles([
            @foreach($galleryMedia as $em)
                @if($em->media)
                {
                    url: '{{ $em->media->full_url }}',
                    name: '{{ $em->media->original_name }}',
                    size: {{ $em->media->file_size ?? 0 }},
                    type: '{{ $em->media->mime_type ?? "image/jpeg" }}',
                    id: {{ $em->media->id }}
                },
                @endif
            @endforeach
        ]);
    @endif

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
                            if (files && files.length > 0 && galleryImagesUploader) {
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
                            if (files && files.length > 0 && galleryImagesUploader) {
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

    // Initial attachment after loading existing images
    attachGalleryMediaManagerHandlers();

    // Form submission
    $('#propertyForm').on('submit', function(e) {
        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $('#submitBtn');
        const originalBtnText = $submitBtn.html();

        // Collect featured image IDs
        let featuredImageIds = [];
        if (featuredImageUploader) {
            const featuredFiles = featuredImageUploader.getFiles();
            featuredImageIds = featuredFiles.map(f => f.id).filter(id => id);
        }

        // Collect gallery image IDs
        let galleryImageIds = [];
        if (galleryImagesUploader) {
            const galleryFiles = galleryImagesUploader.getFiles();
            galleryImageIds = galleryFiles.map(f => f.id).filter(id => id);
        }

        // Create FormData
        const formData = new FormData(this);
        formData.delete('featured_image');
        formData.delete('gallery_images[]');

        // Add image IDs
        if (featuredImageIds.length > 0) {
            formData.append('featured_image_id', featuredImageIds[0]);
        }
        galleryImageIds.forEach(id => {
            formData.append('gallery_image_ids[]', id);
        });

        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Property updated successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("admin.properties.index") }}';
                    });
                } else {
                    Swal.fire('Error!', response.message || 'Failed to update property', 'error');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMsg = '<ul class="text-left">';
                    Object.keys(errors).forEach(field => {
                        errorMsg += '<li>' + errors[field][0] + '</li>';
                    });
                    errorMsg += '</ul>';
                    Swal.fire('Validation Error', errorMsg, 'error');
                } else {
                    Swal.fire('Error!', 'An error occurred while updating the property', 'error');
                }
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });
});
</script>
@endpush

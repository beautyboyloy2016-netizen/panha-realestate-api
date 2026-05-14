@extends('admin.layouts.master_layout')

@section('pageTitle', __('admin.properties.title'))

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

    /* Fix modal scroll - Override Bootstrap modal-fullscreen */
    .modal-fullscreen {
      max-height: 100vh !important;
      overflow-y: auto !important;
    }

    .modal-fullscreen .modal-content {
      height: auto !important;
      min-height: 100vh;
      border: 0;
      border-radius: 0;
    }

    .modal-fullscreen .modal-body {
      overflow-y: visible !important;
      overflow-x: hidden;
    }
  </style>
@endpush

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ __('admin.properties.title') }}</h4>
          <div class="card-tools">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#propertyModal"
              id="createPropertyBtn">
              <i class="fas fa-plus"></i> Add New Property
            </button>
            <button type="button" class="btn btn-info" id="refreshTableBtn">
              <i class="fas fa-sync-alt"></i> Refresh
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-bordered" id="propertiesTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Image</th>
                  <th>Title</th>
                  <th>Listing</th>
                  <th>Price/Area</th>
                  <th>Location</th>
                  <th>Details</th>
                  <th>Owner</th>
                  <th>Status</th>
                  <th>Views</th>
                  <th>Date</th>
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

  <!-- Property Modal -->
  <div class="modal fade" id="propertyModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
      <form id="propertyForm" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="propertyId" name="property_id">
        <input type="hidden" id="formMethod" name="_method" value="POST">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Create New Property</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <!-- Left Column: Form Fields (9 columns) -->
              <div class="col-lg-9">
                <div class="row">
                  <!-- Basic Information -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title" id="title" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Property Type <span class="text-danger">*</span></label>
                    <select class="form-control" name="property_type" id="property_type" required>
                      <option value="">Select Type</option>
                      @foreach ($propertyTypes as $type)
                        <option value="{{ $type->name }}">{{ $type->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Listing Type <span class="text-danger">*</span></label>
                    <select class="form-control" name="listing_type" id="listing_type" required>
                      <option value="">Select Type</option>
                      <option value="For Sale">For Sale</option>
                      <option value="For Rent">For Rent</option>
                    </select>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Price <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="price" id="price" step="0.01" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Bedrooms <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="bedrooms" id="bedrooms" min="0" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Bathrooms <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="bathrooms" id="bathrooms" min="0" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Area <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="area" id="area" step="0.01" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Unit</label>
                    <select class="form-control" name="area_unit" id="area_unit">
                      <option value="sqm">sqm</option>
                      <option value="sqft">sqft</option>
                    </select>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Owner <span class="text-danger">*</span></label>
                    <select class="form-control" name="user_id" id="user_id" required>
                      <option value="">Select Owner</option>
                      @foreach (\App\Models\User::all() as $user)
                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                      @endforeach
                    </select>
                  </div>

                  <!-- Location -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">City <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="city" id="city" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">District</label>
                    <input type="text" class="form-control" name="district" id="district">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Location/Address <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="location" id="location" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Latitude</label>
                    <input type="number" class="form-control" name="latitude" id="latitude" step="0.00000001">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Longitude</label>
                    <input type="number" class="form-control" name="longitude" id="longitude" step="0.00000001">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status" id="status">
                      <option value="available">Available</option>
                      <option value="sold">Sold</option>
                      <option value="rented">Rented</option>
                      <option value="pending">Pending</option>
                    </select>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Is Featured</label>
                    <select class="form-control" name="is_featured" id="is_featured">
                      <option value="0">No</option>
                      <option value="1">Yes</option>
                    </select>
                  </div>
                  <!-- Features -->
                  <div class="col-md-8 mb-3">
                    <label class="form-label">Features (Select multiple)</label>
                    <select class="form-control" name="features[]" id="features" multiple>
                      @foreach ($features as $feature)
                        <option value="{{ $feature->name }}">{{ $feature->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <!-- Description -->
                  <div class="col-md-12 mb-3">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <div id="description-editor" style="min-height: 100px;"></div>
                    <textarea name="description" id="description" style="display: none;"></textarea>
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

              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="submitBtn">
              <i class="fas fa-save"></i> Save Property
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="{{ asset('assets/backend/phanaSelect-Vanilla/panhaSelectVanilla.js') }}"></script>
  <script src="{{ asset('assets/backend/filemanager/panha-media-manager-lib.js') }}"></script>
  <script src="{{ asset('assets/backend/filemanager/panha-media-upload-preview.js') }}"></script>
  <script src="{{ asset('assets/backend/PanhaNote-Editor/panha-note-editor.bundle.js') }}"></script>

  <!-- Media Manager Modal for Featured Image -->
  <x-media-manager-modal modalId="featuredImageMediaManager" title="Select Featured Image" :maxFiles="1"
    acceptedTypes="image/*" />

  <!-- Media Manager Modal for Gallery Images -->
  <x-media-manager-modal modalId="galleryImagesMediaManager" title="Select Gallery Images" :maxFiles="10"
    acceptedTypes="image/*" />

  <script>
    let descriptionEditor = null;

    // Initialize Panha Note Editor when ready
    document.addEventListener('panhaNoteEditorReady', function() {
      descriptionEditor = PanhaNoteEditor.init('#description-editor', {
        placeholder: 'Enter property description...',
        minHeight: '100px',
        toolbar: 'full',
        onChange: function(content) {
          document.getElementById('description').value = content;
        }
      });
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
      const featuredImageUploader = new PanhaMediaUploadPreview('#featuredImageUploader', {
        maxFiles: 1,
        maxFileSize: 5 * 1024 * 1024, // 5MB
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
      const galleryImagesUploader = new PanhaMediaUploadPreview('#galleryImagesUploader', {
        maxFiles: 10,
        maxFileSize: 5 * 1024 * 1024, // 5MB
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

      // Override Gallery Images upload zone click to open media manager
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

      // Initial attachment
      attachGalleryMediaManagerHandlers();

      // Reset image uploaders function
      function resetImageUploaders() {
        featuredImageUploader.clear();
        galleryImagesUploader.clear();
        // Re-attach gallery handlers after clearing
        attachGalleryMediaManagerHandlers();
      }

      // Initialize DataTable
      const table = $('#propertiesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '{{ route('admin.properties.index') }}',
          type: 'GET',
        },
        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
          },
          {
            data: 'image',
            name: 'image',
            orderable: false,
            searchable: false
          },
          {
            data: 'title',
            name: 'title'
          },
          {
            data: 'listing',
            name: 'listing'
          },
          {
            data: 'price',
            name: 'price'
          },
          {
            data: 'location',
            name: 'location'
          },
          {
            data: 'details',
            name: 'details',
            orderable: false,
            searchable: false
          },
          {
            data: 'owner',
            name: 'owner'
          },
          {
            data: 'status',
            name: 'status',
            orderable: false,
            searchable: false
          },
          {
            data: 'views',
            name: 'views',
            orderable: false,
            searchable: false
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
        responsive: true,
        pageLength: 25,
      });

      // Refresh table
      $('#refreshTableBtn').click(function() {
        table.ajax.reload();
      });

      // Create new property - reset form
      $('#createPropertyBtn').click(function() {
        $('#propertyForm')[0].reset();
        $('#propertyId').val('');
        $('#formMethod').val('POST');
        $('#modalTitle').text('Create New Property');
        $('#submitBtn').html('<i class="fas fa-save"></i> Save Property');
        $('#propertyForm').attr('action', '{{ route('admin.properties.store') }}');

        // Reset panhaSelect
        if (featuresSelect) {
          featuresSelect.clear();
        }

        // Reset image uploaders
        resetImageUploaders();

        // Reset description editor
        if (descriptionEditor) {
          descriptionEditor.setContent('');
        }
      });

      // Edit property
      $(document).on('click', '.edit-property', function(e) {
        e.preventDefault();
        const propertyId = $(this).data('id');

        // Clear image uploaders first before loading new data
        resetImageUploaders();

        $.ajax({
          url: '/admin/properties/' + propertyId + '/edit',
          type: 'GET',
          success: function(response) {
            $('#propertyId').val(response.id);
            $('#formMethod').val('PUT');
            $('#modalTitle').text('Edit Property');
            $('#submitBtn').html('<i class="fas fa-save"></i> Update Property');
            $('#propertyForm').attr('action', '/admin/properties/' + response.id);

            // Fill form fields
            $('#title').val(response.title);
            $('#property_type').val(response.property_type);
            $('#listing_type').val(response.listing_type);
            $('#price').val(response.price);
            $('#bedrooms').val(response.bedrooms);
            $('#bathrooms').val(response.bathrooms);
            $('#area').val(response.area);
            $('#area_unit').val(response.area_unit);
            $('#city').val(response.city);
            $('#district').val(response.district);
            $('#location').val(response.location);
            $('#latitude').val(response.latitude);
            $('#longitude').val(response.longitude);

            // Set description in editor
            if (descriptionEditor) {
              descriptionEditor.setContent(response.description || '');
            } else {
              $('#description').val(response.description);
            }

            $('#user_id').val(response.user_id);
            $('#status').val(response.status || 'available');
            $('#is_featured').val(response.is_featured ? 1 : 0);

            // Handle features array
            if (response.features) {
              const features = typeof response.features === 'string' ?
                JSON.parse(response.features) :
                response.features;

              // Update the select element
              $('#features').val(features);

              // Update panhaSelect instance
              if (featuresSelect) {
                featuresSelect.clear();
                if (Array.isArray(features)) {
                  features.forEach(value => {
                    featuresSelect.selectOption(value);
                  });
                }
              }
            }

            // Load featured image if exists
            if (response.featured_image) {
              featuredImageUploader.loadExistingFiles([{
                url: response.featured_image.full_url || response.featured_image.file_url,
                name: response.featured_image.original_name || response.featured_image.file_name,
                size: response.featured_image.file_size || 0,
                type: response.featured_image.mime_type || 'image/jpeg',
                id: response.featured_image.id
              }]);
            }

            // Load gallery images if exist
            if (response.gallery_images && response.gallery_images.length > 0) {
              const galleryFiles = response.gallery_images.map(item => ({
                url: item.media?.full_url || item.media?.file_url || item.full_url,
                name: item.media?.original_name || item.media?.file_name || item.original_name,
                size: item.media?.file_size || item.file_size || 0,
                type: item.media?.mime_type || item.mime_type || 'image/jpeg',
                id: item.media?.id || item.id
              }));
              galleryImagesUploader.loadExistingFiles(galleryFiles);
              // Re-attach gallery handlers after loading existing images
              attachGalleryMediaManagerHandlers();
            }

            $('#propertyModal').modal('show');
          },
          error: function(xhr) {
            Swal.fire('Error!', 'Failed to load property data', 'error');
          }
        });
      });

      // Submit form
      $('#propertyForm').submit(function(e) {
        e.preventDefault();

        // Sync description from editor before submit
        if (descriptionEditor) {
          const content = descriptionEditor.getContent();
          document.getElementById('description').value = content;
        }

        const formData = new FormData(this);

        // Add featured image ID from Media Manager (existing media)
        const featuredFiles = featuredImageUploader.getFiles();
        if (featuredFiles.length > 0 && featuredFiles[0].id) {
          formData.append('featured_image_id', featuredFiles[0].id);
        }

        // Add gallery image IDs from Media Manager (existing media)
        const galleryFiles = galleryImagesUploader.getFiles();
        galleryFiles.forEach(file => {
          if (file.id) {
            formData.append('gallery_image_ids[]', file.id);
          }
        });

        const url = $(this).attr('action');
        const method = $('#formMethod').val();

        $.ajax({
          url: url,
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(response) {
            if (response.success) {
              $('#propertyModal').modal('hide');
              Swal.fire('Success!', response.message, 'success');
              table.ajax.reload();
            }
          },
          error: function(xhr) {
            if (xhr.status === 422) {
              const errors = xhr.responseJSON.errors;
              let errorMsg = '<ul class="text-left">';
              $.each(errors, function(key, value) {
                errorMsg += '<li>' + value[0] + '</li>';
              });
              errorMsg += '</ul>';
              Swal.fire('Validation Error', errorMsg, 'error');
            } else {
              Swal.fire('Error!', 'Failed to save property', 'error');
            }
          }
        });
      });

      // Delete property
      $(document).on('click', '.delete-property', function() {
        const propertyId = $(this).data('id');

        Swal.fire({
          title: 'Are you sure?',
          text: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: '/admin/properties/' + propertyId,
              type: 'DELETE',
              data: {
                _token: '{{ csrf_token() }}'
              },
              success: function(response) {
                if (response.success) {
                  Swal.fire('Deleted!', response.message, 'success');
                  table.ajax.reload();
                }
              },
              error: function(xhr) {
                Swal.fire('Error!', 'Failed to delete property', 'error');
              }
            });
          }
        });
      });
    });
  </script>
@endpush

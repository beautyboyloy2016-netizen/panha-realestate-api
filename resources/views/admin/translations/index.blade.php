@extends('admin.layouts.master_layout')

@section('title', __('admin.translations.title'))

@push('styles')
<style>
    .editable-cell {
        cursor: pointer;
        position: relative;
        min-height: 30px;
        padding: 8px !important;
        transition: background-color 0.2s;
    }

    .editable-cell:hover {
        background-color: #f8f9fa;
    }

    .editable-cell:hover::after {
        content: '\f044';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        font-size: 12px;
    }

    .editing-cell {
        padding: 4px !important;
    }

    .inline-edit-input {
        width: 100%;
        min-height: 60px;
        border: 2px solid #007bff;
        padding: 6px;
        font-size: 14px;
    }

    .filter-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .badge-locale {
        font-size: 0.85rem;
        padding: 6px 12px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ __('admin.translations.title') }}</h1>
            <p class="text-muted mb-0">{{ __('admin.translations.subtitle') }}</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" id="addTranslationBtn">
                <i class="fas fa-plus"></i> {{ __('admin.translations.add_new') }}
            </button>
            <button type="button" class="btn btn-secondary" id="refreshBtn">
                <i class="fas fa-sync-alt"></i> {{ __('common.refresh') }}
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="filterLocale" class="form-label">{{ __('admin.translations.filter_locale') }}</label>
                    <select class="form-select" id="filterLocale" name="locale">
                        <option value="">{{ __('admin.translations.all_locales') }}</option>
                        @foreach($locales as $locale)
                            <option value="{{ $locale }}">{{ \App\Models\Translation::getLocaleDisplayName($locale) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filterModel" class="form-label">{{ __('admin.translations.filter_model') }}</label>
                    <select class="form-select" id="filterModel" name="translatable_type">
                        <option value="">{{ __('admin.translations.all_models') }}</option>
                        @foreach($models as $class => $name)
                            <option value="{{ $class }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filterField" class="form-label">{{ __('admin.translations.filter_field') }}</label>
                    <input type="text" class="form-control" id="filterField" name="field"
                           placeholder="{{ __('admin.translations.search_field') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> {{ __('common.filter') }}
                    </button>
                    <button type="button" class="btn btn-secondary" id="clearFiltersBtn">
                        <i class="fas fa-times"></i> {{ __('common.clear') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- DataTable -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="translationsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="50">{{ __('admin.translations.id') }}</th>
                            <th>{{ __('admin.translations.model_type') }}</th>
                            <th>{{ __('admin.translations.model_id') }}</th>
                            <th>{{ __('admin.translations.locale') }}</th>
                            <th>{{ __('admin.translations.field') }}</th>
                            <th width="30%">{{ __('admin.translations.value') }}</th>
                            <th width="100">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Translation Modal -->
<div class="modal fade" id="translationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="translationModalLabel">{{ __('admin.translations.add_new') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="translationForm">
                @csrf
                <input type="hidden" id="translation_id" name="translation_id">
                <input type="hidden" id="method" name="_method" value="POST">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="model_type" class="form-label">{{ __('admin.translations.model_type') }} *</label>
                        <select class="form-select" id="model_type" name="translatable_type" required>
                            <option value="">{{ __('common.select') }}</option>
                            @foreach($models as $class => $name)
                                <option value="{{ $class }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="model_id" class="form-label">{{ __('admin.translations.model_id') }} *</label>
                        <input type="number" class="form-control" id="model_id" name="translatable_id"
                               placeholder="Enter model ID" required min="1">
                    </div>

                    <div class="mb-3">
                        <label for="locale" class="form-label">{{ __('admin.translations.locale') }} *</label>
                        <select class="form-select" id="locale" name="locale" required>
                            <option value="">{{ __('common.select') }}</option>
                            @foreach($locales as $locale)
                                <option value="{{ $locale }}">{{ \App\Models\Translation::getLocaleDisplayName($locale) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="field" class="form-label">{{ __('admin.translations.field') }} *</label>
                        <input type="text" class="form-control" id="field" name="field"
                               placeholder="e.g., title, description" required>
                        <small class="text-muted">{{ __('admin.translations.field_help') }}</small>
                    </div>

                    <div class="mb-3">
                        <label for="value" class="form-label">{{ __('admin.translations.value') }}</label>
                        <textarea class="form-control" id="value" name="value" rows="4"
                                  placeholder="{{ __('admin.translations.value_placeholder') }}"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ __('common.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let table;
    let editingCell = null;

    // Initialize DataTable
    function initDataTable() {
        table = $('#translationsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.translations.index") }}',
                data: function(d) {
                    d.locale = $('#filterLocale').val();
                    d.translatable_type = $('#filterModel').val();
                    d.field = $('#filterField').val();
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'model_type', name: 'translatable_type' },
                { data: 'model_id', name: 'translatable_id' },
                {
                    data: 'locale_name',
                    name: 'locale',
                    render: function(data, type, row) {
                        const colors = {
                            'en': 'primary',
                            'km': 'success',
                            'zh': 'danger',
                            'fr': 'info'
                        };
                        const color = colors[row.locale] || 'secondary';
                        return '<span class="badge bg-' + color + ' badge-locale">' + data + '</span>';
                    }
                },
                { data: 'field', name: 'field' },
                {
                    data: 'value_preview',
                    name: 'value',
                    orderable: false,
                    render: function(data, type, row) {
                        return '<div class="editable-cell" data-id="' + row.id + '" data-field="value">' +
                               data +
                               '</div>';
                    }
                },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']],
            pageLength: 25,
            language: {
                processing: '{{ __("common.loading") }}...',
                search: '{{ __("common.search") }}:',
                lengthMenu: '{{ __("common.show") }} _MENU_ {{ __("common.entries") }}',
                info: '{{ __("common.showing") }} _START_ {{ __("common.to") }} _END_ {{ __("common.of") }} _TOTAL_ {{ __("common.entries") }}',
                infoEmpty: '{{ __("common.no_entries") }}',
                infoFiltered: '({{ __("common.filtered") }} {{ __("common.from") }} _MAX_ {{ __("common.total") }} {{ __("common.entries") }})',
                paginate: {
                    first: '{{ __("common.first") }}',
                    last: '{{ __("common.last") }}',
                    next: '{{ __("common.next") }}',
                    previous: '{{ __("common.previous") }}'
                }
            }
        });
    }

    initDataTable();

    // Filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });

    // Clear filters
    $('#clearFiltersBtn').on('click', function() {
        $('#filterForm')[0].reset();
        table.ajax.reload();
    });

    // Refresh button
    $('#refreshBtn').on('click', function() {
        table.ajax.reload();
    });

    // Add Translation Modal
    $('#addTranslationBtn').on('click', function() {
        $('#translationForm')[0].reset();
        $('#translation_id').val('');
        $('#method').val('POST');
        $('#translationModalLabel').text('{{ __("admin.translations.add_new") }}');
        $('#translationModal').modal('show');
    });

    // Edit Translation Button
    $(document).on('click', '.edit-translation', function() {
        const id = $(this).data('id');
        const locale = $(this).data('locale');
        const field = $(this).data('field');
        const value = $(this).data('value');

        $('#translation_id').val(id);
        $('#method').val('PUT');
        $('#locale').val(locale);
        $('#field').val(field);
        $('#value').val(value);
        $('#translationModalLabel').text('{{ __("admin.translations.edit") }}');

        // Disable certain fields during edit
        $('#model_type, #model_id').prop('disabled', true);

        $('#translationModal').modal('show');
    });

    // Reset form when modal closes
    $('#translationModal').on('hidden.bs.modal', function() {
        $('#model_type, #model_id').prop('disabled', false);
    });

    // Submit Translation Form
    $('#translationForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const id = $('#translation_id').val();
        const method = $('#method').val();
        const url = id
            ? '{{ route("admin.translations.index") }}/' + id
            : '{{ route("admin.translations.store") }}';

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __("common.success") }}',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#translationModal').modal('hide');
                    table.ajax.reload();
                }
            },
            error: function(xhr) {
                let message = '{{ __("common.error") }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("common.error") }}',
                    text: message
                });
            }
        });
    });

    // Inline Editing - Click to Edit
    $(document).on('click', '.editable-cell', function() {
        if (editingCell) return; // Already editing another cell

        editingCell = $(this);
        const id = editingCell.data('id');
        const field = editingCell.data('field');
        const currentValue = editingCell.text().trim();

        // Replace cell content with textarea
        editingCell.addClass('editing-cell').html(
            '<textarea class="inline-edit-input" data-id="' + id + '" data-field="' + field + '">' +
            currentValue +
            '</textarea>'
        );

        // Focus and select text
        const textarea = editingCell.find('.inline-edit-input');
        textarea.focus().select();

        // Save on blur
        textarea.on('blur', function() {
            saveInlineEdit($(this));
        });

        // Save on Ctrl+Enter
        textarea.on('keydown', function(e) {
            if (e.ctrlKey && e.keyCode === 13) {
                saveInlineEdit($(this));
            }
            // Cancel on Escape
            if (e.keyCode === 27) {
                cancelInlineEdit();
            }
        });
    });

    // Save Inline Edit
    function saveInlineEdit(textarea) {
        if (!editingCell) return;

        const id = textarea.data('id');
        const field = textarea.data('field');
        const newValue = textarea.val();

        $.ajax({
            url: '{{ route("admin.translations.index") }}/' + id + '/inline',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'PUT',
                field: field,
                value: newValue
            },
            success: function(response) {
                if (response.success) {
                    // Update cell with new value
                    const preview = newValue.length > 50
                        ? newValue.substring(0, 50) + '...'
                        : newValue;

                    editingCell.removeClass('editing-cell').html(
                        '<span class="text-truncate d-inline-block" style="max-width: 200px;" title="' +
                        newValue + '">' + preview + '</span>'
                    );

                    editingCell = null;

                    // Show success toast
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __("common.success") }}',
                        text: response.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            },
            error: function(xhr) {
                cancelInlineEdit();
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("common.error") }}',
                    text: xhr.responseJSON?.message || '{{ __("common.error") }}'
                });
            }
        });
    }

    // Cancel Inline Edit
    function cancelInlineEdit() {
        if (editingCell) {
            editingCell.removeClass('editing-cell');
            table.ajax.reload(null, false); // Reload without resetting pagination
            editingCell = null;
        }
    }

    // Delete Translation
    $(document).on('click', '.delete-translation', function() {
        const id = $(this).data('id');

        Swal.fire({
            title: '{{ __("common.confirm_delete") }}',
            text: '{{ __("admin.translations.delete_confirm") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '{{ __("common.delete") }}',
            cancelButtonText: '{{ __("common.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.translations.index") }}/' + id,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '{{ __("common.success") }}',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            table.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("common.error") }}',
                            text: xhr.responseJSON?.message || '{{ __("common.error") }}'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush

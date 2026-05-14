@extends('admin.layouts.master_layout')

@section('title', 'Language Translator')

@push('styles')
<style>
    .translator-container {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
    }

    .add-new-section {
        background: #e8f5e9;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #c8e6c9;
    }

    .add-new-section .form-label {
        font-weight: 600;
        color: #2e7d32;
        margin-bottom: 8px;
    }

    .translation-table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .translation-table thead {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .translation-table thead th {
        padding: 15px 10px;
        font-weight: 600;
        font-size: 14px;
        color: #495057;
        text-align: left;
        vertical-align: middle;
    }

    .translation-table tbody tr {
        border-bottom: 1px solid #dee2e6;
        transition: background-color 0.2s;
    }

    .translation-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .translation-table tbody td {
        padding: 12px 10px;
        vertical-align: middle;
    }

    .key-cell {
        color: #0066cc;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        text-decoration: underline;
    }

    .translation-input {
        width: 100%;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px 10px;
        font-size: 13px;
        min-height: 60px;
        resize: none;
        font-family: inherit;
        transition: all 0.2s;
    }

    .translation-input:focus {
        outline: none;
        border-color: #4CAF50;
        box-shadow: 0 0 0 0.2rem rgba(76,175,80,.25);
        background-color: #f9fff9;
    }

    .translation-input.saving {
        border-color: #ffc107;
        background-color: #fffef5;
    }

    .empty-translation {
        color: #dc3545;
        font-style: italic;
        font-size: 13px;
        display: block;
        padding: 8px;
    }

    .translate-popup {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1050;
        min-width: 400px;
        display: none;
    }

    .translate-popup.active {
        display: block;
    }

    .translate-popup textarea {
        width: 100%;
        min-height: 120px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 10px;
        font-size: 14px;
        resize: vertical;
    }

    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1040;
        display: none;
    }

    .popup-overlay.active {
        display: block;
    }

    .btn-save-translation {
        background: #28a745;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
    }

    .btn-save-translation:hover {
        background: #218838;
    }

    .btn-cancel-translation {
        background: #6c757d;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        margin-left: 10px;
    }

    .btn-cancel-translation:hover {
        background: #5a6268;
    }

    .delete-btn {
        background: #dc3545;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
    }

    .delete-btn:hover {
        background: #c82333;
    }

    .filter-section {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .action-buttons {
        display: flex;
        gap: 4px;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
    }

    .action-buttons .btn {
        padding: 4px 8px;
        font-size: 11px;
        white-space: nowrap;
    }

    .action-buttons .btn i {
        font-size: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid translator-container">
    <!-- Add New Key Section -->
    <div class="add-new-section">
        <div class="row">
            <div class="col-md-5">
                <label class="form-label">Key:</label>
                <input type="text" class="form-control" id="newKeyInput" placeholder="Enter Key...">
            </div>
            <div class="col-md-5">
                <label class="form-label">Value:</label>
                <input type="text" class="form-control" id="newValueInput" placeholder="Enter Value...">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-success w-100" id="addKeyBtn">
                    <i class="fas fa-plus"></i> Add
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">File</label>
                <select class="form-select" id="filterFile" name="file">
                    @foreach($files as $file)
                        <option value="{{ $file }}" {{ $file === 'common' ? 'selected' : '' }}>
                            {{ $file }}.php
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Search Key</label>
                <input type="text" class="form-control" id="searchKeyInput" name="search_key"
                       placeholder="Search by key name...">
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-primary" id="searchBtn">
                    <i class="fas fa-search"></i> Search
                </button>
                <button type="button" class="btn btn-secondary" id="clearSearchBtn">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
            <div class="col-md-3 text-end">
                <button type="button" class="btn btn-info" id="syncBtn">
                    <i class="fas fa-sync"></i> Sync Keys
                </button>
                <button type="button" class="btn btn-success" id="autoTranslateBtn">
                    <i class="fas fa-language"></i> Auto Translate
                </button>
            </div>
        </div>
    </div>

    <!-- Translation Table -->
    <div class="table-responsive">
        <table class="translation-table table table-bordered" id="translationTable">
            <thead>
                <tr>
                    <th style="width: 20%">Key</th>
                    <th style="width: 15%">English(EN)</th>
                    <th style="width: 15%">Khmer(KM)</th>
                    <th style="width: 15%">Chinese(ZH)</th>
                    <th style="width: 15%">French(FR)</th>
                    <th style="width: 20%">Action</th>
                </tr>
            </thead>
            <tbody id="translationTableBody">
                <!-- Data will be loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Sync Modal -->
<div class="modal fade" id="syncModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sync Translation Keys</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="syncForm">
                <div class="modal-body">
                    <p class="text-muted">Copy missing keys from source language to target languages</p>

                    <div class="mb-3">
                        <label class="form-label">Source Language</label>
                        <select class="form-select" id="syncSourceLocale" name="source_locale" required>
                            <option value="en">English (EN)</option>
                            <option value="km">Khmer (KM)</option>
                            <option value="zh">Chinese (ZH)</option>
                            <option value="fr">French (FR)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Target Languages</label>
                        <select class="form-select" id="syncTargetLocales" name="target_locales[]" multiple required size="4">
                            <option value="en">English (EN)</option>
                            <option value="km">Khmer (KM)</option>
                            <option value="zh">Chinese (ZH)</option>
                            <option value="fr">French (FR)</option>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">File</label>
                        <select class="form-select" id="syncFile" name="file" required>
                            <option value="admin">admin.php</option>
                            <option value="common">common.php</option>
                            <option value="langauages">langauages.php</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-sync"></i> Sync Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Auto Translate Modal -->
<div class="modal fade" id="autoTranslateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Auto Translate with Google Translate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="autoTranslateForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        This will automatically translate all missing translations from the source language to the target language using Google Translate.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Source Language</label>
                        <select class="form-select" id="autoTranslateSourceLocale" name="source_locale" required>
                            <option value="en" selected>English (EN)</option>
                            <option value="km">Khmer (KM)</option>
                            <option value="zh">Chinese (ZH)</option>
                            <option value="fr">French (FR)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Target Language</label>
                        <select class="form-select" id="autoTranslateTargetLocale" name="target_locale" required>
                            <option value="km">Khmer (KM)</option>
                            <option value="zh">Chinese (ZH)</option>
                            <option value="fr">French (FR)</option>
                            <option value="en">English (EN)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">File</label>
                        <select class="form-select" id="autoTranslateFile" name="file" required>
                            <option value="admin">admin.php</option>
                            <option value="common" selected>common.php</option>
                            <option value="langauages">langauages.php</option>
                        </select>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> This process may take a while for large files. Please be patient and do not close this window.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-language"></i> Start Translation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit/Add Key Modal (keeping old modal for compatibility) -->
<div class="modal fade" id="keyModal" tabindex="-1" style="display:none">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="keyModalLabel">Add Translation Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="keyForm">
                @csrf
                <input type="hidden" id="isEdit" value="0">

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="keyLocale" class="form-label">Language *</label>
                            <select class="form-select" id="keyLocale" name="locale" required>
                                @foreach($locales as $locale)
                                    <option value="{{ $locale }}">{{ strtoupper($locale) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="keyFile" class="form-label">File *</label>
                            <select class="form-select" id="keyFile" name="file" required>
                                @foreach($files as $file)
                                    <option value="{{ $file }}">{{ $file }}.php</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="keyName" class="form-label">Key *</label>
                        <input type="text" class="form-control" id="keyName" name="key"
                               placeholder="e.g., menu.dashboard or users.title" required>
                        <small class="text-muted">Use dot notation for nested keys</small>
                    </div>

                    <div class="mb-3">
                        <label for="keyValue" class="form-label">Value *</label>
                        <textarea class="form-control" id="keyValue" name="value" rows="5"
                                  placeholder="Enter translation value" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Key</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentFile = 'admin';
    let currentKey = '';
    let currentLocale = '';
    let searchKeyword = '';

    // Load translations
    function loadTranslations() {
        const file = $('#filterFile').val() || 'admin';
        const search = $('#searchKeyInput').val() || '';

        currentFile = file;
        searchKeyword = search;

        $.ajax({
            url: '{{ route("admin.language-files.index") }}',
            type: 'GET',
            data: {
                file: file,
                search_key: search,
                ajax: 1
            },
            success: function(response) {
                renderTranslations(response.data);
            },
            error: function(xhr) {
                console.error('Error loading translations:', xhr);
                Swal.fire('Error', 'Failed to load translations', 'error');
            }
        });
    }

    // Render translations table
    function renderTranslations(data) {
        const tbody = $('#translationTableBody');
        tbody.empty();

        if (!data || data.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        No translations found
                    </td>
                </tr>
            `);
            return;
        }

        data.forEach(function(item) {
            const row = $('<tr>');

            // Key cell
            row.append(`
                <td class="key-cell">${escapeHtml(item.key)}</td>
            `);

            // English column with inline textarea
            const enValue = item.en || '';
            row.append(`
                <td>
                    <textarea class="translation-input"
                              data-key="${escapeHtml(item.key)}"
                              data-locale="en"
                              placeholder="${enValue ? '' : 'Empty'}">${escapeHtml(enValue)}</textarea>
                </td>
            `);

            // Khmer column with inline textarea
            const kmValue = item.km || '';
            row.append(`
                <td>
                    <textarea class="translation-input"
                              data-key="${escapeHtml(item.key)}"
                              data-locale="km"
                              placeholder="${kmValue ? '' : 'Empty'}">${escapeHtml(kmValue)}</textarea>
                </td>
            `);

            // Chinese column with inline textarea
            const zhValue = item.zh || '';
            row.append(`
                <td>
                    <textarea class="translation-input"
                              data-key="${escapeHtml(item.key)}"
                              data-locale="zh"
                              placeholder="${zhValue ? '' : 'Empty'}">${escapeHtml(zhValue)}</textarea>
                </td>
            `);

            // French column with inline textarea
            const frValue = item.fr || '';
            row.append(`
                <td>
                    <textarea class="translation-input"
                              data-key="${escapeHtml(item.key)}"
                              data-locale="fr"
                              placeholder="${frValue ? '' : 'Empty'}">${escapeHtml(frValue)}</textarea>
                </td>
            `);

            // Action column
            row.append(`
                <td class="text-center">
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-info auto-translate-key"
                                data-key="${escapeHtml(item.key)}"
                                data-locale="km"
                                title="Auto-translate to Khmer">
                            <i class="fas fa-language"></i> KM
                        </button>
                        <button class="btn btn-sm btn-warning auto-translate-key"
                                data-key="${escapeHtml(item.key)}"
                                data-locale="zh"
                                title="Auto-translate to Chinese">
                            <i class="fas fa-language"></i> ZH
                        </button>
                        <button class="btn btn-sm btn-primary auto-translate-key"
                                data-key="${escapeHtml(item.key)}"
                                data-locale="fr"
                                title="Auto-translate to French">
                            <i class="fas fa-language"></i> FR
                        </button>
                        <button class="delete-btn btn btn-sm btn-danger"
                                data-key="${escapeHtml(item.key)}"
                                title="Delete key from all languages">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `);

            tbody.append(row);
        });
    }

    // Escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Auto-save translation on change (with debounce)
    let saveTimeout = null;
    $(document).on('input', '.translation-input', function() {
        const textarea = $(this);
        const key = textarea.data('key');
        const locale = textarea.data('locale');
        const value = textarea.val().trim();
        const originalValue = textarea.attr('data-original') || '';

        // Store original value on first edit
        if (!textarea.attr('data-original')) {
            textarea.attr('data-original', textarea.val());
        }

        // Clear previous timeout
        if (saveTimeout) {
            clearTimeout(saveTimeout);
        }

        // Show saving indicator
        textarea.addClass('saving');

        // Debounce save (wait 1 second after last keystroke)
        saveTimeout = setTimeout(function() {
            saveTranslation(key, locale, value, textarea);
        }, 1000);
    });

    // Save translation function
    function saveTranslation(key, locale, value, textarea) {
        $.ajax({
            url: '{{ route("admin.language-files.update") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                locale: locale,
                file: currentFile,
                key: key,
                value: value
            },
            success: function(response) {
                textarea.removeClass('saving');
                textarea.attr('data-original', value);

                // Show brief success feedback
                textarea.css('border-color', '#28a745');
                setTimeout(function() {
                    textarea.css('border-color', '');
                }, 1000);
            },
            error: function(xhr) {
                textarea.removeClass('saving');
                const message = xhr.responseJSON?.message || 'Failed to save translation';

                // Show error feedback
                textarea.css('border-color', '#dc3545');
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: message,
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        });
    }    // Delete translation key
    $(document).on('click', '.delete-btn', function() {
        const key = $(this).data('key');

        Swal.fire({
            title: 'Delete Translation Key?',
            text: `This will remove "${key}" from all language files`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.language-files.destroy") }}',
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}',
                        key: key,
                        file: currentFile
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.message || 'Translation key deleted', 'success');
                        loadTranslations();
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Failed to delete key';
                        Swal.fire('Error', message, 'error');
                    }
                });
            }
        });
    });

    // Add new key
    $('#addKeyBtn').click(function() {
        const key = $('#newKeyInput').val().trim();
        const value = $('#newValueInput').val().trim();

        if (!key) {
            Swal.fire('Warning', 'Please enter a key name', 'warning');
            $('#newKeyInput').focus();
            return;
        }

        if (!value) {
            Swal.fire('Warning', 'Please enter a value', 'warning');
            $('#newValueInput').focus();
            return;
        }

        $.ajax({
            url: '{{ route("admin.language-files.store") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                locale: 'en', // Default to English
                file: currentFile,
                key: key,
                value: value
            },
            success: function(response) {
                Swal.fire('Success', response.message || 'Translation key added', 'success');
                $('#newKeyInput').val('');
                $('#newValueInput').val('');
                loadTranslations();
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to add translation key';
                Swal.fire('Error', message, 'error');
            }
        });
    });

    // Search button
    $('#searchBtn').click(function() {
        loadTranslations();
    });

    // Clear search button
    $('#clearSearchBtn').click(function() {
        $('#searchKeyInput').val('');
        loadTranslations();
    });

    // Filter file change
    $('#filterFile').change(function() {
        loadTranslations();
    });

    // Enter key in search input
    $('#searchKeyInput').keypress(function(e) {
        if (e.which === 13) {
            e.preventDefault();
            loadTranslations();
        }
    });

    // Sync translations button
    $('#syncBtn').click(function() {
        $('#syncModal').modal('show');
    });

    // Sync form submit
    $('#syncForm').submit(function(e) {
        e.preventDefault();

        const sourceLocale = $('#syncSourceLocale').val();
        const targetLocales = $('#syncTargetLocales').val();
        const file = $('#syncFile').val();

        if (!targetLocales || targetLocales.length === 0) {
            Swal.fire('Warning', 'Please select at least one target language', 'warning');
            return;
        }

        if (targetLocales.includes(sourceLocale)) {
            Swal.fire('Warning', 'Target languages cannot include source language', 'warning');
            return;
        }

        $.ajax({
            url: '{{ route("admin.language-files.sync") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                source_locale: sourceLocale,
                target_locales: targetLocales,
                file: file
            },
            success: function(response) {
                $('#syncModal').modal('hide');
                Swal.fire('Success', response.message || 'Keys synchronized successfully', 'success');
                loadTranslations();
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to sync keys';
                Swal.fire('Error', message, 'error');
            }
        });
    });

    // Auto Translate button
    $('#autoTranslateBtn').click(function() {
        // Set current file as default
        $('#autoTranslateFile').val(currentFile);
        $('#autoTranslateModal').modal('show');
    });

    // Auto Translate form submit
    $('#autoTranslateForm').submit(function(e) {
        e.preventDefault();

        const sourceLocale = $('#autoTranslateSourceLocale').val();
        const targetLocale = $('#autoTranslateTargetLocale').val();
        const file = $('#autoTranslateFile').val();

        if (sourceLocale === targetLocale) {
            Swal.fire('Warning', 'Source and target languages cannot be the same', 'warning');
            return;
        }

        // Show loading message
        Swal.fire({
            title: 'Translating...',
            html: 'Please wait while we translate the file. This may take a few moments.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("admin.language-files.auto-translate-file") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                source_locale: sourceLocale,
                target_locale: targetLocale,
                file: file
            },
            success: function(response) {
                $('#autoTranslateModal').modal('hide');

                let message = response.message || 'Translation completed successfully';
                if (response.translated_count) {
                    message += `<br><br><strong>${response.translated_count}</strong> keys were translated.`;
                }
                if (response.errors && response.errors.length > 0) {
                    message += `<br><br><strong>Errors:</strong><br>` + response.errors.join('<br>');
                }

                Swal.fire({
                    title: 'Success',
                    html: message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });

                loadTranslations();
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to auto-translate file';
                Swal.fire('Error', message, 'error');
            }
        });
    });

    // Auto translate single key button (in action column)
    $(document).on('click', '.auto-translate-key', function() {
        const key = $(this).data('key');
        const locale = $(this).data('locale');

        Swal.fire({
            title: 'Auto Translate',
            html: `
                <div class="text-start">
                    <label class="form-label">Translate from:</label>
                    <select class="form-select mb-3" id="swalSourceLocale">
                        <option value="en">English (EN)</option>
                        <option value="km">Khmer (KM)</option>
                        <option value="zh">Chinese (ZH)</option>
                        <option value="fr">French (FR)</option>
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Translate',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                return $('#swalSourceLocale').val();
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const sourceLocale = result.value;

                if (sourceLocale === locale) {
                    Swal.fire('Warning', 'Source and target languages cannot be the same', 'warning');
                    return;
                }

                // Show loading
                Swal.fire({
                    title: 'Translating...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route("admin.language-files.auto-translate") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        key: key,
                        source_locale: sourceLocale,
                        target_locale: locale,
                        file: currentFile
                    },
                    success: function(response) {
                        Swal.fire('Success', response.message || 'Translation completed', 'success');
                        loadTranslations();
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Failed to translate';
                        Swal.fire('Error', message, 'error');
                    }
                });
            }
        });
    });

    // Initial load
    loadTranslations();
});
</script>
@endpush

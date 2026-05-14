/**
 * MediaManagerLib - A self-contained, reusable media management library
 * @version 1.0.0
 * @author Your Name
 * @license MIT
 */

(function (global) {
    'use strict';

    /**
     * MediaManagerLib Class
     * A complete file management solution with grid/list views, uploads, and selections
     */
    class PanhaMediaManager {
        constructor(options = {}) {
            // Configuration
            this.config = {
                container: options.container || 'body',
                title: options.title || 'Media Manager Library',
                initialFiles: options.files || [],
                uploadUrl: options.uploadUrl || null,
                onFileSelect: options.onFileSelect || null,
                onFileUpload: options.onFileUpload || null,
                onFileDelete: options.onFileDelete || null,
                onFileRename: options.onFileRename || null,
                onFolderOpen: options.onFolderOpen || null,
                onFolderRename: options.onFolderRename || null,
                onFolderDelete: options.onFolderDelete || null,
                onFolderMove: options.onFolderMove || null,
                onCreateFolder: options.onCreateFolder || null,
                viewMode: options.viewMode || 'grid',
                allowUpload: options.allowUpload !== false,
                allowDelete: options.allowDelete !== false,
                allowRename: options.allowRename !== false,
                maxFileSize: options.maxFileSize || 10 * 1024 * 1024, // 10MB
                acceptedFileTypes: options.acceptedFileTypes || '*',
                theme: options.theme || 'default'
            };

            // Core properties
            this.files = this.config.initialFiles;
            this.filteredFiles = [...this.files];
            this.currentPath = '/';
            this.selectedFiles = new Set();
            this.viewMode = this.config.viewMode;
            this.sortDirection = 'asc';
            this.sortBy = 'name';
            this.contextMenuTarget = null;

            // DOM elements cache
            this.elements = {};

            // Initialize
            this.init();
        }

        /**
         * Initialize the Media Manager
         */
        init() {
            this.injectStyles();
            this.createHTML();
            this.cacheElements();
            this.setupEventListeners();
            this.setupAppsDropdown();
            this.render();
            this.setupSorting();
            this.setupSelectAll();
        }

        /**
         * Inject CSS styles into the page
         */
        injectStyles() {
            if (document.getElementById('media-manager-styles')) return;

            const style = document.createElement('style');
            style.id = 'media-manager-styles';
            style.textContent = this.getStyles();
            document.head.appendChild(style);
        }

        /**
         * Get CSS styles
         */
        getStyles() {
            return `
                /* Media Manager Library Styles */
                .mm-container {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                    background: #f5f5f5;
                    color: #333;
                    line-height: 1.6;
                    -webkit-font-smoothing: antialiased;
                    -moz-osx-font-smoothing: grayscale;
                    /* Prevent text size adjustment on mobile */
                    -webkit-text-size-adjust: 100%;
                    -moz-text-size-adjust: 100%;
                    -ms-text-size-adjust: 100%;
                    text-size-adjust: 100%;
                    /* Touch optimization */
                    -webkit-tap-highlight-color: transparent;
                    touch-action: manipulation;
                }

                .mm-container * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                .mm-wrapper {
                    // max-width: 1400px;
                    margin: 0 auto;
                    padding: 10px 0px;
                    /* Improve scrolling on mobile */
                    -webkit-overflow-scrolling: touch;
                }

                /* Header */
                .mm-header {
                    background: white;
                    padding: 0px 12px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
                    border-bottom: 1px solid #e9ecef;
                    margin-bottom: 0;
                }

                .mm-header-left {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    flex: 1;
                }

                .mm-header-right {
                    display: flex;
                    align-items: center;
                    gap: 6px;
                }

                /* Search */
                .mm-search-group {
                    display: flex;
                    align-items: center;
                    gap: 0;
                }

                .mm-search-input {
                    width: 250px;
                    padding: 8px 12px;
                    border: 1px solid #dee2e6;
                    border-right: none;
                    border-radius: 4px 0 0 4px;
                    font-size: 14px;
                    outline: none;
                    transition: border-color 0.2s ease;
                }

                .mm-search-input:focus {
                    border-color: #80bdff;
                    box-shadow: 0 0 0 2px rgba(128, 189, 255, 0.1);
                }

                .mm-search-btn {
                    padding: 8px 16px;
                    background: #f8f9fa;
                    border: 1px solid #dee2e6;
                    border-radius: 0 4px 4px 0;
                    cursor: pointer;
                    font-size: 14px;
                    color: #495057;
                    display: flex;
                    align-items: center;
                    gap: 5px;
                    transition: background-color 0.2s ease;
                }

                .mm-search-btn:hover {
                    background: #e9ecef;
                }

                /* File Count */
                .mm-file-count {
                    color: #6c757d;
                    font-size: 14px;
                    font-style: italic;
                }

                .mm-file-count span {
                    font-weight: 600;
                    color: #495057;
                }

                /* Buttons */
                .mm-btn {
                    padding: 8px 10px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    transition: all 0.2s ease;
                    white-space: nowrap;
                    min-height: 36px;
                    touch-action: manipulation;
                    user-select: none;
                    -webkit-tap-highlight-color: transparent;
                }

                .mm-btn-fu {
                    padding: 8px 10px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    transition: all 0.2s ease;
                    white-space: nowrap;
                    min-height: 36px;
                    touch-action: manipulation;
                    user-select: none;
                    -webkit-tap-highlight-color: transparent;
                }

                .mm-btn-primary {
                    background: #007bff;
                    color: white;
                }

                .mm-btn-primary:hover {
                    background: #0056b3;
                    transform: translateY(-1px);
                    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
                }

                .mm-btn-success {
                    background: #28a745;
                    color: white;
                }

                .mm-btn-success:hover {
                    background: #218838;
                    transform: translateY(-1px);
                    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
                }

                /* View Controls */
                .mm-view-controls {
                    display: flex;
                    gap: 0;
                    background: #f8f9fa;
                    border: 1px solid #dee2e6;
                    border-radius: 4px;
                    overflow: hidden;
                }

                .mm-view-btn {
                    padding: 8px 12px;
                    background: transparent;
                    border: none;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #6c757d;
                    min-width: 36px;
                    min-height: 36px;
                    touch-action: manipulation;
                    -webkit-tap-highlight-color: transparent;
                }

                .mm-view-btn:not(:last-child) {
                    border-right: 1px solid #dee2e6;
                }

                .mm-view-btn:hover {
                    background: #e9ecef;
                }

                .mm-view-btn.active {
                    background: white;
                    color: #495057;
                    box-shadow: 0 0 4px rgba(0, 0, 0, 0.1);
                }

                /* Apps Dropdown */
                .mm-apps-dropdown-container {
                    position: relative;
                    padding: 6px 0px;
                    margin-right: 0px;
                }

                .mm-apps-dropdown-btn {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    padding: 8px 16px;
                    background: #f8f9fa;
                    border: 1px solid #dee2e6;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 14px;
                    color: #495057;
                    transition: all 0.2s ease;
                    min-height: 36px;
                    touch-action: manipulation;
                    -webkit-tap-highlight-color: transparent;
                }

                .mm-apps-dropdown-btn:hover {
                    background: #e9ecef;
                    border-color: #adb5bd;
                }

                .mm-apps-dropdown-btn svg {
                    flex-shrink: 0;
                }

                .mm-dropdown-arrow {
                    margin-left: 4px;
                    transition: transform 0.2s ease;
                }

                .mm-apps-dropdown-container.open .mm-dropdown-arrow {
                    transform: rotate(180deg);
                }

                .mm-apps-dropdown-menu {
                    position: absolute;
                    top: calc(100% + 1px);
                    right: 0;
                    background: white;
                    border: 1px solid #dee2e6;
                    border-radius: 8px;
                    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
                    min-width: 280px;
                    padding: 10px;
                    z-index: 1000;
                    display: none;
                    animation: mm-dropdownFadeIn 0.2s ease;
                }

                @keyframes mm-dropdownFadeIn {
                    from {
                        opacity: 0;
                        transform: translateY(-8px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .mm-apps-dropdown-container.open .mm-apps-dropdown-menu {
                    display: block;
                }

                .mm-dropdown-section {
                    margin-bottom: 16px;
                }

                .mm-dropdown-section:last-child {
                    margin-bottom: 0;
                }

                .mm-dropdown-label {
                    display: block;
                    font-size: 11px;
                    font-weight: 600;
                    color: #6c757d;
                    margin-bottom: 8px;
                    letter-spacing: 0.5px;
                }

                .mm-dropdown-select {
                    width: 100%;
                    padding: 8px 12px;
                    border: 1px solid #dee2e6;
                    border-radius: 4px;
                    font-size: 14px;
                    color: #495057;
                    background: white;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }

                .mm-dropdown-select:hover {
                    border-color: #adb5bd;
                }

                .mm-dropdown-select:focus {
                    outline: none;
                    border-color: #007bff;
                    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
                }

                .mm-thumbnail-slider-container {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }

                .mm-thumbnail-slider {
                    flex: 1;
                    height: 6px;
                    border-radius: 3px;
                    background: #dee2e6;
                    outline: none;
                    cursor: pointer;
                    -webkit-appearance: none;
                    appearance: none;
                }

                .mm-thumbnail-slider::-webkit-slider-thumb {
                    -webkit-appearance: none;
                    appearance: none;
                    width: 16px;
                    height: 16px;
                    border-radius: 50%;
                    background: #007bff;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }

                .mm-thumbnail-slider::-webkit-slider-thumb:hover {
                    background: #0056b3;
                    transform: scale(1.1);
                }

                .mm-thumbnail-slider::-moz-range-thumb {
                    width: 16px;
                    height: 16px;
                    border-radius: 50%;
                    background: #007bff;
                    cursor: pointer;
                    border: none;
                    transition: all 0.2s ease;
                }

                .mm-thumbnail-slider::-moz-range-thumb:hover {
                    background: #0056b3;
                    transform: scale(1.1);
                }

                .mm-slider-value {
                    font-size: 13px;
                    font-weight: 600;
                    color: #495057;
                    min-width: 32px;
                    text-align: right;
                }

                .mm-toggle-switch {
                    position: relative;
                    display: inline-block;
                    width: 44px;
                    height: 24px;
                    cursor: pointer;
                }

                .mm-toggle-switch input {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }

                .mm-toggle-slider {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #ccc;
                    border-radius: 24px;
                    transition: 0.3s;
                }

                .mm-toggle-slider:before {
                    position: absolute;
                    content: "";
                    height: 18px;
                    width: 18px;
                    left: 3px;
                    bottom: 3px;
                    background-color: white;
                    border-radius: 50%;
                    transition: 0.3s;
                }

                .mm-toggle-switch input:checked + .mm-toggle-slider {
                    background-color: #007bff;
                }

                .mm-toggle-switch input:checked + .mm-toggle-slider:before {
                    transform: translateX(20px);
                }

                /* Selection Header */
                .mm-selection-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 6px;
                    padding: 6px 12px;
                    background: #1976d2;
                    box-shadow: 0 2px 8px rgba(25, 118, 210, 0.3);
                    animation: mm-slideDown 0.3s ease;
                }

                @keyframes mm-slideDown {
                    from {
                        opacity: 0;
                        transform: translateY(-10px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .mm-selection-info {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    flex-shrink: 0;
                    max-height:35px;
                }

                .mm-selection-count {
                    font-size: 14px;
                    font-weight: 600;
                    color: #ffffff;
                    padding: 6px 16px;
                    background: rgba(255, 255, 255, 0.2);
                    border-radius: 20px;
                    white-space: nowrap;
                }

                .mm-clear-selection-btn {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 32px;
                    height: 32px;
                    min-width: 36px;
                    min-height: 36px;
                    background: rgba(255, 255, 255, 0.15);
                    border: 1px solid rgba(255, 255, 255, 0.3);
                    border-radius: 50%;
                    color: #ffffff;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    -webkit-tap-highlight-color: transparent;
                }

                .mm-clear-selection-btn:hover {
                    background: rgba(255, 255, 255, 0.25);
                    transform: scale(1.05);
                }

                .mm-selection-actions {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                    scrollbar-width: none;
                    max-height:35px;
                }

                .mm-selection-actions::-webkit-scrollbar {
                    display: none;
                }

                .mm-selection-btn {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    padding: 8px 12px;
                    background: rgba(255, 255, 255, 0.95);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    border-radius: 6px;
                    color: #1976d2;
                    font-size: 14px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    white-space: nowrap;
                    flex-shrink: 0;
                    min-height: 36px;
                }

                .mm-selection-btn:hover {
                    background: #ffffff;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                }

                .mm-selection-btn.danger {
                    background: rgba(244, 67, 54, 0.95);
                    color: #ffffff;
                }

                .mm-selection-btn.danger:hover {
                    background: #f44336;
                }

                .mm-selection-btn svg {
                    width: 18px;
                    height: 18px;
                    fill: currentColor;
                    flex-shrink: 0;
                }

                /* Breadcrumb */
                .mm-breadcrumb {
                    background: #f8f9fa;
                    padding: 10px 20px;
                    border-bottom: 1px solid #e9ecef;
                    font-size: 14px;
                }

                .mm-breadcrumb-item {
                    color: #7f8c8d;
                    cursor: pointer;
                    margin-right: 10px;
                    transition: color 0.2s ease;
                }

                .mm-breadcrumb-item:hover {
                    color: #495057;
                }

                .mm-breadcrumb-item.active {
                    color: #2c3e50;
                    font-weight: 500;
                }

                .mm-breadcrumb-item:not(:last-child)::after {
                    content: " / ";
                    margin-left: 10px;
                    color: #bdc3c7;
                }

                /* Media Container */
                .mm-media-container {
                    background: white;
                    min-height: 100vh;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                }

                /* Grid View */
                .mm-media-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                    gap: 12px;
                    padding: 12px;
                    background: white;
                }

                .mm-media-item {
                    background: white;
                    border: 1px solid #ecf0f1;
                    border-radius: 8px;
                    overflow: hidden;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    position: relative;
                    -webkit-tap-highlight-color: transparent;
                }

                .mm-media-item:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
                }

                .mm-media-item.selected {
                    border-color: #3498db;
                    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.3);
                }

                .mm-media-item.selected::before {
                    content: '✓';
                    position: absolute;
                    top: 2px;
                    left: 2px;
                    width: 24px;
                    height: 24px;
                    background: #2196f3;
                    color: white;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    font-size: 14px;
                    z-index: 10;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                }

                .mm-media-preview {
                    width: 100%;
                    height: 90px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #f8f9fa;
                    position: relative;
                    overflow: hidden;
                }

                .mm-media-preview img {
                    max-width: 100%;
                    max-height: 100%;
                    object-fit: cover;
                }

                .mm-file-icon {
                    font-size: 48px;
                }

            .mm-media-info {
                padding: 6px 12px;
                border-top: 1px solid #ecf0f1;
                text-align: center;/
            }

                .mm-media-name {
                    font-size: 14px;
                    font-weight: 500;
                    margin-bottom: 5px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                .mm-media-meta {
                    font-size: 12px;
                    color: #95a5a6;
                }

                /* List View */
                .mm-media-grid.list-view {
                    display: block;
                    padding: 0;
                }

                .mm-list-header {
                    display: grid;
                    grid-template-columns: 40px minmax(300px, 1fr) 120px 180px 120px 100px;
                    padding: 15px 20px;
                    background: #f8f9fa;
                    border-bottom: 2px solid #e9ecef;
                    font-weight: 600;
                    font-size: 13px;
                    color: #6c757d;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                .mm-list-header-item {
                    display: flex;
                    align-items: center;
                    cursor: pointer;
                    user-select: none;
                }

                .mm-sort-icon {
                    margin-left: 5px;
                    font-size: 10px;
                    opacity: 0.5;
                }

                .mm-media-grid.list-view .mm-media-item {
                    display: grid;
                    grid-template-columns: 40px minmax(300px, 1fr) 120px 180px 120px 100px;
                    align-items: center;
                    margin: 0;
                    padding: 12px 20px;
                    border-radius: 0;
                    border: none;
                    border-bottom: 1px solid #f1f3f4;
                    transition: background-color 0.2s ease;
                }

                .mm-media-grid.list-view .mm-media-item:hover {
                    background-color: #f8f9fa;
                    transform: none;
                    box-shadow: none;
                }

                .mm-media-grid.list-view .mm-media-item.selected {
                    background-color: #e3f2fd;
                }

                /* Hide the circular selected badge in list view to avoid double-check visuals */
                .mm-media-grid.list-view .mm-media-item.selected::before {
                    display: none;
                }

                .mm-list-checkbox {
                    width: 22px;
                    height: 18px;
                    border: 2px solid #dee2e6;
                    border-radius: 4px;
                    cursor: pointer;
                    position: relative;
                    transition: all 0.2s ease;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .mm-list-checkbox::after {
                    content: '';
                    width: 22px;
                    height: 18px;
                    border: 2px solid #dee2e6;
                    border-radius: 4px;
                    transition: all 0.2s ease;
                }

                .mm-list-checkbox.checked::after {
                    background-color: #3498db;
                    border-color: #3498db;
                }

                .mm-list-checkbox.checked::before {
                    content: '✓';
                    position: absolute;
                    color: white;
                    font-size: 12px;
                    z-index: 1;
                }

                .mm-list-name {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    min-width: 0;
                }

                .mm-list-filename {
                    flex: 1;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    font-size: 14px;
                    color: #212529;
                }

                /* Modal */
                .mm-modal {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    z-index: 1000;
                    animation: mm-fadeIn 0.3s ease;
                }

                .mm-modal.show {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                @keyframes mm-fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }

                .mm-modal-content {
                    background: white;
                    border-radius: 16px;
                    padding: 25px;
                    max-width: 640px;
                    width: 90%;
                    max-height: 90vh;
                    overflow-y: auto;
                    position: relative;
                    animation: mm-slideUp 0.3s ease;
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
                }

                @keyframes mm-slideUp {
                    from {
                        transform: translateY(20px);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }

                .mm-modal-close {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    width: 25px;
                    height: 25px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 24px;
                    cursor: pointer;
                    color: #eb0d0d;
                    background: #f3f4f6;
                    border-radius: 8px;
                    transition: all 0.2s ease;
                    border: none;
                }

                .mm-modal-close:hover {
                    color: #374151;
                    background: #e5e7eb;
                    transform: scale(1.05);
                }

                /* Upload Area */
                .mm-upload-area {
                    border: 3px dashed #d1d5db;
                    border-radius: 12px;
                    padding: 16px 20px;
                    text-align: center;
                    background: #f9fafb;
                    transition: all 0.3s ease;
                    cursor: pointer;
                    min-height: 187px;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                }

                .mm-upload-area:hover {
                    background: #f3f4f6;
                    border-color: #9ca3af;
                }

                .mm-upload-area.drag-over {
                    background: #eff6ff;
                    border-color: #3b82f6;
                    border-style: solid;
                }

                .mm-upload-icon {
                    font-size: 64px;
                    margin-bottom: 20px;
                    opacity: 0.8;
                }

                /* Notification */
                .mm-notification {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: #333;
                    color: white;
                    padding: 12px 20px;
                    border-radius: 4px;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                    z-index: 9999;
                    animation: mm-slideUp 0.3s ease;
                }

                /* ============================================
                CONTEXT MENU
                ============================================ */
                .mm-context-menu {
                    position: fixed;
                    background: white;
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
                    min-width: 200px;
                    padding: 6px 0;
                    z-index: 10000;
                    animation: mm-fadeIn 0.15s ease;
                }

                .mm-context-item {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    padding: 10px 16px;
                    font-size: 14px;
                    color: #333;
                    cursor: pointer;
                    transition: background-color 0.15s ease;
                }

                .mm-context-item:hover {
                    background-color: #f5f5f5;
                }

                .mm-context-item svg {
                    width: 16px;
                    height: 16px;
                    flex-shrink: 0;
                    color: #666;
                }

                .mm-context-item:hover svg {
                    color: #333;
                }

                .mm-context-item.mm-context-danger {
                    color: #dc3545;
                }

                .mm-context-item.mm-context-danger svg {
                    color: #dc3545;
                }

                .mm-context-item.mm-context-danger:hover {
                    background-color: #fff5f5;
                }

                .mm-context-icon {
                    font-size: 16px;
                    width: 20px;
                    text-align: center;
                }

                .mm-context-separator {
                    height: 1px;
                    background-color: #e0e0e0;
                    margin: 6px 0;
                }

                @keyframes mm-fadeIn {
                    from {
                        opacity: 0;
                        transform: scale(0.95);
                    }
                    to {
                        opacity: 1;
                        transform: scale(1);
                    }
                }

                /* ============================================
                UTILITY CLASSES
                ============================================ */
                .mm-hidden {
                    display: none !important;
                }

                .mm-text-center {
                    text-align: center;
                }

                .mm-mt-1 { margin-top: 0.5rem; }
                .mm-mt-2 { margin-top: 1rem; }
                .mm-mt-3 { margin-top: 1.5rem; }

                .mm-mb-1 { margin-bottom: 0.5rem; }
                .mm-mb-2 { margin-bottom: 1rem; }
                .mm-mb-3 { margin-bottom: 1.5rem; }

                /* ============================================
                RESPONSIVE DESIGN
                ============================================ */
                @media (max-width: 1024px) {
                    .mm-media-grid {
                        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                    }
                }

                @media (max-width: 768px) {
                    /* Header improvements */
                    .mm-header {
                        flex-direction: column;
                        gap: 12px;
                        padding: 12px;
                    }

                    .mm-header-left {
                        width: 100%;
                        flex-direction: column;
                        gap: 10px;
                    }

                    .mm-header-right {
                        width: 100%;
                        justify-content: space-between;
                        flex-wrap: wrap;
                        gap: 6px;
                    }

                    /* Search improvements */
                    .mm-search-group {
                        width: 100%;
                    }

                    .mm-search-input {
                        flex: 1;
                        min-width: 0;
                        font-size: 14px;
                        padding: 10px 12px;
                    }

                    .mm-search-btn {
                        padding: 10px 14px;
                        white-space: nowrap;
                    }

                    /* File count */
                    .mm-file-count {
                        font-size: 13px;
                        text-align: center;
                    }

                    /* View controls */
                    .mm-view-controls {
                        order: -1;
                    }

                    .mm-view-btn {
                        padding: 10px 14px;
                    }

                    /* Buttons */
                    .mm-btn {
                        padding: 10px 14px;
                        font-size: 14px;
                        flex: 1;
                        min-width: 0;
                        justify-content: center;
                    }

                    .mm-btn span {
                        display: none;
                    }

                    .mm-upload-btn::before {
                        content: '📤';
                        font-size: 18px;
                    }

                    .mm-new-folder-btn::before {
                        content: '📁';
                        font-size: 18px;
                    }

                    /* Apps dropdown */
                    .mm-apps-dropdown-container {
                        margin-right: 0;
                    }

                    .mm-apps-dropdown-container .mm-btn {
                        min-width: 36px;
                    }

                    .mm-apps-dropdown-container .mm-btn span {
                        display: none;
                    }

                    .mm-apps-dropdown-menu {
                        right: 0;
                        left: auto;
                        min-width: 260px;
                    }

                    /* Selection toolbar */
                    .mm-selection-header {
                        gap: 8px;
                        padding: 10px 12px;
                        flex-wrap: nowrap;
                    }

                    .mm-selection-info {
                        gap: 8px;
                    }

                    .mm-selection-count {
                        font-size: 13px;
                        padding: 6px 10px;
                    }

                    .mm-clear-selection-btn {
                        width: 28px;
                        height: 28px;
                        min-width: 36px;
                        min-height: 36px;
                    }

                    .mm-clear-selection-btn svg {
                        width: 14px;
                        height: 14px;
                    }

                    .mm-selection-actions {
                        gap: 6px;
                    }

                    .mm-selection-btn {
                        padding: 8px 14px;
                        font-size: 13px;
                        gap: 6px;
                        min-height: 42px;
                    }

                    .mm-selection-btn svg {
                        width: 16px;
                        height: 16px;
                    }

                    .mm-selection-btn .btn-text {
                        display: inline;
                    }

                    /* Breadcrumb */
                    .mm-breadcrumb {
                        padding: 10px 12px;
                        font-size: 13px;
                        overflow-x: auto;
                        white-space: nowrap;
                        -webkit-overflow-scrolling: touch;
                    }

                    /* Grid improvements */
                    .mm-media-grid {
                        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                        gap: 12px;
                        padding: 12px;
                    }

                    .mm-media-item .mm-media-preview {
                        height: 120px;
                    }

                    .mm-media-name {
                        font-size: 13px;
                    }

                    .mm-media-meta {
                        font-size: 11px;
                    }

                    /* List view for mobile */
                    .mm-list-header,
                    .mm-media-grid.list-view .mm-media-item {
                        grid-template-columns: 32px 1fr 80px;
                        padding: 10px 12px;
                    }

                    .mm-list-header {
                        padding: 12px;
                        font-size: 11px;
                    }

                    .mm-list-header-item:nth-child(3),
                    .mm-list-header-item:nth-child(4),
                    .mm-list-header-item:nth-child(5),
                    .mm-media-grid.list-view .mm-list-type,
                    .mm-media-grid.list-view .mm-list-date,
                    .mm-media-grid.list-view .mm-list-size {
                        display: none;
                    }

                    .mm-list-filename {
                        font-size: 13px;
                    }

                    .mm-list-icon {
                        width: 20px;
                        height: 20px;
                    }

                    .mm-list-actions {
                        opacity: 1;
                        gap: 4px;
                    }

                    .mm-action-btn {
                        width: 28px;
                        height: 28px;
                    }

                    /* Modal improvements */
                    .mm-modal-content {
                        padding: 24px 16px;
                        width: 95%;
                        max-height: 95vh;
                    }

                    .mm-modal-content h2 {
                        font-size: 20px;
                        margin-bottom: 20px;
                    }

                    .mm-upload-area {
                        padding: 40px 20px;
                        min-height: 220px;
                    }

                    .mm-upload-icon {
                        font-size: 48px;
                    }

                    .mm-upload-text {
                        font-size: 14px;
                    }

                    .mm-upload-preview {
                        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
                        gap: 8px;
                    }

                    /* Context menu */
                    .mm-context-menu {
                        min-width: 180px;
                    }

                    .mm-context-item {
                        padding: 12px 16px;
                        font-size: 14px;
                    }
                }

                @media (max-width: 480px) {
                    /* Extra small devices */
                    .mm-wrapper {
                        padding: 10px;
                    }

                    /* Header */
                    .mm-header {
                        padding: 10px;
                        gap: 10px;
                    }

                    .mm-header-right {
                        gap: 6px;
                    }

                    .mm-apps-dropdown-menu {
                        min-width: 240px;
                        padding: 12px;
                        position: absolute;
                        right: -100px;
                    }

                    .mm-btn {
                        padding: 10px 12px;
                        font-size: 13px;
                        min-width: auto;
                        min-height: 32px;
                    }

                    /* Selection toolbar - mobile optimized */
                    .mm-selection-header {
                        padding: 6px 10px;
                        gap: 4px;
                        flex-direction: row;
                        align-items: center;
                        max-height: 40px !important;
                    }

                    .mm-selection-info {
                        justify-content: flex-start;
                        flex-shrink: 0;
                    }

                    .mm-selection-count {
                        font-size: 12px;
                        padding: 6px 10px;
                    }

                    .mm-clear-selection-btn {
                        width: 28px;
                        height: 28px;
                        min-width: 32px;
                        min-height: 32px;
                    }

                    .mm-clear-selection-btn svg {
                        width: 14px;
                        height: 14px;
                    }

                    .mm-selection-actions {
                        gap: 6px;
                        justify-content: flex-start;
                        overflow-x: auto;
                        -webkit-overflow-scrolling: touch;
                        flex: 1;
                        padding-bottom: 2px;
                    }

                    .mm-selection-btn {
                        padding: 8px 12px;
                        font-size: 12px;
                        gap: 6px;
                        min-height: 38px;
                    }

                    .mm-selection-btn svg {
                        width: 16px;
                        height: 16px;
                    }

                    /* Show icons and text on phones in landscape/medium size */
                    .mm-selection-btn .btn-text {
                        display: inline;
                    }

                    /* Search */
                    .mm-search-input {
                        padding: 10px;
                        font-size: 14px;
                    }

                    .mm-search-btn {
                        padding: 10px 12px;
                    }

                    .mm-search-btn span {
                        display: none;
                    }

                    .mm-search-btn::before {
                        content: '🔍';
                        font-size: 16px;
                    }

                    /* Grid - 2 columns on very small screens */
                    .mm-media-grid {
                        grid-template-columns: repeat(2, 1fr);
                        gap: 14px;
                        padding: 10px;
                    }

                    .mm-media-item .mm-media-preview {
                        height: 100px;
                    }

                    .mm-media-preview .mm-file-icon {
                        font-size: 36px;
                    }

                    .mm-media-info {
                        padding: 8px;
                    }

                    .mm-media-name {
                        font-size: 12px;
                        margin-bottom: 3px;
                    }

                    .mm-media-meta {
                        font-size: 10px;
                    }

                    /* List view - simplified */
                    .mm-list-header,
                    .mm-media-grid.list-view .mm-media-item {
                        grid-template-columns: 28px 1fr 60px;
                        padding: 8px 10px;
                    }

                    .mm-list-header {
                        font-size: 10px;
                        padding: 10px;
                    }

                    .mm-list-checkbox {
                        width: 16px;
                        height: 16px;
                    }

                    .mm-list-name {
                        gap: 8px;
                    }

                    .mm-list-icon {
                        width: 18px;
                        height: 18px;
                    }

                    .mm-list-filename {
                        font-size: 12px;
                    }

                    .mm-list-actions {
                        gap: 2px;
                    }

                    .mm-action-btn {
                        width: 26px;
                        height: 26px;
                    }

                    .mm-action-btn svg {
                        width: 14px;
                        height: 14px;
                    }

                    .mm-selection-btn {
                        padding: 8px 10px;
                        font-size: 12px;
                        gap: 4px;
                    }

                    .mm-selection-btn svg {
                        width: 14px;
                        height: 14px;
                    }

                    /* Breadcrumb */
                    .mm-breadcrumb {
                        padding: 8px 10px;
                        font-size: 12px;
                    }

                    .mm-breadcrumb-item {
                        margin-right: 6px;
                    }

                    .mm-breadcrumb-item:not(:last-child)::after {
                        margin-left: 6px;
                    }

                    /* Modal */
                    .mm-modal-content {
                        padding: 20px 12px;
                        border-radius: 12px;
                    }

                    .mm-modal-content h2 {
                        font-size: 18px;
                        margin-bottom: 16px;
                    }

                    .mm-close {
                        top: 12px;
                        right: 12px;
                        width: 32px;
                        height: 32px;
                        font-size: 20px;
                    }

                    .mm-upload-area {
                        padding: 30px 16px;
                        min-height: 180px;
                    }

                    .mm-upload-icon {
                        font-size: 40px;
                        margin-bottom: 16px;
                    }

                    .mm-upload-text {
                        font-size: 13px;
                    }

                    .mm-upload-preview {
                        grid-template-columns: repeat(2, 1fr);
                        gap: 6px;
                        margin-top: 16px;
                    }

                    /* Apps dropdown */
                    .mm-apps-dropdown-menu {
                        min-width: 240px;
                        padding: 12px;
                    }

                    .mm-dropdown-section {
                        margin-bottom: 16px;
                    }

                    .mm-dropdown-label {
                        font-size: 10px;
                        margin-bottom: 6px;
                    }

                    .mm-dropdown-select {
                        padding: 8px 10px;
                        font-size: 13px;
                    }

                    /* Context menu */
                    .mm-context-menu {
                        min-width: 160px;
                    }

                    .mm-context-item {
                        padding: 10px 14px;
                        font-size: 13px;
                        gap: 8px;
                    }
                }

                @media (max-width: 412px) {
                    /* Extra small devices */
                    .mm-apps-dropdown-container {
                        min-height: 32px;
                    }

                    .mm-view-btn {
                        padding: 8px 10px;
                        min-height: 32px;
                    }

                    .mm-wrapper {
                        padding: 10px;
                    }

                    /* Header */
                    .mm-header {
                        padding: 10px;
                        gap: 10px;
                    }

                    .mm-header-right {
                        gap: 6px;
                    }

                    .mm-apps-dropdown-menu {
                        min-width: 240px;
                        padding: 12px;
                        position: absolute;
                        right: -100px;
                    }

                    .mm-btn {
                        padding: 10px 12px;
                        font-size: 13px;
                        min-width: auto;
                        min-height: 32px;
                    }
                    .mm-new-folder-btn::before, .mm-upload-btn::before  {
                        display:none;
                    }
                    .mm-btn-fu {
                        padding: 9px 12px;
                        font-size: 13px;
                        min-width: auto;
                        min-height: 32px;
                    }

                    /* Selection toolbar - mobile optimized */
                    .mm-selection-header {
                        padding: 0px 6px;
                        gap: 4px;
                        flex-direction: row;
                        align-items: center;
                    }

                    .mm-selection-info {
                        justify-content: flex-start;
                        flex-shrink: 0;
                        max-height: 28px;
                    }

                    .mm-selection-count {
                        font-size: 12px;
                        padding: 6px 10px;
                    }

                    .mm-clear-selection-btn {
                        width: 28px;
                        height: 28px;
                        min-width: 32px;
                        min-height: 32px;
                    }

                    .mm-clear-selection-btn svg {
                        width: 14px;
                        height: 14px;
                    }

                    .mm-selection-actions {
                        gap: 6px;
                        justify-content: flex-start;
                        overflow-x: auto;
                        -webkit-overflow-scrolling: touch;
                        flex: 1;
                        padding-bottom: 2px;
                        max-height: 28px;
                    }

                    .mm-selection-btn {
                        padding: 8px 12px;
                        font-size: 12px;
                        gap: 6px;
                        min-height: 38px;
                    }

                    .mm-selection-btn svg {
                        width: 16px;
                        height: 16px;
                    }

                    /* Show icons and text on phones in landscape/medium size */
                    .mm-selection-btn .btn-text {
                        display: inline;
                    }

                    /* Search */
                    .mm-search-input {
                        padding: 10px;
                        font-size: 14px;
                    }

                    .mm-search-btn {
                        padding: 10px 12px;
                    }

                    .mm-search-btn span {
                        display: none;
                    }

                    .mm-search-btn::before {
                        content: '🔍';
                        font-size: 16px;
                    }

                    /* Grid - 2 columns on very small screens */
                    .mm-media-grid {
                        grid-template-columns: repeat(2, 1fr);
                        gap: 10px;
                        padding: 10px;
                    }

                    .mm-media-item .mm-media-preview {
                        height: 100px;
                    }

                    .mm-media-preview .mm-file-icon {
                        font-size: 36px;
                    }

                    .mm-media-info {
                        padding: 8px;
                    }

                    .mm-media-name {
                        font-size: 12px;
                        margin-bottom: 3px;
                    }

                    .mm-media-meta {
                        font-size: 10px;
                    }

                    /* List view - simplified */
                    .mm-list-header,
                    .mm-media-grid.list-view .mm-media-item {
                        grid-template-columns: 28px 1fr 60px;
                        padding: 8px 10px;
                    }

                    .mm-list-header {
                        font-size: 10px;
                        padding: 10px;
                    }

                    .mm-list-checkbox {
                        width: 16px;
                        height: 16px;
                    }

                    .mm-list-name {
                        gap: 8px;
                    }

                    .mm-list-icon {
                        width: 18px;
                        height: 18px;
                    }

                    .mm-list-filename {
                        font-size: 12px;
                    }

                    .mm-list-actions {
                        gap: 2px;
                    }

                    .mm-action-btn {
                        width: 26px;
                        height: 26px;
                    }

                    .mm-action-btn svg {
                        width: 14px;
                        height: 14px;
                    }

                    /* Selection toolbar - scroll horizontally */
                    .mm-selection-header {
                        padding: 6px;
                        gap: 4px;
                    }

                    .mm-selection-btn {
                        padding: 8px 10px;
                        font-size: 12px;
                        gap: 4px;
                    }

                    .mm-selection-btn svg {
                        width: 14px;
                        height: 14px;
                    }

                    /* Breadcrumb */
                    .mm-breadcrumb {
                        padding: 8px 10px;
                        font-size: 12px;
                    }

                    .mm-breadcrumb-item {
                        margin-right: 6px;
                    }

                    .mm-breadcrumb-item:not(:last-child)::after {
                        margin-left: 6px;
                    }

                    /* Modal */
                    .mm-modal-content {
                        padding: 20px 12px;
                        border-radius: 12px;
                    }

                    .mm-modal-content h2 {
                        font-size: 18px;
                        margin-bottom: 16px;
                    }

                    .mm-close {
                        top: 12px;
                        right: 12px;
                        width: 32px;
                        height: 32px;
                        font-size: 20px;
                    }

                    .mm-upload-area {
                        padding: 30px 16px;
                        min-height: 180px;
                    }

                    .mm-upload-icon {
                        font-size: 40px;
                        margin-bottom: 16px;
                    }

                    .mm-upload-text {
                        font-size: 13px;
                    }

                    .mm-upload-preview {
                        grid-template-columns: repeat(2, 1fr);
                        gap: 6px;
                        margin-top: 16px;
                    }

                    /* Apps dropdown */
                    .mm-apps-dropdown-menu {
                        min-width: 240px;
                        padding: 12px;
                    }

                    .mm-dropdown-section {
                        margin-bottom: 16px;
                    }

                    .mm-dropdown-label {
                        font-size: 10px;
                        margin-bottom: 6px;
                    }

                    .mm-dropdown-select {
                        padding: 8px 10px;
                        font-size: 13px;
                    }

                    /* Context menu */
                    .mm-context-menu {
                        min-width: 160px;
                    }

                    .mm-context-item {
                        padding: 10px 14px;
                        font-size: 13px;
                        gap: 8px;
                    }
                }

                /* ============================================
                EXTRA SMALL DEVICES (< 360px)
                ============================================ */
                @media (max-width: 360px) {
                    .mm-selection-header {
                        padding: 8px;
                        gap: 6px;
                    }

                    .mm-selection-count {
                        font-size: 11px;
                        padding: 5px 8px;
                    }

                    .mm-clear-selection-btn {
                        min-width: 36px;
                        min-height: 36px;
                    }

                    .mm-selection-btn {
                        padding: 6px 10px;
                        font-size: 11px;
                        min-height: 36px;
                    }

                    .mm-selection-btn .btn-text {
                        display: none;
                    }

                    .mm-selection-btn svg {
                        width: 18px;
                        height: 18px;
                    }
                }
            `;
        }

        /**
         * Create HTML structure
         */
        createHTML() {
            const container = typeof this.config.container === 'string'
                ? document.querySelector(this.config.container)
                : this.config.container;

            if (!container) {
                throw new Error('Container not found');
            }

            container.innerHTML = `
            <div class="mm-container">
                <div class="mm-wrapper">
                    <!-- Header -->
                    <header class="mm-header">
                        <div class="mm-header-left">
                            <div class="mm-search-group">
                                <input type="text" class="mm-search-input" placeholder="Search file name...">
                                <button class="mm-search-btn">
                                    <span>🔍 Search</span>
                                </button>
                            </div>
                            <div class="mm-file-count">
                                Total: <span class="mm-total-files">0</span> files
                            </div>
                        </div>

                        <div class="mm-header-right">
                            <!-- Apps Dropdown -->
                            <div class="mm-apps-dropdown-container">
                                <button class="mm-btn mm-btn-secondary mm-apps-dropdown-btn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="5" cy="5" r="2"/>
                                        <circle cx="12" cy="5" r="2"/>
                                        <circle cx="19" cy="5" r="2"/>
                                        <circle cx="5" cy="12" r="2"/>
                                        <circle cx="12" cy="12" r="2"/>
                                        <circle cx="19" cy="12" r="2"/>
                                        <circle cx="5" cy="19" r="2"/>
                                        <circle cx="12" cy="19" r="2"/>
                                        <circle cx="19" cy="19" r="2"/>
                                    </svg>
                                    <span>Apps</span>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="mm-dropdown-arrow">
                                        <path d="M7 10l5 5 5-5z"/>
                                    </svg>
                                </button>

                                <div class="mm-apps-dropdown-menu">
                                    <div class="mm-dropdown-section">
                                        <label class="mm-dropdown-label">SORT BY</label>
                                        <select class="mm-dropdown-select mm-sort-by-select">
                                            <option value="date">Upload date</option>
                                            <option value="name">File name</option>
                                            <option value="size">File size</option>
                                            <option value="type">File type</option>
                                        </select>
                                    </div>

                                    <div class="mm-dropdown-section">
                                        <label class="mm-dropdown-label">DISPLAY</label>
                                        <select class="mm-dropdown-select mm-display-select">
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                            <option value="200">200</option>
                                            <option value="all">All</option>
                                        </select>
                                    </div>

                                    <div class="mm-dropdown-section">
                                        <label class="mm-dropdown-label">THUMBNAILS</label>
                                        <div class="mm-thumbnail-slider-container">
                                            <input type="range" class="mm-thumbnail-slider" min="100" max="240" value="140" step="10">
                                            <span class="mm-slider-value">140</span>
                                        </div>
                                    </div>

                                    <div class="mm-dropdown-section">
                                        <label class="mm-dropdown-label">Keep aspect ratio</label>
                                        <label class="mm-toggle-switch">
                                            <input type="checkbox" class="mm-aspect-ratio-toggle" checked>
                                            <span class="mm-toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mm-view-controls">
                                <button class="mm-view-btn active" data-view="grid" title="Grid View">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <rect x="3" y="3" width="7" height="7"/>
                                        <rect x="14" y="3" width="7" height="7"/>
                                        <rect x="3" y="14" width="7" height="7"/>
                                        <rect x="14" y="14" width="7" height="7"/>
                                    </svg>
                                </button>
                                <button class="mm-view-btn" data-view="list" title="List View">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <rect x="3" y="4" width="18" height="2"/>
                                        <rect x="3" y="9" width="18" height="2"/>
                                        <rect x="3" y="14" width="18" height="2"/>
                                        <rect x="3" y="19" width="18" height="2"/>
                                    </svg>
                                </button>
                            </div>

                            ${this.config.allowUpload ? `
                            <button class="mm-btn-fu mm-btn-primary mm-new-folder-btn">
                                <span>📁 New Folder</span>
                            </button>
                            <button class="mm-btn-fu mm-btn-success mm-upload-btn">
                                <span>📤 Upload</span>
                            </button>
                            ` : ''}
                        </div>
                    </header>

                    <!-- Selection Header -->
                    <div class="mm-selection-header mm-hidden">
                        <div class="mm-selection-info">
                            <span class="mm-selection-count">0 selected</span>
                            <button class="mm-clear-selection-btn" title="Clear selection">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>

                        <div class="mm-selection-actions">
                            <button class="mm-selection-btn mm-download-btn" title="Download">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z" />
                                </svg>
                                <span class="btn-text">Download</span>
                            </button>
                            <button class="mm-selection-btn mm-view-btn-sel" title="View">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" />
                                </svg>
                                <span class="btn-text">View</span>
                            </button>
                            ${this.config.allowRename ? `
                            <button class="mm-selection-btn mm-rename-btn" title="Rename">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                                </svg>
                                <span class="btn-text">Rename</span>
                            </button>
                            ` : ''}
                            ${this.config.allowDelete ? `
                            <button class="mm-selection-btn danger mm-delete-btn" title="Delete">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" />
                                </svg>
                                <span class="btn-text">Delete</span>
                            </button>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Breadcrumb -->
                    <nav class="mm-breadcrumb">
                        <span class="mm-breadcrumb-item active" data-path="/">Home</span>
                    </nav>

                    <!-- Media Container -->
                    <div class="mm-media-container">
                        <!-- List Header -->
                        <div class="mm-list-header mm-hidden">
                            <div class="mm-list-header-item">
                                <div class="mm-list-checkbox mm-select-all"></div>
                            </div>
                            <div class="mm-list-header-item" data-sort="name">
                                Name <span class="mm-sort-icon">▼</span>
                            </div>
                            <div class="mm-list-header-item" data-sort="type">Type</div>
                            <div class="mm-list-header-item" data-sort="date">Created At</div>
                            <div class="mm-list-header-item" data-sort="size">Size</div>
                            <div class="mm-list-header-item">Actions</div>
                        </div>

                        <!-- Media Grid -->
                        <div class="mm-media-grid"></div>
                    </div>

                    <!-- Upload Modal -->
                    ${this.config.allowUpload ? `
                        <div class="mm-modal mm-upload-modal">
                            <div class="mm-modal-content">
                                <button class="mm-modal-close">&times;</button>
                                <h2>Upload Files</h2>
                                <div class="mm-upload-area">
                                    <input type="file" class="mm-file-input" multiple hidden accept="${this.config.acceptedFileTypes}">
                                    <div class="mm-upload-icon">📁</div>
                                    <p>Drag & drop files here or click to browse</p>
                                </div>
                                <div class="mm-upload-progress" style="display: none; margin-top: 20px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                        <span class="mm-upload-status">Uploading...</span>
                                        <span class="mm-upload-percent">0%</span>
                                    </div>
                                    <div style="width: 100%; background-color: #e0e0e0; border-radius: 10px; height: 8px; overflow: hidden;">
                                        <div class="mm-progress-bar" style="width: 0%; height: 100%; background-color: #4CAF50; transition: width 0.3s ease;"></div>
                                    </div>
                                    <div class="mm-upload-filename" style="margin-top: 8px; font-size: 12px; color: #666;"></div>
                                </div>
                                <div class="mm-upload-preview"></div>
                            </div>
                        </div>
                        ` : ''}

                    <!-- Preview Modal -->
                    <div class="mm-modal mm-preview-modal">
                        <div class="mm-modal-content">
                            <button class="mm-modal-close">&times;</button>
                            <div class="mm-preview-container"></div>
                        </div>
                    </div>
                </div>
            </div>
            `;
        }

        /**
         * Cache DOM elements
         */
        cacheElements() {
            const c = this.config.container;
            const container = typeof c === 'string' ? document.querySelector(c) : c;

            this.elements = {
                grid: container.querySelector('.mm-media-grid'),
                searchInput: container.querySelector('.mm-search-input'),
                searchBtn: container.querySelector('.mm-search-btn'),
                totalFiles: container.querySelector('.mm-total-files'),
                uploadBtn: container.querySelector('.mm-upload-btn'),
                newFolderBtn: container.querySelector('.mm-new-folder-btn'),
                viewBtns: container.querySelectorAll('.mm-view-btn'),
                appsDropdownContainer: container.querySelector('.mm-apps-dropdown-container'),
                appsDropdownBtn: container.querySelector('.mm-apps-dropdown-btn'),
                appsDropdownMenu: container.querySelector('.mm-apps-dropdown-menu'),
                sortBySelect: container.querySelector('.mm-sort-by-select'),
                displaySelect: container.querySelector('.mm-display-select'),
                thumbnailSlider: container.querySelector('.mm-thumbnail-slider'),
                sliderValue: container.querySelector('.mm-slider-value'),
                aspectRatioToggle: container.querySelector('.mm-aspect-ratio-toggle'),
                selectionHeader: container.querySelector('.mm-selection-header'),
                selectionCount: container.querySelector('.mm-selection-count'),
                clearSelectionBtn: container.querySelector('.mm-clear-selection-btn'),
                downloadBtn: container.querySelector('.mm-download-btn'),
                viewBtnSel: container.querySelector('.mm-view-btn-sel'),
                renameBtn: container.querySelector('.mm-rename-btn'),
                deleteBtn: container.querySelector('.mm-delete-btn'),
                breadcrumb: container.querySelector('.mm-breadcrumb'),
                listHeader: container.querySelector('.mm-list-header'),
                selectAll: container.querySelector('.mm-select-all'),
                uploadModal: container.querySelector('.mm-upload-modal'),
                previewModal: container.querySelector('.mm-preview-modal'),
                fileInput: container.querySelector('.mm-file-input'),
                uploadArea: container.querySelector('.mm-upload-area'),
                uploadPreview: container.querySelector('.mm-upload-preview'),
                uploadProgress: container.querySelector('.mm-upload-progress'),
                uploadStatus: container.querySelector('.mm-upload-status'),
                uploadPercent: container.querySelector('.mm-upload-percent'),
                progressBar: container.querySelector('.mm-progress-bar'),
                uploadFilename: container.querySelector('.mm-upload-filename'),
                previewContainer: container.querySelector('.mm-preview-container')
            };
        }

        /**
         * Setup event listeners
         */
        setupEventListeners() {
            // Search
            if (this.elements.searchInput) {
                this.elements.searchInput.addEventListener('input', (e) => {
                    this.searchFiles(e.target.value);
                });
            }

            // View toggle
            this.elements.viewBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    this.changeViewMode(btn.dataset.view);
                });
            });

            // Upload
            if (this.elements.uploadBtn) {
                this.elements.uploadBtn.addEventListener('click', () => {
                    this.showUploadModal();
                });
            }

            // New folder
            if (this.elements.newFolderBtn) {
                this.elements.newFolderBtn.addEventListener('click', () => {
                    this.createNewFolder();
                });
            }

            // Selection actions
            if (this.elements.clearSelectionBtn) {
                this.elements.clearSelectionBtn.addEventListener('click', () => {
                    this.clearSelection();
                });
            }

            if (this.elements.downloadBtn) {
                this.elements.downloadBtn.addEventListener('click', () => {
                    this.downloadSelected();
                });
            }

            if (this.elements.viewBtnSel) {
                this.elements.viewBtnSel.addEventListener('click', () => {
                    this.viewSelected();
                });
            }

            if (this.elements.renameBtn) {
                this.elements.renameBtn.addEventListener('click', () => {
                    this.renameSelected();
                });
            }

            if (this.elements.deleteBtn) {
                this.elements.deleteBtn.addEventListener('click', () => {
                    this.deleteSelected();
                });
            }

            // Upload modal
            if (this.elements.uploadModal) {
                this.setupUploadEvents();
                this.setupModalEvents(this.elements.uploadModal);
            }

            // Preview modal
            if (this.elements.previewModal) {
                this.setupModalEvents(this.elements.previewModal);
            }
        }

        /**
         * Setup upload events
         */
        setupUploadEvents() {
            const { fileInput, uploadArea } = this.elements;

            if (fileInput) {
                fileInput.addEventListener('change', (e) => {
                    this.handleFileSelect(e.target.files);
                });
            }

            if (uploadArea) {
                uploadArea.addEventListener('click', () => {
                    fileInput?.click();
                });

                uploadArea.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    uploadArea.classList.add('drag-over');
                });

                uploadArea.addEventListener('dragleave', () => {
                    uploadArea.classList.remove('drag-over');
                });

                uploadArea.addEventListener('drop', (e) => {
                    e.preventDefault();
                    uploadArea.classList.remove('drag-over');
                    this.handleFileSelect(e.dataTransfer.files);
                });
            }
        }

        /**
         * Setup modal events
         */
        setupModalEvents(modal) {
            const closeBtn = modal.querySelector('.mm-modal-close');

            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    modal.classList.remove('show');
                });
            }

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('show');
                }
            });
        }

        /**
         * Setup sorting
         */
        setupSorting() {
            const headers = this.elements.listHeader?.querySelectorAll('[data-sort]');
            headers?.forEach(header => {
                header.addEventListener('click', () => {
                    this.sortFiles(header.dataset.sort);
                    this.updateSortIcons(header);
                });
            });
        }

        /**
         * Setup select all
         */
        setupSelectAll() {
            if (this.elements.selectAll) {
                this.elements.selectAll.addEventListener('click', () => {
                    this.toggleSelectAll();
                });
            }
        }

        /**
         * Setup apps dropdown
         */
        setupAppsDropdown() {
            if (!this.elements.appsDropdownBtn) return;

            // Toggle dropdown
            this.elements.appsDropdownBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.elements.appsDropdownContainer.classList.toggle('open');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!this.elements.appsDropdownContainer.contains(e.target)) {
                    this.elements.appsDropdownContainer.classList.remove('open');
                }
            });

            // Prevent dropdown from closing when clicking inside
            this.elements.appsDropdownMenu.addEventListener('click', (e) => {
                e.stopPropagation();
            });

            // Sort by select
            if (this.elements.sortBySelect) {
                this.elements.sortBySelect.addEventListener('change', (e) => {
                    this.sortBy = e.target.value;
                    this.sortFiles(this.sortBy);
                });
            }

            // Display select
            if (this.elements.displaySelect) {
                this.elements.displaySelect.addEventListener('change', (e) => {
                    const value = e.target.value;
                    // Implement display limit functionality
                    console.log('Display limit:', value);
                });
            }

            // Thumbnail slider
            if (this.elements.thumbnailSlider) {
                this.elements.thumbnailSlider.addEventListener('input', (e) => {
                    const value = e.target.value;
                    this.elements.sliderValue.textContent = value;

                    // Update thumbnail size CSS variable (used by styles)
                    document.documentElement.style.setProperty('--thumbnail-size', value + 'px');

                    // Update the grid container so the main grid respects the new item size
                    const grid = this.elements.grid || document.querySelector('.mm-media-grid');
                    if (grid && this.viewMode === 'grid') {
                        // Make columns equal to thumbnail size so the grid layout adapts
                        grid.style.gridTemplateColumns = `repeat(auto-fill, ${value}px)`;
                    } else if (grid) {
                        // Reset to default when not in grid view
                        grid.style.gridTemplateColumns = '';
                    }

                    // Update individual grid items (fallback)
                    const items = grid ? grid.querySelectorAll('.mm-media-item') : document.querySelectorAll('.mm-media-item');
                    items.forEach(item => {
                        if (this.viewMode === 'grid') {
                            item.style.width = value + 'px';
                            item.style.height = value + 'px';
                        } else {
                            // Clear inline style when in list view
                            item.style.width = '';
                            item.style.height = '';
                        }
                    });
                });
            }

            // Aspect ratio toggle
            if (this.elements.aspectRatioToggle) {
                this.elements.aspectRatioToggle.addEventListener('change', (e) => {
                    const keepAspectRatio = e.target.checked;

                    // Update all media icons
                    const icons = document.querySelectorAll('.mm-file-icon');
                    icons.forEach(icon => {
                        if (keepAspectRatio) {
                            icon.style.objectFit = 'contain';
                        } else {
                            icon.style.objectFit = 'cover';
                        }
                    });
                });
            }
        }

        /**
         * Update breadcrumb navigation
         */
        updateBreadcrumb() {
            if (!this.elements.breadcrumb) return;

            const path = this.currentPath || '/';
            const parts = path === '/' ? [] : path.split('/').filter(p => p);

            let breadcrumbHTML = '<span class="mm-breadcrumb-item" data-path="/">Home</span>';

            if (parts.length > 0) {
                let currentPath = '';
                parts.forEach((part, index) => {
                    currentPath += (currentPath ? '/' : '') + part;
                    const isLast = index === parts.length - 1;
                    breadcrumbHTML += `<span class="mm-breadcrumb-item ${isLast ? 'active' : ''}" data-path="${currentPath}">${this.escapeHtml(part)}</span>`;
                });
            }

            this.elements.breadcrumb.innerHTML = breadcrumbHTML;

            // Add click handlers to breadcrumb items
            this.elements.breadcrumb.querySelectorAll('.mm-breadcrumb-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    const targetPath = e.target.getAttribute('data-path');
                    // Always trigger onFolderOpen to reload files from server
                    if (this.config.onFolderOpen) {
                        this.config.onFolderOpen({ path: targetPath });
                    }
                });
            });
        }

        /**
         * Render files
         */
        render() {
            if (!this.elements.grid) return;

            this.elements.grid.innerHTML = '';
            this.elements.grid.className = this.viewMode === 'list' ? 'mm-media-grid list-view' : 'mm-media-grid';

            const filesToRender = this.filteredFiles.length > 0 ? this.filteredFiles : this.files;

            filesToRender.forEach(file => {
                const item = this.createMediaItem(file);
                this.elements.grid.appendChild(item);
            });

            this.updateFileCount();
            this.updateListHeaderVisibility();
            this.updateBreadcrumb();
            this.updateSelectionHeader();
        }        /**
         * Create media item element
         */
        createMediaItem(file) {
            const div = document.createElement('div');
            div.className = 'mm-media-item';
            div.dataset.fileId = file.id;

            if (this.selectedFiles.has(file.id)) {
                div.classList.add('selected');
            }

            if (this.viewMode === 'list') {
                div.innerHTML = this.createListViewItem(file);
                this.attachListViewEvents(div, file);
            } else {
                div.innerHTML = this.createGridViewItem(file);
            }

            // Right-click context menu for folders and files
            div.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.showContextMenu(e, file);
            });

            div.addEventListener('click', (e) => {
                if (e.target.closest('.mm-list-checkbox') || e.target.closest('.mm-action-btn')) {
                    return;
                }

                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    this.toggleFileSelection(file.id);
                    div.classList.toggle('selected');
                    this.updateSelectionHeader();
                    return;
                }

                if (file.type === 'folder') {
                    this.openFolder(file);
                } else {
                    this.toggleFileSelection(file.id);
                    div.classList.toggle('selected');
                    this.updateSelectionHeader();
                }
            });

            return div;
        }

        /**
         * Create grid view item HTML
         */
        createGridViewItem(file) {
            // console.log("allfile", file);
            // Show actual image thumbnail if it's an image with URL
            const previewContent = (file.type === 'image' && file.url)
                ? `<img src="${file.url}" alt="${this.escapeHtml(file.name)}" style="width: 100%; height: 100%; object-fit: cover;">`
                : `<span class="mm-file-icon">${this.getFileIcon(file.type)}</span>`;
            return `
                <div class="mm-media-preview">
                    ${previewContent}
                </div>
                <div class="mm-media-info">
                    <div class="mm-media-name">${this.escapeHtml(file.name)}</div>
                    <div class="mm-media-meta">
                        ${file.size ? this.formatFileSize(file.size) : 'Folder'}
                        ${file.modified ? ' • ' + this.formatDate(file.modified) : ''}
                    </div>
                </div>
            `;
        }

        /**
         * Create list view item HTML
         */
        createListViewItem(file) {
            return `
                <div class="mm-list-checkbox ${this.selectedFiles.has(file.id) ? 'checked' : ''}" data-file-id="${file.id}"></div>
                <div class="mm-list-name">
                    <span>${this.getFileIcon(file.type)}</span>
                    <span class="mm-list-filename">${this.escapeHtml(file.name)}</span>
                </div>
                <div>${file.type}</div>
                <div>${this.formatDateTime(file.modified)}</div>
                <div>${file.size ? this.formatFileSize(file.size) : '--'}</div>
                <div></div>
            `;
        }

        /**
         * Attach list view events
         */
        attachListViewEvents(element, file) {
            const checkbox = element.querySelector('.mm-list-checkbox');
            if (checkbox) {
                checkbox.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.toggleFileSelection(file.id);
                    checkbox.classList.toggle('checked');
                    this.updateSelectionHeader();
                });
            }
        }

        /**
         * Get file icon
         */
        getFileIcon(type) {
            const icons = {
                folder: '📁',
                image: '🖼️',
                video: '🎥',
                audio: '🎵',
                document: '📄',
                pdf: '📕',
                archive: '🗜️'
            };
            return icons[type] || icons.document;
        }

        /**
         * Format file size
         */
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        /**
         * Format date
         */
        formatDate(date) {
            if (!date || !(date instanceof Date)) return '--';
            return new Intl.DateTimeFormat('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            }).format(date);
        }

        /**
         * Format date time
         */
        formatDateTime(date) {
            if (!date || !(date instanceof Date)) return '--';
            return new Intl.DateTimeFormat('en-US', {
                month: '2-digit',
                day: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }).format(date);
        }

        /**
         * Escape HTML
         */
        escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        /**
         * Change view mode
         */
        changeViewMode(mode) {
            if (this.viewMode === mode) return;

            this.viewMode = mode;

            this.elements.viewBtns.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.view === mode);
            });

            this.render();
        }

        /**
         * Update list header visibility
         */
        updateListHeaderVisibility() {
            if (this.elements.listHeader) {
                this.elements.listHeader.classList.toggle('mm-hidden', this.viewMode !== 'list');
            }
        }

        /**
         * Search files
         */
        searchFiles(query) {
            if (!query || query.trim() === '') {
                this.filteredFiles = [...this.files];
            } else {
                const searchTerm = query.toLowerCase();
                this.filteredFiles = this.files.filter(file =>
                    file.name.toLowerCase().includes(searchTerm)
                );
            }
            this.render();
        }

        /**
         * Sort files
         */
        sortFiles(sortBy) {
            this.sortBy = sortBy;
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';

            this.filteredFiles.sort((a, b) => {
                let aVal, bVal;

                switch (sortBy) {
                    case 'name':
                        aVal = a.name.toLowerCase();
                        bVal = b.name.toLowerCase();
                        break;
                    case 'type':
                        aVal = a.type;
                        bVal = b.type;
                        break;
                    case 'date':
                        aVal = a.modified ? a.modified.getTime() : 0;
                        bVal = b.modified ? b.modified.getTime() : 0;
                        break;
                    case 'size':
                        aVal = a.size || 0;
                        bVal = b.size || 0;
                        break;
                    default:
                        return 0;
                }

                if (this.sortDirection === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });

            this.render();
        }

        /**
         * Update sort icons
         */
        updateSortIcons(activeHeader) {
            const icons = this.elements.listHeader?.querySelectorAll('.mm-sort-icon');
            icons?.forEach(icon => {
                icon.textContent = '';
            });

            const icon = activeHeader.querySelector('.mm-sort-icon');
            if (icon) {
                icon.textContent = this.sortDirection === 'asc' ? '▲' : '▼';
            }
        }

        /**
         * Toggle file selection
         */
        toggleFileSelection(fileId) {
            if (this.selectedFiles.has(fileId)) {
                this.selectedFiles.delete(fileId);
            } else {
                this.selectedFiles.add(fileId);
            }
        }

        /**
         * Toggle select all
         */
        toggleSelectAll() {
            const isAllSelected = this.elements.selectAll.classList.contains('checked');

            if (isAllSelected) {
                this.selectedFiles.clear();
                this.elements.selectAll.classList.remove('checked');
                document.querySelectorAll('.mm-media-item').forEach(item => {
                    item.classList.remove('selected');
                });
                document.querySelectorAll('.mm-list-checkbox').forEach(cb => {
                    cb.classList.remove('checked');
                });
            } else {
                this.files.forEach(file => {
                    this.selectedFiles.add(file.id);
                });
                this.elements.selectAll.classList.add('checked');
                document.querySelectorAll('.mm-media-item').forEach(item => {
                    item.classList.add('selected');
                });
                document.querySelectorAll('.mm-list-checkbox').forEach(cb => {
                    cb.classList.add('checked');
                });
            }

            this.updateSelectionHeader();
        }

        /**
         * Clear selection
         */
        clearSelection() {
            this.selectedFiles.clear();
            document.querySelectorAll('.mm-media-item.selected').forEach(item => {
                item.classList.remove('selected');
            });
            document.querySelectorAll('.mm-list-checkbox.checked').forEach(checkbox => {
                checkbox.classList.remove('checked');
            });
            if (this.elements.selectAll) {
                this.elements.selectAll.classList.remove('checked');
            }
            this.updateSelectionHeader();
        }

        /**
         * Update selection header
         */
        updateSelectionHeader() {
            if (!this.elements.selectionHeader) return;

            if (this.selectedFiles.size > 0) {
                this.elements.selectionHeader.classList.remove('mm-hidden');
                if (this.elements.selectionCount) {
                    this.elements.selectionCount.textContent = `${this.selectedFiles.size} selected`;
                }
            } else {
                this.elements.selectionHeader.classList.add('mm-hidden');
            }
        }

        /**
         * Update file count
         */
        updateFileCount() {
            if (this.elements.totalFiles) {
                const count = this.filteredFiles.length > 0 ? this.filteredFiles.length : this.files.length;
                this.elements.totalFiles.textContent = count;
            }
        }

        /**
         * Show upload modal
         */
        showUploadModal() {
            if (this.elements.uploadModal) {
                this.elements.uploadModal.classList.add('show');
                if (this.elements.uploadPreview) {
                    this.elements.uploadPreview.innerHTML = '';
                }
            }
        }

        /**
         * Handle file select
         */
        handleFileSelect(files) {
            if (!files || files.length === 0) return;

            if (this.elements.uploadPreview) {
                this.elements.uploadPreview.innerHTML = '';
            }

            Array.from(files).forEach(file => {
                const newFile = {
                    id: Date.now() + Math.random(),
                    name: file.name,
                    type: this.getFileType(file.type),
                    size: file.size,
                    modified: new Date(),
                    file: file
                };

                this.files.push(newFile);
                this.filteredFiles.push(newFile);

                if (this.config.onFileUpload) {
                    this.config.onFileUpload(newFile);
                }
            });

            setTimeout(() => {
                this.render();
                if (this.elements.uploadModal) {
                    this.elements.uploadModal.classList.remove('show');
                }
                this.showNotification(`${files.length} file(s) uploaded successfully`);
            }, 1500);
        }

        /**
         * Get file type from MIME type
         */
        getFileType(mimeType) {
            if (!mimeType) return 'document';
            if (mimeType.startsWith('image/')) return 'image';
            if (mimeType.startsWith('video/')) return 'video';
            if (mimeType.startsWith('audio/')) return 'audio';
            if (mimeType.includes('pdf')) return 'pdf';
            if (mimeType.includes('zip') || mimeType.includes('rar')) return 'archive';
            return 'document';
        }

        /**
         * Create new folder
         */
        createNewFolder() {
            const name = prompt('Enter folder name:');
            if (name && name.trim()) {
                const folderName = name.trim();

                // Validate folder name
                if (!/^[a-zA-Z0-9_-]+$/.test(folderName)) {
                    this.showNotification('Invalid folder name. Use only letters, numbers, hyphens, and underscores.', 'error');
                    return;
                }

                // Use callback if provided (for backend integration)
                if (this.config.onCreateFolder && typeof this.config.onCreateFolder === 'function') {
                    // Pass folder name and current path
                    const currentPath = this.currentPath === '/' ? '' : this.currentPath;
                    this.config.onCreateFolder(folderName, currentPath);
                } else {
                    // Fallback: just add to local list (no persistence)
                    const newFolder = {
                        id: 'folder-' + Date.now(),
                        name: folderName,
                        type: 'folder',
                        path: this.currentPath === '/' ? folderName : this.currentPath + '/' + folderName,
                        size: null,
                        modified: new Date()
                    };

                    this.files.unshift(newFolder);
                    this.filteredFiles.unshift(newFolder);
                    this.render();
                    this.showNotification(`Folder "${folderName}" created (local only - no callback configured)`, 'warning');
                }
            }
        }

        /**
         * Open folder
         */
        openFolder(folder) {
            // Call the onFolderOpen callback if provided
            if (this.config.onFolderOpen && typeof this.config.onFolderOpen === 'function') {
                this.config.onFolderOpen(folder);
            } else {
                console.log('Open folder:', folder);
            }
        }

        /**
         * Show context menu for file/folder
         */
        showContextMenu(e, item) {
            // Remove any existing context menu
            this.hideContextMenu();

            this.contextMenuTarget = item;
            const isFolder = item.type === 'folder';

            // Create context menu
            const menu = document.createElement('div');
            menu.className = 'mm-context-menu';
            menu.id = 'mm-context-menu';

            // Build menu items based on type
            let menuItems = '';

            if (isFolder) {
                menuItems = `
                    <div class="mm-context-item" data-action="open">
                        <span class="mm-context-icon">📂</span>
                        <span>Open Folder</span>
                    </div>
                    <div class="mm-context-separator"></div>
                    <div class="mm-context-item" data-action="rename">
                        <span class="mm-context-icon">✏️</span>
                        <span>Rename</span>
                    </div>
                    <div class="mm-context-item" data-action="move">
                        <span class="mm-context-icon">📁</span>
                        <span>Move To...</span>
                    </div>
                    <div class="mm-context-separator"></div>
                    <div class="mm-context-item mm-context-danger" data-action="delete">
                        <span class="mm-context-icon">🗑️</span>
                        <span>Delete Folder</span>
                    </div>
                `;
            } else {
                menuItems = `
                    <div class="mm-context-item" data-action="view">
                        <span class="mm-context-icon">👁️</span>
                        <span>View Details</span>
                    </div>
                    <div class="mm-context-item" data-action="download">
                        <span class="mm-context-icon">⬇️</span>
                        <span>Download</span>
                    </div>
                    <div class="mm-context-separator"></div>
                    <div class="mm-context-item" data-action="rename">
                        <span class="mm-context-icon">✏️</span>
                        <span>Rename</span>
                    </div>
                    <div class="mm-context-item" data-action="move">
                        <span class="mm-context-icon">📁</span>
                        <span>Move To...</span>
                    </div>
                    <div class="mm-context-separator"></div>
                    <div class="mm-context-item mm-context-danger" data-action="delete">
                        <span class="mm-context-icon">🗑️</span>
                        <span>Delete</span>
                    </div>
                `;
            }

            menu.innerHTML = menuItems;

            // Position menu at cursor
            menu.style.position = 'fixed';
            menu.style.left = e.clientX + 'px';
            menu.style.top = e.clientY + 'px';
            menu.style.zIndex = '10000';

            document.body.appendChild(menu);

            // Adjust position if menu goes off screen
            const menuRect = menu.getBoundingClientRect();
            if (menuRect.right > window.innerWidth) {
                menu.style.left = (window.innerWidth - menuRect.width - 10) + 'px';
            }
            if (menuRect.bottom > window.innerHeight) {
                menu.style.top = (window.innerHeight - menuRect.height - 10) + 'px';
            }

            // Add click handlers
            menu.querySelectorAll('.mm-context-item').forEach(menuItem => {
                menuItem.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    const action = menuItem.dataset.action;
                    this.handleContextMenuAction(action, item);
                    this.hideContextMenu();
                });
            });

            // Close menu on click outside
            setTimeout(() => {
                document.addEventListener('click', this.hideContextMenuHandler = () => {
                    this.hideContextMenu();
                });
            }, 10);
        }

        /**
         * Hide context menu
         */
        hideContextMenu() {
            const existingMenu = document.getElementById('mm-context-menu');
            if (existingMenu) {
                existingMenu.remove();
            }
            if (this.hideContextMenuHandler) {
                document.removeEventListener('click', this.hideContextMenuHandler);
                this.hideContextMenuHandler = null;
            }
            this.contextMenuTarget = null;
        }

        /**
         * Handle context menu action
         */
        handleContextMenuAction(action, item) {
            console.log('handleContextMenuAction called:', action, item);
            const isFolder = item.type === 'folder';

            switch (action) {
                case 'open':
                    if (isFolder) {
                        this.openFolder(item);
                    }
                    break;

                case 'view':
                    if (!isFolder) {
                        this.viewFileDetails(item);
                    }
                    break;

                case 'download':
                    if (!isFolder && item.url) {
                        const link = document.createElement('a');
                        link.href = item.url;
                        link.download = item.name || '';
                        link.target = '_blank';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }
                    break;

                case 'rename':
                    if (isFolder) {
                        this.showRenameFolderModal(item);
                    } else {
                        this.showRenameFileModal(item);
                    }
                    break;

                case 'move':
                    console.log('Move action triggered for:', item);
                    this.showMoveModal(item);
                    break;

                case 'delete':
                    if (isFolder) {
                        this.confirmDeleteFolder(item);
                    } else {
                        this.confirmDeleteFile(item);
                    }
                    break;
            }
        }

        /**
         * Show rename folder modal
         */
        showRenameFolderModal(folder) {
            const newName = prompt('Enter new folder name:', folder.name);
            if (newName && newName !== folder.name) {
                if (this.config.onFolderRename && typeof this.config.onFolderRename === 'function') {
                    this.config.onFolderRename(folder, newName);
                } else {
                    console.log('Rename folder:', folder.name, 'to', newName);
                    this.showNotification('Folder rename callback not configured', 'error');
                }
            }
        }

        /**
         * Show rename file modal
         */
        showRenameFileModal(file) {
            const currentName = file.name || file.filename || '';
            const newName = prompt('Enter new file name:', currentName);
            if (newName && newName !== currentName) {
                if (this.config.onFileRename && typeof this.config.onFileRename === 'function') {
                    this.config.onFileRename(file.id, newName);
                } else {
                    console.log('Rename file:', currentName, 'to', newName);
                    this.showNotification('File rename callback not configured', 'error');
                }
            }
        }

        /**
         * Show move modal
         */
        showMoveModal(item) {
            console.log('showMoveModal called for:', item);
            console.log('Current path:', this.currentPath);
            console.log('All files:', this.files);

            // Get list of available folders for moving (exclude the item being moved)
            const folders = this.files.filter(f => f.type === 'folder' && f.id !== item.id);
            console.log('Available folders:', folders);

            // Build folder options - always show root option
            let options = '';

            // Add parent folder option if we're not at root
            if (this.currentPath && this.currentPath !== '/') {
                options += '<option value="/">Root (/)</option>';
                // Add parent directory option
                const parentPath = this.currentPath.split('/').slice(0, -1).join('/') || '/';
                if (parentPath !== '/') {
                    options += `<option value="${parentPath}">Parent (${parentPath})</option>`;
                }
            } else {
                options += '<option value="/" disabled>Root (/) - Current location</option>';
            }

            // Add available folders in current directory
            folders.forEach(f => {
                options += `<option value="${f.path || f.name}">${f.name}</option>`;
            });

            // If no folders available and we're at root, show message
            if (folders.length === 0 && (!this.currentPath || this.currentPath === '/')) {
                this.showNotification('No folders available to move to. Create a folder first.', 'warning');
                return;
            }

            // Create simple modal
            const modal = document.createElement('div');
            modal.className = 'mm-modal show';
            modal.id = 'mm-move-modal';
            modal.innerHTML = `
                <div class="mm-modal-content" style="max-width: 400px;">
                    <span class="mm-modal-close" id="mm-move-close">&times;</span>
                    <h2>Move "${this.escapeHtml(item.name)}"</h2>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Select destination folder:</label>
                        <select id="mm-move-destination" class="mm-dropdown-select" style="width: 100%; padding: 10px;">
                            ${options}
                        </select>
                    </div>
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button class="mm-btn mm-btn-secondary" id="mm-move-cancel">Cancel</button>
                        <button class="mm-btn mm-btn-primary" id="mm-move-confirm">Move</button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            console.log('Modal appended to body');

            // Handle close button
            document.getElementById('mm-move-close').addEventListener('click', () => {
                modal.remove();
            });

            // Handle cancel
            document.getElementById('mm-move-cancel').addEventListener('click', () => {
                modal.remove();
            });

            // Handle confirm
            document.getElementById('mm-move-confirm').addEventListener('click', () => {
                const destination = document.getElementById('mm-move-destination').value;
                console.log('Moving to destination:', destination);
                modal.remove();

                if (item.type === 'folder') {
                    if (this.config.onFolderMove && typeof this.config.onFolderMove === 'function') {
                        this.config.onFolderMove(item, destination);
                    } else {
                        this.showNotification('Folder move callback not configured', 'error');
                    }
                } else {
                    // For files, we can use existing move functionality
                    console.log('Move file:', item.name, 'to', destination);
                    this.showNotification('File move not yet implemented', 'info');
                }
            });

            // Close on overlay click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }

        /**
         * Confirm delete folder
         */
        confirmDeleteFolder(folder) {
            if (confirm(`Are you sure you want to delete the folder "${folder.name}"?\n\nThis will also delete all files inside the folder.`)) {
                if (this.config.onFolderDelete && typeof this.config.onFolderDelete === 'function') {
                    this.config.onFolderDelete(folder);
                } else {
                    console.log('Delete folder:', folder);
                    this.showNotification('Folder delete callback not configured', 'error');
                }
            }
        }

        /**
         * Confirm delete file
         */
        confirmDeleteFile(file) {
            if (confirm(`Are you sure you want to delete "${file.name}"?`)) {
                if (this.config.onFileDelete && typeof this.config.onFileDelete === 'function') {
                    this.config.onFileDelete([file.id]);
                } else {
                    console.log('Delete file:', file);
                    this.showNotification('File delete callback not configured', 'error');
                }
            }
        }

        /**
         * View file details
         */
        viewFileDetails(file) {
            console.log('viewFileDetails called for:', file);

            // Create details modal
            const modal = document.createElement('div');
            modal.className = 'mm-modal show';
            modal.id = 'mm-details-modal';

            const isImage = file.type === 'image' && file.url;
            const isVideo = file.type === 'video' && file.url;
            const isAudio = file.type === 'audio' && file.url;

            // Build preview content based on file type
            let previewContent = '';
            if (isImage) {
                previewContent = `
                    <div style="flex: 1; min-width: 250px; max-width: 400px; text-align: center;">
                        <img src="${file.url}" alt="${this.escapeHtml(file.name)}"
                             style="max-width: 100%; max-height: 300px; border-radius: 8px; border: 1px solid #ddd; object-fit: contain;">
                    </div>
                `;
            } else if (isVideo) {
                previewContent = `
                    <div style="flex: 1; min-width: 250px; max-width: 400px;">
                        <video controls style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                            <source src="${file.url}" type="${file.mime || 'video/mp4'}">
                            Your browser does not support video playback.
                        </video>
                    </div>
                `;
            } else if (isAudio) {
                previewContent = `
                    <div style="flex: 1; min-width: 250px; padding: 20px; text-align: center;">
                        <div style="font-size: 64px; margin-bottom: 15px;">🎵</div>
                        <audio controls style="width: 100%;">
                            <source src="${file.url}" type="${file.mime || 'audio/mpeg'}">
                            Your browser does not support audio playback.
                        </audio>
                    </div>
                `;
            } else {
                // Show file icon for other types
                previewContent = `
                    <div style="flex: 0 0 120px; text-align: center; padding: 20px;">
                        <div style="font-size: 64px;">${this.getFileIcon(file.type)}</div>
                    </div>
                `;
            }

            modal.innerHTML = `
                <div class="mm-modal-content" style="max-width: 700px;">
                    <span class="mm-modal-close" id="mm-details-close-btn">&times;</span>
                    <h2 style="margin-bottom: 20px; padding-right: 30px;">📄 File Details</h2>
                    <div style="display: flex; gap: 25px; flex-wrap: wrap; align-items: flex-start;">
                        ${previewContent}
                        <div style="flex: 1; min-width: 220px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 10px 0; font-weight: 600; color: #555; width: 100px;">Name:</td>
                                    <td style="padding: 10px 0; word-break: break-all;">${this.escapeHtml(file.name)}</td>
                                </tr>
                                <tr style="background: #f8f9fa;">
                                    <td style="padding: 10px 0; font-weight: 600; color: #555;">Type:</td>
                                    <td style="padding: 10px 0;">${file.type || 'Unknown'}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; font-weight: 600; color: #555;">Size:</td>
                                    <td style="padding: 10px 0;">${typeof file.size === 'string' ? file.size : (file.size ? this.formatFileSize(file.size) : 'Unknown')}</td>
                                </tr>
                                <tr style="background: #f8f9fa;">
                                    <td style="padding: 10px 0; font-weight: 600; color: #555;">Modified:</td>
                                    <td style="padding: 10px 0;">${file.date || file.modified || 'Unknown'}</td>
                                </tr>
                                ${file.mime ? `
                                <tr>
                                    <td style="padding: 10px 0; font-weight: 600; color: #555;">MIME:</td>
                                    <td style="padding: 10px 0;">${file.mime}</td>
                                </tr>` : ''}
                                ${file.width && file.height ? `
                                <tr style="background: #f8f9fa;">
                                    <td style="padding: 10px 0; font-weight: 600; color: #555;">Dimensions:</td>
                                    <td style="padding: 10px 0;">${file.width} × ${file.height} px</td>
                                </tr>` : ''}
                                ${file.extension ? `
                                <tr>
                                    <td style="padding: 10px 0; font-weight: 600; color: #555;">Extension:</td>
                                    <td style="padding: 10px 0;">.${file.extension}</td>
                                </tr>` : ''}
                            </table>

                            ${file.url ? `
                            <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 6px;">
                                <div style="font-weight: 600; color: #555; margin-bottom: 5px; font-size: 12px;">URL:</div>
                                <div style="font-size: 11px; word-break: break-all; color: #666;">${file.url}</div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    <div style="margin-top: 25px; display: flex; gap: 10px; justify-content: flex-end;">
                        ${file.url ? `<a href="${file.url}" download="${this.escapeHtml(file.name)}" class="mm-btn mm-btn-primary" style="text-decoration: none;">⬇️ Download</a>` : ''}
                        <button class="mm-btn mm-btn-secondary" id="mm-details-close">Close</button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            // Handle close button (×)
            document.getElementById('mm-details-close-btn').addEventListener('click', () => modal.remove());

            // Handle Close button
            document.getElementById('mm-details-close').addEventListener('click', () => modal.remove());

            // Handle click outside modal
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });
        }

        /**
         * Download selected
         */
        downloadSelected() {
            const fileIds = Array.from(this.selectedFiles);
            const files = this.files.filter(f => fileIds.includes(f.id));

            if (files.length === 0) {
                this.showNotification('No files selected to download', 'error');
                return;
            }

            // Filter out folders
            const downloadableFiles = files.filter(f => f.type !== 'folder');

            if (downloadableFiles.length === 0) {
                this.showNotification('Cannot download folders. Please select files only.', 'error');
                return;
            }

            console.log('Downloading files:', downloadableFiles);

            // Download each file
            downloadableFiles.forEach(file => {
                const link = document.createElement('a');
                link.href = file.url || file.full_url;
                link.download = file.name || file.filename || '';
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            this.showNotification(`Downloading ${downloadableFiles.length} file(s)...`, 'success');
        }

        /**
         * View selected
         */
        viewSelected() {
            if (this.selectedFiles.size === 0) {
                this.showNotification('No files selected to view', 'error');
                return;
            }

            if (this.selectedFiles.size === 1) {
                const fileId = Array.from(this.selectedFiles)[0];
                const file = this.files.find(f => f.id === fileId);

                if (!file) {
                    this.showNotification('File not found', 'error');
                    return;
                }

                // Don't preview folders
                if (file.type === 'folder') {
                    this.showNotification('Cannot preview folders', 'error');
                    return;
                }

                // Show preview modal
                this.showPreview(file);
            } else {
                this.showNotification('Please select only one file to view', 'error');
            }
        }

        /**
         * Show preview modal for a file
         */
        showPreview(file) {
            if (!this.elements.previewModal || !this.elements.previewContainer) {
                console.error('Preview modal elements not found');
                return;
            }

            const modal = this.elements.previewModal;
            const container = this.elements.previewContainer;

            // Clear previous content
            container.innerHTML = '';

            // Create preview content based on file type
            let previewContent = '';
            const fileUrl = file.url || file.full_url;

            if (!fileUrl) {
                previewContent = `<p style="text-align: center; padding: 40px;">No preview available</p>`;
            } else if (file.type === 'image' || (file.mime && file.mime.startsWith('image/'))) {
                // Image preview
                previewContent = `
                    <div style="text-align: center;">
                        <img src="${fileUrl}" alt="${this.escapeHtml(file.name)}"
                             style="max-width: 100%; max-height: 80vh; object-fit: contain;">
                    </div>
                    <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
                        <h3 style="margin: 0 0 10px 0;">${this.escapeHtml(file.name)}</h3>
                        <p style="margin: 5px 0; color: #666;">
                            ${file.width && file.height ? `Size: ${file.width} × ${file.height}px<br>` : ''}
                            ${file.size ? `File Size: ${this.formatFileSize(file.size)}<br>` : ''}
                            ${file.mime ? `Type: ${file.mime}` : ''}
                        </p>
                    </div>
                `;
            } else if (file.type === 'video' || (file.mime && file.mime.startsWith('video/'))) {
                // Video preview
                previewContent = `
                    <div style="text-align: center;">
                        <video controls style="max-width: 100%; max-height: 70vh;">
                            <source src="${fileUrl}" type="${file.mime || 'video/mp4'}">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
                        <h3 style="margin: 0 0 10px 0;">${this.escapeHtml(file.name)}</h3>
                        <p style="margin: 5px 0; color: #666;">
                            ${file.size ? `File Size: ${this.formatFileSize(file.size)}<br>` : ''}
                            ${file.mime ? `Type: ${file.mime}` : ''}
                        </p>
                    </div>
                `;
            } else if (file.type === 'audio' || (file.mime && file.mime.startsWith('audio/'))) {
                // Audio preview
                previewContent = `
                    <div style="text-align: center; padding: 40px;">
                        <div style="font-size: 64px; margin-bottom: 20px;">🎵</div>
                        <audio controls style="width: 100%; max-width: 500px;">
                            <source src="${fileUrl}" type="${file.mime || 'audio/mpeg'}">
                            Your browser does not support the audio tag.
                        </audio>
                    </div>
                    <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
                        <h3 style="margin: 0 0 10px 0;">${this.escapeHtml(file.name)}</h3>
                        <p style="margin: 5px 0; color: #666;">
                            ${file.size ? `File Size: ${this.formatFileSize(file.size)}<br>` : ''}
                            ${file.mime ? `Type: ${file.mime}` : ''}
                        </p>
                    </div>
                `;
            } else if (file.mime === 'application/pdf') {
                // PDF preview
                previewContent = `
                    <div style="text-align: center;">
                        <iframe src="${fileUrl}" style="width: 100%; height: 80vh; border: none;"></iframe>
                    </div>
                    <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
                        <h3 style="margin: 0 0 10px 0;">${this.escapeHtml(file.name)}</h3>
                        <p style="margin: 5px 0; color: #666;">
                            ${file.size ? `File Size: ${this.formatFileSize(file.size)}` : ''}
                        </p>
                    </div>
                `;
            } else {
                // Default preview for unsupported types
                previewContent = `
                    <div style="text-align: center; padding: 40px;">
                        <div style="font-size: 64px; margin-bottom: 20px;">${this.getFileIcon(file.type)}</div>
                        <h3>${this.escapeHtml(file.name)}</h3>
                        <p style="color: #666; margin: 15px 0;">
                            ${file.mime || file.type}<br>
                            ${file.size ? this.formatFileSize(file.size) : ''}
                        </p>
                        <a href="${fileUrl}" download="${file.name}"
                           style="display: inline-block; margin-top: 20px; padding: 10px 20px;
                                  background: #007bff; color: white; text-decoration: none; border-radius: 5px;">
                            Download File
                        </a>
                    </div>
                `;
            }

            container.innerHTML = previewContent;
            modal.classList.add('show');
        }

        /**
         * Rename selected
         */
        renameSelected() {
            if (this.selectedFiles.size === 1) {
                const fileId = Array.from(this.selectedFiles)[0];
                const file = this.files.find(f => f.id === fileId);
                if (file) {
                    const newName = prompt('Enter new name:', file.name);
                    if (newName && newName.trim() && newName !== file.name) {
                        file.name = newName.trim();
                        this.render();
                        this.showNotification(`File renamed to "${newName}"`);

                        if (this.config.onFileRename) {
                            this.config.onFileRename(file, newName);
                        }
                    }
                }
            } else {
                this.showNotification('Please select only one file to rename');
            }
        }

        /**
         * Delete selected
         */
        deleteSelected() {
            if (confirm(`Delete ${this.selectedFiles.size} selected file(s)?`)) {
                this.selectedFiles.forEach(fileId => {
                    const file = this.files.find(f => f.id === fileId);
                    this.files = this.files.filter(f => f.id !== fileId);
                    this.filteredFiles = this.filteredFiles.filter(f => f.id !== fileId);

                    if (this.config.onFileDelete && file) {
                        this.config.onFileDelete(file);
                    }
                });
                this.selectedFiles.clear();
                this.render();
                this.updateSelectionHeader();
                this.showNotification('Files deleted successfully');
            }
        }

        /**
         * Show notification
         */
        showNotification(message) {
            let notification = document.querySelector('.mm-notification');
            if (!notification) {
                notification = document.createElement('div');
                notification.className = 'mm-notification';
                document.body.appendChild(notification);
            }

            notification.textContent = message;
            notification.style.display = 'block';

            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        /**
         * Public API: Add files
         */
        addFiles(files) {
            if (!Array.isArray(files)) files = [files];

            files.forEach(file => {
                if (!file.id) file.id = Date.now() + Math.random();
                if (!file.modified) file.modified = new Date();
                this.files.push(file);
                this.filteredFiles.push(file);
            });

            this.render();
        }

        /**
         * Public API: Remove files
         */
        removeFiles(fileIds) {
            if (!Array.isArray(fileIds)) fileIds = [fileIds];

            fileIds.forEach(id => {
                this.files = this.files.filter(f => f.id !== id);
                this.filteredFiles = this.filteredFiles.filter(f => f.id !== id);
                this.selectedFiles.delete(id);
            });

            this.render();
            this.updateSelectionHeader();
        }

        /**
         * Public API: Get selected files
         */
        getSelectedFiles() {
            return Array.from(this.selectedFiles).map(id =>
                this.files.find(f => f.id === id)
            );
        }

        /**
         * Public API: Clear files
         */
        clearFiles() {
            this.files = [];
            this.filteredFiles = [];
            this.selectedFiles.clear();
            this.render();
            this.updateSelectionHeader();
        }

        /**
         * Public API: Refresh/Re-render
         */
        refresh() {
            this.render();
        }

        /**
         * Public API: Destroy instance
         */
        destroy() {
            const container = typeof this.config.container === 'string'
                ? document.querySelector(this.config.container)
                : this.config.container;

            if (container) {
                container.innerHTML = '';
            }

            const style = document.getElementById('media-manager-styles');
            if (style && document.querySelectorAll('.mm-container').length <= 1) {
                style.remove();
            }
        }
    }

    // Export to global scope
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = MediaManagerLib;
    } else {
        global.PanhaMediaManager = PanhaMediaManager;
    }

})(typeof window !== 'undefined' ? window : this);

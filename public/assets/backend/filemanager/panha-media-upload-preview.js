/**
 * PanhaMediaUploadPreview Library
 * A reusable media upload preview component with drag-and-drop support
 * @version 2.0.1
 * @author Im Samnang (Panha)
 * @license MIT
 */

(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
    typeof define === 'function' && define.amd ? define(factory) :
      (global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.PanhaMediaUploadPreview = factory());
})(this, (function () {
  'use strict';

  class PanhaMediaUploadPreview {
    constructor(selector, options = {}) {
      // Default configuration
      this.config = {
        maxFileSize: 5 * 1024 * 1024, // 5MB
        allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
        maxFiles: 8,
        gridColumns: 5,
        showFileInfo: true,
        showActionBar: true,
        enableDragDrop: true,
        fieldName: 'images[]',  // ADD THIS - configurable field name for form submission
        texts: {
          title: 'Media',
          uploadText: 'Drop your images here',
          uploadSubtext: 'Max 8 files, 5MB each',
          addMoreText: 'Add More',
          clearAllText: 'Clear All',
          uploadFilesText: 'Upload Files',
          filesSelectedText: 'files selected',
          confirmClearText: 'Are you sure you want to remove all images?',
          fileNotImageError: 'File "{filename}" is not an image.',
          fileTooLargeError: 'File "{filename}" is too large. Maximum size is 20MB.',
          noFilesToUploadError: 'No files to upload',
          readyToUploadMessage: 'Ready to upload {count} image(s)'
        },
        theme: {
          primaryColor: '#805ad5',
          primaryHover: '#9f7aea',
          borderColor: '#e5e7eb',
          backgroundColor: '#f8f9fa',
          textColor: '#2d3748'
        },
        callbacks: {
          onFileAdd: null,
          onFileRemove: null,
          onClearAll: null,
          onUpload: null,
          beforeFileAdd: null,
          onError: null
        },
        customStyles: null,
        autoInjectStyles: true
      };

      // Merge user options with defaults
      this.config = this.deepMerge(this.config, options);

      // Initialize properties
      this.container = typeof selector === 'string' ? document.querySelector(selector) : selector;
      this.mediaFiles = [];
      this.fileIdCounter = 0;
      this.instanceId = this.generateId();
      this.eventListeners = {}; // Event system for CRUD operations
      this.crud = null; // CRUD configuration
      this.uploadQueue = [];
      this.uploading = false;

      if (!this.container) {
        throw new Error('PanhaMediaUploadPreview: Container element not found');
      }

      // Initialize the component
      this.init();
    }

    init() {
      // Inject styles if enabled
      if (this.config.autoInjectStyles) {
        this.injectStyles();
      }

      // Create HTML structure
      this.createHTML();

      // Setup event listeners
      this.setupEventListeners();

      // Setup drag and drop if enabled
      if (this.config.enableDragDrop) {
        this.setupDragAndDrop();
      }
    }

    createHTML() {
      const instanceId = this.instanceId;

      this.container.innerHTML = `
        <div class="pmup-container">
          <div class="pmup-header">
            <h2 class="pmup-title">${this.config.texts.title}</h2>
          </div>

          <div class="pmup-upload-zone" id="pmup-upload-zone-${instanceId}">
            <div class="pmup-placeholder-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
              </svg>
            </div>
            <div class="pmup-upload-text">${this.config.texts.uploadText}</div>
            <div class="pmup-upload-subtext">${this.config.texts.uploadSubtext}</div>
          </div>

          <div class="pmup-gallery-grid" id="pmup-gallery-${instanceId}"></div>

          <input type="file" class="pmup-file-input" id="pmup-file-input-${instanceId}" multiple accept="${this.config.allowedTypes.join(',')}" style="display: none;">
        </div>
      `;

      // Store references to elements
      this.elements = {
        uploadZone: this.container.querySelector('.pmup-upload-zone'),
        galleryGrid: this.container.querySelector('.pmup-gallery-grid'),
        actionBar: this.container.querySelector('.pmup-action-bar'),
        fileInput: this.container.querySelector('.pmup-file-input'),
        fileCount: this.container.querySelector(`#pmup-file-count-${instanceId}`),
        totalSize: this.container.querySelector(`#pmup-total-size-${instanceId}`),
        clearBtn: this.container.querySelector('.pmup-btn-clear'),
        uploadBtn: this.container.querySelector('.pmup-btn-upload')
      };
    }

    setupEventListeners() {
      // File input change
      this.elements.fileInput.addEventListener('change', (e) => {
        this.handleFiles(e.target.files);
        // Reset input value to allow selecting the same file again
        e.target.value = '';
      });

      // Upload zone click
      this.elements.uploadZone.addEventListener('click', (e) => {
        e.preventDefault();
        this.elements.fileInput.click();
      });

      // Clear all button
      if (this.elements.clearBtn) {
        this.elements.clearBtn.addEventListener('click', () => this.clearAll());
      }

      // Upload button
      if (this.elements.uploadBtn) {
        this.elements.uploadBtn.addEventListener('click', () => this.uploadFiles());
      }
    }

    setupDragAndDrop() {
      const zones = [this.elements.uploadZone, this.container];

      zones.forEach(zone => {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
          zone.addEventListener(eventName, this.preventDefaults, false);
        });
      });

      ['dragenter', 'dragover'].forEach(eventName => {
        this.elements.uploadZone.addEventListener(eventName, () => {
          this.elements.uploadZone.classList.add('pmup-drag-over');
        });
      });

      ['dragleave', 'drop'].forEach(eventName => {
        this.elements.uploadZone.addEventListener(eventName, () => {
          this.elements.uploadZone.classList.remove('pmup-drag-over');
        });
      });

      this.elements.uploadZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        this.handleFiles(files);
      });

      // Handle drag and drop on add more button
      this.container.addEventListener('dragover', (e) => {
        const addMoreBtn = this.container.querySelector('.pmup-add-more-btn');
        if (addMoreBtn && e.target.closest('.pmup-add-more-btn')) {
          e.preventDefault();
          addMoreBtn.classList.add('pmup-drag-over');
        }
      });

      this.container.addEventListener('dragleave', (e) => {
        const addMoreBtn = this.container.querySelector('.pmup-add-more-btn');
        if (addMoreBtn && !this.container.contains(e.relatedTarget)) {
          addMoreBtn.classList.remove('pmup-drag-over');
        }
      });

      this.container.addEventListener('drop', (e) => {
        const addMoreBtn = this.container.querySelector('.pmup-add-more-btn');
        if (addMoreBtn && e.target.closest('.pmup-add-more-btn')) {
          e.preventDefault();
          addMoreBtn.classList.remove('pmup-drag-over');
          const files = e.dataTransfer.files;
          this.handleFiles(files);
        }
      });
    }

    preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    async handleFiles(files) {
      const validFiles = Array.from(files).filter(file => this.validateFile(file));

      if (this.config.maxFiles && this.mediaFiles.length + validFiles.length > this.config.maxFiles) {
        this.showError(`Maximum ${this.config.maxFiles} files allowed`);
        return;
      }

      for (const file of validFiles) {
        // Call beforeFileAdd callback if exists
        if (this.config.callbacks.beforeFileAdd) {
          const shouldAdd = await this.config.callbacks.beforeFileAdd(file);
          if (!shouldAdd) continue;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
          const fileObj = {
            id: this.fileIdCounter++,
            file: file,
            url: e.target.result,
            name: file.name,
            size: file.size,
            type: file.type
          };

          this.mediaFiles.push(fileObj);

          // Call onFileAdd callback
          if (this.config.callbacks.onFileAdd) {
            this.config.callbacks.onFileAdd(fileObj);
          }

          this.updateUI();
        };

        reader.onerror = () => {
          this.showError(`Failed to read file: ${file.name}`);
        };

        reader.readAsDataURL(file);
      }
    }

    validateFile(file) {
      // Check file type
      const fileType = file.type.toLowerCase();
      const isValidType = this.config.allowedTypes.some(type => {
        // Handle both MIME types and extensions
        if (type.startsWith('.')) {
          return file.name.toLowerCase().endsWith(type);
        }
        return fileType === type;
      });

      if (!isValidType) {
        this.showError(this.config.texts.fileNotImageError.replace('{filename}', file.name));
        return false;
      }

      // Check file size
      if (file.size > this.config.maxFileSize) {
        this.showError(this.config.texts.fileTooLargeError.replace('{filename}', file.name));
        return false;
      }

      return true;
    }

    updateUI() {
      if (this.mediaFiles.length > 0) {
        this.elements.uploadZone.classList.add('pmup-has-files');
        this.elements.galleryGrid.classList.add('pmup-active');
        if (this.elements.actionBar) {
          this.elements.actionBar.classList.add('pmup-active');
        }

        // Clear and rebuild gallery
        this.elements.galleryGrid.innerHTML = '';

        // Add media items
        this.mediaFiles.forEach((item, index) => {
          const galleryItem = this.createGalleryItem(item, index);
          this.elements.galleryGrid.appendChild(galleryItem);
        });

        // Add the "add more" button only if we have 2 or more images
        if (this.mediaFiles.length >= 2) {
          const addMoreBtn = this.createAddMoreButton();
          this.elements.galleryGrid.appendChild(addMoreBtn);
        }

        // Update stats
        this.updateStats();
      } else {
        this.elements.uploadZone.classList.remove('pmup-has-files');
        this.elements.galleryGrid.classList.remove('pmup-active');
        if (this.elements.actionBar) {
          this.elements.actionBar.classList.remove('pmup-active');
        }
        this.elements.galleryGrid.innerHTML = '';
      }
    }

    createGalleryItem(item, index) {
      const div = document.createElement('div');
      div.className = 'pmup-gallery-item';
      // Only the first item should be large 2x2
      if (index === 0) {
        div.classList.add('pmup-gallery-item-first');
      }
      div.dataset.id = item.id;

      const imgWrapper = document.createElement('div');
      imgWrapper.className = 'pmup-gallery-item-wrapper';

      const img = document.createElement('img');
      img.src = item.url;
      img.alt = item.name;
      img.loading = 'lazy';
      imgWrapper.appendChild(img);
      div.appendChild(imgWrapper);

      // Add overlay
      const overlay = document.createElement('div');
      overlay.className = 'pmup-gallery-item-overlay';
      div.appendChild(overlay);

      // Add remove button
      const removeBtn = document.createElement('button');
      removeBtn.className = 'pmup-gallery-item-remove';
      removeBtn.onclick = (e) => {
        e.stopPropagation();
        this.removeImage(item.id);
      };
      removeBtn.innerHTML = `
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      `;
      div.appendChild(removeBtn);

      // Add info if enabled
      if (this.config.showFileInfo) {
        const info = document.createElement('div');
        info.className = 'pmup-gallery-item-info';
        info.innerHTML = `
          <div class="pmup-gallery-item-name">${this.truncateFileName(item.name, 20)}</div>
          <div class="pmup-gallery-item-size">${this.formatFileSize(item.size)}</div>
        `;
        div.appendChild(info);
      }

      return div;
    }

    createAddMoreButton() {
      const div = document.createElement('div');
      div.className = 'pmup-gallery-item pmup-add-more-btn';
      div.onclick = () => this.elements.fileInput.click();
      div.innerHTML = `
        <div class="pmup-add-more-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
        </div>
        <span class="pmup-add-more-text">${this.config.texts.addMoreText}</span>
      `;
      return div;
    }

    truncateFileName(name, maxLength) {
      if (name.length <= maxLength) return name;
      const ext = name.split('.').pop();
      const nameWithoutExt = name.substring(0, name.lastIndexOf('.'));
      const truncatedName = nameWithoutExt.substring(0, maxLength - ext.length - 4);
      return `${truncatedName}...${ext}`;
    }

    removeImage(id) {
      const removedFile = this.mediaFiles.find(item => item.id === id);
      this.mediaFiles = this.mediaFiles.filter(item => item.id !== id);

      // Call onFileRemove callback
      if (this.config.callbacks.onFileRemove) {
        this.config.callbacks.onFileRemove(removedFile);
      }

      this.updateUI();
    }

    clearAll() {
      if (this.mediaFiles.length > 0 && confirm(this.config.texts.confirmClearText)) {
        // Call onClearAll callback
        if (this.config.callbacks.onClearAll) {
          this.config.callbacks.onClearAll([...this.mediaFiles]);
        }

        this.mediaFiles = [];
        this.fileIdCounter = 0;
        this.updateUI();
      }
    }

    updateStats() {
      if (this.elements.fileCount) {
        this.elements.fileCount.textContent = this.mediaFiles.length;
      }
      if (this.elements.totalSize) {
        const bytes = this.mediaFiles.reduce((acc, item) => acc + item.size, 0);
        this.elements.totalSize.textContent = this.formatFileSize(bytes);
      }
    }

    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    uploadFiles() {
      if (this.mediaFiles.length === 0) {
        this.showError(this.config.texts.noFilesToUploadError);
        return;
      }

      // Create FormData
      const formData = this.getFilesAsFormData(this.config.fieldName);
      // const formData = new FormData();
      // this.mediaFiles.forEach((item, index) => {
      //   formData.append(`image_${index}`, item.file);
      // });

      // Call onUpload callback
      if (this.config.callbacks.onUpload) {
        this.config.callbacks.onUpload(formData, this.mediaFiles);
      } else {
        alert(this.config.texts.readyToUploadMessage.replace('{count}', this.mediaFiles.length));
      }
    }

    showError(message) {
      if (this.config.callbacks.onError) {
        this.config.callbacks.onError(message);
      } else {
        alert(message);
      }
    }

    // Public Methods
    getFiles() {
      return [...this.mediaFiles];
    }

    getFilesAsFormData(fieldName = null, additionalData = {}) {
      const formData = new FormData();

      // Use provided fieldName or fall back to config
      const field = fieldName || this.config.fieldName || 'images[]';

      // For Laravel array notation (ends with [])
      if (field.endsWith('[]')) {
        this.mediaFiles.forEach((item) => {
          formData.append(field, item.file);
        });
      } else {
        // For indexed field names (legacy support)
        this.mediaFiles.forEach((item, index) => {
          formData.append(`${field}_${index}`, item.file);
        });
      }

      // Add any additional data
      Object.keys(additionalData).forEach(key => {
        formData.append(key, additionalData[key]);
      });

      return formData;
    }

    /**
     * Get raw File object(s) without FormData wrapper
     * @param {number} index - Optional index for specific file (if not provided, returns all)
     * @returns {File|File[]|null} Single file, array of files, or null
     */
    getRawFile(index = null) {
      if (index !== null) {
        // Return specific file by index
        const fileItem = this.mediaFiles[index];
        return fileItem ? fileItem.file : null;
      }

      // Return all files as array
      if (this.mediaFiles.length === 0) return null;
      if (this.mediaFiles.length === 1) return this.mediaFiles[0].file;
      return this.mediaFiles.map(item => item.file);
    }

    /**
     * Get the first raw File object
     * @returns {File|null} File object or null
     */
    getFirstRawFile() {
      return this.mediaFiles.length > 0 ? this.mediaFiles[0].file : null;
    }

    /**
     * Get all raw File objects as array
     * @returns {File[]} Array of File objects
     */
    getAllRawFiles() {
      return this.mediaFiles.map(item => item.file);
    }

    /**
     * Merge this uploader's files into existing FormData
     * @param {FormData} targetFormData - Target FormData to merge into
     * @param {string} fieldName - Field name for files
     * @returns {FormData} The same FormData object (for chaining)
     */
    mergeIntoFormData(targetFormData, fieldName = null) {
      const field = fieldName || this.config.fieldName || 'images[]';

      if (field.endsWith('[]')) {
        // Array notation for multiple files
        this.mediaFiles.forEach(item => {
          targetFormData.append(field, item.file);
        });
      } else {
        // Single file or indexed names
        if (this.mediaFiles.length === 1) {
          targetFormData.append(field, this.mediaFiles[0].file);
        } else {
          this.mediaFiles.forEach((item, index) => {
            targetFormData.append(`${field}_${index}`, item.file);
          });
        }
      }

      return targetFormData;
    }

    addFiles(files) {
      this.handleFiles(files);
    }

    removeFile(id) {
      this.removeImage(id);
    }

    clear() {
      this.mediaFiles = [];
      this.fileIdCounter = 0;
      this.updateUI();
    }

    /**
     * Load existing images from URLs (useful for edit mode)
     * @param {string|Array<string>} imageUrls - Single URL or array of URLs
     * @returns {Promise} - Resolves when all images are loaded
     */
    async loadExistingImages(imageUrls) {
      const urls = Array.isArray(imageUrls) ? imageUrls : [imageUrls];

      if (this.config.maxFiles && this.mediaFiles.length + urls.length > this.config.maxFiles) {
        const error = `Cannot load ${urls.length} image(s). Maximum is ${this.config.maxFiles} files.`;
        this.showError(error);
        throw new Error(error);
      }

      try {
        const filePromises = urls.map(async (url) => {
          const response = await fetch(url);
          if (!response.ok) throw new Error(`Failed to fetch image from ${url}`);

          const blob = await response.blob();
          const urlParts = url.split('/');
          const fileName = urlParts[urlParts.length - 1] || 'existing-image.jpg';

          return new File([blob], fileName, { type: blob.type });
        });

        const files = await Promise.all(filePromises);

        files.forEach(file => {
          const fileObj = {
            id: this.fileIdCounter++,
            file: file,
            url: URL.createObjectURL(file),
            name: file.name,
            size: file.size,
            type: file.type
          };
          this.mediaFiles.push(fileObj);
        });

        this.updateUI();
        return files;
      } catch (error) {
        this.showError(`Error loading existing images: ${error.message}`);
        throw error;
      }
    }

    /**
     * Load a single existing image from URL
     * @param {string} imageUrl - Image URL
     * @returns {Promise<File>} - Resolves with the File object
     */
    async loadExistingImage(imageUrl) {
      const files = await this.loadExistingImages([imageUrl]);
      return files[0];
    }

    /**
     * Load existing files with metadata (for edit forms)
     * This method loads files without fetching them from URLs
     * Perfect for edit forms where files are already uploaded
     *
     * @param {Array} filesData - Array of file objects with {url, name, size, id}
     * @returns {void}
     *
     * Usage:
     *   uploader.loadExistingFiles([
     *     {url: '/storage/image1.jpg', name: 'image1.jpg', size: 12345, id: 1},
     *     {url: '/storage/image2.jpg', name: 'image2.jpg', size: 67890, id: 2}
     *   ]);
     */
    loadExistingFiles(filesData) {
      const files = Array.isArray(filesData) ? filesData : [filesData];

      if (this.config.maxFiles && this.mediaFiles.length + files.length > this.config.maxFiles) {
        const error = `Cannot load ${files.length} file(s). Maximum is ${this.config.maxFiles} files.`;
        this.showError(error);
        throw new Error(error);
      }

      files.forEach(fileData => {
        const fileObj = {
          id: fileData.id || this.fileIdCounter++,
          file: null, // Existing files don't have File objects
          url: fileData.url,
          name: fileData.name || 'existing-file',
          size: fileData.size || 0,
          type: fileData.type || 'image/jpeg',
          isExisting: true // Mark as existing file
        };
        this.mediaFiles.push(fileObj);
      });

      this.updateUI();
      console.log(`[PanhaMediaUploadPreview] Loaded ${files.length} existing file(s)`);
    }

    /**
     * Convert files to base64 strings
     * @returns {Promise<Array>} - Array of base64 strings
     */
    async getFilesAsBase64() {
      const base64Promises = this.mediaFiles.map(item => {
        return new Promise((resolve, reject) => {
          const reader = new FileReader();
          reader.onload = () => resolve({
            name: item.name,
            type: item.type,
            size: item.size,
            base64: reader.result
          });
          reader.onerror = reject;
          reader.readAsDataURL(item.file);
        });
      });

      return Promise.all(base64Promises);
    }

    /**
     * Get total size of all files in bytes
     * @returns {number} - Total size in bytes
     */
    getTotalSize() {
      return this.mediaFiles.reduce((total, item) => total + item.size, 0);
    }

    /**
     * Get total size in human-readable format
     * @returns {string} - Size with appropriate unit
     */
    getTotalSizeFormatted() {
      return this.formatFileSize(this.getTotalSize());
    }

    /**
     * Check if files array is empty
     * @returns {boolean}
     */
    isEmpty() {
      return this.mediaFiles.length === 0;
    }

    /**
     * Check if files array is at maximum capacity
     * @returns {boolean}
     */
    isFull() {
      return this.mediaFiles.length >= this.config.maxFiles;
    }

    /**
     * Get remaining slots
     * @returns {number} - Number of files that can still be added
     */
    getRemainingSlots() {
      return this.config.maxFiles - this.mediaFiles.length;
    }

    /**
     * Filter files by criteria
     * @param {Function} callback - Filter function (item) => boolean
     * @returns {Array} - Filtered file objects
     */
    filterFiles(callback) {
      return this.mediaFiles.filter(callback);
    }

    /**
     * Find file by criteria
     * @param {Function} callback - Find function (item) => boolean
     * @returns {Object|undefined} - Found file object or undefined
     */
    findFile(callback) {
      return this.mediaFiles.find(callback);
    }

    /**
     * Sort files
     * @param {Function} compareFn - Sort comparison function
     */
    sortFiles(compareFn) {
      this.mediaFiles.sort(compareFn);
      this.updateUI();
    }

    /**
     * Sort files by name
     * @param {boolean} ascending - Sort order (default: true)
     */
    sortByName(ascending = true) {
      this.sortFiles((a, b) => {
        const nameA = a.name.toLowerCase();
        const nameB = b.name.toLowerCase();
        return ascending ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
      });
    }

    /**
     * Sort files by size
     * @param {boolean} ascending - Sort order (default: true)
     */
    sortBySize(ascending = true) {
      this.sortFiles((a, b) => ascending ? a.size - b.size : b.size - a.size);
    }

    /**
     * Get file by index
     * @param {number} index - Index of the file
     * @returns {Object|null} - File object or null
     */
    getFileByIndex(index) {
      return this.mediaFiles[index] || null;
    }

    /**
     * Remove file by index
     * @param {number} index - Index of the file to remove
     * @returns {Object|null} - Removed file or null
     */
    removeFileByIndex(index) {
      if (index >= 0 && index < this.mediaFiles.length) {
        const removedFile = this.mediaFiles[index];
        this.removeImage(removedFile.id);
        return removedFile;
      }
      return null;
    }

    /**
     * Disable/Enable file browsing
     * @param {boolean} disable - True to disable, false to enable
     */
    disableBrowse(disable = true) {
      if (disable) {
        this.elements.uploadZone.style.pointerEvents = 'none';
        this.elements.uploadZone.style.opacity = '0.5';
        this.elements.fileInput.disabled = true;
      } else {
        this.elements.uploadZone.style.pointerEvents = '';
        this.elements.uploadZone.style.opacity = '';
        this.elements.fileInput.disabled = false;
      }
    }

    /**
     * Enable file browsing
     */
    enableBrowse() {
      this.disableBrowse(false);
    }

    /**
     * Refresh/re-render the upload UI
     */
    refresh() {
      this.updateUI();
    }

    /**
     * Set configuration option(s) dynamically
     * @param {string|Object} option - Option name or object with options
     * @param {*} value - Option value (ignored if option is object)
     */
    setOption(option, value) {
      if (typeof option === 'object') {
        Object.keys(option).forEach(key => {
          if (this.config.hasOwnProperty(key)) {
            this.config[key] = option[key];
          }
        });
      } else if (typeof option === 'string') {
        if (this.config.hasOwnProperty(option)) {
          this.config[option] = value;
        }
      }
    }

    /**
     * Get configuration option value(s)
     * @param {string} option - Option name (if not provided, returns all options)
     * @returns {*} - Option value or all options
     */
    getOption(option) {
      if (option) {
        return this.config[option];
      }
      return { ...this.config };
    }

    /**
     * Attach files to form for Laravel submission (without AJAX)
     * Similar to Dropzone's autoDiscover feature - automatically handles form submission
     *
     * Usage:
     *   const uploader = new PanhaMediaUploadPreview('#container', {...});
     *   uploader.attachToForm('#myForm', 'media_feature');
     *
     * @param {string|HTMLFormElement} formSelector - Form selector or form element
     * @param {string} fieldName - Name attribute for the file input (e.g., 'media_feature' or 'media_gallery[]')
     * @param {Object} options - Additional options
     * @param {boolean} options.preventDefault - Whether to prevent default submission (default: true)
     * @param {Function} options.beforeSubmit - Callback before form submits (can return false to cancel)
     * @param {Function} options.afterAttach - Callback after files are attached
     * @returns {void}
     */
    attachToForm(formSelector, fieldName = null, options = {}) {
      const form = typeof formSelector === 'string'
        ? document.querySelector(formSelector)
        : formSelector;

      if (!form) {
        console.error('PanhaMediaUploadPreview: Form not found', formSelector);
        return;
      }

      const finalFieldName = fieldName || this.config.fieldName;
      let formSubmitting = false;

      // Store reference for potential cleanup
      const submitHandler = (e) => {
        // Prevent infinite loop
        if (formSubmitting) {
          return true;
        }

        if (options.preventDefault !== false) {
          e.preventDefault();
        }

        // Call beforeSubmit callback
        if (options.beforeSubmit && typeof options.beforeSubmit === 'function') {
          const shouldContinue = options.beforeSubmit(this.mediaFiles);
          if (shouldContinue === false) {
            return false;
          }
        }

        // Get files (only actual File objects, not existing/loaded files)
        const files = this.mediaFiles
          .filter(item => item.file instanceof File)
          .map(item => item.file);

        console.log(`[PanhaMediaUploadPreview] Attaching ${files.length} file(s) to form as "${finalFieldName}"`);

        // Create DataTransfer to hold files
        const dataTransfer = new DataTransfer();
        files.forEach(file => {
          dataTransfer.items.add(file);
        });

        // Create or update hidden file input
        const inputId = `pmup_hidden_${finalFieldName.replace(/\[|\]/g, '_')}`;
        let fileInput = document.getElementById(inputId);

        if (!fileInput) {
          fileInput = document.createElement('input');
          fileInput.type = 'file';
          fileInput.name = finalFieldName;
          fileInput.id = inputId;
          fileInput.style.display = 'none';

          // For array fields (e.g., 'media_gallery[]'), add multiple attribute
          if (finalFieldName.includes('[]')) {
            fileInput.multiple = true;
          }

          form.appendChild(fileInput);
        }

        // Assign files using DataTransfer API
        fileInput.files = dataTransfer.files;

        console.log(`[PanhaMediaUploadPreview] Attached ${fileInput.files.length} file(s) to input "${finalFieldName}"`);

        // Call afterAttach callback
        if (options.afterAttach && typeof options.afterAttach === 'function') {
          options.afterAttach(fileInput.files, fileInput);
        }

        // Submit the form
        formSubmitting = true;
        form.submit();
      };

      // Attach event listener
      form.addEventListener('submit', submitHandler);

      // Store reference for cleanup
      this._formSubmitHandler = submitHandler;
      this._attachedForm = form;

      console.log(`[PanhaMediaUploadPreview] Attached to form with field name: ${finalFieldName}`);
    }

    /**
     * Detach from form submission handling
     * Removes the submit event listener
     */
    detachFromForm() {
      if (this._attachedForm && this._formSubmitHandler) {
        this._attachedForm.removeEventListener('submit', this._formSubmitHandler);
        this._formSubmitHandler = null;
        this._attachedForm = null;
        console.log('[PanhaMediaUploadPreview] Detached from form');
      }
    }

    // =================================================================
    // EVENT SYSTEM
    // =================================================================

    /**
     * Trigger an event
     * @param {string} eventName - Event name
     * @param {*} data - Event data
     */
    trigger(eventName, data) {
      if (!this.eventListeners) this.eventListeners = {};
      if (this.eventListeners[eventName]) {
        this.eventListeners[eventName].forEach(callback => {
          try {
            callback(data);
          } catch (error) {
            console.error(`Error in ${eventName} listener:`, error);
          }
        });
      }
    }

    /**
     * Bind an event listener
     * @param {string} eventName - Event name
     * @param {Function} callback - Callback function
     */
    on(eventName, callback) {
      if (!this.eventListeners) this.eventListeners = {};
      if (!this.eventListeners[eventName]) {
        this.eventListeners[eventName] = [];
      }
      this.eventListeners[eventName].push(callback);
      return this;
    }

    /**
     * Remove an event listener
     * @param {string} eventName - Event name
     * @param {Function} callback - Callback function to remove
     */
    off(eventName, callback) {
      if (!this.eventListeners) this.eventListeners = {};
      if (this.eventListeners[eventName]) {
        this.eventListeners[eventName] = this.eventListeners[eventName].filter(
          cb => cb !== callback
        );
      }
      return this;
    }

    // =================================================================
    // CRUD OPERATIONS
    // =================================================================

    /**
     * Configure CRUD settings
     * @param {object} crudOptions - CRUD configuration
     * @returns {PanhaMediaUploadPreview} Returns this for chaining
     */
    configureCRUD(crudOptions) {
      this.crud = {
        enabled: true,
        uploadUrl: '',
        deleteUrl: '',
        updateUrl: '',
        loadUrl: '',
        method: 'POST',
        headers: {},
        csrfToken: null,
        csrfTokenName: '_token',
        fieldName: 'images[]',
        additionalData: {},
        chunkSize: null,
        concurrent: 3,
        autoUpload: false,
        ...crudOptions
      };

      this.uploadQueue = [];
      this.uploading = false;

      this.trigger('CRUDConfigured', this.crud);
      return this;
    }

    /**
     * Upload files to server
     * @param {string} url - The upload endpoint URL
     * @param {Object} options - Upload options
     * @returns {Promise} - Upload promise
     */
    async uploadToServer(url, options = {}) {
      const uploadUrl = url || this.crud?.uploadUrl;

      if (!uploadUrl) {
        throw new Error('Upload URL not provided');
      }

      if (this.mediaFiles.length === 0) {
        throw new Error('No files to upload');
      }

      const {
        fieldName = this.config.fieldName || this.crud?.fieldName || 'images[]',  // Updated
        additionalData = {},
        headers = {},
        onProgress = null,
        onSuccess = null,
        onError = null
      } = options;


      const formData = new FormData();

      // Add files to FormData with proper field name
      if (fieldName.endsWith('[]')) {
        this.mediaFiles.forEach((item) => {
          formData.append(fieldName, item.file);
        });
      } else {
        this.mediaFiles.forEach((item, index) => {
          formData.append(`${fieldName}_${index}`, item.file);
        });
      }

      // Add additional data
      Object.keys(additionalData).forEach(key => {
        formData.append(key, additionalData[key]);
      });

      // Add CRUD additional data if available
      if (this.crud?.additionalData) {
        Object.keys(this.crud.additionalData).forEach(key => {
          if (!additionalData[key]) {
            formData.append(key, this.crud.additionalData[key]);
          }
        });
      }

      this.trigger('BeforeServerUpload', { files: this.mediaFiles });

      try {
        const xhr = new XMLHttpRequest();

        // Track upload progress
        if (onProgress) {
          xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
              const percentComplete = (e.loaded / e.total) * 100;
              onProgress(percentComplete);
            }
          });
        }

        const uploadPromise = new Promise((resolve, reject) => {
          xhr.onload = () => {
            if (xhr.status >= 200 && xhr.status < 300) {
              let response;
              try {
                response = JSON.parse(xhr.responseText);
              } catch (e) {
                response = xhr.responseText;
              }
              this.trigger('ServerUploadSuccess', { response });
              if (onSuccess) onSuccess(response);
              resolve(response);
            } else {
              const error = new Error(`Upload failed with status ${xhr.status}`);
              this.trigger('ServerUploadError', { error });
              if (onError) onError(error);
              reject(error);
            }
          };

          xhr.onerror = () => {
            const error = new Error('Network error during upload');
            this.trigger('ServerUploadError', { error });
            if (onError) onError(error);
            reject(error);
          };

          xhr.open('POST', uploadUrl);

          // Add CSRF token if available
          if (this.crud?.csrfToken) {
            xhr.setRequestHeader('X-CSRF-TOKEN', this.crud.csrfToken);
          }

          // Add custom headers
          const allHeaders = { ...this.crud?.headers, ...headers };
          Object.keys(allHeaders).forEach(key => {
            xhr.setRequestHeader(key, allHeaders[key]);
          });

          xhr.send(formData);
        });

        return uploadPromise;
      } catch (error) {
        this.trigger('ServerUploadError', { error });
        if (onError) onError(error);
        throw error;
      }
    }

    /**
     * Delete file from server by filename
     * @param {string} filename - Filename to delete
     * @param {object} options - Delete options
     * @returns {Promise} Delete promise
     */
    async deleteFromServer(filename, options = {}) {
      if (!this.crud || !this.crud.deleteUrl) {
        throw new Error('CRUD not configured or deleteUrl not set');
      }

      const deleteOptions = { ...this.crud, ...options };
      const url = deleteOptions.deleteUrl;

      const headers = {
        'Content-Type': 'application/json',
        ...deleteOptions.headers
      };

      if (deleteOptions.csrfToken) {
        headers['X-CSRF-TOKEN'] = deleteOptions.csrfToken;
      }

      const body = {
        filename: filename,
        ...deleteOptions.additionalData
      };

      if (deleteOptions.csrfToken) {
        body[deleteOptions.csrfTokenName] = deleteOptions.csrfToken;
      }

      this.trigger('BeforeServerDelete', { filename });

      try {
        const response = await fetch(url, {
          method: 'DELETE',
          headers: headers,
          body: JSON.stringify(body),
          credentials: 'same-origin'
        });

        if (!response.ok) {
          throw new Error(`Delete failed: ${response.statusText}`);
        }

        const data = await response.json();
        this.trigger('ServerDeleteSuccess', { filename, response: data });

        return data;
      } catch (error) {
        this.trigger('ServerDeleteError', { filename, error });
        throw error;
      }
    }

    /**
     * Update file metadata on server
     * @param {string} fileId - File ID
     * @param {object} updateData - Data to update
     * @param {object} options - Update options
     * @returns {Promise} Update promise
     */
    async updateOnServer(fileId, updateData, options = {}) {
      if (!this.crud || !this.crud.updateUrl) {
        throw new Error('CRUD not configured or updateUrl not set');
      }

      const updateOptions = { ...this.crud, ...options };
      const url = updateOptions.updateUrl.replace(':id', fileId);

      const headers = {
        'Content-Type': 'application/json',
        ...updateOptions.headers
      };

      if (updateOptions.csrfToken) {
        headers['X-CSRF-TOKEN'] = updateOptions.csrfToken;
      }

      const body = {
        id: fileId,
        ...updateData,
        ...updateOptions.additionalData
      };

      if (updateOptions.csrfToken) {
        body[updateOptions.csrfTokenName] = updateOptions.csrfToken;
      }

      this.trigger('BeforeServerUpdate', { fileId, updateData });

      try {
        const response = await fetch(url, {
          method: updateOptions.method === 'POST' ? 'POST' : 'PUT',
          headers: headers,
          body: JSON.stringify(body),
          credentials: 'same-origin'
        });

        if (!response.ok) {
          throw new Error(`Update failed: ${response.statusText}`);
        }

        const data = await response.json();
        this.trigger('ServerUpdateSuccess', { fileId, response: data });

        return data;
      } catch (error) {
        this.trigger('ServerUpdateError', { fileId, error });
        throw error;
      }
    }

    /**
     * Load images from server
     * @param {object} options - Load options
     * @returns {Promise} Load promise
     */
    async loadFromServer(options = {}) {
      if (!this.crud || !this.crud.loadUrl) {
        throw new Error('CRUD not configured or loadUrl not set');
      }

      const loadOptions = { ...this.crud, ...options };
      const url = loadOptions.loadUrl;

      const headers = {
        'Content-Type': 'application/json',
        ...loadOptions.headers
      };

      if (loadOptions.csrfToken) {
        headers['X-CSRF-TOKEN'] = loadOptions.csrfToken;
      }

      this.trigger('BeforeServerLoad');

      try {
        const response = await fetch(url, {
          method: 'GET',
          headers: headers,
          credentials: 'same-origin'
        });

        if (!response.ok) {
          throw new Error(`Load failed: ${response.statusText}`);
        }

        const data = await response.json();
        this.trigger('ServerLoadSuccess', { response: data });

        let images = [];

        if (data.data) {
          if (data.data.data && Array.isArray(data.data.data)) {
            images = data.data.data;
          } else if (Array.isArray(data.data)) {
            images = data.data;
          }
        } else if (data.images && Array.isArray(data.images)) {
          images = data.images;
        } else if (Array.isArray(data)) {
          images = data;
        }

        if (images.length > 0) {
          const urls = images
            .map(img => img.url || img.full_url || img.path)
            .filter(url => url);

          if (urls.length > 0) {
            await this.loadExistingImages(urls);
          }
        }

        return data;
      } catch (error) {
        this.trigger('ServerLoadError', { error });
        throw error;
      }
    }

    /**
     * Get CSRF token from meta tag (Laravel/PHP)
     * @param {string} name - Meta tag name
     * @returns {string|null} CSRF token
     */
    getCSRFToken(name = 'csrf-token') {
      const meta = document.querySelector(`meta[name="${name}"]`);
      return meta ? meta.getAttribute('content') : null;
    }

    /**
     * Auto-configure CSRF token from meta tag
     * @param {string} metaName - Meta tag name (default: 'csrf-token')
     * @returns {PanhaMediaUploadPreview} Returns this for chaining
     */
    autoConfigureCSRF(metaName = 'csrf-token') {
      const token = this.getCSRFToken(metaName);
      if (token) {
        if (!this.crud) {
          this.configureCRUD({});
        }
        this.crud.csrfToken = token;
      }
      return this;
    }

    // =================================================================
    // FRAMEWORK ADAPTERS
    // =================================================================

    /**
     * Get data in format suitable for Laravel
     * @returns {object} Laravel-formatted data
     */
    toLaravel() {
      return {
        images: this.mediaFiles.map(item => ({
          name: item.name,
          size: item.size,
          type: item.type
        })),
        image_count: this.mediaFiles.length,
        _token: this.crud?.csrfToken || this.getCSRFToken()
      };
    }

    /**
     * Get data in format suitable for Vue.js
     * @returns {object} Vue-formatted data
     */
    toVue() {
      return {
        files: this.mediaFiles.map(item => item.file),
        count: this.mediaFiles.length,
        maxFiles: this.config.maxFiles,
        isEmpty: this.isEmpty(),
        isFull: this.isFull()
      };
    }

    /**
     * Get data in format suitable for React
     * @returns {object} React-formatted data
     */
    toReact() {
      return {
        files: this.mediaFiles.map(item => item.file),
        count: this.mediaFiles.length,
        maxFiles: this.config.maxFiles,
        isEmpty: this.isEmpty(),
        isFull: this.isFull(),
        handlers: {
          onFilesChange: (callback) => this.on('FilesChanged', callback),
          onUploadSuccess: (callback) => this.on('ServerUploadSuccess', callback),
          onUploadError: (callback) => this.on('ServerUploadError', callback),
          onDeleteSuccess: (callback) => this.on('ServerDeleteSuccess', callback),
          onDeleteError: (callback) => this.on('ServerDeleteError', callback)
        }
      };
    }

    /**
     * Get FormData ready for PHP upload
     * @param {string} fieldName - Field name for files (default: 'images[]')
     * @returns {FormData} FormData object
     */
    toPHP(fieldName = null) {
      const field = fieldName || this.config.fieldName || 'images[]';
      return this.getFilesAsFormData(field, {
        _token: this.crud?.csrfToken || this.getCSRFToken()
      });
    }

    /**
     * Load images from Laravel-formatted data
     * @param {object} data - Laravel data object with images array
     * @returns {Promise} Promise that resolves when images are loaded
     */
    async fromLaravel(data) {
      if (!data || !data.images) {
        throw new Error('Invalid Laravel data format. Expected {images: [...]}');
      }

      const imageUrls = data.images
        .map(img => img.url || img.path || img.full_url)
        .filter(url => url);

      if (imageUrls.length === 0) {
        throw new Error('No valid image URLs found in Laravel data');
      }

      return await this.loadExistingImages(imageUrls);
    }

    /**
     * Load images from Vue.js-formatted data
     * @param {object} data - Vue data object with files array
     * @returns {Promise} Promise that resolves when images are loaded
     */
    async fromVue(data) {
      if (!data || !data.files) {
        throw new Error('Invalid Vue data format. Expected {files: [...]}');
      }

      if (data.files.length > 0 && data.files[0] instanceof File) {
        this.mediaFiles = data.files.map((file, index) => ({
          id: this.fileIdCounter++,
          file: file,
          url: URL.createObjectURL(file),
          name: file.name,
          size: file.size,
          type: file.type
        }));
        this.updateUI();
        return this.mediaFiles;
      }

      const imageUrls = data.files
        .map(file => file.url || file.path || file)
        .filter(url => typeof url === 'string');

      if (imageUrls.length === 0) {
        throw new Error('No valid image URLs found in Vue data');
      }

      return await this.loadExistingImages(imageUrls);
    }

    /**
     * Load images from React-formatted data
     * @param {object} data - React data object with files array
     * @returns {Promise} Promise that resolves when images are loaded
     */
    async fromReact(data) {
      if (!data || !data.files) {
        throw new Error('Invalid React data format. Expected {files: [...]}');
      }

      if (data.files.length > 0 && data.files[0] instanceof File) {
        this.mediaFiles = data.files.map((file, index) => ({
          id: this.fileIdCounter++,
          file: file,
          url: URL.createObjectURL(file),
          name: file.name,
          size: file.size,
          type: file.type
        }));
        this.updateUI();
        return this.mediaFiles;
      }

      const imageUrls = data.files
        .map(file => file.url || file.src || file.path || file)
        .filter(url => typeof url === 'string');

      if (imageUrls.length === 0) {
        throw new Error('No valid image URLs found in React data');
      }

      return await this.loadExistingImages(imageUrls);
    }

    /**
     * Load images from PHP-formatted data
     * @param {Array|object} data - PHP data (array of URLs or object with images array)
     * @returns {Promise} Promise that resolves when images are loaded
     */
    async fromPHP(data) {
      let imageUrls = [];

      if (Array.isArray(data)) {
        imageUrls = data
          .map(item => {
            if (typeof item === 'string') return item;
            return item.url || item.path || item.src || item.file_path;
          })
          .filter(url => url);
      } else if (data && typeof data === 'object') {
        const imagesArray = data.images || data.files || data.data;
        if (Array.isArray(imagesArray)) {
          imageUrls = imagesArray
            .map(item => {
              if (typeof item === 'string') return item;
              return item.url || item.path || item.src || item.file_path;
            })
            .filter(url => url);
        }
      }

      if (imageUrls.length === 0) {
        throw new Error('No valid image URLs found in PHP data');
      }

      return await this.loadExistingImages(imageUrls);
    }

    destroy() {
      // Remove event listeners
      if (this.elements) {
        this.elements.uploadZone?.removeEventListener('click', null);
        this.elements.fileInput?.removeEventListener('change', null);
      }

      // Clear custom event listeners
      this.eventListeners = {};

      // Clear state
      this.uploadQueue = [];
      this.uploading = false;

      // Remove injected styles for this instance (if any)
      try {
        const styleId = `pmup-styles-${this.instanceId}`;
        const styleEl = document.getElementById(styleId);
        if (styleEl && styleEl.parentNode) styleEl.parentNode.removeChild(styleEl);
        const customStyleEl = document.getElementById(`${styleId}-custom`);
        if (customStyleEl && customStyleEl.parentNode) customStyleEl.parentNode.removeChild(customStyleEl);
      } catch (e) {
        // ignore
      }

      // Clear container and internal state
      this.container.innerHTML = '';
      this.mediaFiles = [];
      this.fileIdCounter = 0;
      this.elements = null;
    }

    // Utility methods
    generateId() {
      return Math.random().toString(36).substr(2, 9);
    }

    deepMerge(target, source) {
      const output = Object.assign({}, target);
      if (this.isObject(target) && this.isObject(source)) {
        Object.keys(source).forEach(key => {
          if (this.isObject(source[key])) {
            if (!(key in target))
              Object.assign(output, { [key]: source[key] });
            else
              output[key] = this.deepMerge(target[key], source[key]);
          } else {
            Object.assign(output, { [key]: source[key] });
          }
        });
      }
      return output;
    }

    isObject(item) {
      return item && typeof item === 'object' && !Array.isArray(item);
    }

    // Style injection - Fixed grid layout
    injectStyles() {
      // Use an instance-scoped style id so multiple instances with different
      // `gridColumns` or theme values don't overwrite each other.
      const styleId = `pmup-styles-${this.instanceId}`;
      if (document.getElementById(styleId)) return;

      const style = document.createElement('style');
      style.id = styleId;
      style.innerHTML = this.getStyles();
      document.head.appendChild(style);

      // Inject custom styles if provided (also scoped by instance)
      if (this.config.customStyles) {
        const customStyle = document.createElement('style');
        customStyle.id = `${styleId}-custom`;
        customStyle.innerHTML = this.config.customStyles;
        document.head.appendChild(customStyle);
      }
    }

    getStyles() {
      const theme = this.config.theme;
      const cols = this.config.gridColumns || 5;

      return `
        .pmup-container {
          background: white;
          border-radius: 16px;
          padding: 12px;
          max-width: 100%;
          width: 100%;
          box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
          font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .pmup-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 10px;
        }

        .pmup-title {
          font-size: 16px;
          font-weight: 600;
          color: ${theme.textColor};
          margin: 0;
        }

        /* Upload Zone */
        .pmup-upload-zone {
          border: 3px dashed #d1d5db;
          border-radius: 24px;
          padding: 16px 25px;
          text-align: center;
          transition: all 0.3s ease;
          cursor: pointer;
          background: #fafbfc;
          position: relative;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          min-height: 165px;
        }

        .pmup-upload-zone:hover {
          border-color: ${theme.primaryColor};
          background: #f9fafb;
          transform: translateY(-2px);
        }

        .pmup-upload-zone.pmup-drag-over {
          border-color: ${theme.primaryColor};
          background: #f3f4f6;
          transform: scale(1.01);
          box-shadow: 0 10px 40px rgba(128, 90, 213, 0.1);
        }

        .pmup-upload-zone.pmup-has-files {
          display: none;
        }

        /* Placeholder Icon - Circular with Upload SVG */
        .pmup-placeholder-icon {
          width: 80px;
          height: 80px;
          margin-bottom: 5px;
          position: relative;
          background: linear-gradient(135deg, #7c3aed 0%, #6c6ee9ff 100%);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          box-shadow: 0 2px 2px rgba(124, 58, 237, 0.3);
          transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .pmup-placeholder-icon svg {
          width: 40px;
          height: 40px;
          stroke: white;
          transition: inherit;
        }

        .pmup-upload-zone:hover .pmup-placeholder-icon {
          transform: translateY(-5px) rotate(180deg);
          box-shadow: 0 3px 3px rgba(124, 58, 237, 0.4);
        }

        .pmup-upload-text {
          font-size: 28px;
          font-weight: 600;
          color: #1f2937;
          margin-bottom: 12px;
          letter-spacing: -0.5px;
        }

        .pmup-upload-subtext {
          font-size: 16px;
          color: ${theme.primaryColor};
          font-weight: 500;
          line-height: 1.4;
        }

        .pmup-upload-zone:hover .pmup-upload-text {
          color: #111827;
        }

        /* Gallery Grid - Fixed Layout */
        .pmup-gallery-grid {
          display: none;
          grid-template-columns: repeat(${cols}, 1fr);
          grid-auto-rows: 1fr 1fr;
          margin-bottom: 12px;
        }

        .pmup-gallery-grid.pmup-active {
          display: grid;
        }

        .pmup-gallery-item {
          margin: 4px 2px;
          position: relative;
          border-radius: 12px;
          overflow: hidden;
          background: ${theme.backgroundColor};
          border: 2px solid transparent;
          cursor: pointer;
          transition: all 0.3s ease;
          animation: pmupFadeInScale 0.4s ease;
        }

        @keyframes pmupFadeInScale {
          from {
            opacity: 0;
            transform: scale(0.8) rotate(-2deg);
          }
          to {
            opacity: 1;
            transform: scale(1) rotate(0deg);
          }
        }

        /* First item spans 2x2 */
        .pmup-gallery-item-first {
          grid-column: span 2;
          grid-row: span 2;
        }

        .pmup-gallery-item:hover {
          transform: translateY(-4px) scale(1.02);
          box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
          border-color: ${theme.primaryColor};
          z-index: 10;
        }

        .pmup-gallery-item-wrapper {
          width: 100%;
          height: 100%;
          position: relative;
        }

        .pmup-gallery-item img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .pmup-gallery-item-overlay {
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background: linear-gradient(180deg, rgba(0,0,0,0) 60%, rgba(0,0,0,0.7) 100%);
          opacity: 0;
          transition: opacity 0.3s;
        }

        .pmup-gallery-item:hover .pmup-gallery-item-overlay {
          opacity: 1;
        }

        /* Remove Button */
        .pmup-gallery-item-remove {
            position: absolute;
            top: 3px;
            right: 3px;
            width: 22px;
            height: 22px;
            background: #f46e6eff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.3s;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            border: none;
        }

        .pmup-gallery-item:hover .pmup-gallery-item-remove {
          opacity: 1;
          transform: scale(1);
        }

        .pmup-gallery-item-remove:hover {
          background: #ef4444;
          transform: scale(1.1);
        }

        .pmup-gallery-item-remove:hover svg {
          color: #ffffffff;
          background: #ef4444;
        }

        .pmup-gallery-item-remove svg {
          width: 14px;
          height: 14px;
          color: #ffffffff;
        }

        /* File Info */
        .pmup-gallery-item-info {
          position: absolute;
          bottom: 0;
          left: 0;
          right: 0;
          padding: 8px;
          color: white;
          opacity: 0;
          transform: translateY(5px);
          transition: all 0.3s;
          background: linear-gradient(180deg, transparent, rgba(0,0,0,0.8));
        }

        .pmup-gallery-item-first .pmup-gallery-item-info {
          padding: 12px;
        }

        .pmup-gallery-item:hover .pmup-gallery-item-info {
          opacity: 1;
          transform: translateY(0);
        }

        .pmup-gallery-item-name {
          font-size: 11px;
          font-weight: 600;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
          text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .pmup-gallery-item-first .pmup-gallery-item-name {
          font-size: 13px;
        }

        .pmup-gallery-item-size {
          font-size: 10px;
          opacity: 0.9;
          margin-top: 2px;
        }

        /* Add More Button */
        .pmup-add-more-btn {
          border: 2px dashed ${theme.borderColor};
          background: ${theme.backgroundColor};
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          transition: all 0.3s;
        }

        .pmup-add-more-btn:hover {
          border-color: ${theme.primaryColor};
          background: #faf5ff;
          transform: scale(1.02);
        }

        .pmup-add-more-btn.pmup-drag-over {
          border-color: ${theme.primaryColor};
          background: #f3e8ff;
          transform: scale(1.05);
        }

        .pmup-add-more-icon {
          width: 32px;
          height: 32px;
          border-radius: 50%;
          background: white;
          display: flex;
          align-items: center;
          justify-content: center;
          margin-bottom: 8px;
          color: ${theme.primaryColor};
        }

        .pmup-add-more-text {
          font-size: 12px;
          color: #6b7280;
          font-weight: 500;
        }

        .pmup-add-more-btn:hover .pmup-add-more-text {
          color: ${theme.primaryColor};
        }

        /* Action Bar */
        .pmup-action-bar {
          display: none;
          justify-content: space-between;
          align-items: center;
          padding-top: 16px;
          border-top: 1px solid ${theme.borderColor};
        }

        .pmup-action-bar.pmup-active {
          display: flex;
        }

        .pmup-file-count {
          font-size: 14px;
          color: #6b7280;
        }

        .pmup-file-count strong {
          color: ${theme.textColor};
          font-weight: 600;
          font-size: 15px;
        }

        .pmup-action-buttons {
          display: flex;
          gap: 10px;
        }

        .pmup-btn {
          padding: 8px 20px;
          border-radius: 6px;
          font-size: 14px;
          font-weight: 500;
          cursor: pointer;
          transition: all 0.2s;
          border: none;
          outline: none;
        }

        .pmup-btn-clear {
          background: #f3f4f6;
          color: #4b5563;
        }

        .pmup-btn-clear:hover {
          background: #e5e7eb;
          transform: translateY(-1px);
        }

        .pmup-btn-upload {
          background: ${theme.primaryColor};
          color: white;
        }

        .pmup-btn-upload:hover {
          background: ${theme.primaryHover};
          transform: translateY(-1px);
          box-shadow: 0 4px 12px rgba(128, 90, 213, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
          .pmup-container {
            padding: 16px;
          }

          .pmup-placeholder-icon {
            width: 100px;
            height: 100px;
            margin-bottom: 24px;
          }

          .pmup-upload-text {
            font-size: 22px;
          }

          .pmup-upload-subtext {
            font-size: 14px;
          }

          .pmup-upload-zone {
            padding: 50px 30px;
            min-height: 250px;
          }
        }

        @media (max-width: 480px) {
          .pmup-gallery-grid {
            grid-template-columns: repeat(2, 1fr);
          }

          .pmup-gallery-item-first {
            grid-column: span 2;
            grid-row: span 1;
          }

          .pmup-upload-zone {
            padding: 40px 20px;
            min-height: 220px;
          }

          .pmup-placeholder-icon {
            width: 90px;
            height: 90px;
            margin-bottom: 20px;
          }

          .pmup-upload-text {
            font-size: 20px;
          }

          .pmup-upload-subtext {
            font-size: 13px;
          }
        }
      `;
    }
  }
  return PanhaMediaUploadPreview;
}));

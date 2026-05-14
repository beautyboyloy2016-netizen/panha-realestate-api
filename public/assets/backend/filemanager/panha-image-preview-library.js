/**
 * PanhaImagePreview - A vanilla JavaScript library for drag-and-drop image preview
 * No dependencies required - just plug and play!
 *
 * Usage:
 *   const uploader = new PanhaImagePreview('#container', {
 *     maxFiles: 8,
 *     maxSize: 5 * 1024 * 1024,
 *     onFilesChange: (files) => console.log(files)
 *   });
 */

class PanhaImagePreview {
  constructor(selector, options = {}) {
    // Configuration with defaults
    this.config = {
      maxFiles: options.maxFiles || 8,
      maxSize: options.maxSize || 5 * 1024 * 1024, // 5MB default
      acceptTypes: options.acceptTypes || 'image/*',
      onFilesChange: options.onFilesChange || null,
      onError: options.onError || null,
      theme: {
        primaryColor: '#7c3aed',
        gradientStart: '#667eea',
        gradientEnd: '#764ba2',
        deleteColor: '#ff4757',
        ...(options.theme || {}) // Merge user theme with defaults
      }
    };

    // State
    this.selectedFiles = [];
    this.eventListeners = {}; // Event system for CRUD operations
    this.crud = null; // CRUD configuration
    this.uploadQueue = [];
    this.uploading = false;

    // Get container element
    this.container = typeof selector === 'string'
      ? document.querySelector(selector)
      : selector;

    if (!this.container) {
      throw new Error(`Container element not found: ${selector}`);
    }

    // Initialize
    this.injectStyles();
    this.injectHTML();
    this.attachEventListeners();
  }

  injectStyles() {
    // Check if styles already injected
    if (document.getElementById('image-preview-upload-styles')) return;

    const style = document.createElement('style');
    style.id = 'image-preview-upload-styles';
    style.textContent = `
            .ipu-container {
                background: white;
                border-radius: 10px;
                padding: 12px;
                width: 100%;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            .ipu-drop-zone {
                border: 2px dashed #d3d3d3;
                border-radius: 15px;
                padding: 16px 20px;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s ease;
                background: #fafafa;
            }

            .ipu-drop-zone:hover {
                border-color: ${this.config.theme.primaryColor};
                background: #f9f7ff;
            }

            .ipu-drop-zone.dragover {
                border-color: ${this.config.theme.primaryColor};
                background: #f3effe;
                transform: scale(1.01);
            }

            .ipu-drop-zone-content {
                pointer-events: none;
            }

            .ipu-upload-icon {
                width: 80px;
                height: 80px;
                margin: 0 auto 16px;
                background: linear-gradient(135deg, ${this.config.theme.gradientStart} 0%, ${this.config.theme.gradientEnd} 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
            }

            .ipu-upload-icon svg {
                transition: transform 0.3s ease;
            }

            .ipu-drop-zone:hover .ipu-upload-icon svg {
                transform: rotate(180deg);
            }

            .ipu-drop-zone h3 {
                color: #333;
                font-size: 20px;
                font-weight: 600;
                margin-bottom: 10px;
            }

            .ipu-drop-zone p {
                color: ${this.config.theme.primaryColor};
                font-size: 14px;
            }

            .ipu-gallery-container {
                background: #f5f5f5;
                border-radius: 10px;
                padding: 16px;
                min-height: 150px;
                display: none;
            }

            .ipu-image-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                align-items: center;
            }

            .ipu-image-item {
                position: relative;
                width: 135px;
                height: 135px;
                border-radius: 10px;
                overflow: visible;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                animation: ipuFadeIn 0.3s ease;
                background: white;
                z-index: 1;
            }

            @keyframes ipuFadeIn {
                from {
                    opacity: 0;
                    transform: scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            .ipu-image-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 10px;
            }

            .ipu-delete-btn {
                position: absolute;
                top: -4px;
                right: -4px;
                background: ${this.config.theme.deleteColor};
                color: white;
                border: none;
                width: 18px;
                height: 18px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
                font-weight: bold;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                transition: all 0.2s ease;
                z-index: 10;
            }

            .ipu-delete-btn:hover {
                background: #ff3838;
                transform: scale(1.1);
            }

            .ipu-add-more-box {
                width: 135px;
                height: 135px;
                border: 2px dashed ${this.config.theme.primaryColor};
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s ease;
                background: rgba(124, 58, 237, 0.05);
            }

            .ipu-add-more-box:hover {
                background: rgba(124, 58, 237, 0.1);
                transform: scale(1.05);
            }

            .ipu-add-more-content {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 5px;
                color: ${this.config.theme.primaryColor};
                pointer-events: none;
            }

            .ipu-add-more-content span {
                font-size: 14px;
                font-weight: 500;
            }

            @media (max-width: 768px) {
                .ipu-image-grid {
                    justify-content: center;
                }

                .ipu-image-item,
                .ipu-add-more-box {
                    width: 120px;
                    height: 120px;
                }
            }

            @media (max-width: 480px) {
                .ipu-drop-zone {
                    padding: 10px 8px;
                }

                .ipu-upload-icon {
                    width: 60px;
                    height: 60px;
                    margin-bottom: 8px;
                }

                .ipu-image-item,
                .ipu-add-more-box {
                    width: 100px;
                    height: 100px;
                }

                .ipu-drop-zone h3 {
                    margin-top: 6px;
                    font-size: 18px;
                }

                .ipu-drop-zone p {
                    font-size: 14px;
                    margin: 4px 0px;
                }
            }
        `;
    document.head.appendChild(style);
  }

  injectHTML() {
    const maxFilesText = this.config.maxFiles;
    const maxSizeMB = Math.round(this.config.maxSize / (1024 * 1024));

    this.container.innerHTML = `
            <div class="ipu-container">
                <!-- Drop Zone - Initial State -->
                <div class="ipu-drop-zone" data-ipu-dropzone>
                    <input type="file" accept="${this.config.acceptTypes}" multiple hidden data-ipu-input>
                    <div class="ipu-drop-zone-content">
                        <div class="ipu-upload-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                        </div>
                        <h3>Drop your images here</h3>
                        <p>Max ${maxFilesText} files, ${maxSizeMB}MB each</p>
                    </div>
                </div>

                <!-- Gallery View - After Upload State -->
                <div class="ipu-gallery-container" data-ipu-gallery>
                    <div class="ipu-image-grid" data-ipu-grid>
                        <!-- Images will be dynamically added here -->
                    </div>
                    <!-- Add More Button -->
                    <div class="ipu-add-more-box" data-ipu-addmore>
                        <div class="ipu-add-more-content">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Add More</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

    // Cache DOM elements
    this.dropZone = this.container.querySelector('[data-ipu-dropzone]');
    this.fileInput = this.container.querySelector('[data-ipu-input]');
    this.galleryContainer = this.container.querySelector('[data-ipu-gallery]');
    this.imageGrid = this.container.querySelector('[data-ipu-grid]');
    this.addMoreBox = this.container.querySelector('[data-ipu-addmore]');
  }

  attachEventListeners() {
    // Click to select files
    this.dropZone.addEventListener('click', (e) => {
      e.stopPropagation();
      this.fileInput.click();
    });

    // Add More functionality
    this.addMoreBox.addEventListener('click', (e) => {
      e.stopPropagation();
      this.fileInput.click();
    });

    // File input change event
    this.fileInput.addEventListener('change', (e) => this.handleFiles(e));

    // Drag and drop events
    this.dropZone.addEventListener('dragover', (e) => {
      e.preventDefault();
      this.dropZone.classList.add('dragover');
    });

    this.dropZone.addEventListener('dragleave', () => {
      this.dropZone.classList.remove('dragover');
    });

    this.dropZone.addEventListener('drop', (e) => {
      e.preventDefault();
      this.dropZone.classList.remove('dragover');

      const files = e.dataTransfer.files;
      this.handleFilesArray(Array.from(files));
    });

    // Prevent default drag behaviors on document
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      document.addEventListener(eventName, this.preventDefaults, false);
    });
  }

  preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  handleFiles(e) {
    const files = e.target.files;
    this.handleFilesArray(Array.from(files));
    e.target.value = ''; // Reset input
  }

  handleFilesArray(files) {
    // Filter valid image files
    const validFiles = files.filter(file => {
      // Check if it's an image
      if (!file.type.startsWith('image/')) {
        this.showError(`${file.name} is not an image file`);
        return false;
      }

      // Check file size
      if (file.size > this.config.maxSize) {
        const sizeMB = Math.round(this.config.maxSize / (1024 * 1024));
        this.showError(`${file.name} exceeds ${sizeMB}MB limit`);
        return false;
      }

      return true;
    });

    // Check total files limit
    const remainingSlots = this.config.maxFiles - this.selectedFiles.length;
    if (validFiles.length > remainingSlots) {
      this.showError(`You can only add ${remainingSlots} more image(s). Maximum is ${this.config.maxFiles} files.`);
      validFiles.splice(remainingSlots);
    }

    // Add files to selectedFiles array
    this.selectedFiles = [...this.selectedFiles, ...validFiles];

    // Trigger callback
    if (this.config.onFilesChange) {
      this.config.onFilesChange(this.selectedFiles);
    }

    // Update UI
    if (this.selectedFiles.length > 0) {
      this.showGalleryView();
      this.renderImages();
    }
  }

  showError(message) {
    if (this.config.onError) {
      this.config.onError(message);
    } else {
      alert(message);
    }
  }

  showGalleryView() {
    this.dropZone.style.display = 'none';
    this.galleryContainer.style.display = 'block';
  }

  showDropZoneView() {
    this.dropZone.style.display = 'block';
    this.galleryContainer.style.display = 'none';
  }

  renderImages() {
    // Clear the entire grid first
    this.imageGrid.innerHTML = '';

    // Add all image previews
    this.selectedFiles.forEach((file, index) => {
      this.createImagePreview(file, index);
    });

    // Add the "Add More" box at the end (if not at max capacity AND we have 2 or more images)
    // Hide "Add More" button if only 1 image is uploaded
    if (this.selectedFiles.length >= 2 && this.selectedFiles.length < this.config.maxFiles) {
      this.imageGrid.appendChild(this.addMoreBox);
      this.addMoreBox.style.display = 'flex';
    } else {
      this.addMoreBox.style.display = 'none';
    }
  }

  createImagePreview(file, index) {
    const imageItem = document.createElement('div');
    imageItem.className = 'ipu-image-item';

    const img = document.createElement('img');

    // Use FileReader to display the image
    const reader = new FileReader();
    reader.onload = function () {
      img.src = reader.result;
    };
    reader.readAsDataURL(file);

    // Create delete button
    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'ipu-delete-btn';
    deleteBtn.innerHTML = '×';
    deleteBtn.type = 'button'; // CRITICAL: Prevent form submission

    // Use addEventListener instead of onclick to properly handle events
    deleteBtn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      this.removeImage(index);
    });

    // Append elements
    imageItem.appendChild(img);
    imageItem.appendChild(deleteBtn);

    // Simply append to the grid
    this.imageGrid.appendChild(imageItem);
  }

  removeImage(index) {
    this.selectedFiles.splice(index, 1);

    // Trigger callback
    if (this.config.onFilesChange) {
      this.config.onFilesChange(this.selectedFiles);
    }

    if (this.selectedFiles.length === 0) {
      this.showDropZoneView();
    } else {
      this.renderImages();
    }
  }

  // Public API methods
  getFiles() {
    return this.selectedFiles;
  }

  getFirstFile() {
    return this.selectedFiles.length > 0 ? this.selectedFiles[0] : null;
  }

  getFileCount() {
    return this.selectedFiles.length;
  }

  clearFiles() {
    this.selectedFiles = [];
    this.showDropZoneView();

    if (this.config.onFilesChange) {
      this.config.onFilesChange(this.selectedFiles);
    }
  }

  /**
   * Load existing images from URLs (useful for edit mode)
   * @param {string|Array<string>} imageUrls - Single URL or array of URLs
   * @returns {Promise} - Resolves when all images are loaded
   */
  async loadExistingImages(imageUrls) {
    // Ensure imageUrls is an array
    const urls = Array.isArray(imageUrls) ? imageUrls : [imageUrls];

    // Check if adding these images would exceed maxFiles
    if (this.selectedFiles.length + urls.length > this.config.maxFiles) {
      const error = `Cannot load ${urls.length} image(s). Maximum is ${this.config.maxFiles} files.`;
      this.showError(error);
      throw new Error(error);
    }

    try {
      // Fetch all images and convert to File objects
      const filePromises = urls.map(async (url) => {
        try {
          const response = await fetch(url);
          if (!response.ok) {
            throw new Error(`Failed to fetch image from ${url}`);
          }

          const blob = await response.blob();

          // Extract filename from URL
          const urlParts = url.split('/');
          const fileName = urlParts[urlParts.length - 1] || 'existing-image.jpg';

          // Create File object from blob
          const file = new File([blob], fileName, { type: blob.type });

          return file;
        } catch (error) {
          console.error(`Error loading image from ${url}:`, error);
          throw error;
        }
      });

      // Wait for all images to be fetched
      const files = await Promise.all(filePromises);

      // Add to selectedFiles
      this.selectedFiles = [...this.selectedFiles, ...files];

      // Trigger callback
      if (this.config.onFilesChange) {
        this.config.onFilesChange(this.selectedFiles);
      }

      // Update UI
      if (this.selectedFiles.length > 0) {
        this.showGalleryView();
        this.renderImages();
      }

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
   * Upload files to server using FormData
   * @param {string} url - The upload endpoint URL
   * @param {Object} options - Upload options
   * @param {string} options.fieldName - The field name for files (default: 'images[]')
   * @param {Object} options.additionalData - Additional data to send with the request
   * @param {Object} options.headers - Custom headers
   * @param {Function} options.onProgress - Progress callback (progress) => {}
   * @param {Function} options.onSuccess - Success callback (response) => {}
   * @param {Function} options.onError - Error callback (error) => {}
   * @returns {Promise} - Upload promise
   */
  async uploadFiles(url, options = {}) {
    const {
      fieldName = 'images[]',
      additionalData = {},
      headers = {},
      onProgress = null,
      onSuccess = null,
      onError = null
    } = options;

    if (this.selectedFiles.length === 0) {
      const error = new Error('No files to upload');
      if (onError) onError(error);
      throw error;
    }

    const formData = new FormData();

    // Add files to FormData
    this.selectedFiles.forEach((file, index) => {
      formData.append(fieldName, file);
    });

    // Add additional data
    Object.keys(additionalData).forEach(key => {
      formData.append(key, additionalData[key]);
    });

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

      // Create promise for XHR
      const uploadPromise = new Promise((resolve, reject) => {
        xhr.onload = () => {
          if (xhr.status >= 200 && xhr.status < 300) {
            let response;
            try {
              response = JSON.parse(xhr.responseText);
            } catch (e) {
              response = xhr.responseText;
            }
            if (onSuccess) onSuccess(response);
            resolve(response);
          } else {
            const error = new Error(`Upload failed with status ${xhr.status}`);
            if (onError) onError(error);
            reject(error);
          }
        };

        xhr.onerror = () => {
          const error = new Error('Network error during upload');
          if (onError) onError(error);
          reject(error);
        };

        xhr.open('POST', url);

        // Add custom headers
        Object.keys(headers).forEach(key => {
          xhr.setRequestHeader(key, headers[key]);
        });

        xhr.send(formData);
      });

      return uploadPromise;
    } catch (error) {
      if (onError) onError(error);
      throw error;
    }
  }

  /**
   * Get FormData object with files
   * @param {string} fieldName - The field name for files (default: 'images[]')
   * @param {Object} additionalData - Additional data to include
   * @returns {FormData}
   */
  getFormData(fieldName = 'images[]', additionalData = {}) {
    const formData = new FormData();

    // Add files
    this.selectedFiles.forEach((file) => {
      formData.append(fieldName, file);
    });

    // Add additional data
    Object.keys(additionalData).forEach(key => {
      formData.append(key, additionalData[key]);
    });

    return formData;
  }

  /**
   * Convert files to base64 strings
   * @returns {Promise<Array>} - Array of base64 strings
   */
  async getFilesAsBase64() {
    const base64Promises = this.selectedFiles.map(file => {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve({
          name: file.name,
          type: file.type,
          size: file.size,
          base64: reader.result
        });
        reader.onerror = reject;
        reader.readAsDataURL(file);
      });
    });

    return Promise.all(base64Promises);
  }

  /**
   * Set option(s) dynamically
   * @param {string|Object} option - Option name or object with options
   * @param {*} value - Option value (ignored if option is object)
   */
  setOption(option, value) {
    if (typeof option === 'object') {
      // Set multiple options
      Object.keys(option).forEach(key => {
        if (this.config.hasOwnProperty(key)) {
          this.config[key] = option[key];
        }
      });
    } else if (typeof option === 'string') {
      // Set single option
      if (this.config.hasOwnProperty(option)) {
        this.config[option] = value;
      }
    }
  }

  /**
   * Get option value(s)
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
   * Get file by index
   * @param {number} index - Index of the file
   * @returns {File|null} - File object or null
   */
  getFileByIndex(index) {
    return this.selectedFiles[index] || null;
  }

  /**
   * Remove file by index
   * @param {number} index - Index of the file to remove
   * @returns {File|null} - Removed file or null
   */
  removeFileByIndex(index) {
    if (index >= 0 && index < this.selectedFiles.length) {
      const removedFile = this.selectedFiles[index];
      this.removeImage(index);
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
      this.dropZone.style.pointerEvents = 'none';
      this.dropZone.style.opacity = '0.5';
      this.addMoreBox.style.pointerEvents = 'none';
      this.addMoreBox.style.opacity = '0.5';
      this.fileInput.disabled = true;
    } else {
      this.dropZone.style.pointerEvents = '';
      this.dropZone.style.opacity = '';
      this.addMoreBox.style.pointerEvents = '';
      this.addMoreBox.style.opacity = '';
      this.fileInput.disabled = false;
    }
  }

  /**
   * Enable file browsing (shorthand for disableBrowse(false))
   */
  enableBrowse() {
    this.disableBrowse(false);
  }

  /**
   * Check if files array is empty
   * @returns {boolean}
   */
  isEmpty() {
    return this.selectedFiles.length === 0;
  }

  /**
   * Check if files array is at maximum capacity
   * @returns {boolean}
   */
  isFull() {
    return this.selectedFiles.length >= this.config.maxFiles;
  }

  /**
   * Get remaining slots
   * @returns {number} - Number of files that can still be added
   */
  getRemainingSlots() {
    return this.config.maxFiles - this.selectedFiles.length;
  }

  /**
   * Get total size of all files in bytes
   * @returns {number} - Total size in bytes
   */
  getTotalSize() {
    return this.selectedFiles.reduce((total, file) => total + file.size, 0);
  }

  /**
   * Get total size in human-readable format
   * @returns {string} - Size with appropriate unit (KB, MB, GB)
   */
  getTotalSizeFormatted() {
    const bytes = this.getTotalSize();
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
  }

  /**
   * Filter files by criteria
   * @param {Function} callback - Filter function (file) => boolean
   * @returns {Array<File>} - Filtered files
   */
  filterFiles(callback) {
    return this.selectedFiles.filter(callback);
  }

  /**
   * Find file by criteria
   * @param {Function} callback - Find function (file) => boolean
   * @returns {File|undefined} - Found file or undefined
   */
  findFile(callback) {
    return this.selectedFiles.find(callback);
  }

  /**
   * Sort files
   * @param {Function} compareFn - Sort comparison function
   */
  sortFiles(compareFn) {
    this.selectedFiles.sort(compareFn);
    this.renderImages();
  }

  /**
   * Sort files by name
   * @param {boolean} ascending - Sort order (default: true)
   */
  sortByName(ascending = true) {
    this.sortFiles((a, b) => {
      const nameA = a.name.toLowerCase();
      const nameB = b.name.toLowerCase();
      if (ascending) {
        return nameA.localeCompare(nameB);
      } else {
        return nameB.localeCompare(nameA);
      }
    });
  }

  /**
   * Sort files by size
   * @param {boolean} ascending - Sort order (default: true)
   */
  sortBySize(ascending = true) {
    this.sortFiles((a, b) => {
      if (ascending) {
        return a.size - b.size;
      } else {
        return b.size - a.size;
      }
    });
  }

  /**
   * Sort files by type
   * @param {boolean} ascending - Sort order (default: true)
   */
  sortByType(ascending = true) {
    this.sortFiles((a, b) => {
      const typeA = a.type.toLowerCase();
      const typeB = b.type.toLowerCase();
      if (ascending) {
        return typeA.localeCompare(typeB);
      } else {
        return typeB.localeCompare(typeA);
      }
    });
  }

  /**
   * Refresh/re-render the upload UI
   */
  refresh() {
    if (this.selectedFiles.length > 0) {
      this.renderImages();
    } else {
      this.showDropZoneView();
    }
  }

  /**
   * Validate a file before adding
   * @param {File} file - File to validate
   * @returns {Object} - {valid: boolean, error: string|null}
   */
  validateFile(file) {
    // Check if it's an image
    if (!file.type.startsWith('image/')) {
      return {
        valid: false,
        error: `${file.name} is not an image file`
      };
    }

    // Check file size
    if (file.size > this.config.maxSize) {
      const sizeMB = Math.round(this.config.maxSize / (1024 * 1024));
      return {
        valid: false,
        error: `${file.name} exceeds ${sizeMB}MB limit`
      };
    }

    return { valid: true, error: null };
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
   * @returns {PanhaImagePreview} Returns this for chaining
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
      chunkSize: null, // For chunk upload
      concurrent: 3, // Concurrent uploads
      autoUpload: false,
      ...crudOptions
    };

    this.uploadQueue = [];
    this.uploading = false;

    this.trigger('CRUDConfigured', this.crud);
    return this;
  }

  /**
   * Upload file to server using the built-in uploadFiles method
   * @param {object} options - Upload options
   * @returns {Promise} Upload promise
   */
  async uploadToServer(options = {}) {
    if (!this.crud || !this.crud.uploadUrl) {
      throw new Error('CRUD not configured. Call configureCRUD() first.');
    }

    if (this.selectedFiles.length === 0) {
      throw new Error('No files to upload');
    }

    const uploadOptions = {
      fieldName: this.crud.fieldName,
      additionalData: { ...this.crud.additionalData, ...options.additionalData },
      headers: { ...this.crud.headers, ...options.headers },
      onProgress: options.onProgress,
      onSuccess: options.onSuccess,
      onError: options.onError
    };

    // Add CSRF token to headers if available
    if (this.crud.csrfToken) {
      uploadOptions.headers['X-CSRF-TOKEN'] = this.crud.csrfToken;
    }

    this.trigger('BeforeServerUpload', { files: this.selectedFiles });

    try {
      const response = await this.uploadFiles(this.crud.uploadUrl, uploadOptions);
      this.trigger('ServerUploadSuccess', { response });
      return response;
    } catch (error) {
      this.trigger('ServerUploadError', { error });
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
      throw new Error('CRUD not configured. Call configureCRUD() first.');
    }

    const deleteOptions = { ...this.crud, ...options };
    const url = deleteOptions.deleteUrl;

    const headers = {
      'Content-Type': 'application/json',
      ...deleteOptions.headers
    };

    // Add CSRF token if available
    if (deleteOptions.csrfToken) {
      headers['X-CSRF-TOKEN'] = deleteOptions.csrfToken;
    }

    const body = {
      filename: filename,
      ...deleteOptions.additionalData
    };

    // Add CSRF token to body for Laravel compatibility
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
      throw new Error('CRUD not configured. Call configureCRUD() first.');
    }

    const updateOptions = { ...this.crud, ...options };
    const url = updateOptions.updateUrl.replace(':id', fileId);

    const headers = {
      'Content-Type': 'application/json',
      ...updateOptions.headers
    };

    // Add CSRF token if available
    if (updateOptions.csrfToken) {
      headers['X-CSRF-TOKEN'] = updateOptions.csrfToken;
    }

    const body = {
      id: fileId,
      ...updateData,
      ...updateOptions.additionalData
    };

    // Add CSRF token to body
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
      throw new Error('CRUD not configured. Call configureCRUD() first.');
    }

    const loadOptions = { ...this.crud, ...options };
    const url = loadOptions.loadUrl;

    const headers = {
      'Content-Type': 'application/json',
      ...loadOptions.headers
    };

    // Add CSRF token if available
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

      // Parse and add images based on response structure
      let images = [];

      // Handle different response structures
      if (data.data) {
        // Laravel pagination or wrapped response
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

      // Load images using existing loadExistingImages method
      if (images.length > 0) {
        const urls = images
          .map(img => img.url || img.full_url || img.path)
          .filter(url => url); // Filter out undefined/null URLs

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
   * @returns {PanhaImagePreview} Returns this for chaining
   */
  autoConfigureCSRF(metaName = 'csrf-token') {
    const token = this.getCSRFToken(metaName);
    if (token) {
      // Auto-initialize CRUD if not configured
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
      images: this.selectedFiles.map(file => ({
        name: file.name,
        size: file.size,
        type: file.type
      })),
      image_count: this.selectedFiles.length,
      _token: this.crud?.csrfToken || this.getCSRFToken()
    };
  }

  /**
   * Get data in format suitable for Vue.js
   * @returns {object} Vue-formatted data
   */
  toVue() {
    return {
      files: this.selectedFiles,
      count: this.selectedFiles.length,
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
      files: this.selectedFiles,
      count: this.selectedFiles.length,
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
  toPHP(fieldName = 'images[]') {
    return this.getFormData(fieldName, {
      _token: this.crud?.csrfToken || this.getCSRFToken()
    });
  }

  destroy() {
    // Remove event listeners
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      document.removeEventListener(eventName, this.preventDefaults, false);
    });

    // Clear custom event listeners
    this.eventListeners = {};

    // Clear state
    this.selectedFiles = [];
    this.uploadQueue = [];
    this.uploading = false;

    // Clear container
    this.container.innerHTML = '';
  }
}

// Export for different module systems
if (typeof module !== 'undefined' && module.exports) {
  module.exports = PanhaImagePreview;
}
if (typeof window !== 'undefined') {
  window.PanhaImagePreview = PanhaImagePreview;
}

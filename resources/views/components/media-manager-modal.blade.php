@props([
    'modalId' => 'mediaManagerModal',
    'title' => 'Select Media',
    'maxFiles' => 1,
    'acceptedTypes' => 'image/*',
    'defaultPath' => '', // Empty = root folder with folder list
])

<!-- Media Manager Modal Container -->
<div id="{{ $modalId }}" class="media-manager-modal"
  style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow: auto;">
  <div class="media-manager-modal-content"
    style="position: relative; background: white; margin: 2% auto; max-width: 90%; width: 1200px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div class="media-manager-modal-header"
      style="padding: 15px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
      <h5 style="margin: 0; font-size: 18px; font-weight: 600;">{{ $title }}</h5>
      <button type="button" class="close-media-manager"
        style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">&times;</button>
    </div>
    <div class="media-manager-container" style="min-height: 500px;"></div>
    <div class="media-manager-modal-footer"
      style="padding: 15px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
      <div class="selection-info" style="color: #6b7280; font-size: 14px;">No file selected</div>
      <div>
        <button type="button" class="btn btn-secondary cancel-selection">Cancel</button>
        <button type="button" class="btn btn-primary confirm-selection" disabled>
          <i class="fas fa-check"></i> Select
        </button>
      </div>
    </div>
  </div>
</div>

@once
  @push('scripts')
    <script>
      (function() {
        'use strict';
        /**
         * PanhaMediaManagerModal - Reusable Media Manager Component
         * Usage: window.PanhaMediaManagerModal.open({ modalId, onSelect, defaultPath })
         */
        window.PanhaMediaManagerModal = {
          instances: {},

          /**
           * Initialize a media manager modal
           */
          init: function(config) {
            const modalId = config.modalId || 'mediaManagerModal';
            const modal = document.getElementById(modalId);

            if (!modal) {
              console.error('Media Manager Modal not found:', modalId);
              return null;
            }

            // Store instance
            this.instances[modalId] = {
              modal: modal,
              config: {
                title: config.title || 'Select Media',
                maxFiles: config.maxFiles || 1,
                acceptedTypes: config.acceptedTypes || 'image/*',
                defaultPath: config.defaultPath || '',
                onSelect: config.onSelect || null,
                onCancel: config.onCancel || null
              },
              manager: null,
              observer: null
            };

            this.setupEventHandlers(modalId);
            return this.instances[modalId];
          },

          /**
           * Open media manager modal
           */
          open: function(config) {
            const modalId = config.modalId || 'mediaManagerModal';
            let instance = this.instances[modalId];

            if (!instance) {
              instance = this.init(config);
            } else {
              // Update config if provided
              if (config.onSelect) instance.config.onSelect = config.onSelect;
              if (config.onCancel) instance.config.onCancel = config.onCancel;
              if (config.defaultPath !== undefined) instance.config.defaultPath = config.defaultPath;
            }

            if (!instance) return;

            // Show modal
            $(instance.modal).fadeIn(200);
            instance.modal.querySelector('.selection-info').textContent = 'No file selected';
            instance.modal.querySelector('.confirm-selection').disabled = true;

            // Initialize manager if needed
            if (!instance.manager) {
              this.initializeManager(modalId);
            }

            // Start monitoring selection
            this.startSelectionMonitor(modalId);
          },

          /**
           * Close media manager modal
           */
          close: function(modalId) {
            const instance = this.instances[modalId];
            if (!instance) return;

            $(instance.modal).fadeOut(200);

            if (instance.manager) {
              instance.manager.selectedFiles.clear();
              instance.manager.render();
            }

            this.stopSelectionMonitor(modalId);
          },

          /**
           * Load media files from server
           */
          loadMediaFiles: async function(path = '') {
            try {
              const response = await fetch(`{{ route('admin.media.files') }}?path=${encodeURIComponent(path)}`);
              const result = await response.json();

              if (result.success) {
                const items = [];

                // Add folders first
                if (result.data.folders && result.data.folders.length > 0) {
                  result.data.folders.forEach(folder => {
                    items.push({
                      id: 'folder_' + folder.name,
                      name: folder.name,
                      type: 'folder',
                      size: 0,
                      modified: new Date(folder.modified || Date.now()),
                      path: folder.path || folder.name
                    });
                  });
                }

                // Then add files
                if (result.data.files && result.data.files.length > 0) {
                  result.data.files.forEach(file => {
                    items.push({
                      id: file.id,
                      name: file.file_name,
                      type: file.is_image ? 'image' : 'document',
                      size: file.size || 0,
                      modified: new Date(file.modified || file.created_at),
                      url: file.full_url || file.file_url,
                      path: file.file_path
                    });
                  });
                }

                return items;
              }
              return [];
            } catch (error) {
              console.error('Error loading media files:', error);
              return [];
            }
          },

          /**
           * Initialize PanhaMediaManager instance
           */
          initializeManager: async function(modalId) {
            const instance = this.instances[modalId];
            if (!instance) return;

            const files = await this.loadMediaFiles(instance.config.defaultPath);
            const container = instance.modal.querySelector('.media-manager-container');

            const self = this; // Store reference for callback

            instance.manager = new PanhaMediaManager({
              container: container,
              title: instance.config.title,
              files: files,
              viewMode: 'grid',
              allowUpload: true,
              allowDelete: false,
              allowRename: false,
              maxFileSize: 5 * 1024 * 1024,
              acceptedFileTypes: instance.config.acceptedTypes,
              onFileSelect: function(file) {
                // Don't trigger selection for folders, only files
                if (file.type === 'folder') {
                  return;
                }
                console.log('File selected:', file);
              },
              onFolderOpen: async function(folder) {
                console.log('Opening folder:', folder);
                const folderPath = folder.path || folder.name;
                const files = await self.loadMediaFiles(folderPath);
                console.log('Loaded files from folder:', folderPath, files);
                instance.manager.files = files;
                instance.manager.filteredFiles = files;
                instance.manager.render();
              }
            });
          },

          /**
           * Start monitoring file selection
           */
          startSelectionMonitor: function(modalId) {
            const instance = this.instances[modalId];
            if (!instance) return;

            setTimeout(() => {
              const observer = new MutationObserver(() => {
                if (instance.manager && instance.manager.selectedFiles) {
                  const count = instance.manager.selectedFiles.size;
                  const selectionInfo = instance.modal.querySelector('.selection-info');
                  const confirmBtn = instance.modal.querySelector('.confirm-selection');

                  if (count > 0) {
                    const selected = instance.manager.getSelectedFiles();
                    if (selected && selected.length > 0) {
                      selectionInfo.textContent = `Selected: ${selected[0].name}`;
                      confirmBtn.disabled = false;
                    }
                  } else {
                    selectionInfo.textContent = 'No file selected';
                    confirmBtn.disabled = true;
                  }
                }
              });

              const container = instance.modal.querySelector('.media-manager-container');
              if (container) {
                observer.observe(container, {
                  attributes: true,
                  subtree: true,
                  attributeFilter: ['class']
                });
                instance.observer = observer;
              }
            }, 500);
          },

          /**
           * Stop monitoring file selection
           */
          stopSelectionMonitor: function(modalId) {
            const instance = this.instances[modalId];
            if (instance && instance.observer) {
              instance.observer.disconnect();
              instance.observer = null;
            }
          },

          /**
           * Setup event handlers for modal
           */
          setupEventHandlers: function(modalId) {
            const instance = this.instances[modalId];
            if (!instance) return;

            const modal = instance.modal;

            // Close button
            modal.querySelector('.close-media-manager').addEventListener('click', () => {
              if (instance.config.onCancel) {
                instance.config.onCancel();
              }
              this.close(modalId);
            });

            // Cancel button
            modal.querySelector('.cancel-selection').addEventListener('click', () => {
              if (instance.config.onCancel) {
                instance.config.onCancel();
              }
              this.close(modalId);
            });

            // Confirm button
            modal.querySelector('.confirm-selection').addEventListener('click', () => {
              if (instance.manager && instance.manager.selectedFiles.size > 0) {
                const selectedFiles = instance.manager.getSelectedFiles();
                if (selectedFiles && selectedFiles.length > 0) {
                  const files = selectedFiles.filter(f => f.type !== 'folder');

                  if (files.length > 0) {
                    if (instance.config.onSelect) {
                      instance.config.onSelect(files);
                    }
                    this.close(modalId);
                  } else {
                    alert('Please select a file (not a folder)');
                  }
                }
              }
            });
          }
        };
      })
      ();
    </script>
  @endpush
@endonce

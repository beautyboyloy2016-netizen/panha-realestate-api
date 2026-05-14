@extends('admin.layouts.master_layout')

@push('styles')
@endpush
@section('content')
  <!-- Page Content -->

  <div class="row">
    <div class="col-md-12">

      <!-- Media Manager Container -->
      <div id="media-manager-container"></div>

    </div>
  </div>
@endsection

@push('scripts')
  <!-- Load Media Manager Library -->
  <script src="{{ asset('assets/backend/filemanager/panha-media-manager-lib.js') }}"></script>

  <script>
    // Initialize Media Manager with Laravel backend
    document.addEventListener('DOMContentLoaded', function() {
      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      let mediaManagerInstance = null;

      // Function to load files from a specific path
      async function loadFiles(path = '') {
        try {
          const url = '{{ route('admin.media.files') }}' + (path ? '?path=' + encodeURIComponent(path) : '');
          const response = await fetch(url, {
            headers: {
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            }
          });

          const result = await response.json();
          console.log('Load files result:', result);

          // The backend returns data with folders and files
          const filesData = result.success && result.data && result.data.files ? result.data.files : [];
          const foldersData = result.success && result.data && result.data.folders ? result.data.folders : [];

          // Map folders to file format with type 'folder'
          const folders = foldersData.map(folder => ({
            id: 'folder-' + folder.name, // Unique ID for folder
            name: folder.name,
            type: 'folder', // Important: mark as folder
            path: folder.path,
            modified: folder.modified,
            size: folder.size,
            file_count: folder.file_count
          }));

          // Map files
          const files = filesData.map(media => ({
            id: media.id,
            name: media.file_name || media.original_filename,
            filename: media.original_filename,
            type: media.type || media.file_type,
            size: media.size,
            date: media.modified || media.created_at,
            url: media.url || media.full_url,
            thumbnail: media.url || media.full_url,
            mime: media.mime_type,
            extension: media.extension || media.file_extension,
            folder: media.folder || media.folder_path || '/',
            width: media.width,
            height: media.height,
            description: media.description,
            alt_text: media.alt_text
          }));

          // Combine folders and files - folders first
          const allItems = [...folders, ...files];

          // If manager already exists, update files
          if (mediaManagerInstance) {
            mediaManagerInstance.files = allItems;
            mediaManagerInstance.filteredFiles = [...allItems];
            mediaManagerInstance.currentPath = path || '/';

            // Clear search input when navigating
            const searchInput = document.querySelector('.mm-search-input');
            if (searchInput) {
              searchInput.value = '';
            }

            // Clear selected files
            mediaManagerInstance.selectedFiles.clear();

            mediaManagerInstance.render();
          } else {
            // Initialize the Media Manager for the first time
            initializeMediaManager(allItems);
          }
        } catch (error) {
          console.error('Error loading files:', error);
        }
      }

      // Initialize Media Manager
      function initializeMediaManager(allItems) {
        mediaManagerInstance = new PanhaMediaManager({
          container: '#media-manager-container',
          title: 'Media Library',
          files: allItems, // Pass combined folders and files
          viewMode: 'grid',
          allowUpload: true,
          allowDelete: true,
          allowRename: true,
          maxFileSize: 10 * 1024 * 1024, // 10MB
          acceptedFileTypes: '*',

          // Create Folder Handler
          onCreateFolder: async function(folderName, parentPath) {
            try {
              const response = await fetch('{{ route('admin.media.create-folder') }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken,
                  'Accept': 'application/json'
                },
                body: JSON.stringify({
                  name: folderName,
                  path: parentPath
                })
              });

              const result = await response.json();

              if (result.success) {
                showNotification(result.message || `Folder "${folderName}" created successfully`, 'success');
                // Reload files to show the new folder
                const currentPath = mediaManagerInstance.currentPath || '';
                await loadFiles(currentPath === '/' ? '' : currentPath);
              } else {
                showNotification('Create folder failed: ' + (result.message || 'Unknown error'), 'error');
              }
            } catch (error) {
              console.error('Create folder error:', error);
              showNotification('Create folder failed', 'error');
            }
          },

          // Folder Open Handler
          onFolderOpen: async function(folder) {
            console.log('Opening folder:', folder);
            await loadFiles(folder.path || folder.name);
          },

          // File Upload Handler
          onFileUpload: async function(fileData) {
            // fileData contains the file object from the library
            // We need to upload it to the Laravel backend
            const formData = new FormData();

            let file = null;

            // Handle both single file and FileList
            if (fileData.file) {
              // Single file object from library
              file = fileData.file;
              formData.append('file', file);
            } else if (fileData instanceof FileList) {
              // Multiple files - take first one since backend expects single file
              file = fileData[0];
              formData.append('file', file);
            } else if (fileData instanceof File) {
              // Single File object
              file = fileData;
              formData.append('file', file);
            } else {
              console.error('Unknown file data type:', fileData);
              return;
            }

            // Add the current path from media manager instance
            const currentPath = mediaManagerInstance.currentPath || '';
            if (currentPath && currentPath !== '/') {
              formData.append('path', currentPath);
              console.log('Uploading to folder:', currentPath);
            }

            // Get progress elements from the upload modal
            const uploadProgress = document.querySelector('.mm-upload-progress');
            const uploadStatus = document.querySelector('.mm-upload-status');
            const uploadPercent = document.querySelector('.mm-upload-percent');
            const progressBar = document.querySelector('.mm-progress-bar');
            const uploadFilename = document.querySelector('.mm-upload-filename');

            // Show progress bar
            if (uploadProgress) {
              uploadProgress.style.display = 'block';
              if (uploadFilename && file) {
                uploadFilename.textContent = file.name;
              }
            }

            return new Promise((resolve, reject) => {
              const xhr = new XMLHttpRequest();

              // Track upload progress
              xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                  const percentComplete = Math.round((e.loaded / e.total) * 100);

                  // Update progress bar in modal
                  if (progressBar) progressBar.style.width = percentComplete + '%';
                  if (uploadPercent) uploadPercent.textContent = percentComplete + '%';
                  if (uploadStatus) uploadStatus.textContent = percentComplete === 100 ?
                    'Processing...' : 'Uploading...';
                }
              });

              // Handle completion
              xhr.addEventListener('load', async () => {
                // Hide progress bar
                if (uploadProgress) {
                  setTimeout(() => {
                    uploadProgress.style.display = 'none';
                  }, 1000);
                }

                if (xhr.status === 200) {
                  const result = JSON.parse(xhr.responseText);
                  if (result.success) {
                    if (uploadStatus) uploadStatus.textContent = 'Upload complete!';
                    showNotification(result.message, 'success');
                    // Reload files to show the newly uploaded file
                    const reloadPath = mediaManagerInstance.currentPath === '/' ? '' :
                      mediaManagerInstance.currentPath;
                    await loadFiles(reloadPath);
                    resolve(result);
                  } else {
                    if (uploadStatus) uploadStatus.textContent = 'Upload failed';
                    showNotification('Upload failed: ' + result.message, 'error');
                    reject(new Error(result.message));
                  }
                } else {
                  if (uploadStatus) uploadStatus.textContent = 'Upload failed';
                  showNotification('Upload failed', 'error');
                  reject(new Error('Upload failed'));
                }
              });

              // Handle errors
              xhr.addEventListener('error', () => {
                if (uploadProgress) uploadProgress.style.display = 'none';
                if (uploadStatus) uploadStatus.textContent = 'Upload failed';
                showNotification('Upload failed: Network error', 'error');
                reject(new Error('Network error'));
              });

              // Handle abort
              xhr.addEventListener('abort', () => {
                if (uploadProgress) uploadProgress.style.display = 'none';
                if (uploadStatus) uploadStatus.textContent = 'Upload cancelled';
                showNotification('Upload cancelled', 'error');
                reject(new Error('Upload cancelled'));
              });

              // Send request
              xhr.open('POST', '{{ route('admin.media.upload') }}');
              xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
              xhr.setRequestHeader('Accept', 'application/json');
              xhr.send(formData);
            });
          },

          // File Delete Handler
          onFileDelete: async function(fileIds) {
            // Don't show confirmation here - the library already shows it
            try {
              const response = await fetch('{{ route('admin.media.bulk-destroy') }}', {
                method: 'DELETE',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken,
                  'Accept': 'application/json'
                },
                body: JSON.stringify({
                  ids: fileIds
                })
              });

              const result = await response.json();

              if (result.success) {
                showNotification(result.message, 'success');

                // Reload files after successful deletion
                const currentPath = mediaManagerInstance.currentPath || '';
                await loadFiles(currentPath);

                return true; // Allow deletion in UI
              } else {
                showNotification('Delete failed: ' + result.message, 'error');
                return false;
              }
            } catch (error) {
              console.error('Delete error:', error);
              showNotification('Delete failed', 'error');
              return false;
            }
          },

          // File Rename Handler
          onFileRename: async function(fileId, newName) {
            try {
              const response = await fetch(`/admin/media/${fileId}`, {
                method: 'PUT',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken,
                  'Accept': 'application/json'
                },
                body: JSON.stringify({
                  filename: newName
                })
              });

              const result = await response.json();

              if (result.success) {
                showNotification('File renamed successfully', 'success');
                return true;
              } else {
                showNotification('Rename failed: ' + result.message, 'error');
                return false;
              }
            } catch (error) {
              console.error('Rename error:', error);
              showNotification('Rename failed', 'error');
              return false;
            }
          },

          // Folder Rename Handler
          onFolderRename: async function(folder, newName) {
            try {
              const response = await fetch('{{ route('admin.media.rename-folder') }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken,
                  'Accept': 'application/json'
                },
                body: JSON.stringify({
                  path: folder.path,
                  new_name: newName
                })
              });

              const result = await response.json();

              if (result.success) {
                showNotification(result.message || 'Folder renamed successfully', 'success');
                // Reload files to reflect the change
                const currentPath = mediaManagerInstance.currentPath || '';
                // Go up one level since current folder might be renamed
                const parentPath = currentPath.split('/').slice(0, -1).join('/') || '';
                await loadFiles(parentPath);
                return true;
              } else {
                showNotification('Rename failed: ' + (result.message || 'Unknown error'), 'error');
                return false;
              }
            } catch (error) {
              console.error('Folder rename error:', error);
              showNotification('Rename failed', 'error');
              return false;
            }
          },

          // Folder Delete Handler
          onFolderDelete: async function(folder) {
            try {
              const response = await fetch('{{ route('admin.media.delete-folder') }}', {
                method: 'DELETE',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken,
                  'Accept': 'application/json'
                },
                body: JSON.stringify({
                  path: folder.path
                })
              });

              const result = await response.json();

              if (result.success) {
                showNotification(result.message || 'Folder deleted successfully', 'success');
                // Reload files to reflect the change
                const currentPath = mediaManagerInstance.currentPath || '';
                await loadFiles(currentPath);
                return true;
              } else {
                showNotification('Delete failed: ' + (result.message || 'Unknown error'), 'error');
                return false;
              }
            } catch (error) {
              console.error('Folder delete error:', error);
              showNotification('Delete failed', 'error');
              return false;
            }
          },

          // Folder Move Handler
          onFolderMove: async function(folder, targetPath) {
            try {
              const response = await fetch('{{ route('admin.media.move-folder') }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken,
                  'Accept': 'application/json'
                },
                body: JSON.stringify({
                  path: folder.path,
                  target_path: targetPath
                })
              });

              const result = await response.json();

              if (result.success) {
                showNotification(result.message || 'Folder moved successfully', 'success');
                // Reload files to reflect the change
                const currentPath = mediaManagerInstance.currentPath || '';
                await loadFiles(currentPath);
                return true;
              } else {
                showNotification('Move failed: ' + (result.message || 'Unknown error'), 'error');
                return false;
              }
            } catch (error) {
              console.error('Folder move error:', error);
              showNotification('Move failed', 'error');
              return false;
            }
          },

          // File Select Handler
          onFileSelect: function(selectedFiles) {
            console.log('Selected files:', selectedFiles);
          }
        });
      }

      // Notification helper
      function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${
                    type === 'success' ? 'bg-green-500' :
                    type === 'error' ? 'bg-red-500' :
                    'bg-blue-500'
                }`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
          notification.style.opacity = '0';
          notification.style.transition = 'opacity 0.3s ease';
          setTimeout(() => notification.remove(), 300);
        }, 3000);
      }

      // Progress notification helpers
      function createProgressNotification() {
        const notification = document.createElement('div');
        notification.className =
          'fixed bottom-4 right-4 px-6 py-4 rounded-lg shadow-lg bg-blue-500 text-white z-50 min-w-80';
        notification.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold">Uploading file...</span>
                        <span class="progress-percent">0%</span>
                    </div>
                    <div class="w-full bg-blue-300 rounded-full h-2">
                        <div class="progress-bar bg-white h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                `;
        document.body.appendChild(notification);
        return notification;
      }

      function updateProgressNotification(notification, percent) {
        const progressBar = notification.querySelector('.progress-bar');
        const progressPercent = notification.querySelector('.progress-percent');
        if (progressBar) progressBar.style.width = percent + '%';
        if (progressPercent) progressPercent.textContent = percent + '%';
      }

      function removeProgressNotification(notification) {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s ease';
        setTimeout(() => notification.remove(), 300);
      }

      // Load initial files (root folder)
      loadFiles();
    });
  </script>
@endpush

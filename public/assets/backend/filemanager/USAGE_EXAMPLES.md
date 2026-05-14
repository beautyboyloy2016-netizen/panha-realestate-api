# PanhaImagePreview Library - Usage Examples

## Basic Initialization

```javascript
const uploader = new PanhaImagePreview('#container', {
    maxFiles: 8,
    maxSize: 5 * 1024 * 1024, // 5MB
    acceptTypes: 'image/*',
    onFilesChange: (files) => console.log('Files:', files),
    onError: (error) => alert(error)
});
```

## Load Existing Images (Edit Mode)

### Load a Single Image

```javascript
// Single image URL
const imageUrl = 'https://example.com/uploads/image.jpg';

uploader.loadExistingImage(imageUrl)
    .then(file => console.log('Image loaded:', file))
    .catch(error => console.error('Error:', error));
```

### Load Multiple Images

```javascript
// Array of image URLs
const imageUrls = [
    'https://example.com/uploads/image1.jpg',
    'https://example.com/uploads/image2.jpg',
    'https://example.com/uploads/image3.jpg'
];

uploader.loadExistingImages(imageUrls)
    .then(files => console.log('All images loaded:', files))
    .catch(error => console.error('Error:', error));
```

### Load Existing Images in Edit Form

```javascript
// Edit item handler
$('.edit-button').click(function() {
    const itemId = $(this).data('id');
    
    $.get('/items/' + itemId + '/edit', function(data) {
        // Initialize uploader
        const uploader = new PanhaImagePreview('#imageContainer', {
            maxFiles: 5
        });
        
        // Load existing image
        if (data.image_url) {
            uploader.loadExistingImage(data.image_url);
        }
        
        // Or load multiple images
        if (data.gallery_urls && data.gallery_urls.length > 0) {
            uploader.loadExistingImages(data.gallery_urls);
        }
    });
});
```

## Get Files

```javascript
// Get all files
const files = uploader.getFiles();

// Get first file only
const firstFile = uploader.getFirstFile();

// Get file count
const count = uploader.getFileCount();
```

## Clear Files

```javascript
uploader.clearFiles();
```

## Upload Files to Server

```javascript
uploader.uploadFiles('/api/upload', {
    fieldName: 'images[]',
    additionalData: {
        folder: 'products',
        user_id: 123
    },
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    onProgress: (percent) => {
        console.log('Upload progress:', percent + '%');
    },
    onSuccess: (response) => {
        console.log('Upload success:', response);
    },
    onError: (error) => {
        console.error('Upload error:', error);
    }
});
```

## Get FormData

```javascript
// Get FormData object for manual AJAX submission
const formData = uploader.getFormData('photos[]', {
    title: 'My Album',
    description: 'Summer vacation'
});

// Use with jQuery AJAX
$.ajax({
    url: '/upload',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
        console.log('Success:', response);
    }
});
```

## Convert to Base64

```javascript
// Get all files as base64 strings
uploader.getFilesAsBase64()
    .then(base64Files => {
        base64Files.forEach(file => {
            console.log('File name:', file.name);
            console.log('File type:', file.type);
            console.log('File size:', file.size);
            console.log('Base64:', file.base64);
        });
    });
```

## Dynamic Configuration

### Set Options

```javascript
// Set single option
uploader.setOption('maxFiles', 10);

// Set multiple options
uploader.setOption({
    maxFiles: 10,
    maxSize: 10 * 1024 * 1024, // 10MB
    acceptTypes: 'image/jpeg,image/png'
});
```

### Get Options

```javascript
// Get single option
const maxFiles = uploader.getOption('maxFiles');

// Get all options
const allOptions = uploader.getOption();
console.log(allOptions);
```

## File Management

### Get File by Index

```javascript
// Get first file
const firstFile = uploader.getFileByIndex(0);

// Get last file
const lastFile = uploader.getFileByIndex(uploader.getFileCount() - 1);
```

### Remove File by Index

```javascript
// Remove first file
const removedFile = uploader.removeFileByIndex(0);
console.log('Removed:', removedFile.name);

// Remove last file
uploader.removeFileByIndex(uploader.getFileCount() - 1);
```

## Disable/Enable Browsing

```javascript
// Disable file selection
uploader.disableBrowse();

// Enable file selection
uploader.enableBrowse();

// Conditional disable based on state
if (uploader.isFull()) {
    uploader.disableBrowse();
}
```

## Check Upload State

```javascript
// Check if empty
if (uploader.isEmpty()) {
    alert('Please select at least one file');
}

// Check if full
if (uploader.isFull()) {
    alert('Maximum files reached');
}

// Get remaining slots
const remaining = uploader.getRemainingSlots();
console.log(`You can add ${remaining} more file(s)`);

// Get total size
const totalBytes = uploader.getTotalSize();
const totalFormatted = uploader.getTotalSizeFormatted();
console.log(`Total size: ${totalFormatted}`); // e.g., "15.2 MB"
```

## Filter and Find Files

### Filter Files

```javascript
// Get only JPG files
const jpgFiles = uploader.filterFiles(file => {
    return file.type === 'image/jpeg';
});

// Get files larger than 1MB
const largeFiles = uploader.filterFiles(file => {
    return file.size > 1024 * 1024;
});

// Get files with specific name pattern
const pngFiles = uploader.filterFiles(file => {
    return file.name.endsWith('.png');
});
```

### Find File

```javascript
// Find specific file by name
const file = uploader.findFile(file => {
    return file.name === 'photo.jpg';
});

// Find first file over 5MB
const bigFile = uploader.findFile(file => {
    return file.size > 5 * 1024 * 1024;
});
```

## Sort Files

### Sort by Name

```javascript
// Sort ascending (A-Z)
uploader.sortByName();

// Sort descending (Z-A)
uploader.sortByName(false);
```

### Sort by Size

```javascript
// Sort smallest to largest
uploader.sortBySize();

// Sort largest to smallest
uploader.sortBySize(false);
```

### Sort by Type

```javascript
// Sort by file type (A-Z)
uploader.sortByType();

// Sort by type (Z-A)
uploader.sortByType(false);
```

### Custom Sort

```javascript
// Sort by custom criteria
uploader.sortFiles((a, b) => {
    // Sort by file extension, then by name
    const extA = a.name.split('.').pop();
    const extB = b.name.split('.').pop();
    
    if (extA === extB) {
        return a.name.localeCompare(b.name);
    }
    return extA.localeCompare(extB);
});
```

## Validate Files

```javascript
// Validate before adding manually
const fileInput = document.querySelector('input[type="file"]');
fileInput.addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    
    files.forEach(file => {
        const validation = uploader.validateFile(file);
        if (!validation.valid) {
            alert(validation.error);
        }
    });
});
```

## Refresh UI

```javascript
// Manually refresh the uploader UI
// Useful after external file array modifications
uploader.refresh();

// Example: Clear and refresh
uploader.selectedFiles = [];
uploader.refresh();
```

## Complete Example - Laravel Form with Edit Support

```javascript
$(document).ready(function() {
    let featureUploader, galleryUploader;
    
    // Initialize uploaders function
    function initUploaders() {
        featureUploader = new PanhaImagePreview('#featureImage', {
            maxFiles: 1,
            maxSize: 5 * 1024 * 1024,
            acceptTypes: 'image/*'
        });
        
        galleryUploader = new PanhaImagePreview('#galleryImages', {
            maxFiles: 8,
            maxSize: 5 * 1024 * 1024,
            acceptTypes: 'image/*'
        });
    }
    
    // Create new
    $('#createBtn').click(function() {
        $('#container').html('');
        initUploaders();
        $('#modal').modal('show');
    });
    
    // Edit existing
    $('.editBtn').click(function() {
        const id = $(this).data('id');
        
        $.get('/items/' + id + '/edit', function(data) {
            $('#container').html('');
            initUploaders();
            
            // Populate form fields
            $('#name').val(data.name);
            $('#description').val(data.description);
            
            // Load existing images
            if (data.feature_image_url) {
                featureUploader.loadExistingImage(data.feature_image_url);
            }
            
            if (data.gallery_urls && data.gallery_urls.length > 0) {
                galleryUploader.loadExistingImages(data.gallery_urls);
            }
            
            $('#modal').modal('show');
        });
    });
    
    // Submit form
    $('#form').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Add feature image
        const featureFile = featureUploader.getFirstFile();
        if (featureFile) {
            formData.append('feature_image', featureFile);
        }
        
        // Add gallery images
        const galleryFiles = galleryUploader.getFiles();
        galleryFiles.forEach(file => {
            formData.append('gallery[]', file);
        });
        
        $.ajax({
            url: '/items',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                alert('Success!');
                $('#modal').modal('hide');
            }
        });
    });
});
```

## Customization Options

```javascript
const uploader = new PanhaImagePreview('#container', {
    // Maximum number of files
    maxFiles: 8,
    
    // Maximum file size in bytes (5MB)
    maxSize: 5 * 1024 * 1024,
    
    // Accepted file types
    acceptTypes: 'image/*',
    
    // Callback when files change
    onFilesChange: function(files) {
        console.log('Current files:', files);
    },
    
    // Error callback
    onError: function(message) {
        Swal.fire('Error!', message, 'error');
    },
    
    // Custom theme colors
    theme: {
        primaryColor: '#7c3aed',
        gradientStart: '#667eea',
        gradientEnd: '#764ba2',
        deleteColor: '#ff4757'
    }
});
```

## API Reference

| Method | Description | Returns |
|--------|-------------|---------|
| `getFiles()` | Get all selected files | `Array<File>` |
| `getFirstFile()` | Get the first file | `File\|null` |
| `getFileCount()` | Get number of files | `number` |
| `getFileByIndex(index)` | Get file at specific index | `File\|null` |
| `clearFiles()` | Remove all files | `void` |
| `removeFileByIndex(index)` | Remove file at index | `File\|null` |
| `loadExistingImage(url)` | Load single image from URL | `Promise<File>` |
| `loadExistingImages(urls)` | Load multiple images from URLs | `Promise<Array<File>>` |
| `uploadFiles(url, options)` | Upload files to server | `Promise` |
| `getFormData(fieldName, additionalData)` | Get FormData object | `FormData` |
| `getFilesAsBase64()` | Convert files to base64 | `Promise<Array>` |
| `setOption(option, value)` | Set configuration option(s) | `void` |
| `getOption(option)` | Get configuration option(s) | `*` |
| `disableBrowse(disable)` | Disable/enable file browsing | `void` |
| `enableBrowse()` | Enable file browsing | `void` |
| `isEmpty()` | Check if no files selected | `boolean` |
| `isFull()` | Check if at max capacity | `boolean` |
| `getRemainingSlots()` | Get available slots | `number` |
| `getTotalSize()` | Get total size in bytes | `number` |
| `getTotalSizeFormatted()` | Get human-readable size | `string` |
| `filterFiles(callback)` | Filter files by criteria | `Array<File>` |
| `findFile(callback)` | Find file by criteria | `File\|undefined` |
| `sortFiles(compareFn)` | Sort files with custom function | `void` |
| `sortByName(ascending)` | Sort files by name | `void` |
| `sortBySize(ascending)` | Sort files by size | `void` |
| `sortByType(ascending)` | Sort files by type | `void` |
| `refresh()` | Re-render the UI | `void` |
| `validateFile(file)` | Validate a file | `Object` |
| `destroy()` | Cleanup and remove | `void` |

## Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers

## Features

- ✅ Drag and drop support
- ✅ Click to browse
- ✅ Multiple file upload
- ✅ Image preview
- ✅ Delete images
- ✅ File validation (size, type)
- ✅ Load existing images from URLs
- ✅ Responsive design
- ✅ No dependencies
- ✅ Easy to use API

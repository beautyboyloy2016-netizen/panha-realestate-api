/**
 * Panha Note Editor - Bundle Loader
 * Automatically loads all required files in the correct order
 * @version 1.0.0
 * @license MIT
 * 
 * Usage: 
 *   <script src="panha-note-editor.bundle.js"></script>
 * 
 * This will automatically load:
 *   - panha-note-editor.core.min.js
 *   - panha-note-editor.full-toolbar.min.js
 *   - panha-note-editor.table-plugin.min.js
 *   - panha-note-editor.image-plugin.min.js
 *   - panha-note-editor.adapter.min.js
 */

(function () {
  'use strict';

  // List of files to load in order
  const files = [
    'panha-note-editor.core.min.js',
    'panha-note-editor.full-toolbar.min.js',
    'panha-note-editor.table-plugin.min.js',
    'panha-note-editor.image-plugin.min.js',
    'panha-note-editor.adapter.min.js'
  ];

  // Get the base URL from this script's location
  function getBaseUrl() {
    const scripts = document.getElementsByTagName('script');
    for (let i = 0; i < scripts.length; i++) {
      const src = scripts[i].src;
      if (src && src.indexOf('panha-note-editor.bundle.js') !== -1) {
        return src.substring(0, src.lastIndexOf('/') + 1);
      }
    }
    // Fallback to current directory
    return './';
  }

  const baseUrl = getBaseUrl();
  let loadedCount = 0;

  // Function to load a single script
  function loadScript(index) {
    if (index >= files.length) {
      console.log('✅ Panha Note Editor: All components loaded successfully');
      // Dispatch a custom event when all scripts are loaded
      const event = new CustomEvent('panhaNoteEditorReady', {
        detail: { loadedFiles: files }
      });
      document.dispatchEvent(event);
      return;
    }

    const script = document.createElement('script');
    script.src = baseUrl + files[index];
    script.async = false; // Ensure scripts load in order

    script.onload = function () {
      loadedCount++;
      console.log(`📦 Loaded (${loadedCount}/${files.length}):`, files[index]);
      loadScript(index + 1);
    };

    script.onerror = function () {
      console.error('❌ Failed to load:', files[index]);
      console.error('   URL:', script.src);
      // Continue loading other files even if one fails
      loadScript(index + 1);
    };

    document.head.appendChild(script);
  }

  // Start loading scripts
  console.log('🚀 Panha Note Editor: Starting to load components...');
  loadScript(0);
})();

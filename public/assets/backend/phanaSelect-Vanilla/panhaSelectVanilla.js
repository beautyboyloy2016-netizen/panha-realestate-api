/**
 * Tech-Select - A customizable vanilla JS select plugin
 * Inspired by Select2 with search, tagging, and customization features
 * Version: 1.0.0 (Vanilla JS)
 */

(function () {
  'use strict';

  // Helper functions
  const createElement = (tag, className, attributes = {}) => {
    const element = document.createElement(tag);
    if (className) element.className = className;
    Object.entries(attributes).forEach(([key, value]) => {
      if (key === 'text') {
        element.textContent = value;
      } else if (key === 'html') {
        element.innerHTML = value;
      } else {
        element.setAttribute(key, value);
      }
    });
    return element;
  };

  const show = (element) => {
    if (element) element.style.display = '';
  };

  const hide = (element) => {
    if (element) element.style.display = 'none';
  };

  const trigger = (element, eventName, detail = {}) => {
    const event = new CustomEvent(eventName, { detail, bubbles: true });
    element.dispatchEvent(event);
  };

  class PanhaSelect {
    constructor(select, settings) {
      this.select = select;
      this.settings = Object.assign({
        placeholder: 'Select an option...',
        searchPlaceholder: 'Search...',
        allowClear: false,
        multiple: select.hasAttribute('multiple'),
        width: '100%',
        dropdownCssClass: '',
        containerCssClass: '',
        tags: false,
        maximumSelectionLength: 0,
        minimumResultsForSearch: 0,
        closeOnSelect: true,
        theme: 'default',
        templateResult: null,
        templateSelection: null,
        matcher: null
      }, settings);

      this.container = null;
      this.selection = null;
      this.dropdown = null;
      this.search = null;
      this.results = null;
      this.isOpen = false;
      this.selectedValues = [];
      this.options = [];
      this.documentClickHandler = null;
    }

    init() {
      this.parseOptions();
      this.createStructure();
      this.bindEvents();
      this.updateSelection();
      hide(this.select);
    }

    parseOptions() {
      this.options = [];
      const optionElements = this.select.querySelectorAll('option');

      optionElements.forEach(option => {
        this.options.push({
          id: option.value,
          text: option.textContent,
          selected: option.selected,
          disabled: option.disabled,
          element: option
        });
      });

      this.selectedValues = this.options
        .filter(opt => opt.selected)
        .map(opt => opt.id);
    }

    createStructure() {
      const containerId = 'tech-select-' + Math.random().toString(36).substr(2, 9);

      // Main container
      this.container = createElement('div', 'tech-select-container', { id: containerId });
      this.container.style.width = this.settings.width;
      if (this.settings.containerCssClass) {
        this.container.classList.add(...this.settings.containerCssClass.split(' '));
      }
      this.container.classList.add('tech-select-theme-' + this.settings.theme);

      if (this.settings.multiple) {
        this.container.classList.add('tech-select-container--multiple');
      }

      // Selection container
      this.selection = createElement('div', 'tech-select-selection');

      // Rendered selection
      this.rendered = createElement('div', 'tech-select-selection__rendered');
      this.selection.appendChild(this.rendered);

      // Add inline search input
      this.search = createElement('input', 'tech-select-search__field tech-select-search__field--inline', {
        type: 'text',
        autocomplete: 'off',
        placeholder: this.settings.multiple ? '' : ''
      });

      if (!this.settings.multiple) {
        this.search.classList.add('tech-select-search__field--hidden');
      }
      this.rendered.appendChild(this.search);

      // Clear button
      if (this.settings.allowClear) {
        this.clearBtn = createElement('span', 'tech-select-selection__clear', { html: '&times;' });
        this.selection.appendChild(this.clearBtn);
      }

      // Arrow (only for single select)
      if (!this.settings.multiple) {
        this.arrow = createElement('span', 'tech-select-selection__arrow');
        const arrowB = createElement('b', '', { role: 'presentation' });
        this.arrow.appendChild(arrowB);
        this.selection.appendChild(this.arrow);
      }

      this.container.appendChild(this.selection);

      // Dropdown container
      this.dropdown = createElement('div', 'tech-select-dropdown');
      if (this.settings.dropdownCssClass) {
        this.dropdown.classList.add(...this.settings.dropdownCssClass.split(' '));
      }

      if (this.settings.multiple) {
        this.dropdown.classList.add('tech-select-dropdown--below');
      }

      // Results container
      this.results = createElement('ul', 'tech-select-results');
      this.dropdown.appendChild(this.results);

      this.container.appendChild(this.dropdown);

      // Insert after original select
      this.select.parentNode.insertBefore(this.container, this.select.nextSibling);

      // Render results
      this.renderResults();
    }

    bindEvents() {
      // Selection click
      this.selection.addEventListener('click', (e) => {
        if (e.target.classList.contains('tech-select-selection__clear') ||
          e.target.classList.contains('tech-select-selection__choice__remove')) {
          return;
        }

        if (!this.isOpen) {
          this.open();
        }

        if (this.search) {
          this.search.focus();
        }
      });

      // Clear selection
      if (this.settings.allowClear && this.clearBtn) {
        this.clearBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          this.clear();
        });
      }

      // Search functionality
      if (this.search) {
        this.search.addEventListener('input', () => {
          const query = this.search.value;
          this.filterResults(query);

          if (!this.isOpen) {
            this.open();
          }
        });

        this.search.addEventListener('click', (e) => {
          e.stopPropagation();
        });

        this.search.addEventListener('focus', () => {
          if (!this.isOpen) {
            this.open();
          }

          if (!this.settings.multiple) {
            this.search.classList.remove('tech-select-search__field--hidden');
            const textEl = this.rendered.querySelector('.tech-select-selection__rendered__text');
            const placeholderEl = this.rendered.querySelector('.tech-select-selection__placeholder');
            if (textEl) hide(textEl);
            if (placeholderEl) hide(placeholderEl);
          }
        });

        this.search.addEventListener('blur', () => {
          setTimeout(() => {
            if (this.isOpen && !this.container.matches(':hover')) {
              this.close();

              if (!this.settings.multiple) {
                this.search.classList.add('tech-select-search__field--hidden');
                this.search.value = '';
                const textEl = this.rendered.querySelector('.tech-select-selection__rendered__text');
                const placeholderEl = this.rendered.querySelector('.tech-select-selection__placeholder');
                if (textEl) show(textEl);
                if (placeholderEl) show(placeholderEl);
                this.renderResults('');
              }
            }
          }, 200);
        });

        // Tags support
        if (this.settings.tags) {
          this.search.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && this.search.value.trim()) {
              e.preventDefault();
              this.addTag(this.search.value.trim());
            }
          });
        }
      }

      // Select option
      this.results.addEventListener('click', (e) => {
        const optionEl = e.target.closest('.tech-select-results__option');
        if (optionEl && !optionEl.classList.contains('tech-select-results__option--disabled')) {
          e.preventDefault();
          e.stopPropagation();
          const value = optionEl.dataset.value;
          this.selectOption(value);
        }
      });

      // Prevent dropdown from closing when clicking inside
      this.dropdown.addEventListener('mousedown', (e) => {
        e.preventDefault();
      });

      // Close on outside click
      this.documentClickHandler = (e) => {
        if (!this.container.contains(e.target)) {
          this.close();
        }
      };
      document.addEventListener('click', this.documentClickHandler);

      // Keyboard navigation
      this.container.addEventListener('keydown', (e) => {
        this.handleKeyboard(e);
      });

      // Handle choice removal for multiple select
      this.rendered.addEventListener('click', (e) => {
        if (e.target.classList.contains('tech-select-selection__choice__remove')) {
          e.stopPropagation();
          const choice = e.target.closest('.tech-select-selection__choice');
          if (choice) {
            const value = choice.dataset.value;
            this.selectOption(value);
          }
        }
      });
    }

    renderResults(filter = '') {
      this.results.innerHTML = '';

      const filteredOptions = filter
        ? this.filterOptions(filter)
        : this.options;

      if (filteredOptions.length === 0) {
        const noResults = createElement('li', 'tech-select-results__option tech-select-results__option--no-results', {
          text: 'No results found'
        });
        this.results.appendChild(noResults);
        return;
      }

      filteredOptions.forEach(option => {
        const isSelected = this.selectedValues.includes(option.id);
        const optionEl = createElement('li', 'tech-select-results__option', {
          text: option.text
        });
        optionEl.dataset.value = option.id;

        if (isSelected) optionEl.classList.add('tech-select-results__option--selected');
        if (option.disabled) optionEl.classList.add('tech-select-results__option--disabled');

        if (this.settings.templateResult && typeof this.settings.templateResult === 'function') {
          optionEl.innerHTML = this.settings.templateResult(option);
        }

        this.results.appendChild(optionEl);
      });
    }

    filterOptions(query) {
      const lowerQuery = query.toLowerCase();

      if (this.settings.matcher && typeof this.settings.matcher === 'function') {
        return this.options.filter(opt => this.settings.matcher(query, opt));
      }

      return this.options.filter(option =>
        option.text.toLowerCase().includes(lowerQuery)
      );
    }

    filterResults(query) {
      this.renderResults(query);
    }

    selectOption(value) {
      if (this.settings.multiple) {
        const index = this.selectedValues.indexOf(value);
        if (index > -1) {
          this.selectedValues.splice(index, 1);
        } else {
          if (this.settings.maximumSelectionLength > 0 &&
            this.selectedValues.length >= this.settings.maximumSelectionLength) {
            return;
          }
          this.selectedValues.push(value);
        }

        this.updateSelection();
        this.updateSelectElement();

        if (this.search) {
          this.search.value = '';
        }

        this.renderResults('');
        trigger(this.select, 'change');
        this.close();

        setTimeout(() => {
          if (this.search) {
            this.search.blur();
          }
        }, 100);
      } else {
        this.selectedValues = [value];

        this.updateSelection();
        this.updateSelectElement();
        this.renderResults('');
        trigger(this.select, 'change');

        if (this.settings.closeOnSelect) {
          this.close();
        }

        setTimeout(() => {
          if (this.search) {
            this.search.blur();
          }
        }, 100);
      }
    }

    addTag(text) {
      const existingOption = this.options.find(opt => opt.text === text);

      if (existingOption) {
        this.selectOption(existingOption.id);
      } else {
        const newValue = text;
        const newOption = createElement('option', '', {
          value: newValue,
          text: text
        });

        this.select.appendChild(newOption);

        this.options.push({
          id: newValue,
          text: text,
          selected: true,
          disabled: false,
          element: newOption
        });

        this.selectedValues.push(newValue);
        this.updateSelection();
        this.updateSelectElement();
        this.renderResults();
      }

      if (this.search) {
        this.search.value = '';
      }
    }

    updateSelection() {
      if (this.settings.multiple) {
        // Remove existing choices
        const choices = this.rendered.querySelectorAll('.tech-select-selection__choice');
        choices.forEach(choice => choice.remove());

        const placeholder = this.rendered.querySelector('.tech-select-selection__placeholder');
        if (placeholder) placeholder.remove();

        if (this.selectedValues.length === 0 && (!this.search || !this.search.value)) {
          const placeholderEl = createElement('span', 'tech-select-selection__placeholder', {
            text: this.settings.placeholder
          });
          this.rendered.insertBefore(placeholderEl, this.rendered.firstChild);
        }

        this.selectedValues.forEach(value => {
          const option = this.options.find(opt => opt.id === value);
          if (option) {
            const choice = createElement('span', 'tech-select-selection__choice');
            choice.dataset.value = value;

            const remove = createElement('span', 'tech-select-selection__choice__remove', {
              html: '&times;'
            });

            const text = createElement('span', 'tech-select-selection__choice__text', {
              text: option.text
            });

            choice.appendChild(remove);
            choice.appendChild(text);

            if (this.search) {
              this.rendered.insertBefore(choice, this.search);
            } else {
              this.rendered.appendChild(choice);
            }
          }
        });
      } else {
        // Single select
        const textEl = this.rendered.querySelector('.tech-select-selection__rendered__text');
        const placeholderEl = this.rendered.querySelector('.tech-select-selection__placeholder');

        if (textEl) textEl.remove();
        if (placeholderEl) placeholderEl.remove();

        if (this.selectedValues.length === 0) {
          const placeholder = createElement('span', 'tech-select-selection__placeholder', {
            text: this.settings.placeholder
          });
          if (this.search) {
            this.rendered.insertBefore(placeholder, this.search);
          } else {
            this.rendered.appendChild(placeholder);
          }
        } else {
          const option = this.options.find(opt => opt.id === this.selectedValues[0]);
          if (option) {
            const single = createElement('span', 'tech-select-selection__rendered__text');

            if (this.settings.templateSelection && typeof this.settings.templateSelection === 'function') {
              single.innerHTML = this.settings.templateSelection(option);
            } else {
              single.textContent = option.text;
            }

            if (this.search) {
              this.rendered.insertBefore(single, this.search);
            } else {
              this.rendered.appendChild(single);
            }
          }
        }
      }

      // Toggle clear button visibility
      if (this.settings.allowClear && this.clearBtn) {
        this.clearBtn.style.display = this.selectedValues.length > 0 ? '' : 'none';
      }
    }

    updateSelectElement() {
      const options = this.select.querySelectorAll('option');
      options.forEach(option => {
        option.selected = this.selectedValues.includes(option.value);
      });
    }

    clear() {
      this.selectedValues = [];
      this.updateSelection();
      this.updateSelectElement();
      this.renderResults();
      trigger(this.select, 'change');
    }

    open() {
      if (this.isOpen) return;

      this.isOpen = true;
      this.container.classList.add('tech-select-container--open');
      show(this.dropdown);

      if (this.search) {
        this.search.focus();
      }

      trigger(this.select, 'tech-select:open');
    }

    close() {
      if (!this.isOpen) return;

      this.isOpen = false;
      this.container.classList.remove('tech-select-container--open');
      hide(this.dropdown);

      if (this.search) {
        if (this.settings.multiple) {
          this.search.value = '';
          this.renderResults();
        } else {
          this.search.classList.add('tech-select-search__field--hidden');
          this.search.value = '';
          const textEl = this.rendered.querySelector('.tech-select-selection__rendered__text');
          const placeholderEl = this.rendered.querySelector('.tech-select-selection__placeholder');
          if (textEl) show(textEl);
          if (placeholderEl) show(placeholderEl);
          this.renderResults('');
        }
      }

      trigger(this.select, 'tech-select:close');
    }

    toggle() {
      if (this.isOpen) {
        this.close();
      } else {
        this.open();
      }
    }

    handleKeyboard(e) {
      if (e.key === 'Escape') {
        this.close();
      }
    }

    destroy() {
      if (this.documentClickHandler) {
        document.removeEventListener('click', this.documentClickHandler);
      }
      if (this.container && this.container.parentNode) {
        this.container.remove();
      }
      show(this.select);

      // Clean up data attributes
      delete this.select._techSelectInstance;
    }
  }

  // Initialize function
  function panhaSelect(selector, options = {}) {
    const elements = typeof selector === 'string'
      ? document.querySelectorAll(selector)
      : selector instanceof NodeList
        ? selector
        : [selector];

    const instances = [];

    elements.forEach(element => {
      if (element.tagName === 'SELECT' && !element._techSelectInstance) {
        const instance = new PanhaSelect(element, options);
        instance.init();
        element._techSelectInstance = instance;
        instances.push(instance);
      }
    });

    return instances.length === 1 ? instances[0] : instances;
  }

  // API methods
  panhaSelect.destroy = function (selector) {
    const elements = typeof selector === 'string'
      ? document.querySelectorAll(selector)
      : selector instanceof NodeList
        ? selector
        : [selector];

    elements.forEach(element => {
      if (element._techSelectInstance) {
        element._techSelectInstance.destroy();
        delete element._techSelectInstance;
      }
    });
  };

  // Expose globally
  window.panhaSelect = panhaSelect;

})();

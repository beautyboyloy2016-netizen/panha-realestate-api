// Pure Vanilla JS Accordion Menu - No Dependencies
class AccordionMenu {
  constructor(selector) {
    this.menu = document.querySelector(selector);
    if (!this.menu) return;

    this.init();
  }

  init() {
    // Find all menu items with submenus
    const hasArrowItems = this.menu.querySelectorAll('.has-arrow');

    hasArrowItems.forEach(item => {
      item.addEventListener('click', (e) => {
        // Check if the actual clicked element or its parent is a submenu link
        const clickedElement = e.target;
        const isSubmenuLink = clickedElement.closest('.submenu-link');

        // If clicking inside a submenu link, do nothing - let it navigate
        if (isSubmenuLink) {
          console.log('Submenu link detected, allowing navigation to:', isSubmenuLink.href);
          return; // Exit early, don't preventDefault
        }

        // Only prevent default for the parent toggle link
        e.preventDefault();
        this.toggle(item);
      });
    });

    // Handle single links
    const singleLinks = this.menu.querySelectorAll('.single-link');
    singleLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        // Don't prevent default - allow navigation to href
        this.setActive(link.parentElement);
        // Let the link navigate naturally
      });
    });

    // Collapse all menus by default on initialization
    this.collapseAll();
  }

  toggle(element) {
    const parent = element.parentElement;
    const submenu = parent.querySelector('ul');

    if (!submenu) return;

    const isOpen = parent.classList.contains('mm-active');

    // Close all other open menus at the same level
    const siblings = Array.from(parent.parentElement.children);
    siblings.forEach(sibling => {
      if (sibling !== parent && sibling.classList.contains('mm-active')) {
        this.close(sibling);
      }
    });

    // Toggle current menu
    if (isOpen) {
      this.close(parent);
    } else {
      this.open(parent);
    }
  }

  open(element) {
    const submenu = element.querySelector('ul');
    if (!submenu) return;

    element.classList.add('mm-active');
    submenu.style.height = '0px';
    submenu.style.overflow = 'hidden';
    submenu.style.display = 'block';

    // Trigger reflow
    submenu.offsetHeight;

    // Animate height
    submenu.style.transition = 'height 0.3s ease';
    submenu.style.height = submenu.scrollHeight + 'px';

    setTimeout(() => {
      submenu.style.height = 'auto';
      submenu.style.overflow = 'visible';
    }, 300);
  }

  close(element) {
    const submenu = element.querySelector('ul');
    if (!submenu) return;

    element.classList.remove('mm-active');
    submenu.style.height = submenu.scrollHeight + 'px';
    submenu.style.overflow = 'hidden';

    // Trigger reflow
    submenu.offsetHeight;

    // Animate height
    submenu.style.transition = 'height 0.3s ease';
    submenu.style.height = '0px';

    setTimeout(() => {
      submenu.style.display = 'none';
      submenu.style.height = 'auto';
      submenu.style.overflow = 'visible';
    }, 300);
  }

  setActive(element) {
    // Remove active from all items
    this.menu.querySelectorAll('li').forEach(item => {
      item.classList.remove('active');
    });

    // Add active to clicked item
    element.classList.add('active');
  }

  // Public methods
  expandAll() {
    this.menu.querySelectorAll('.has-arrow').forEach(item => {
      this.open(item.parentElement);
    });
  }

  collapseAll() {
    this.menu.querySelectorAll('li').forEach(item => {
      const submenu = item.querySelector('ul');
      if (submenu) {
        item.classList.remove('mm-active');
        submenu.style.display = 'none';
      }
    });
  }

  update() {
    // Refresh menu state if needed
    console.log('Menu updated');
  }
}

// Initialize the accordion menu
document.addEventListener('DOMContentLoaded', function () {

  // Initialize Accordion Menu
  const menu = new AccordionMenu('#menu');

  // Sidebar Collapse/Expand
  const sidebar = document.getElementById('sidebar');
  const collapseBtn = document.getElementById('collapse-btn');

  if (collapseBtn && sidebar) {
    collapseBtn.addEventListener('click', function (e) {
      e.preventDefault();
      sidebar.classList.toggle('collapsed');
      localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
      setTimeout(() => menu?.update(), 300);
    });

    // Restore sidebar state
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
      sidebar.classList.add('collapsed');
    }
  }

  // Mobile Sidebar Toggle
  const mobileToggle = document.getElementById('mobile-toggle');
  const mobileOverlay = document.getElementById('mobile-overlay');

  if (mobileToggle && sidebar && mobileOverlay) {
    mobileToggle.addEventListener('click', function (e) {
      e.preventDefault();
      sidebar.classList.toggle('show');
      mobileOverlay.classList.toggle('show');
    });

    mobileOverlay.addEventListener('click', function () {
      sidebar.classList.remove('show');
      mobileOverlay.classList.remove('show');
    });
  }

  // Language Selector
  window.changeLang = function (lang) {
    const label = document.getElementById('current-lang');
    if (label) {
      label.textContent = lang;
      localStorage.setItem('selectedLanguage', lang);
      document.getElementById('lang-menu')?.classList.add('d-none');
    }
  };

  // Restore saved language
  const savedLang = localStorage.getItem('selectedLanguage');
  if (savedLang) {
    const label = document.getElementById('current-lang');
    if (label) label.textContent = savedLang;
  }

  // Custom Dropdown Manager
  class DropdownManager {
    constructor() {
      this.dropdowns = new Map();
      this.init();
    }

    init() {
      this.register('lang-btn', 'lang-menu');
      this.register('profile-btn', 'profile-menu');
      document.addEventListener('click', this.handleGlobalClick.bind(this));
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') this.closeAll();
      });
    }

    register(btnId, menuId) {
      const btn = document.getElementById(btnId);
      const menu = document.getElementById(menuId);

      if (!btn || !menu) return;

      this.dropdowns.set(btnId, { button: btn, menu: menu, id: menuId });

      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        this.toggle(menuId);
      });
    }

    toggle(menuId) {
      const dropdown = Array.from(this.dropdowns.values()).find(d => d.id === menuId);
      if (!dropdown) return;

      const isOpen = !dropdown.menu.classList.contains('d-none');
      this.closeAll();

      if (!isOpen) {
        dropdown.menu.classList.remove('d-none');
      }
    }

    closeAll() {
      this.dropdowns.forEach(dropdown => {
        dropdown.menu.classList.add('d-none');
      });
    }

    handleGlobalClick(e) {
      const clickedDropdown = Array.from(this.dropdowns.values())
        .find(d => d.button.contains(e.target) || d.menu.contains(e.target));

      if (!clickedDropdown) {
        this.closeAll();
      }
    }
  }

  new DropdownManager();

  // Fullscreen Toggle
  const fullscreenBtn = document.getElementById('fullscreen-btn');
  if (fullscreenBtn) {
    fullscreenBtn.addEventListener('click', function () {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
          console.log('Fullscreen error:', err);
        });
        this.innerHTML = '<i class="fa-solid fa-compress"></i>';
      } else {
        document.exitFullscreen();
        this.innerHTML = '<i class="fa-solid fa-expand"></i>';
      }
    });
  }

});

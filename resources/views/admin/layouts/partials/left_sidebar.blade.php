<nav class="py-3">
<ul class="accordionmenu list-unstyled" id="menu">
    {{-- dashboard menu --}}
    <li class="active">
      <a href="{{ route('admin.dashboard') }}" class="single-link">
          <i class="fas fa-th-large menu-icon"></i>
          <span class="sidebar-text">{{ __('admin.menu.dashboard') }}</span>
      </a>
    </li>

    {{-- user management menu --}}
    <li>
      <a href="#" class="has-arrow" aria-expanded="false">
          <i class="fas fa-user-friends menu-icon"></i>
          <span class="sidebar-text">{{ __('admin.menu.user_management') }}</span>
          <i class="fas fa-chevron-down chevron-icon"></i>
      </a>
      <ul class="submenu">
          <li><a href="{{ route('admin.users.index') }}" class="submenu-link"><span class="sidebar-text">{{ __('admin.menu.users') }}</span></a></li>
          <li><a href="{{ route('admin.roles.index') }}" class="submenu-link"><span class="sidebar-text">{{ __('admin.menu.roles') }}</span></a></li>
          <li><a href="{{ route('admin.permissions.index') }}" class="submenu-link"><span
              class="sidebar-text">{{ __('admin.menu.permissions') }}</span></a></li>
      </ul>
    </li>

    {{-- content management menu --}}
    <li>
      <a href="#" class="has-arrow" aria-expanded="false">
          <i class="fas fa-building menu-icon"></i>
          <span class="sidebar-text">{{ __('admin.menu.content_management') }}</span>
          <i class="fas fa-chevron-down chevron-icon"></i>
      </a>
      <ul class="submenu">
          <li><a href="{{ route('admin.properties.index') }}" class="submenu-link"><span
              class="sidebar-text">{{ __('admin.menu.properties') }}</span></a></li>
          <li><a href="{{ route('admin.projects.index') }}" class="submenu-link"><span
              class="sidebar-text">{{ __('admin.menu.projects') }}</span></a></li>
          <li><a href="{{ route('admin.inquiries.index') }}" class="submenu-link"><span
              class="sidebar-text">{{ __('admin.menu.inquiries') }}</span></a></li>
          <li><a href="{{ route('admin.news-articles.index') }}" class="submenu-link"><span
              class="sidebar-text">{{ __('admin.menu.news_articles') }}</span></a></li>
      </ul>
    </li>

    {{-- property sections menu --}}
    <li>
      <a href="#" class="has-arrow" aria-expanded="false">
          <i class="fas fa-layer-group menu-icon"></i>
          <span class="sidebar-text">Property Sections</span>
          <i class="fas fa-chevron-down chevron-icon"></i>
      </a>
      <ul class="submenu">
          <li><a href="{{ route('admin.properties.serviced-apartments') }}" class="submenu-link">
              <i class="fas fa-concierge-bell me-1"></i><span class="sidebar-text">Serviced Apartments</span></a></li>
          <li><a href="{{ route('admin.properties.boreys') }}" class="submenu-link">
              <i class="fas fa-home me-1"></i><span class="sidebar-text">Boreys</span></a></li>
          <li><a href="{{ route('admin.properties.luxury-villas') }}" class="submenu-link">
              <i class="fas fa-crown me-1"></i><span class="sidebar-text">Luxury Villas</span></a></li>
          <li><a href="{{ route('admin.properties.under-market-value') }}" class="submenu-link">
              <i class="fas fa-tags me-1"></i><span class="sidebar-text">Under Market Value</span></a></li>
          <li><a href="{{ route('admin.properties.locations') }}" class="submenu-link">
              <i class="fas fa-map-marked-alt me-1"></i><span class="sidebar-text">Locations</span></a></li>
      </ul>
    </li>

    {{-- media management menu --}}
    <li>
      <a href="{{ route('admin.media.index') }}" class="single-link">
          <i class="fas fa-file-alt menu-icon"></i>
          <span class="sidebar-text">{{ __('admin.media.title') }}</span>
      </a>
    </li>

    {{-- blog management menu --}}
    <li>
      <a href="#" class="has-arrow" aria-expanded="false">
          <i class="fas fa-blog menu-icon"></i>
          <span class="sidebar-text">Blog</span>
          <i class="fas fa-chevron-down chevron-icon"></i>
      </a>
      <ul class="submenu">
          <li><a href="{{ route('admin.posts.index') }}" class="submenu-link">
              <i class="fas fa-file-alt me-1"></i><span class="sidebar-text">All Posts</span></a></li>
          <li><a href="{{ route('admin.posts.create') }}" class="submenu-link">
              <i class="fas fa-plus me-1"></i><span class="sidebar-text">Add New</span></a></li>
          <li><a href="{{ route('admin.post-categories.index') }}" class="submenu-link">
              <i class="fas fa-folder me-1"></i><span class="sidebar-text">Categories</span></a></li>
          <li><a href="{{ route('admin.post-tags.index') }}" class="submenu-link">
              <i class="fas fa-tags me-1"></i><span class="sidebar-text">Tags</span></a></li>
      </ul>
    </li>

    {{-- payments menu --}}
    <li>
      <a href="#" class="has-arrow" aria-expanded="false">
          <i class="fas fa-credit-card menu-icon"></i>
          <span class="sidebar-text">Payments</span>
          <i class="fas fa-chevron-down chevron-icon"></i>
      </a>
      <ul class="submenu">
          <li><a href="{{ route('admin.transactions.index') }}" class="submenu-link">
              <i class="fas fa-exchange-alt me-1"></i><span class="sidebar-text">Transactions</span></a></li>
          <li><a href="{{ route('admin.payment-methods.index') }}" class="submenu-link">
              <i class="fas fa-wallet me-1"></i><span class="sidebar-text">Payment Methods</span></a></li>
          <li><a href="{{ route('admin.invoices.index') }}" class="submenu-link">
              <i class="fas fa-file-invoice-dollar me-1"></i><span class="sidebar-text">Invoices</span></a></li>
      </ul>
    </li>

    {{-- reports menu --}}
    <li>
      <a href="#" class="has-arrow" aria-expanded="false">
          <i class="fas fa-chart-bar menu-icon"></i>
          <span class="sidebar-text">Reports</span>
          <i class="fas fa-chevron-down chevron-icon"></i>
      </a>
      <ul class="submenu">
          <li><a href="{{ route('admin.reports.index') }}" class="submenu-link">
              <i class="fas fa-list me-1"></i><span class="sidebar-text">All Reports</span></a></li>
          <li><a href="{{ route('admin.reports.sales') }}" class="submenu-link">
              <i class="fas fa-chart-line me-1"></i><span class="sidebar-text">Sales Report</span></a></li>
          <li><a href="{{ route('admin.reports.analytics') }}" class="submenu-link">
              <i class="fas fa-chart-pie me-1"></i><span class="sidebar-text">Analytics</span></a></li>
      </ul>

    </li>

    {{-- language management menu --}}
    <li>
      <a href="#" class="has-arrow" aria-expanded="false">
          <i class="fas fa-chart-bar menu-icon"></i>
          <span class="sidebar-text">Language Mgt  </span>
          <i class="fas fa-chevron-down chevron-icon"></i>
      </a>
      <ul class="submenu">
          <li><a href="{{ route('admin.translations.index') }}" class="submenu-link"><span
              class="sidebar-text">{{ __('admin.menu.translations') }}</span></a></li>
          <li><a href="{{ route('admin.language-files.index') }}" class="submenu-link"><span
              class="sidebar-text">{{ __('admin.menu.language_files') }}</span></a></li>
      </ul>
    </li>

    {{-- settings menu --}}
    <li>
      <a href="{{ route('admin.settings.index') }}" class="single-link">
          <i class="fas fa-cog menu-icon"></i>
          <span class="sidebar-text">{{ __('admin.menu.settings') }}</span>
      </a>
    </li>
</ul>
</nav>

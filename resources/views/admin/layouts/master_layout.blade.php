@include('admin.layouts.partials.head')

  <body>

    <div class="main-wrapper">

      <!-- Sidebar -->
      <aside id="sidebar" class="sidebar">

        <!-- Toggle Button -->
        <div class="toggle-btn-wrapper">
          <button id="collapse-btn" class="sidebar-toggle-btn">
            <i class="fa-solid fa-chevron-left"></i>
          </button>
        </div>

        <!-- Logo -->
        <div
          class="p-3 border-bottom border-secondary logo-container d-flex align-items-center">
          <div class="d-flex align-items-center">
            <div
              class="bg-primary rounded d-flex align-items-center justify-content-center flex-shrink-0"
              style="width: 32px; height: 32px;">
              <i class="fas fa-layer-group text-white"></i>
            </div>
            <span
              class="ms-3 text-white h5 mb-0 logo-text fw-semibold">PanhaTech</span>
          </div>
        </div>

        <!-- Navigation Menu -->
        <div class="flex-fill overflow-auto">
            @include('admin.layouts.partials.left_sidebar')
        </div>

      </aside>

      <!-- Main Content Area -->
      <div class="content-wrapper">

        <!-- Top Header -->
        @include('admin.layouts.partials.top_header')

        <!-- Main Content -->
        <main class="main-content">
          <div class="container-fluid p-4">

            <!-- Page Title -->
            <h1 class="h3 mb-4 fw-bold text-dark">Dashboard</h1>

            <!-- Stats Main Content -->
            @yield('content')

          </div>
        </main>

      </div>

    </div>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobile-overlay"></div>

    <!-- Bootstrap 5 JS Bundle -->
    @include('admin.layouts.partials.scripts')
  </body>
</html>

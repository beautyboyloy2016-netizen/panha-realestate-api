<header class="top-header position-relative">
    <div class="container-fluid h-100">
    <div class="row h-100 align-items-center">

        <!-- Left Section -->
        <div class="col-6 col-md-4">
        <div class="d-flex align-items-center"
            style="margin-left: 20px;">
            <button id="mobile-toggle"
            class="btn btn-link text-secondary d-md-none p-0 me-3">
            <i class="fa-solid fa-bars fa-lg"></i>
            </button>
            <a href="#"
            class="btn btn-outline-secondary btn-sm d-none d-sm-flex align-items-center">
            <i class="fa-solid fa-store me-2"></i> Storefront
            </a>
        </div>
        </div>

        <!-- Right Section -->
        <div class="col-6 col-md-8">
        <div class="d-flex align-items-center justify-content-end">

            <!-- Fullscreen Toggle -->
            <button
            class="btn btn-link text-secondary p-2 d-none d-sm-inline-block"
            id="fullscreen-btn">
            <i class="fa-solid fa-expand"></i>
            </button>

            <!-- Language Switcher Component -->
            <x-language-switcher theme="admin" />

            <!-- Profile Dropdown -->
            <div class="position-relative ms-3"
            id="profile-dropdown-container">
            <button id="profile-btn"
                class="btn btn-link p-0 border-0 d-flex align-items-center">
                <img class="rounded-circle" width="36" height="36"
                src="https://ui-avatars.com/api/?name=Panha+Tech&color=fff&background=007bff"
                alt="User avatar">
            </button>
            <div id="profile-menu"
                class="d-none position-absolute bg-white border rounded shadow-lg py-0"
                style="min-width: 15rem; right: 0; z-index: 1050; top: 100%; margin-top: 0.5rem; overflow: hidden;">
                <div
                class="px-3 py-2 border-bottom bg-light d-flex align-items-center">
                <img class="rounded-circle me-2" width="40" height="40"
                    src="https://ui-avatars.com/api/?name=Panha+Tech&color=fff&background=007bff"
                    alt="User avatar">
                <div>
                    <p class="mb-0 fw-bold small">Panha Tech</p>
                    <p
                    class="mb-0 text-muted small">admin@panhatech.com</p>
                </div>
                </div>
                <div class="py-1">
                <a href="#"
                    class="d-flex align-items-center px-3 py-2 small text-dark text-decoration-none hover-bg-light">
                    <i class="fa-regular fa-user me-2 text-muted"
                    style="width: 1.5rem;"></i>
                    My Profile
                </a>
                <a href="#"
                    class="d-flex align-items-center px-3 py-2 small text-dark text-decoration-none hover-bg-light">
                    <i class="fa-solid fa-gear me-2 text-muted"
                    style="width: 1.5rem;"></i>
                    Settings
                </a>
                </div>
                <div class="border-top py-1">
                <form method="POST" action="{{ route('logout') }}">
                @csrf
                  <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"
                      class="d-flex align-items-center px-3 py-2 small text-danger text-decoration-none hover-bg-danger-light">
                      <i class="fa-solid fa-arrow-right-from-bracket me-2"
                      style="width: 1.5rem;"></i>
                      Logout
                  </a>
                </form>
                </div>
            </div>
            </div>

        </div>
        </div>

    </div>
    </div>
</header>

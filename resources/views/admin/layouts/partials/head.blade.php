<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('pageTitle','PanhaTech - Bootstrap 5 Admin Dashboard')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
      href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,100..700;1,100..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
      rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- Select2 Bootstrap 4 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ assetUrl() }}assets/backend/css/main.css">

    <style>
      /* Bootstrap 5 specific overrides */
      body {
        overflow: hidden;
      }
      .sidebar {
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
          transition: width 0.3s ease-in-out;
          position: relative;
          z-index: 40;
          width: 250px;
          box-shadow: 2px 0 20px rgba(102, 126, 234, 0.3);
      }

      /* Beautiful sidebar menu styles */
      .sidebar .accordionmenu li > a {
          color: rgba(255, 255, 255, 0.85);
          border-left-color: transparent;
      }

      .sidebar .accordionmenu li > a:hover {
          background: rgba(255, 255, 255, 0.15);
          color: #ffffff;
          border-left-color: #ffffff;
          backdrop-filter: blur(10px);
      }

      .sidebar .accordionmenu li.active > a {
          background: rgba(255, 255, 255, 0.2);
          color: #ffffff;
          border-left-color: #ffffff;
          font-weight: 500;
      }

      .sidebar .accordionmenu ul {
          background: rgba(0, 0, 0, 0.15);
      }

      .sidebar .accordionmenu ul a {
          color: rgba(255, 255, 255, 0.75);
      }

      .sidebar .accordionmenu ul a:hover {
          background: rgba(255, 255, 255, 0.1);
          color: #ffffff;
      }

      .sidebar .logo-text {
          color: #ffffff;
          text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      .sidebar .chevron-icon {
          color: rgba(255, 255, 255, 0.7);
      }

      .sidebar-toggle-btn {
          width: 24px;
          height: 24px;
          background: linear-gradient(135deg, #764ba2 0%, #5f78e6 100%);
          border: 2px solid rgba(255, 255, 255, 0.3);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          color: rgba(255, 255, 255, 0.9);
          cursor: pointer;
          font-size: 10px;
          box-shadow: 0 2px 12px rgba(118, 75, 162, 0.4);
          transition: all 0.3s ease;
      }

      .sidebar-toggle-btn:hover {
          background: linear-gradient(135deg, #3a5bf0 0%, #b880f0 100%);
          border-color: rgba(255, 255, 255, 0.5);
          color: #ffffff;
          box-shadow: 0 4px 16px rgba(118, 75, 162, 0.6);
          transform: scale(1.1);
      }

      .main-wrapper {
        display: flex;
        height: 100vh;
        overflow: hidden;
      }

      .content-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
      }

      .main-content {
        flex: 1;
        overflow-y: auto;
        background: #f8f9fa;
      }

      /* Header styling */
      /* .top-header {
        height: 64px;
        background: #fff;
        border-bottom: 1px solid #e9ecef;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
      } */

      /* Badge styles for stat cards */
      .stat-badge {
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
      }

      /* Custom dropdown animations */
      @keyframes slideInUp {
        from {
          transform: translateY(20px);
          opacity: 0;
        }
        to {
          transform: translateY(0);
          opacity: 1;
        }
      }

      /* Animate custom dropdowns */
      #lang-menu:not(.d-none),
      #profile-menu:not(.d-none) {
        animation: slideInUp 0.5s ease-out;
      }

      /* Custom dropdown hover effects */
      .hover-bg-light:hover {
        background-color: #f8f9fa !important;
        color: #0d6efd !important;
      }

      .hover-bg-danger-light:hover {
        background-color: #fff5f5 !important;
      }

      /* Status badges */
      .badge-pending {
        background: #cfe2ff;
        color: #084298;
      }

      .badge-completed {
        background: #d1e7dd;
        color: #0a3622;
      }

      /* Responsive sidebar */
      @media (max-width: 768px) {
        .sidebar {
          position: fixed;
          left: 0;
          top: 0;
          height: 100vh;
          z-index: 1050;
          transform: translateX(-100%);
          transition: transform 0.3s ease;
        }

        .sidebar.show {
          transform: translateX(0);
        }

        .mobile-overlay {
          display: none;
          position: fixed;
          inset: 0;
          background: rgba(0,0,0,0.5);
          z-index: 1040;
        }

        .mobile-overlay.show {
          display: block;
        }
      }

      /* Table styling */
      .table thead th {
        border-bottom: 2px solid #dee2e6;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #6c757d;
      }

      .table tbody tr:hover {
        background: #f8f9fa;
      }

      /* Accordion menu submenu styling */
      .accordionmenu ul {
        background: #16192c;
        margin: 0;
        padding: 0;
      }

      .accordionmenu ul li {
        list-style: none;
        margin: 0;
      }

      .accordionmenu ul a {
        display: flex;
        align-items: center;
        padding: 12px 20px 12px 50px;
        font-size: 14px;
        color: #8391a2;
        border-left: 3px solid transparent;
        transition: all 0.3s ease;
        margin: 0;
      }

      .accordionmenu ul a:hover,
      .accordionmenu ul a.active {
        color: #ffffff;
        background: #262945;
        border-left: 3px solid #5b7cfd;
      }
    </style>
    @stack('styles')
  </head>

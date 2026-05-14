<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Backend\MediaController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PostController;

Route::get('/', function () {
    return view('welcome');
});

// Route::resource('posts', App\Http\Controllers\PostController::class);


Route::get('posts', [PostController::class, 'index'])->name('posts.index');
Route::post('posts', [PostController::class, 'store'])->name('posts.store');
Route::get('posts/{id}/edit', [PostController::class, 'edit'])->name('posts.edit');
Route::post('posts/{id}', [PostController::class, 'update'])->name('posts.update'); // Using POST for update to simplify AJAX
Route::delete('posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');


Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'role:admin,super_admin']], function () {
  Route::get('/dashboard', function () {
      return view('admin.dashboard');
  })->name('dashboard');
  // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [App\Http\Controllers\Backend\UserController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Backend\UserController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Backend\UserController::class, 'store'])->name('store');
        Route::get('/{user}', [App\Http\Controllers\Backend\UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [App\Http\Controllers\Backend\UserController::class, 'edit'])->name('edit');
        Route::patch('/{user}', [App\Http\Controllers\Backend\UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [App\Http\Controllers\Backend\UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/verify', [App\Http\Controllers\Backend\UserController::class, 'verify'])->name('verify');
        Route::post('/{user}/suspend', [App\Http\Controllers\Backend\UserController::class, 'suspend'])->name('suspend');
        Route::post('/bulk-destroy', [App\Http\Controllers\Backend\UserController::class, 'bulkDestroy'])->name('bulk-destroy');
    });
    // Roles
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [App\Http\Controllers\Backend\RoleController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Backend\RoleController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Backend\RoleController::class, 'store'])->name('store');
        Route::get('/{role}', [App\Http\Controllers\Backend\RoleController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [App\Http\Controllers\Backend\RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [App\Http\Controllers\Backend\RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [App\Http\Controllers\Backend\RoleController::class, 'destroy'])->name('destroy');
        Route::post('/{role}/toggle-status', [App\Http\Controllers\Backend\RoleController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{role}/assign-permissions', [App\Http\Controllers\Backend\RoleController::class, 'assignPermissions'])->name('assign-permissions');
    });
    // Permissions
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Backend\PermissionController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Backend\PermissionController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Backend\PermissionController::class, 'store'])->name('store');
        Route::get('/{permission}', [App\Http\Controllers\Backend\PermissionController::class, 'show'])->name('show');
        Route::get('/{permission}/edit', [App\Http\Controllers\Backend\PermissionController::class, 'edit'])->name('edit');
        Route::patch('/{permission}', [App\Http\Controllers\Backend\PermissionController::class, 'update'])->name('update');
        Route::delete('/{permission}', [App\Http\Controllers\Backend\PermissionController::class, 'destroy'])->name('destroy');
        Route::post('/{permission}/toggle-status', [App\Http\Controllers\Backend\PermissionController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/bulk-destroy', [App\Http\Controllers\Backend\PermissionController::class, 'bulkDestroy'])->name('bulk-destroy');
    });

    // Media Management Routes
    Route::prefix('media')->name('media.')->group(function () {
      Route::get('/', [MediaController::class, 'index'])->name('index');
      Route::get('/files', [MediaController::class, 'getFiles'])->name('files');
      Route::get('/folders', [MediaController::class, 'getFolders'])->name('folders');

      // Folder operations
      Route::post('/create-folder', [MediaController::class, 'createFolder'])->name('create-folder');
      Route::post('/rename-folder', [MediaController::class, 'renameFolder'])->name('rename-folder');
      Route::delete('/delete-folder', [MediaController::class, 'deleteFolder'])->name('delete-folder');
      Route::post('/move-folder', [MediaController::class, 'moveFolderToFolder'])->name('move-folder');

      // File operations
      Route::post('/upload', [MediaController::class, 'upload'])->name('upload');
      Route::post('/bulk-upload', [MediaController::class, 'bulkUpload'])->name('bulk-upload');
      Route::post('/rename-file', [MediaController::class, 'renameFile'])->name('rename-file');
      Route::delete('/{id}', [MediaController::class, 'destroy'])->name('destroy');
      Route::delete('/', [MediaController::class, 'destroy'])->name('bulk-destroy');

      // Move and copy operations
      Route::post('/move-to-folder', [MediaController::class, 'moveToFolder'])->name('move-to-folder');
      Route::post('/copy-to-folder', [MediaController::class, 'copyToFolder'])->name('copy-to-folder');

      // Bulk operations
      Route::post('/bulk-move-to-folder', [MediaController::class, 'bulkMoveToFolder'])->name('bulk-move-to-folder');
      Route::post('/bulk-copy-to-folder', [MediaController::class, 'bulkCopyToFolder'])->name('bulk-copy-to-folder');
    });

    // Settings
    Route::get('/settings', [\App\Http\Controllers\Backend\SettingController::class, 'index'])
      ->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Backend\SettingController::class, 'update'])
      ->name('settings.update');
    Route::post('/settings/clear-cache', [\App\Http\Controllers\Backend\SettingController::class, 'clearCache'])
      ->name('settings.clear-cache');
    Route::post('/settings/toggle', [\App\Http\Controllers\Backend\SettingController::class, 'toggle'])
      ->name('settings.toggle');
    Route::post('/settings/update-single', [\App\Http\Controllers\Backend\SettingController::class, 'updateSingle'])
      ->name('settings.update-single');

      // Properties
    Route::resource('properties', \App\Http\Controllers\Backend\PropertyController::class);

    // Property Section Views (for homepage sections management)
    Route::prefix('properties')->name('properties.')->group(function () {
        Route::get('/sections/serviced-apartments', [\App\Http\Controllers\Backend\PropertyController::class, 'servicedApartments'])->name('serviced-apartments');
        Route::get('/sections/boreys', [\App\Http\Controllers\Backend\PropertyController::class, 'boreys'])->name('boreys');
        Route::get('/sections/luxury-villas', [\App\Http\Controllers\Backend\PropertyController::class, 'luxuryVillas'])->name('luxury-villas');
        Route::get('/sections/under-market-value', [\App\Http\Controllers\Backend\PropertyController::class, 'underMarketValue'])->name('under-market-value');
        Route::get('/sections/locations', [\App\Http\Controllers\Backend\PropertyController::class, 'locations'])->name('locations');
        Route::get('/sections/dashboard-stats', [\App\Http\Controllers\Backend\PropertyController::class, 'dashboardStats'])->name('dashboard-stats');
    });

    // Projects
    Route::resource('projects', \App\Http\Controllers\Backend\ProjectController::class);

    // News Articles
    Route::resource('news-articles', \App\Http\Controllers\Backend\NewsArticleController::class);

    // Inquiries
    Route::prefix('inquiries')->name('inquiries.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Backend\InquiryController::class, 'index'])->name('index');
        Route::get('/{inquiry}', [\App\Http\Controllers\Backend\InquiryController::class, 'show'])->name('show');
        Route::patch('/{inquiry}', [\App\Http\Controllers\Backend\InquiryController::class, 'update'])->name('update');
        Route::delete('/{inquiry}', [\App\Http\Controllers\Backend\InquiryController::class, 'destroy'])->name('destroy');
        Route::post('/{inquiry}/reply', [\App\Http\Controllers\Backend\InquiryController::class, 'reply'])->name('reply');
        Route::get('/export/csv', [\App\Http\Controllers\Backend\InquiryController::class, 'export'])->name('export');
    });

    // Translations
    Route::prefix('translations')->name('translations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Backend\TranslationController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Backend\TranslationController::class, 'store'])->name('store');
        Route::put('/{translation}', [\App\Http\Controllers\Backend\TranslationController::class, 'update'])->name('update');
        Route::put('/{translation}/inline', [\App\Http\Controllers\Backend\TranslationController::class, 'updateInline'])->name('update-inline');
        Route::delete('/{translation}', [\App\Http\Controllers\Backend\TranslationController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-destroy', [\App\Http\Controllers\Backend\TranslationController::class, 'bulkDestroy'])->name('bulk-destroy');
    });

    // Language Files
    Route::prefix('language-files')->name('language-files.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Backend\LanguageFileController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Backend\LanguageFileController::class, 'store'])->name('store');
        Route::post('/update', [\App\Http\Controllers\Backend\LanguageFileController::class, 'update'])->name('update');
        Route::delete('/destroy', [\App\Http\Controllers\Backend\LanguageFileController::class, 'destroy'])->name('destroy');
        Route::post('/sync', [\App\Http\Controllers\Backend\LanguageFileController::class, 'sync'])->name('sync');
        Route::post('/auto-translate', [\App\Http\Controllers\Backend\LanguageFileController::class, 'autoTranslate'])->name('auto-translate');
        Route::post('/auto-translate-file', [\App\Http\Controllers\Backend\LanguageFileController::class, 'autoTranslateFile'])->name('auto-translate-file');
    });

    // Blog Posts
    Route::resource('posts', \App\Http\Controllers\Backend\PostController::class);

    // Post Categories
    Route::resource('post-categories', \App\Http\Controllers\Backend\PostCategoryController::class);

    // Post Tags
    Route::resource('post-tags', \App\Http\Controllers\Backend\PostTagController::class);

    // Payment Methods
    Route::resource('payment-methods', \App\Http\Controllers\Backend\PaymentMethodController::class);
    Route::post('payment-methods/{paymentMethod}/toggle-status', [\App\Http\Controllers\Backend\PaymentMethodController::class, 'toggleStatus'])->name('payment-methods.toggle-status');

    // Invoices
    Route::resource('invoices', \App\Http\Controllers\Backend\InvoiceController::class);
    Route::post('invoices/{invoice}/mark-paid', [\App\Http\Controllers\Backend\InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
    Route::post('invoices/{invoice}/send', [\App\Http\Controllers\Backend\InvoiceController::class, 'send'])->name('invoices.send');

    // Transactions
    Route::resource('transactions', \App\Http\Controllers\Backend\TransactionController::class);
    Route::post('transactions/{transaction}/approve', [\App\Http\Controllers\Backend\TransactionController::class, 'approve'])->name('transactions.approve');
    Route::post('transactions/{transaction}/reject', [\App\Http\Controllers\Backend\TransactionController::class, 'reject'])->name('transactions.reject');
    Route::post('transactions/{transaction}/refund', [\App\Http\Controllers\Backend\TransactionController::class, 'refund'])->name('transactions.refund');
    Route::get('transactions-stats', [\App\Http\Controllers\Backend\TransactionController::class, 'stats'])->name('transactions.stats');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Backend\ReportController::class, 'index'])->name('index');
        Route::get('/sales-report', [\App\Http\Controllers\Backend\ReportController::class, 'salesReport'])->name('sales');
        Route::get('/sales-report/data', [\App\Http\Controllers\Backend\ReportController::class, 'getSalesData'])->name('sales.data');
        Route::get('/sales-report/export', [\App\Http\Controllers\Backend\ReportController::class, 'exportSales'])->name('sales.export');
        Route::get('/analytics', [\App\Http\Controllers\Backend\ReportController::class, 'analytics'])->name('analytics');
        Route::get('/analytics/data', [\App\Http\Controllers\Backend\ReportController::class, 'getAnalyticsData'])->name('analytics.data');
        Route::post('/', [\App\Http\Controllers\Backend\ReportController::class, 'store'])->name('store');
        Route::get('/{report}', [\App\Http\Controllers\Backend\ReportController::class, 'show'])->name('show');
        Route::get('/{report}/edit', [\App\Http\Controllers\Backend\ReportController::class, 'edit'])->name('edit');
        Route::put('/{report}', [\App\Http\Controllers\Backend\ReportController::class, 'update'])->name('update');
        Route::delete('/{report}', [\App\Http\Controllers\Backend\ReportController::class, 'destroy'])->name('destroy');
        Route::post('/{report}/run', [\App\Http\Controllers\Backend\ReportController::class, 'run'])->name('run');
    });

});

require __DIR__.'/auth.php';

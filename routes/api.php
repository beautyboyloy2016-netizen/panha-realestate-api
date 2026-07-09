<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\NewsArticleController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\LookupController;
use Illuminate\Support\Facades\Route;

// Public auth routes (rate-limited to slow brute force / mass registration)
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Settings API (Public)
Route::get('/settings', [SettingController::class, 'index']);
Route::get('/settings/{group}', [SettingController::class, 'group']);
Route::get('/setting/{key}', [SettingController::class, 'show']);

// Lookup API (Public) - Property Types, Features, etc.
Route::prefix('lookups')->group(function () {
    Route::get('/', [LookupController::class, 'all']);
    Route::get('/property-types', [LookupController::class, 'propertyTypes']);
    Route::get('/features', [LookupController::class, 'features']);
    Route::get('/features/grouped', [LookupController::class, 'featuresGrouped']);
});

// Social Authentication routes for API clients
Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
    Route::get('/google', [SocialAuthController::class, 'getGoogleAuthUrl']);
    Route::get('/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);
    Route::post('/telegram', [SocialAuthController::class, 'handleTelegramAuth']);
});

// Public property routes
Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/featured', [PropertyController::class, 'featured']);
Route::get('/properties/for-sale', [PropertyController::class, 'forSale']);
Route::get('/properties/for-rent', [PropertyController::class, 'forRent']);
Route::get('/properties/serviced-apartments', [PropertyController::class, 'servicedApartments']);
Route::get('/properties/boreys', [PropertyController::class, 'boreys']);
Route::get('/properties/luxury-villas', [PropertyController::class, 'luxuryVillas']);
Route::get('/properties/under-market-value', [PropertyController::class, 'underMarketValue']);
Route::get('/properties/locations', [PropertyController::class, 'locations']);
Route::get('/properties/{property}', [PropertyController::class, 'show']);

// Public project routes
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/featured', [ProjectController::class, 'featured']);
Route::get('/projects/high-yield', [ProjectController::class, 'highYield']);
Route::get('/projects/{project}', [ProjectController::class, 'show']);

// Public news routes
Route::get('/news', [NewsArticleController::class, 'index']);
Route::get('/news/latest', [NewsArticleController::class, 'latest']);
Route::get('/news/categories', [NewsArticleController::class, 'categories']);
Route::get('/news/{article}', [NewsArticleController::class, 'show']);

// Public inquiry submission (rate-limited to slow spam)
Route::post('/inquiries', [InquiryController::class, 'store'])->middleware('throttle:10,1');

// Authenticated routes (any logged-in user)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Favorites (scoped to the current user inside the controller)
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy']);
});

// Admin-only content management
// Requires the 'admin' middleware alias — see README-SECURITY-FIXES.md
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Property management
    Route::post('/properties', [PropertyController::class, 'store']);
    Route::put('/properties/{property}', [PropertyController::class, 'update']);
    Route::delete('/properties/{property}', [PropertyController::class, 'destroy']);

    // Project management
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    // News management
    Route::post('/news', [NewsArticleController::class, 'store']);
    Route::put('/news/{article}', [NewsArticleController::class, 'update']);
    Route::delete('/news/{article}', [NewsArticleController::class, 'destroy']);

    // Inquiry management (leads contain customer names/emails/phones)
    Route::get('/inquiries', [InquiryController::class, 'index']);
    Route::get('/inquiries/{inquiry}', [InquiryController::class, 'show']);
    Route::put('/inquiries/{inquiry}', [InquiryController::class, 'update']);
    Route::delete('/inquiries/{inquiry}', [InquiryController::class, 'destroy']);
});

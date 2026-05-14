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

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

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
Route::prefix('auth')->group(function () {
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

// Public inquiry submission
Route::post('/inquiries', [InquiryController::class, 'store']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Property management (authenticated users)
    Route::post('/properties', [PropertyController::class, 'store']);
    Route::put('/properties/{property}', [PropertyController::class, 'update']);
    Route::delete('/properties/{property}', [PropertyController::class, 'destroy']);

    // Project management (authenticated users)
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    // News management (authenticated users)
    Route::post('/news', [NewsArticleController::class, 'store']);
    Route::put('/news/{article}', [NewsArticleController::class, 'update']);
    Route::delete('/news/{article}', [NewsArticleController::class, 'destroy']);

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy']);

    // Inquiry management (for property owners/admins)
    Route::get('/inquiries', [InquiryController::class, 'index']);
    Route::get('/inquiries/{inquiry}', [InquiryController::class, 'show']);
    Route::put('/inquiries/{inquiry}', [InquiryController::class, 'update']);
    Route::delete('/inquiries/{inquiry}', [InquiryController::class, 'destroy']);
});


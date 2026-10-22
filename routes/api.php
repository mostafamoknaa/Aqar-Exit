<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\DealController;
use App\Http\Controllers\Api\V1\EmailVerificationController;
use App\Http\Controllers\Api\V1\EvaluationController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\InterestController;
use App\Http\Controllers\Api\V1\InterestMessageController;
use App\Http\Controllers\Api\V1\ListingController;
use App\Http\Controllers\Api\V1\MyListingController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OtpController;
use App\Http\Controllers\Api\V1\PasswordResetController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SavedSearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API v1 (Flutter / React Native) — Sanctum bearer tokens
| OpenAPI: docs/openapi.yaml · Guide: STRUCTURE.md
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public
    Route::get('home', HomeController::class)->name('home');
    Route::get('branding', [CatalogController::class, 'branding'])->name('branding');
    Route::get('categories', [CatalogController::class, 'categories'])->name('categories.index');
    Route::get('locations', [CatalogController::class, 'locations'])->name('locations.index');
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('auth/forgot-password', [PasswordResetController::class, 'forgot'])->name('auth.forgot');
        Route::post('auth/reset-password', [PasswordResetController::class, 'reset'])->name('auth.reset');
        Route::post('auth/otp/send', [OtpController::class, 'send'])->name('auth.otp.send');
        Route::post('auth/otp/login', [OtpController::class, 'verifyLogin'])->name('auth.otp.login');
    });
    Route::get('auth/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('evaluations/calculate', [EvaluationController::class, 'calculate'])->middleware('throttle:20,1')->name('evaluations.calculate');
    Route::post('evaluations', [EvaluationController::class, 'store'])->middleware('throttle:20,1')->name('evaluations.store');
    Route::get('listings', [ListingController::class, 'index'])->name('listings.index');
    Route::get('listings/map', [ListingController::class, 'map'])->name('listings.map');
    Route::get('listings/{listing}', [ListingController::class, 'show'])->name('listings.show');
    Route::get('developers', [CatalogController::class, 'developers'])->name('developers.index');
    Route::get('projects', [CatalogController::class, 'projects'])->name('projects.index');
    Route::get('filters', [CatalogController::class, 'filters'])->name('filters');

    // Authenticated
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/otp/verify-phone', [OtpController::class, 'verifyPhone'])->middleware('throttle:10,1')->name('auth.otp.verify-phone');
        Route::post('auth/verify-email/resend', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.resend');
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::get('profile/buyer-preference', [ProfileController::class, 'showPreference'])->name('profile.preference.show');
        Route::put('profile/buyer-preference', [ProfileController::class, 'updatePreference'])->name('profile.preference.update');

        // Seller
        Route::apiResource('my-listings', MyListingController::class)->parameters(['my-listings' => 'listing']);
        Route::post('my-listings/{listing}/documents', [MyListingController::class, 'uploadDocument'])->middleware('throttle:20,1')->name('my-listings.documents.store');
        Route::get('my-listings/{listing}/documents/{document}/download', [MyListingController::class, 'downloadDocument'])->name('my-listings.documents.download');
        Route::post('my-listings/{listing}/documents/{document}/signed-url', [MyListingController::class, 'signedDocumentUrl'])->name('my-listings.documents.signed-url');
        Route::post('my-listings/{listing}/documents/{document}/replace', [MyListingController::class, 'replaceDocument'])->middleware('throttle:20,1')->name('my-listings.documents.replace');
        Route::post('my-listings/{listing}/submit', [MyListingController::class, 'submit'])->name('my-listings.submit');

        // Buyer
        Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('listings/{listing}/favorite', [FavoriteController::class, 'toggle'])->name('listings.favorite');
        Route::get('interests', [InterestController::class, 'index'])->name('interests.index');
        Route::post('listings/{listing}/interest', [InterestController::class, 'store'])->middleware('throttle:20,1')->name('listings.interest');
        Route::delete('interests/{interest}', [InterestController::class, 'destroy'])->name('interests.destroy');
        Route::get('interests/{interest}/messages', [InterestMessageController::class, 'index'])->name('interests.messages.index');
        Route::post('interests/{interest}/messages', [InterestMessageController::class, 'store'])->middleware('throttle:30,1')->name('interests.messages.store');
        Route::apiResource('saved-searches', SavedSearchController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');

        // Deal tracker (buyer + seller, read-only for clients)
        Route::get('deals', [DealController::class, 'index'])->name('deals.index');
        Route::get('deals/{deal}', [DealController::class, 'show'])->name('deals.show');

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('notifications/register-device', [NotificationController::class, 'registerDevice'])->name('notifications.device');
        Route::match(['GET', 'PUT'], 'notifications/preferences', [NotificationController::class, 'preferences'])->name('notifications.preferences');
    });
});

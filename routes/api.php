<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\DealController;
use App\Http\Controllers\Api\V1\EvaluationController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\InterestController;
use App\Http\Controllers\Api\V1\ListingController;
use App\Http\Controllers\Api\V1\MyListingController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API v1 (Flutter / React Native) — Sanctum bearer tokens
| Docs: STRUCTURE.md → "Mobile API"
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public
    Route::get('home', HomeController::class)->name('home');
    Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('evaluations/calculate', [EvaluationController::class, 'calculate'])->name('evaluations.calculate');
    Route::post('evaluations', [EvaluationController::class, 'store'])->name('evaluations.store');
    Route::get('listings', [ListingController::class, 'index'])->name('listings.index');
    Route::get('listings/{listing}', [ListingController::class, 'show'])->name('listings.show');
    Route::get('developers', [CatalogController::class, 'developers'])->name('developers.index');
    Route::get('projects', [CatalogController::class, 'projects'])->name('projects.index');
    Route::get('filters', [CatalogController::class, 'filters'])->name('filters');

    // Authenticated
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::get('profile/buyer-preference', [ProfileController::class, 'showPreference'])->name('profile.preference.show');
        Route::put('profile/buyer-preference', [ProfileController::class, 'updatePreference'])->name('profile.preference.update');

        // Seller
        Route::apiResource('my-listings', MyListingController::class)->parameters(['my-listings' => 'listing']);
        Route::post('my-listings/{listing}/documents', [MyListingController::class, 'uploadDocument'])->name('my-listings.documents.store');
        Route::post('my-listings/{listing}/submit', [MyListingController::class, 'submit'])->name('my-listings.submit');

        // Buyer
        Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('listings/{listing}/favorite', [FavoriteController::class, 'toggle'])->name('listings.favorite');
        Route::get('interests', [InterestController::class, 'index'])->name('interests.index');
        Route::post('listings/{listing}/interest', [InterestController::class, 'store'])->name('listings.interest');
        Route::delete('interests/{interest}', [InterestController::class, 'destroy'])->name('interests.destroy');
        Route::get('evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');

        // Deal tracker (buyer + seller)
        Route::get('deals', [DealController::class, 'index'])->name('deals.index');
        Route::get('deals/{deal}', [DealController::class, 'show'])->name('deals.show');
    });
});

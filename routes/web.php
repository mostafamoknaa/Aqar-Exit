<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DealController;
use App\Http\Controllers\Admin\DeveloperController;
use App\Http\Controllers\Admin\EvaluationController;
use App\Http\Controllers\Admin\InterestController;
use App\Http\Controllers\Admin\ListingController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\PropertyCategoryController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Web\DocumentController;
use App\Http\Controllers\Web\EvaluationController as WebEvaluationController;
use App\Http\Controllers\Web\HomeController as WebHomeController;
use App\Http\Controllers\Web\ListingController as WebListingController;
use Illuminate\Support\Facades\Route;

Route::get('/', WebHomeController::class)->name('web.home');
Route::get('/listings', [WebListingController::class, 'index'])->name('web.listings.index');
Route::get('/listings/{listing}', [WebListingController::class, 'show'])->name('web.listings.show');
Route::get('/evaluate', [WebEvaluationController::class, 'create'])->name('web.evaluate');
Route::post('/evaluate/calculate', [WebEvaluationController::class, 'calculate'])->name('web.evaluate.calculate');
Route::post('/evaluate', [WebEvaluationController::class, 'store'])->name('web.evaluate.store');
Route::get('documents/{document}/signed-download', [DocumentController::class, 'signedDownload'])
    ->name('documents.signed-download')->middleware('signed');
Route::get('locale/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'ar'], true)) {
        session(['locale' => $locale]);
        auth()->user()?->update(['locale' => $locale]);
    }

    return back();
})->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Admin Dashboard (Tailwind, Blade, web session auth, staff only)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.store');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('listings', [ListingController::class, 'index'])->name('listings.index');
        Route::get('listings/create', [ListingController::class, 'create'])->name('listings.create');
        Route::post('listings', [ListingController::class, 'store'])->name('listings.store');
        Route::get('listings/export', [ListingController::class, 'export'])->name('listings.export');
        Route::get('listings/map', [ListingController::class, 'map'])->name('listings.map');
        Route::get('listings/{listing}', [ListingController::class, 'show'])->name('listings.show');
        Route::get('listings/{listing}/edit', [ListingController::class, 'edit'])->name('listings.edit');
        Route::patch('listings/{listing}', [ListingController::class, 'update'])->name('listings.update');
        Route::patch('listings/{listing}/status', [ListingController::class, 'updateStatus'])->name('listings.status');
        Route::post('listings/{listing}/featured', [ListingController::class, 'toggleFeatured'])->name('listings.featured');
        Route::post('listings/{listing}/convert-interest', [ListingController::class, 'convertInterest'])->name('listings.convert');
        Route::delete('listings/{listing}', [ListingController::class, 'destroy'])->name('listings.destroy');
        Route::patch('documents/{document}/review', [ListingController::class, 'reviewDocument'])->name('listings.documents.review');
        Route::get('documents/{document}/download', [ListingController::class, 'downloadDocument'])->name('listings.documents.download');

        Route::get('deals', [DealController::class, 'index'])->name('deals.index');
        Route::get('deals/{deal}', [DealController::class, 'show'])->name('deals.show');
        Route::patch('deals/{deal}', [DealController::class, 'update'])->name('deals.update');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle');
        Route::patch('users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');

        Route::get('evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');
        Route::get('evaluations/{evaluation}', [EvaluationController::class, 'show'])->name('evaluations.show');
        Route::patch('evaluations/{evaluation}', [EvaluationController::class, 'update'])->name('evaluations.update');

        Route::get('interests', [InterestController::class, 'index'])->name('interests.index');
        Route::patch('interests/{interest}', [InterestController::class, 'update'])->name('interests.update');
        Route::post('interests/{interest}/reply', [InterestController::class, 'reply'])->name('interests.reply');

        Route::resource('developers', DeveloperController::class)->except('show');
        Route::resource('projects', ProjectController::class)->except('show');
        Route::resource('categories', PropertyCategoryController::class)->except('show')->parameters(['categories' => 'category']);
        Route::resource('locations', LocationController::class)->except('show');

        Route::patch('listings/{listing}/verify-financials', [ListingController::class, 'verifyFinancials'])->name('listings.verify-financials');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::put('permissions', [PermissionController::class, 'update'])->name('permissions.update');
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

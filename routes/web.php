<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GoogleAdsOfflineConversionController;
use App\Http\Controllers\GoogleReportingOAuthController;
use App\Http\Controllers\GoogleReviewOAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\MoveInOutController;
use App\Http\Controllers\QuoteConfirmationController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SiteEventController;
use App\Http\Controllers\SitemapController;
use App\Livewire\BookingConfirmationForm;
use App\Livewire\EstimateWizard;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/google-reviews/connect', [GoogleReviewOAuthController::class, 'connect'])->name('google-reviews.connect');
    Route::get('/google-reviews/callback', [GoogleReviewOAuthController::class, 'callback'])->name('google-reviews.callback');
    Route::get('/google-reporting/connect', [GoogleReportingOAuthController::class, 'connect'])->name('google-reporting.connect');
    Route::get('/google-reporting/callback', [GoogleReportingOAuthController::class, 'callback'])->name('google-reporting.callback');
    Route::get('/crm/google-ads-booked-jobs.csv', GoogleAdsOfflineConversionController::class)->name('crm.google-ads-booked-jobs');
});

Route::post('/analytics/events', SiteEventController::class)
    ->middleware('throttle:60,1')
    ->name('analytics.events');

Route::get('/', HomeController::class)->name('home');
Route::get('/services', [ServiceController::class, 'index'])->name('services');
Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');
Route::get('/areas', [AreaController::class, 'index'])->name('areas');
Route::get('/areas/{area:slug}/{service:slug}', [AreaController::class, 'service'])
    ->withoutScopedBindings()
    ->name('areas.service');
Route::get('/areas/{area:slug}', [AreaController::class, 'show'])->name('areas.show');
Route::get('/reviews', ReviewsController::class)->name('reviews');
Route::get('/move-in-move-out', MoveInOutController::class)->name('move-in-out');
Route::get('/about', AboutController::class)->name('about');
Route::get('/contact', ContactController::class)->name('contact');
Route::livewire('/get-a-quote', EstimateWizard::class)->name('quote');
Route::livewire('/get-a-quote/detailed', EstimateWizard::class)->name('quote.detailed');
Route::get('/get-a-quote/confirmation/{reference}', QuoteConfirmationController::class)->name('quote.confirmation');
Route::livewire('/confirm', BookingConfirmationForm::class)->name('booking.confirm');
Route::get('/privacy', fn (LegalPageController $controller) => $controller('privacy'))->name('legal.privacy');
Route::get('/terms', fn (LegalPageController $controller) => $controller('terms'))->name('legal.terms');
Route::get('/cookies', fn (LegalPageController $controller) => $controller('cookies'))->name('legal.cookies');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

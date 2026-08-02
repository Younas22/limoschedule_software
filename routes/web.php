<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingRequestController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('pages.home');

Route::get('sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('robots.txt', [SitemapController::class, 'robots'])->name('robots');

// Single front-door login for both customers and admins — see
// App\Http\Controllers\Auth\LoginController for why one form covers both.
Route::get('login', [LoginController::class, 'create'])->name('login');
Route::post('login', [LoginController::class, 'store']);

Route::post('locale/{code}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::post('currency/{code}', [CurrencyController::class, 'switch'])->name('currency.switch');

Route::post('contact', [ContactController::class, 'store'])->name('contact.store');

Route::prefix('booking')->name('booking.')->group(function () {
    Route::post('/', [BookingRequestController::class, 'store'])->name('store');
    Route::post('quote', [BookingRequestController::class, 'quote'])->name('quote')->middleware('throttle:30,1');
    Route::get('confirmation/{bookingNumber}', [BookingRequestController::class, 'confirmation'])->name('confirmation');
    Route::get('invoice/{bookingNumber}', [BookingRequestController::class, 'invoice'])->name('invoice');
    Route::get('invoice/{bookingNumber}/download', [BookingRequestController::class, 'downloadInvoice'])->name('invoice.download');
});

Route::get('areas/{area:slug}', [AreaController::class, 'show'])->name('areas.show');

Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('category/{category:slug}', [BlogController::class, 'category'])->name('category');
    Route::get('tag/{tag:slug}', [BlogController::class, 'tag'])->name('tag');
});

Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', implode('|', array_diff(array_keys(\App\Models\Page::PAGES), ['home'])))
    ->name('pages.show');

// Blog post detail lives at the site root (no "/blog/" prefix) — registered
// after pages.show so known static-page slugs are matched there first, and
// any other single-segment slug falls through here to be looked up as a
// blog post (implicit model binding 404s naturally if it doesn't exist).
Route::get('/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

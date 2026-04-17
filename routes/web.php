<?php

use App\Http\Controllers\AdminBlogController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminFaqController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashBasketController;
use App\Http\Controllers\CashOrderController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ForcePasswordResetController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\RecentlyViewedController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/img/{encoded}', [ImageProxyController::class, 'show'])
    ->name('img.proxy')
    ->where('encoded', '[A-Za-z0-9_-]+');

Route::get('/', [HomeController::class, 'index'])->name('home');

// Canonical slug URL — /game/elden-ring
Route::get('/game/{slug}', [GameController::class, 'showBySlug'])
    ->name('game.show')
    ->where('slug', '[a-z][a-z0-9\-]*');

// Legacy numeric ID — 301 redirects to slug URL
Route::get('/game/{id}', [GameController::class, 'show'])
    ->where('id', '[0-9]+');

Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/platform/{id}/{name}', [PlatformController::class, 'show'])
    ->name('platform.show')
    ->where('id', '[0-9]+');

Route::get('/genre/{id}/{name}', [GenreController::class, 'show'])
    ->name('genre.show')
    ->where('id', '[0-9]+');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    // Password reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:password-reset');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update')->middleware('throttle:password-reset');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated user pages
Route::middleware(['auth', 'track.active', 'force.reset'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/security', [SecurityController::class, 'show'])->name('security');
    Route::put('/security/password', [SecurityController::class, 'updatePassword'])->name('security.password');

    // Recently viewed
    Route::get('/recently-viewed', [RecentlyViewedController::class, 'index'])->name('recently-viewed');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{igdbGameId}', [WishlistController::class, 'destroy'])->name('wishlist.destroy')->where('igdbGameId', '[0-9]+');

    // Cash basket
    Route::get('/cash-basket', [CashBasketController::class, 'index'])->name('cash-basket.index');
    Route::post('/cash-basket', [CashBasketController::class, 'store'])->name('cash-basket.store');
    Route::patch('/cash-basket/{id}/condition', [CashBasketController::class, 'updateCondition'])->name('cash-basket.condition')->where('id', '[0-9]+');
    Route::delete('/cash-basket/{id}', [CashBasketController::class, 'destroy'])->name('cash-basket.destroy')->where('id', '[0-9]+');

    // Cash orders (submitted quotes)
    Route::get('/cash-orders', [CashOrderController::class, 'index'])->name('cash-orders.index');
    Route::get('/cash-orders/create', [CashOrderController::class, 'create'])->name('cash-orders.create');
    Route::post('/cash-orders', [CashOrderController::class, 'store'])->name('cash-orders.store');
    Route::get('/cash-orders/{ref}/confirmation', [CashOrderController::class, 'confirmation'])->name('cash-orders.confirmation');
    Route::get('/cash-orders/{ref}', [CashOrderController::class, 'show'])->name('cash-orders.show');
    Route::post('/cash-orders/{ref}/cancel', [CashOrderController::class, 'cancel'])->name('cash-orders.cancel');
});

// Forced password reset (auth required, but bypass force.reset check)
Route::middleware(['auth', 'track.active'])->group(function () {
    Route::get('/password/reset-required', [ForcePasswordResetController::class, 'show'])->name('password.force-reset');
    Route::put('/password/reset-required', [ForcePasswordResetController::class, 'update'])->name('password.force-reset.update');
});

// Admin
Route::middleware(['auth', 'track.active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // User management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}', [AdminController::class, 'userDetail'])->name('users.detail')->where('id', '[0-9]+');
    Route::post('/users/{id}/force-reset', [AdminController::class, 'forceReset'])->name('users.force-reset')->where('id', '[0-9]+');
    Route::post('/users/force-reset-all', [AdminController::class, 'forceResetAll'])->name('users.force-reset-all');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete')->where('id', '[0-9]+');

    // Blacklisted passwords
    Route::get('/blacklist', [AdminController::class, 'showBlacklist'])->name('blacklist');
    Route::post('/blacklist', [AdminController::class, 'addToBlacklist'])->name('blacklist.add');
    Route::delete('/blacklist/{id}', [AdminController::class, 'removeFromBlacklist'])->name('blacklist.remove')->where('id', '[0-9]+');

    // Contact submissions
    Route::get('/contact-submissions', [AdminController::class, 'contactSubmissions'])->name('contact-submissions');
    Route::get('/contact-submissions/{id}', [AdminController::class, 'viewSubmission'])->name('contact-submissions.view')->where('id', '[0-9]+');
    Route::delete('/contact-submissions/{id}', [AdminController::class, 'deleteSubmission'])->name('contact-submissions.delete')->where('id', '[0-9]+');

    // Activity logs
    Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('activity-logs');
    Route::delete('/activity-logs/clear', [AdminController::class, 'clearActivityLogs'])->name('activity-logs.clear');
    Route::delete('/activity-logs/{id}', [AdminController::class, 'deleteActivityLog'])->name('activity-logs.delete')->where('id', '[0-9]+');

    // Settings
    Route::get('/settings', [AdminController::class, 'showSettings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

    // Franchise adjustments
    Route::post('/settings/franchise-adjustments', [AdminController::class, 'storeFranchiseAdjustment'])->name('franchise-adjustments.store');
    Route::patch('/settings/franchise-adjustments/{id}', [AdminController::class, 'updateFranchiseAdjustment'])->name('franchise-adjustments.update')->where('id', '[0-9]+');
    Route::delete('/settings/franchise-adjustments/{id}', [AdminController::class, 'destroyFranchiseAdjustment'])->name('franchise-adjustments.destroy')->where('id', '[0-9]+');

    // Email templates
    Route::get('/email-templates', [AdminController::class, 'showEmailTemplates'])->name('email-templates');
    Route::post('/email-templates', [AdminController::class, 'updateEmailTemplates'])->name('email-templates.update');
    Route::post('/email-templates/test', [AdminController::class, 'testEmailTemplate'])->name('email-templates.test');

    // Cash orders management
    Route::get('/orders', [AdminController::class, 'cashOrders'])->name('orders');
    Route::get('/orders/{id}', [AdminController::class, 'cashOrderDetail'])->name('orders.detail')->where('id', '[0-9]+');
    Route::patch('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.update-status')->where('id', '[0-9]+');

    // FAQ management
    Route::get('/faqs', [AdminFaqController::class, 'index'])->name('faqs.index');
    Route::post('/faqs', [AdminFaqController::class, 'store'])->name('faqs.store');
    Route::get('/faqs/{id}/edit', [AdminFaqController::class, 'edit'])->name('faqs.edit')->where('id', '[0-9]+');
    Route::patch('/faqs/{id}', [AdminFaqController::class, 'update'])->name('faqs.update')->where('id', '[0-9]+');
    Route::delete('/faqs/{id}', [AdminFaqController::class, 'destroy'])->name('faqs.destroy')->where('id', '[0-9]+');

    // Blog management
    Route::get('/blog', [AdminBlogController::class, 'index'])->name('blog.index');
    Route::post('/blog/toggle-visibility', [AdminBlogController::class, 'toggleVisibility'])->name('blog.toggle-visibility');
    Route::get('/blog/create', [AdminBlogController::class, 'create'])->name('blog.create');
    Route::post('/blog', [AdminBlogController::class, 'store'])->name('blog.store');
    Route::get('/blog/{id}/edit', [AdminBlogController::class, 'edit'])->name('blog.edit')->where('id', '[0-9]+');
    Route::patch('/blog/{id}', [AdminBlogController::class, 'update'])->name('blog.update')->where('id', '[0-9]+');
    Route::delete('/blog/{id}', [AdminBlogController::class, 'destroy'])->name('blog.destroy')->where('id', '[0-9]+');
});

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Static pages
Route::view('/about', 'pages.about')->name('about');
Route::view('/terms', 'pages.terms')->name('terms');
Route::get('/contact', fn () => view('pages.contact'))->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/faq', fn () => view('pages.faq', ['faqs' => \App\Models\Faq::orderBy('sort_order')->orderBy('id')->get()]))->name('faq');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/sitemap', 'pages.sitemap')->name('sitemap');
Route::get('/sitemap.xml', [SitemapController::class, 'xml'])->name('sitemap.xml');
Route::get('/robots.txt', function () {
    $sitemap = route('sitemap.xml');
    $content = "User-agent: *\n\n# Private / authenticated pages\nDisallow: /admin\nDisallow: /login\nDisallow: /register\nDisallow: /profile\nDisallow: /security\nDisallow: /cash-basket\nDisallow: /cash-orders\nDisallow: /wishlist\n\n# Internal image proxy\nDisallow: /img/\n\n# Laravel health check\nDisallow: /up\n\nSitemap: {$sitemap}\n";
    return response($content, 200)->header('Content-Type', 'text/plain');
})->name('robots');

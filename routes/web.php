<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Frontend)
|--------------------------------------------------------------------------
| Public website routes are added here as frontend modules are built.
| Admin panel routes live in routes/admin.php (prefix /cms).
*/

Route::get('/', [\App\Http\Controllers\Frontend\PageController::class, 'home'])->name('front.home');

// The home page lives at "/". Old /home links (and their ?utm_… or ?preview=1)
// land there; Route::permanentRedirect() would drop the query string.
Route::get('/home', fn (\Illuminate\Http\Request $request) => redirect(rtrim('/?'.$request->getQueryString(), '?'), 301));

// SEO endpoints — dots keep them clear of the page catch-all.
Route::get('/sitemap.xml', [\App\Http\Controllers\Frontend\SeoController::class, 'sitemap'])->name('front.sitemap');
Route::get('/robots.txt', [\App\Http\Controllers\Frontend\SeoController::class, 'robots'])->name('front.robots');

Route::get('/blogs', [\App\Http\Controllers\Frontend\BlogController::class, 'index'])->name('front.blog.index');
Route::get('/blogs/{slug}', [\App\Http\Controllers\Frontend\BlogController::class, 'show'])->name('front.blog.show');

// The singular form is the one people type and the one other sites link to.
// Registered before the /{slug} catch-all so it redirects instead of 404ing.
Route::permanentRedirect('/blog', '/blogs');
Route::permanentRedirect('/blog/{slug}', '/blogs/{slug}');

Route::post('/forms/{slug}', [\App\Http\Controllers\Frontend\FormSubmissionController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('front.form.submit');

Route::post('/newsletter', [\App\Http\Controllers\Frontend\NewsletterController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('front.newsletter');

// Live search suggestions (pages + posts).
Route::get('/search/suggest', [\App\Http\Controllers\Frontend\SearchController::class, 'suggest'])->middleware('throttle:60,1')->name('front.search.suggest');

// WhatsApp Cloud API webhook (Meta) — verify token + credentials in Settings.
Route::get('/whatsapp/webhook', [\App\Http\Controllers\Frontend\WhatsAppWebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
Route::post('/whatsapp/webhook', [\App\Http\Controllers\Frontend\WhatsAppWebhookController::class, 'receive'])->name('whatsapp.webhook.receive');

// CMS staff login — the only way in. No public registration.
Route::get('/cms/login', [\App\Http\Controllers\Auth\CmsLoginController::class, 'showLoginForm'])->name('cms.login');
Route::post('/cms/login', [\App\Http\Controllers\Auth\CmsLoginController::class, 'login'])->middleware('throttle:10,1');
Route::post('/cms/logout', [\App\Http\Controllers\Auth\CmsLoginController::class, 'logout'])->name('cms.logout');

// Password reset (AdminLTE-styled views), for staff accounts.
Route::get('password/reset', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:6,1')->name('password.email');
Route::get('password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');
Route::get('password/confirm', [\App\Http\Controllers\Auth\ConfirmPasswordController::class, 'showConfirmForm'])->middleware('auth')->name('password.confirm');
Route::post('password/confirm', [\App\Http\Controllers\Auth\ConfirmPasswordController::class, 'confirm'])->middleware('auth');

// Builder pages by slug — keep LAST. Reserved prefixes excluded so module
// routes (blogs, cms, …) always win.
Route::get('/{slug}', [\App\Http\Controllers\Frontend\PageController::class, 'show'])
    ->where('slug', '^(?!cms|admin|blogs|forms|storage|newsletter|search|whatsapp|register|login|logout|password|home|build|vendor|js|css)[a-z0-9-]+$')
    ->name('front.page');

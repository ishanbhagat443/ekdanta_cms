<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\FooterBuilderController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\HeaderBuilderController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MediaFolderController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Loaded by RouteServiceProvider with prefix "admin", name prefix "admin."
| and middleware ["web", "auth"]. Module resource routes are appended here
| as each module is built.
*/

// /cms/dashboard is the canonical CMS home; bare /cms redirects to it.
Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/', fn () => redirect()->route('admin.dashboard'));

// Profile (any authenticated admin user)
Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

// User management
Route::resource('users', UserController::class)->except(['show']);

// Roles & permissions
Route::resource('roles', RoleController::class)->except(['show']);

// Form Builder
Route::resource('forms', FormController::class)->except(['show', 'create']);
Route::get('forms/{form}/submissions', [FormController::class, 'submissions'])->name('forms.submissions');
Route::get('forms/{form}/export', [FormController::class, 'export'])->name('forms.export');
Route::post('forms/{form}/submissions/{submission}/read', [FormController::class, 'markRead'])->name('forms.submissions.read');
Route::delete('forms/{form}/submissions/{submission}', [FormController::class, 'destroySubmission'])->name('forms.submissions.destroy');

// SEO: 301/302 redirects
Route::resource('redirects', \App\Http\Controllers\Admin\RedirectController::class)
    ->only(['index', 'edit', 'store', 'update', 'destroy']);
Route::post('redirects/{redirect}/toggle', [\App\Http\Controllers\Admin\RedirectController::class, 'toggle'])
    ->name('redirects.toggle');

// 404 Manager (feeds the Redirect Manager above)
Route::get('not-found', [\App\Http\Controllers\Admin\NotFoundLogController::class, 'index'])->name('not-found.index');
Route::post('not-found/{notFoundLog}/dismiss', [\App\Http\Controllers\Admin\NotFoundLogController::class, 'dismiss'])->name('not-found.dismiss');
Route::delete('not-found/{notFoundLog}', [\App\Http\Controllers\Admin\NotFoundLogController::class, 'destroy'])->name('not-found.destroy');

// Activity & Login Logs
Route::get('activity', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity.index');

// Enquiries (central inbox — export before resource so "export" isn't captured as {enquiry})
Route::get('enquiries/export', [EnquiryController::class, 'export'])->name('enquiries.export');
Route::resource('enquiries', EnquiryController::class)->only(['index', 'show', 'update', 'destroy']);

// WhatsApp settings "Send Test" button (Cloud API or Custom API).
Route::post('whatsapp/test', function () {
    [$ok, $message] = app(\App\Services\WhatsAppService::class)->sendTest();

    return back()->with($ok ? 'success' : 'error', $message);
})->middleware('can:settings.manage')->name('whatsapp.test');

// Newsletter subscribers
Route::get('subscribers/export', [\App\Http\Controllers\Admin\SubscriberController::class, 'export'])->name('subscribers.export');
Route::get('subscribers', [\App\Http\Controllers\Admin\SubscriberController::class, 'index'])->name('subscribers.index');
Route::delete('subscribers/{subscriber}', [\App\Http\Controllers\Admin\SubscriberController::class, 'destroy'])->name('subscribers.destroy');

// AI writing assistant (optional; the routes 404 when no API key is set).
Route::post('ai/improve', [\App\Http\Controllers\Admin\AiAssistController::class, 'improve'])->name('ai.improve');
Route::post('ai/draft-section', [\App\Http\Controllers\Admin\AiAssistController::class, 'draftSection'])->name('ai.draft-section');

// Brand — the one screen that summarises how the site looks.
Route::get('brand', [\App\Http\Controllers\Admin\BrandController::class, 'index'])->name('brand.index');
Route::put('brand/tokens', [\App\Http\Controllers\Admin\BrandController::class, 'updateTokens'])->name('brand.tokens');

// Theme Customizer — single-page tabbed screen, AJAX-saved per group
Route::prefix('theme-customizer')->name('theme.')->group(function (): void {
    Route::get('/', [\App\Http\Controllers\Admin\ThemeCustomizerController::class, 'index'])->name('index');
    Route::put('typography', [\App\Http\Controllers\Admin\ThemeCustomizerController::class, 'updateTypography'])->name('typography.update');
    Route::put('{group}', [\App\Http\Controllers\Admin\ThemeCustomizerController::class, 'update'])->name('update');
    Route::get('export/download', [\App\Http\Controllers\Admin\ThemeCustomizerController::class, 'export'])->name('export');
    Route::post('import', [\App\Http\Controllers\Admin\ThemeCustomizerController::class, 'import'])->name('import');
    Route::post('reset', [\App\Http\Controllers\Admin\ThemeCustomizerController::class, 'reset'])->name('reset');
    Route::post('backup', [\App\Http\Controllers\Admin\ThemeCustomizerController::class, 'backup'])->name('backup');
    Route::post('backup/{preset}/restore', [\App\Http\Controllers\Admin\ThemeCustomizerController::class, 'restore'])->name('backup.restore');
    Route::delete('backup/{preset}', [\App\Http\Controllers\Admin\ThemeCustomizerController::class, 'destroyBackup'])->name('backup.destroy');
    Route::post('fonts', [\App\Http\Controllers\Admin\ThemeCustomizerController::class, 'storeFont'])->name('fonts.store');
    Route::delete('fonts/{font}', [\App\Http\Controllers\Admin\ThemeCustomizerController::class, 'destroyFont'])->name('fonts.destroy');
});

// Design System: Global Gradient Library + Button Builder
Route::resource('gradients', \App\Http\Controllers\Admin\GradientController::class)->except('show');

// Collections (case studies, services, clients…): permission checked in the controller.
Route::prefix('collections')->name('collections.')->group(function (): void {
    Route::get('{collection}', [\App\Http\Controllers\Admin\CollectionItemController::class, 'index'])->name('index');
    Route::get('{collection}/create', [\App\Http\Controllers\Admin\CollectionItemController::class, 'create'])->name('create');
    Route::post('{collection}', [\App\Http\Controllers\Admin\CollectionItemController::class, 'store'])->name('store');
    Route::post('{collection}/reorder', [\App\Http\Controllers\Admin\CollectionItemController::class, 'reorder'])->name('reorder');
    Route::get('{collection}/{item}/edit', [\App\Http\Controllers\Admin\CollectionItemController::class, 'edit'])->name('edit');
    Route::put('{collection}/{item}', [\App\Http\Controllers\Admin\CollectionItemController::class, 'update'])->name('update');
    Route::delete('{collection}/{item}', [\App\Http\Controllers\Admin\CollectionItemController::class, 'destroy'])->name('destroy');
});

// Motion Library (permission checked in the controller: motion.manage).
Route::get('motion-presets/export', [\App\Http\Controllers\Admin\MotionPresetController::class, 'export'])->name('motion-presets.export');
Route::post('motion-presets/import', [\App\Http\Controllers\Admin\MotionPresetController::class, 'import'])->name('motion-presets.import');
Route::post('motion-presets/{motion_preset}/duplicate', [\App\Http\Controllers\Admin\MotionPresetController::class, 'duplicate'])->name('motion-presets.duplicate');
Route::get('motion-presets/{motion_preset}/preview', [\App\Http\Controllers\Admin\MotionPresetController::class, 'preview'])->name('motion-presets.preview');
Route::resource('motion-presets', \App\Http\Controllers\Admin\MotionPresetController::class)->except('show');
Route::post('button-styles/{buttonStyle}/duplicate', [\App\Http\Controllers\Admin\ButtonStyleController::class, 'duplicate'])->name('button-styles.duplicate');
Route::resource('button-styles', \App\Http\Controllers\Admin\ButtonStyleController::class)->except('show');

// Blog
Route::resource('posts', PostController::class)->except('show');
Route::resource('blog-categories', BlogCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
Route::delete('blog-tags/{tag}', [BlogCategoryController::class, 'destroyTag'])->name('blog-tags.destroy');

// Help → Ekdanta guide (docs/ekdanta-admin-guide.md and the section reference).
Route::get('help/ekdanta', [\App\Http\Controllers\Admin\HelpController::class, 'ekdanta'])->name('help.ekdanta');

// Pages (WordPress-style page builder)
Route::resource('pages', PageController::class)->except(['show', 'create']);
// Export / import a page as JSON (another site, or a backup).
Route::get('pages/{page}/export', [PageController::class, 'export'])->name('pages.export');
Route::post('pages/import', [PageController::class, 'import'])->name('pages.import');

// Starter layouts and multi-page site kits.
Route::post('pages/kits/{kit}', [PageController::class, 'applyKit'])->name('pages.kits.apply');
Route::post('pages/{page}/starter', [PageController::class, 'applyStarter'])->name('pages.starter.apply');

// Version history: list, preview a snapshot, roll back to it.
Route::get('pages/{page}/revisions', [\App\Http\Controllers\Admin\PageRevisionController::class, 'index'])
    ->name('pages.revisions.index');
Route::get('pages/{page}/revisions/{revision}/preview', [\App\Http\Controllers\Admin\PageRevisionController::class, 'preview'])
    ->name('pages.revisions.preview');
Route::post('pages/{page}/revisions/{revision}/restore', [\App\Http\Controllers\Admin\PageRevisionController::class, 'restore'])
    ->name('pages.revisions.restore');

// Header Builder (top bar + header areas)
Route::get('header-builder', [HeaderBuilderController::class, 'edit'])->name('header.edit');
Route::put('header-builder', [HeaderBuilderController::class, 'update'])->name('header.update');

// Footer Builder
Route::get('footer-builder', [FooterBuilderController::class, 'edit'])->name('footer.edit');
Route::put('footer-builder', [FooterBuilderController::class, 'update'])->name('footer.update');

// Developer Mode (Super Admin only): per-section HTML/CSS/JS overrides
Route::prefix('developer/sections/{section}')->name('developer.')->group(function (): void {
    Route::get('/', [\App\Http\Controllers\Admin\DeveloperModeController::class, 'show'])->name('show');
    Route::put('/', [\App\Http\Controllers\Admin\DeveloperModeController::class, 'update'])->name('update');
    Route::post('reset', [\App\Http\Controllers\Admin\DeveloperModeController::class, 'reset'])->name('reset');
    Route::post('preview', [\App\Http\Controllers\Admin\DeveloperModeController::class, 'preview'])->name('preview');
    Route::post('versions/{version}/restore', [\App\Http\Controllers\Admin\DeveloperModeController::class, 'restoreVersion'])->name('versions.restore');
});

// Builder sections (AJAX, shared by header/topbar/footer areas)
Route::post('sections', [SectionController::class, 'store'])->name('sections.store');
Route::post('sections/from-block', [SectionController::class, 'storeFromBlock'])->name('sections.from-block');
Route::post('sections/reorder', [SectionController::class, 'reorder'])->name('sections.reorder');
Route::put('sections/{section}', [SectionController::class, 'update'])->name('sections.update');
Route::post('sections/{section}/clone', [SectionController::class, 'clone'])->name('sections.clone');
Route::post('sections/{section}/promote', [SectionController::class, 'promote'])->name('sections.promote');
Route::post('sections/{section}/detach', [SectionController::class, 'detach'])->name('sections.detach');
Route::post('sections/{section}/reattach', [SectionController::class, 'reattach'])->name('sections.reattach');
Route::delete('sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

// Global Block & Component Library
Route::prefix('global-blocks')->name('global-blocks.')->group(function (): void {
    Route::get('/', [\App\Http\Controllers\Admin\GlobalBlockController::class, 'index'])->name('index');
    Route::get('create', [\App\Http\Controllers\Admin\GlobalBlockController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\GlobalBlockController::class, 'store'])->name('store');
    Route::post('import', [\App\Http\Controllers\Admin\GlobalBlockController::class, 'import'])->name('import');
    Route::get('{block}/edit', [\App\Http\Controllers\Admin\GlobalBlockController::class, 'edit'])->name('edit');
    Route::get('{block}/preview', [\App\Http\Controllers\Admin\GlobalBlockController::class, 'preview'])->name('preview');
    Route::put('{block}', [\App\Http\Controllers\Admin\GlobalBlockController::class, 'update'])->name('update');
    Route::put('{block}/meta', [\App\Http\Controllers\Admin\GlobalBlockController::class, 'updateMeta'])->name('meta.update');
    Route::post('{block}/favorite', [\App\Http\Controllers\Admin\GlobalBlockController::class, 'toggleFavorite'])->name('favorite');
    Route::post('{block}/duplicate', [\App\Http\Controllers\Admin\GlobalBlockController::class, 'duplicate'])->name('duplicate');
    Route::get('{block}/export', [\App\Http\Controllers\Admin\GlobalBlockController::class, 'export'])->name('export');
    Route::post('{block}/versions/{version}/restore', [\App\Http\Controllers\Admin\GlobalBlockController::class, 'restoreVersion'])->name('versions.restore');
    Route::delete('{block}', [\App\Http\Controllers\Admin\GlobalBlockController::class, 'destroy'])->name('destroy');
});

// Menus (dropdown + mega menu trees)
Route::resource('menus', MenuController::class)->except(['show', 'create']);
Route::post('menus/{menu}/tree', [MenuController::class, 'saveTree'])->name('menus.tree');
Route::post('menus/{menu}/items', [MenuItemController::class, 'store'])->name('menus.items.store');
Route::put('menu-items/{item}', [MenuItemController::class, 'update'])->name('menu-items.update');
Route::delete('menu-items/{item}', [MenuItemController::class, 'destroy'])->name('menu-items.destroy');
Route::get('menus/reference/{type}', [\App\Http\Controllers\Admin\MenuReferenceController::class, 'search'])->name('menus.reference.search');

// Media Library (AJAX-first; index also serves picker + CKEditor browse modes)
Route::get('media', [MediaController::class, 'index'])->name('media.index');
Route::get('media/browse', [MediaController::class, 'browse'])->name('media.browse');
Route::post('media', [MediaController::class, 'store'])->name('media.store');
Route::post('media/ckeditor', [MediaController::class, 'ckeditorUpload'])->name('media.ckeditor');
Route::post('media/bulk-delete', [MediaController::class, 'bulkDestroy'])->name('media.bulk-delete');
Route::put('media/{medium}', [MediaController::class, 'update'])->name('media.update');
Route::post('media/{medium}/replace', [MediaController::class, 'replace'])->name('media.replace');
Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');
Route::post('media-folders', [MediaFolderController::class, 'store'])->name('media-folders.store');
Route::put('media-folders/{folder}', [MediaFolderController::class, 'update'])->name('media-folders.update');
Route::delete('media-folders/{folder}', [MediaFolderController::class, 'destroy'])->name('media-folders.destroy');

// Settings (grouped tabs: business, branding, social, seo)
Route::get('settings/{group}', [SettingController::class, 'edit'])
    ->where('group', '[a-z_]+')
    ->name('settings.edit');
Route::put('settings/{group}', [SettingController::class, 'update'])
    ->where('group', '[a-z_]+')
    ->name('settings.update');

<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Collections\CollectionRegistry;
use App\Models\CollectionItem;
use App\Models\Form;
use App\Models\Layout;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\Menu;
use App\Services\LayoutService;
use App\Services\MediaService;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * The Ekdanta site's content, from the design files. Safe to run again:
 * everything is found by its stable key or slug and left alone if it is
 * already there. Run with `php artisan ekdanta:seed`.
 */
class EkdantaSiteSeeder extends Seeder
{
    private const ASSETS = 'docs/ekdanta-design/assets/';

    /** The two client-logo strips: 270 × 96 px per logo, in this order (design: LOGO_A, LOGO_B). */
    public const LOGOS = [
        'client-logos-strip-a.jpg' => ['Trilok Infratech', 'Trilok Precast', 'Global Silk Mills', 'Yantra Design', 'Amplus', 'Shree Ambica Automotive', 'Aditya Laces', 'Heaven Solar', 'Sagar Enterprise', 'The Zula House', 'Cruisemax Sports', 'Syasi Designers', 'M.M. Fashion', 'Fab De Print', 'Belvae', 'Qbits', 'Euro', 'Jalpooree', 'Raj Wafers', 'Jenish Spices', 'Maharana', 'Vidhi Ghee', 'Gaukrish Ghee', 'SRK Dairy'],
        'client-logos-strip-b.jpg' => ['Suzuki dealership', 'Samsung authorised distributor', 'Dharmaraj Suzuki Citylight', 'Vijay Mobile', '09 Mobile Gallery', 'Her & He Cosmetics', 'Karvi Realtors', 'Nainvish', 'Zorko', 'Chapter.1', 'ValleyRides', 'Slimiphy', 'Zeronine Retail ERP', 'Zeronine Tax Consultancy', 'MoneyMatters AM', 'Jiya Financial Services', 'Karma Realtors', 'Signs360', 'GenZ Lifestyle', 'Ruparel'],
    ];

    public function run(): void
    {
        $this->media();
        $this->collections();
        $this->business();
        $this->menus();
        $this->header();
        $this->briefForm();
    }

    /**
     * "Ekdanta Brief" (Contact → Brief Builder): four steps of chips and
     * contact details, hidden fields for the page and campaign, saved as an
     * enquiry, then WhatsApp with the answers and the thank-you page.
     */
    public function briefForm(): void
    {
        if (Form::query()->where('slug', 'ekdanta-brief')->exists()) {
            return;
        }

        $form = Form::query()->create([
            'name' => 'Ekdanta Brief', 'slug' => 'ekdanta-brief', 'button_label' => 'Send my brief on WhatsApp',
            'success_message' => 'Your brief is ready in WhatsApp. Hit send, and our team will take it from there.',
            'notify_emails' => array_filter([setting('business.email')]), 'enquiry_source' => 'ekdanta-brief', 'is_active' => true,
            'settings' => ['success_action' => 'whatsapp', 'whatsapp_intro' => 'Hi Ekdanta!', 'then_label' => 'Sent it? See what happens next', 'then_page' => '/thank-you'],
        ]);

        $fields = [
            // label, type, step, required, options, WhatsApp sentence, autofill
            ['Services', 'checkbox', 1, true, ['Branding', 'Social media and reels', 'Meta ads and leads', 'SEO', 'Website', 'Films and shoots', 'CRM and apps'], 'I would like help with: {value}.'],
            ['Business type', 'radio', 2, true, ['Manufacturing', 'Food and beverage', 'Retail', 'Services', 'Something else'], 'Business type: {value}.'],
            ['When to start', 'radio', 3, true, ['This month', 'In 1 to 3 months', 'Just exploring'], 'When to start: {value}.'],
            ['Your name', 'text', 4, false, null, 'My name is {value}.'],
            ['Phone number', 'phone', 4, false, null, 'Phone: {value}.'],
            ['Company or brand', 'text', 4, false, null, 'Company: {value}.'],
            ['Page', 'hidden', 4, false, null, null, 'page_url'],
            ['Referrer', 'hidden', 4, false, null, null, 'referrer'],
            ['UTM source', 'hidden', 4, false, null, null, 'utm_source'],
            ['UTM medium', 'hidden', 4, false, null, null, 'utm_medium'],
            ['UTM campaign', 'hidden', 4, false, null, null, 'utm_campaign'],
        ];
        foreach ($fields as $order => $row) {
            [$label, $type, $step, $required, $options, $sentence] = $row;
            $form->fields()->create([
                'label' => $label, 'name' => Str::slug($label, '_'), 'type' => $type, 'options' => $options, 'is_required' => $required, 'sort_order' => $order,
                'settings' => array_filter(['step' => $step, 'display' => $options ? 'chips' : null, 'whatsapp' => $sentence, 'autofill' => $row[6] ?? null]),
            ]);
        }
    }

    /**
     * The design's navigation. Home uses its section anchors, blog pages add
     * Blog; each page picks its menu in Page settings (Main is the header's).
     */
    public const MENUS = [
        'Ekdanta main menu' => [['Home', '/'], ['Work', '/work'], ['Services', '/services'], ['About', '/about'], ['Contact', '/contact']],
        'Ekdanta home page menu' => [['Home', '#j-top', 'd-lg-none'], ['Why Ekdanta', '#j-obstacles'], ['Work', '#j-work'], ['Reels', '#j-reels'], ['Services', '/services'], ['About', '#j-about'], ['Contact', '/contact']],
        'Ekdanta blog menu' => [['Home', '/'], ['Work', '/work'], ['Services', '/services'], ['About', '/about'], ['Blog', '/blog'], ['Contact', '/contact']],
    ];

    public function menus(): void
    {
        foreach (self::MENUS as $name => $items) {
            $menu = Menu::query()->firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'location' => 'header', 'is_active' => true]);
            if ($menu->items()->exists()) {
                continue;
            }
            foreach ($items as $order => $item) {
                [$label, $url, $class] = $item + [2 => null];
                $menu->items()->create(['label' => $label, 'link_type' => 'url', 'url' => $url, 'css_class' => $class, 'sort_order' => $order]);
            }
        }
    }

    /**
     * Header Builder: floating pill, inline menu on desktop, WhatsApp and the
     * full-screen menu on phones and tablets, orange progress bar. Applied
     * only to an empty header or the untouched starter one (logo + menu).
     */
    public function header(): void
    {
        $header = Layout::query()->firstOrCreate(['area' => Layout::AREA_HEADER]);
        $sections = $header->sections()->get();
        $starter = $sections->pluck('type')->sort()->values()->all() === ['logo', 'nav-menu']
            && empty($sections->firstWhere('type', 'logo')->data_desktop['media_id']);
        if ($sections->isNotEmpty() && ! $starter) {
            $this->command?->warn('Header already set up: left as it is.');

            return;
        }

        $header->sections()->delete();
        $header->update(['settings' => [
            'sticky' => true, 'container' => 'container-fluid', 'show_topbar' => false, 'variant' => 'pill',
            'progress_bar' => true, 'progress_color' => '#F2A230', 'whatsapp_button' => 'mobile',
            'overlay_cta_label' => 'Become a big brand', 'overlay_cta_url' => '#contact', 'overlay_call_line' => true,
            'overlay_pause_label' => 'Pause animations', 'scrolled_shadow' => true, 'blur' => 0,
            'nav_style' => ['mobile' => 'overlay', 'tablet' => 'overlay', 'desktop' => 'inline'],
        ]]);
        $header->sections()->createMany([
            ['type' => 'logo', 'name' => 'Logo', 'sort_order' => 0, 'is_active' => true,
                'data_desktop' => ['media_id' => media_ref('ekdanta/logo-colour')?->id, 'alt' => 'Ekdanta — Your Branding Partner', 'link' => '/', 'max_height' => 32],
                'data_mobile' => ['max_height' => 27]],
            ['type' => 'nav-menu', 'name' => 'Main menu', 'sort_order' => 1, 'is_active' => true,
                'data_desktop' => ['menu_id' => Menu::query()->where('slug', 'ekdanta-main-menu')->value('id'), 'alignment' => 'center']],
            ['type' => 'cta-button', 'name' => 'Become a big brand', 'sort_order' => 2, 'is_active' => true,
                'data_desktop' => ['label' => 'Become a big brand', 'url' => '#contact', 'style' => 'solid', 'new_tab' => false]],
        ]);
        app(LayoutService::class)->flushCache();
    }

    /** Settings → Business from the design; only values that are still empty are filled. */
    public function business(): void
    {
        $address = 'B-402–403, Bhatar Trade Center, near Bhatar Char Rasta, Uma Bhawan Society, Bhatar, Surat, Gujarat 395017';
        $values = [
            'company_name' => 'Ekdanta Branding and Marketing',
            'email' => 'info@ekdanta.in',
            'country_code' => '91',
            'phones' => [
                ['label' => 'All services', 'number' => '90994 17987', 'menu_label' => 'Call', 'whatsapp' => true],
                ['label' => 'All services', 'number' => '90994 17949', 'menu_label' => '', 'whatsapp' => false],
                ['label' => 'Sales', 'number' => '63544 33194', 'menu_label' => 'Sales', 'whatsapp' => false],
            ],
            'offices' => [
                ['name' => 'Surat', 'address' => $address, 'directions_url' => 'https://share.google/EAOxrObclZeY4QkQR', 'latitude' => '', 'longitude' => '', 'hours' => '[Add office hours]'],
                ['name' => 'Ahmedabad', 'address' => '[Add Ahmedabad address]', 'directions_url' => '', 'latitude' => '', 'longitude' => '', 'hours' => ''],
            ],
        ];

        $missing = array_filter($values, fn ($value, string $key) => blank(setting("business.{$key}")), ARRAY_FILTER_USE_BOTH);
        if ($missing !== []) {
            app(SettingsService::class)->updateGroup('business', $missing);
        }
    }

    /** Case studies, services, clients, reels, FAQs and team roles (database/data/ekdanta-collections.php). */
    public function collections(): void
    {
        $registry = app(CollectionRegistry::class);

        foreach (require database_path('data/ekdanta-collections.php') as $collection => $rows) {
            $defaults = $registry->find($collection)->defaults();
            foreach ($rows as $order => [$slug, $title, $data]) {
                if (CollectionItem::withTrashed()->where('collection', $collection)->where('slug', $slug)->exists()) {
                    continue;
                }
                CollectionItem::create([
                    'collection' => $collection, 'slug' => $slug, 'title' => $title, 'status' => CollectionItem::STATUS_PUBLISHED,
                    'sort_order' => $order + 1, 'data' => array_replace($defaults, $data),
                ]);
            }
        }
    }

    /** Design assets into the Media Library as "ekdanta/<file name>", each logo as "ekdanta/client-<name>". */
    public function media(): void
    {
        $folder = MediaFolder::query()->firstOrCreate(['name' => 'Ekdanta', 'parent_id' => null]);

        foreach (glob(base_path(self::ASSETS.'*.{png,jpg}'), GLOB_BRACE) as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if (! str_starts_with($name, 'client-logos-strip')) {
                $this->store($file, 'ekdanta/'.$name, $folder->id);
            }
        }

        foreach (self::LOGOS as $strip => $names) {
            $source = imagecreatefromjpeg(base_path(self::ASSETS.$strip));
            foreach ($names as $i => $name) {
                $key = 'ekdanta/client-'.Str::slug($name);
                if (Media::query()->where('key', $key)->exists()) {
                    continue;
                }
                $logo = imagecrop($source, ['x' => $i * 270, 'y' => 0, 'width' => 270, 'height' => 96]);
                $path = tempnam(sys_get_temp_dir(), 'ek').'.jpg';
                imagejpeg($logo, $path, 92);
                imagedestroy($logo);
                $this->store($path, $key, $folder->id, Str::slug($name).'.jpg', $name);
                @unlink($path);
            }
            imagedestroy($source);
        }
    }

    private function store(string $path, string $key, int $folderId, ?string $filename = null, ?string $alt = null): void
    {
        if (Media::query()->where('key', $key)->exists()) {
            return;
        }

        $upload = new UploadedFile($path, $filename ?? basename($path), mime_content_type($path) ?: null, null, true);
        $media = app(MediaService::class)->upload($upload, $folderId, null);
        $media->forceFill(['key' => $key, 'alt' => $alt])->save();
    }
}

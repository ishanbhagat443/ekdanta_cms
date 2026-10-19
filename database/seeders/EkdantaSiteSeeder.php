<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Collections\CollectionRegistry;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\CollectionItem;
use App\Models\Form;
use App\Models\GlobalBlock;
use App\Models\Layout;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Section;
use App\Models\ThemePreset;
use App\Models\ThemeSetting;
use App\Services\GlobalBlockService;
use App\Services\LayoutService;
use App\Services\MediaService;
use App\Services\PageRenderService;
use App\Services\SectionBuilderService;
use App\Services\SettingsService;
use App\Services\ThemeService;
use App\Services\SectionTypes\SectionTypeRegistry;
use App\Support\LegalContent;
use App\Support\PageRoles;
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

    /** The Surat office's hours (given by Ekdanta, 13 Oct 2026). */
    private const HOURS = 'Mon–Sat, 10 am – 6:30 pm';

    /** The two client-logo strips: 270 × 96 px per logo, in this order (design: LOGO_A, LOGO_B). */
    public const LOGOS = [
        'client-logos-strip-a.jpg' => ['Trilok Infratech', 'Trilok Precast', 'Global Silk Mills', 'Yantra Design', 'Amplus', 'Shree Ambica Automotive', 'Aditya Laces', 'Heaven Solar', 'Sagar Enterprise', 'The Zula House', 'Cruisemax Sports', 'Syasi Designers', 'M.M. Fashion', 'Fab De Print', 'Belvae', 'Qbits', 'Euro', 'Jalpooree', 'Raj Wafers', 'Jenish Spices', 'Maharana', 'Vidhi Ghee', 'Gaukrish Ghee', 'SRK Dairy'],
        'client-logos-strip-b.jpg' => ['Suzuki dealership', 'Samsung authorised distributor', 'Dharmaraj Suzuki Citylight', 'Vijay Mobile', '09 Mobile Gallery', 'Her & He Cosmetics', 'Karvi Realtors', 'Nainvish', 'Zorko', 'Chapter.1', 'ValleyRides', 'Slimiphy', 'Zeronine Retail ERP', 'Zeronine Tax Consultancy', 'MoneyMatters AM', 'Jiya Financial Services', 'Karma Realtors', 'Signs360', 'GenZ Lifestyle', 'Ruparel'],
    ];

    /** --with-pages (the preview database): the design's sample articles go live as drawn. */
    public bool $withPages = false;

    public function run(): void
    {
        $this->media();
        $this->collections();
        $this->theme();
        $this->business();
        $this->favicon();
        $this->shareImage();
        $this->menus();
        $this->header();
        $this->finale();
        $this->briefForm();
        $this->newFields();
        $this->legalPages();
        $this->samplePosts($this->withPages);
        if ($this->withPages) {
            $this->pages();
        }
    }

    /**
     * --with-pages: every designed page, its sections in the design's order,
     * each ending with the synced Big Brand Finale. slug => [title, settings
     * (header.menu as a menu slug), role, section types after "ekdanta-"].
     */
    public const PAGES = [
        'home' => ['Home', [], 'home', ['home-hero', 'obstacles', 'work-showcase', 'reels-carousel', 'brand-shelves', 'services-panels', 'founders-story']],
        'contact' => ['Contact', [], null, ['contact-hero', 'contact-channels', 'brief-builder', 'visit-us', 'faq-deck']],
        'services' => ['Services', [], null, ['services-hero', 'expertise-cards', 'service-deep-dive', 'process-path', 'proof-carousel', 'pricing']],
        'thank-you' => ['Thank you', [], 'thank_you', ['thank-you-hero', 'next-steps', 'save-our-number', 'while-you-wait']],
        'page-not-found' => ['Page not found', [], 'not_found', ['not-found-hero', 'page-finder', 'report-link']],
        'about' => ['About', [], null, ['about-hero', 'our-story', 'four-promises', 'team-roll-call', 'who-we-work-with']],
        'work' => ['Work', [], null, ['work-hero', 'case-studies-scroller', 'reels-arc', 'client-logo-wall']],
        'case-study-template' => ['Case study (template)', ['template_for' => 'case_study'], null, ['case-hero', 'case-overview', 'case-gallery', 'case-results', 'next-case']],
        'service-template' => ['Service (template)', ['template_for' => 'service'], null, ['service-hero', 'whats-included', 'how-it-works', 'related-work', 'service-pricing-faq', 'works-well-with']],
        'blog' => ['Blog', [], null, ['blog-hero', 'featured-article', 'article-grid', 'whatsapp-tips']],
        'article-template' => ['Blog article (template)', ['template_for' => 'post'], null, ['article-hero', 'article-body', 'keep-reading']],
    ];

    /** The pages (only with --with-pages). A page that already has sections is left as it is. */
    public function pages(): void
    {
        $finale = GlobalBlock::query()->where('slug', 'big-brand-finale')->firstOrFail();
        $builder = app(SectionBuilderService::class);

        foreach (self::PAGES as $slug => [$title, $settings, $role, $types]) {
            $page = Page::query()->firstOrCreate(['slug' => $slug], ['title' => $title, 'status' => Page::STATUS_PUBLISHED]);
            if ($page->sections()->exists()) {
                continue;
            }
            if ($menu = $settings['header']['menu'] ?? null) {
                $settings['header'] = ['menu_id' => Menu::query()->where('slug', $menu)->value('id')];
            }
            $page->update(['status' => Page::STATUS_PUBLISHED, 'show_banner' => false, 'settings' => array_replace((array) $page->settings, $settings)]);
            foreach ($types as $type) {
                $builder->addSection($page, 'page', 'ekdanta-'.$type);
            }
            app(GlobalBlockService::class)->placeOnPage($finale, $page, count($types) + 1);
            if ($role && PageRoles::page($role, false) === null) {
                PageRoles::assign($page, $role);
            }
        }
    }

    /**
     * The Ekdanta theme: kept as a preset (Theme Customizer → Backups) and
     * applied once, to a site that doesn't have it yet. The theme the site
     * had before is saved as a backup first.
     */
    public function theme(): void
    {
        $settings = json_decode((string) file_get_contents(database_path('presets/ekdanta-theme.json')), true)['settings'];
        ThemePreset::query()->firstOrCreate(['slug' => 'ekdanta'], ['name' => 'Ekdanta', 'payload' => $settings, 'is_system' => true]);

        if (ThemeSetting::query()->where('key', 'css_variables')->where('value', 'like', '%--tk-ink%')->exists()) {
            return;
        }
        $theme = app(ThemeService::class);
        $theme->savePreset('Before Ekdanta');
        $theme->importPayload($settings);
    }

    /** The Big Brand Finale as a saved block: pages place it synced, so one edit changes every page. */
    public function finale(): void
    {
        $block = GlobalBlock::query()->where('slug', 'big-brand-finale')->first() ?? app(GlobalBlockService::class)->create([
            'name' => 'Big Brand Finale', 'slug' => 'big-brand-finale', 'type' => 'ekdanta-finale',
            'description' => 'The closing section of every Ekdanta page, with the contact card and the site footer.',
        ]);
        // The footer's page links: the main menu, unless a menu is picked already.
        $main = Menu::query()->where('slug', 'ekdanta-main-menu')->value('id');
        if ($main && empty($block->data_desktop['menu_id'])) {
            app(GlobalBlockService::class)->update($block, ['data_desktop' => ['menu_id' => $main] + (array) $block->data_desktop], validate: false);
        }
    }

    /** The design's blog articles: [category, title, excerpt, cover media key or null, featured]. */
    private const POSTS = [
        ['Social media', 'Hindi, Gujarati or English? Choosing a language for your posts', 'Your buyers scroll in more than one language. Here is how we decide which one each post should speak, with examples from brands across Gujarat.', 'ekdanta/case-heaven-solar', true],
        ['Social media', 'Why your factory needs reels, not just brochures', 'Buyers decide faster when they can see your plant, your machines and your people at work.', 'ekdanta/case-trilok-infratech', false],
        ['Ads', 'Meta ads for manufacturers: a simple first campaign', 'One campaign that brings real enquiries, without spending your budget on likes.', 'ekdanta/case-yantra-design', false],
        ['Social media', 'Festival posts that bring enquiries, not just likes', 'Diwali, Navratri and Uttarayan posts that sell, with an offer and a clear next step.', 'ekdanta/case-heaven-solar', false],
        ['Websites', 'Five signs your website is losing you leads', 'Slow pages, hidden phone numbers and forms nobody fills. Fix these first.', null, false],
        ['Branding', 'Packaging that sells on a crowded shelf', 'What shoppers notice in the first three seconds, and how to design for it.', 'ekdanta/case-srk-dairy', false],
        ['Growth', 'Why leads go cold, and how a CRM fixes it', 'Most enquiries are lost to slow follow-ups, not to competitors. Here is the fix.', null, false],
        ['Shoots', 'How we plan a product shoot in one day', 'Shot lists, props and light: how one well-planned day gives you a month of content.', 'ekdanta/case-her-and-he', false],
        ['Social media', 'Reels or posts: what to make first on a small budget', 'Where to start when you can only make a few pieces of content a month.', 'ekdanta/reel-1', false],
        ['Branding', "Does your logo still work in WhatsApp's tiny circle?", 'Most people see your logo at the size of a coin. Here is how to make sure it still reads.', null, false],
    ];

    /**
     * The design's articles, so the blog pages have something to show. On a
     * real site they are drafts titled "SAMPLE – …" (replace or delete
     * them); on the preview database (--with-pages) they are live as drawn.
     * Their text is a placeholder until the real article is written.
     */
    public function samplePosts(bool $live = false): void
    {
        // The topics in the design's order (Blog: All, Social media, Ads, …).
        foreach (['Social media', 'Ads', 'Branding', 'Websites', 'Shoots', 'Growth'] as $order => $name) {
            BlogCategory::query()->firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'is_active' => true, 'sort_order' => $order + 1]);
        }
        foreach (self::POSTS as $i => [$category, $title, $excerpt, $cover, $featured]) {
            $slug = Str::slug($title);
            // A sample the site has deleted (soft delete) stays deleted.
            if (Post::withTrashed()->where('slug', $slug)->exists()) {
                continue;
            }
            $cat = BlogCategory::query()->where('slug', Str::slug($category))->firstOrFail();
            $post = Post::query()->create([
                'title' => $live ? $title : 'SAMPLE – '.$title,
                'slug' => $slug,
                'author_name' => 'the Ekdanta team',
                'excerpt' => $excerpt,
                'body' => '<p>'.e($excerpt).'</p><p>[Write the article here.]</p>',
                'featured_media_id' => $cover ? Media::query()->where('key', $cover)->value('id') : null,
                'status' => $live ? Post::STATUS_PUBLISHED : Post::STATUS_DRAFT,
                'is_featured' => $featured,
                'published_at' => now()->subDays($i + 1),
            ]);
            $post->categories()->sync([$cat->id]);
        }
        $this->sampleArticle();
    }

    /**
     * The article the design draws in full (Blog-Post): its text, blocks,
     * tags and the three articles under "Keep reading". Only while it still
     * holds the placeholder text, so a written article is never replaced.
     */
    private function sampleArticle(): void
    {
        $post = Post::query()->where('slug', 'why-your-factory-needs-reels-not-just-brochures')->first();
        if (! $post || ! str_contains((string) $post->body, '[Write the article here.]')) {
            return;
        }
        $block = fn (string $name) => trim(view('frontend.article-blocks.'.$name)->render());
        $post->update(['body' => implode("\n", [
            '<p>A buyer in Pune gets your brochure on WhatsApp. Before he calls you back, he opens your Instagram. What he finds there decides whether he calls you, or the next supplier on his list.</p>',
            '<p>The brochure gets you on the list. What buyers see next decides whether you stay on it.</p>',
            '<h2 data-toc="Buyers check you out first">Buyers check you out before they call</h2>',
            '<p>Most buyers do their homework on their phone before they pick it up to call. They look at your website, your Google listing and your Instagram, and they are asking one simple question: is this a real, serious company I can trust with my order?</p>',
            $block('journey'),
            '<p>A brochure answers that question with claims: modern plant, quality products, timely delivery. The trouble is, every supplier says the same things. A reel answers it with proof.</p>',
            "<h2>What a brochure can't show</h2>",
            '<p>Some things are hard to believe until you see them. A fifteen-second reel can show what pages of text cannot.</p>',
            $block('compare'),
            $block('figure'),
            $block('quote'),
            '<h2 data-toc="Five reels to shoot this month">Five reels every factory can shoot this month</h2>',
            "<p>You don't need a film crew. A recent phone, good light and a plan are enough. Start with these five, in any order.</p>",
            $block('reels'),
            '<h2>Keep it simple</h2>',
            '<p>Good factory reels are simple. Before you post, run through this list:</p>',
            $block('checklist'),
            '<h2>Start small, then build</h2>',
            '<p>Start with one reel a week for a month. Watch which ones bring messages and calls, then make more of those. Doing it every week matters more than making it perfect.</p>',
            '<p>And keep your brochure. It still has a job: it is what buyers save, print and forward to their boss. Let your reels bring buyers in, and let your brochure help close the deal.</p>',
        ])]);
        $post->tags()->sync(collect(['Reels', 'Manufacturing', 'Instagram', 'B2B marketing'])
            ->map(fn ($name) => BlogTag::query()->firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id)->all());
        $related = [];
        foreach (['Festival posts that bring enquiries, not just likes', 'How we plan a product shoot in one day', 'Reels or posts: what to make first on a small budget'] as $title) {
            if ($id = Post::query()->where('slug', Str::slug($title))->value('id')) {
                $related[$id] = ['sort_order' => count($related)];
            }
        }
        $post->relatedPosts()->sync($related);
    }

    /**
     * "Ekdanta Brief" (Contact → Brief Builder): four steps of chips and
     * contact details, hidden fields for the page and campaign, saved as an
     * enquiry, then WhatsApp with the answers and the thank-you page.
     */
    /**
     * Fields added to a section type after its sections were built (the brief's
     * hints, the finale's footer links, …): each existing Ekdanta section and
     * saved block gains the default of every key it lacks, and the phone
     * default of a key that is new. A value that is there, even empty, stays.
     * Saved blocks go through GlobalBlockService (versioned, pushed to synced
     * copies), so synced copies aren't touched twice.
     */
    public function newFields(): void
    {
        $registry = app(SectionTypeRegistry::class);
        $fill = function ($row) use ($registry): ?array {
            $type = $registry->find($row->type);
            $desktop = (array) $row->data_desktop;
            $missing = array_diff_key($type->defaults(), $desktop);
            if ($missing === []) {
                return null;
            }
            $mobile = array_intersect_key((array) ($type->deviceDefaults()['mobile'] ?? []), $missing);

            return ['data_desktop' => $desktop + $missing] + ($mobile === [] ? [] : ['data_mobile' => (array) $row->data_mobile + $mobile]);
        };

        GlobalBlock::query()->where('type', 'like', 'ekdanta-%')->each(function (GlobalBlock $block) use ($fill): void {
            if ($data = $fill($block)) {
                app(GlobalBlockService::class)->update($block, $data, validate: false);
            }
        });
        Section::query()->where('type', 'like', 'ekdanta-%')->where(fn ($q) => $q->whereNull('global_block_id')->orWhere('is_synced', false))
            ->each(function (Section $section) use ($fill): void {
                if ($data = $fill($section)) {
                    $section->update($data);
                }
            });
        app(PageRenderService::class)->flushCache();
    }

    /** slug => [title, docs/ekdanta-content/legal file, anchor prefix]. */
    public const LEGAL_PAGES = [
        'privacy-policy' => ['Privacy Policy', 'privacy-policy', 'l'],
        'terms-of-service' => ['Terms of Service', 'terms-of-service', 't'],
    ];

    /**
     * Privacy Policy and Terms of Service, from docs/ekdanta-content/legal:
     * the four legal sections, then the Big Brand Finale. Only a page that
     * has no sections yet is filled (its title banner off, as every Ekdanta
     * page); a new page stays a draft. The page's
     * SEO title and description are set only if empty.
     */
    public function legalPages(): void
    {
        $finale = GlobalBlock::query()->where('slug', 'big-brand-finale')->first();
        $builder = app(SectionBuilderService::class);

        foreach (self::LEGAL_PAGES as $slug => [$title, $file, $prefix]) {
            $page = Page::query()->firstOrCreate(['slug' => $slug], ['title' => $title, 'status' => Page::STATUS_DRAFT]);
            if (! $page->sections()->exists()) {
                $page->update(['show_banner' => false]); // the Legal Hero is the page's heading
                foreach (LegalContent::sections($file, $prefix) as $type => $data) {
                    $section = $builder->addSection($page, 'page', $type);
                    $section->update(['data_desktop' => $data + (array) $section->data_desktop]);
                }
                if ($finale) {
                    app(GlobalBlockService::class)->placeOnPage($finale, $page, $page->sections()->count() + 1);
                }
            }
            $seo = $page->seoMeta()->firstOrNew();
            if (blank($seo->meta_title)) {
                $seo->fill(['meta_title' => "{$title} | Ekdanta Branding and Marketing", 'meta_description' => $seo->meta_description ?: LegalContent::sections($file, $prefix)['ekdanta-legal-hero']['text']])->save();
            }
        }
        app(PageRenderService::class)->flushCache();
    }

    /** The brief's consent box (docs/ekdanta-content/copy.json → brief_consent). */
    private const BRIEF_CONSENT = [
        'label' => 'I agree that Ekdanta can contact me on WhatsApp or by phone about this brief.',
        'note' => 'We use these details only to reply to you.', 'link_text' => 'Privacy Policy', 'link_url' => '/privacy-policy',
        'short_label' => 'I agree that Ekdanta can contact me about this brief.', 'short_note' => 'We only use them to reply.',
    ];

    public function briefForm(): void
    {
        if ($form = Form::query()->where('slug', 'ekdanta-brief')->first()) {
            $this->upgradeBrief($form);

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
            ['Your name', 'text', 4, true, null, 'My name is {value}.'],
            ['Phone number', 'phone', 4, true, null, 'Phone: {value}.'],
            ['Company or brand (optional)', 'text', 4, false, null, 'Company: {value}.'],
            ['Page', 'hidden', 4, false, null, null, 'page_url'],
            ['Referrer', 'hidden', 4, false, null, null, 'referrer'],
            ['UTM source', 'hidden', 4, false, null, null, 'utm_source'],
            ['UTM medium', 'hidden', 4, false, null, null, 'utm_medium'],
            ['UTM campaign', 'hidden', 4, false, null, null, 'utm_campaign'],
        ];
        foreach ($fields as $order => $row) {
            [$label, $type, $step, $required, $options, $sentence] = $row;
            $form->fields()->create([
                'label' => $label, 'name' => $label === 'Company or brand (optional)' ? 'company_or_brand' : Str::slug($label, '_'), 'type' => $type, 'options' => $options, 'is_required' => $required, 'sort_order' => $order,
                'settings' => array_filter(['step' => $step, 'display' => $options ? 'chips' : null, 'whatsapp' => $sentence, 'autofill' => $row[6] ?? null]),
            ]);
        }
        $this->upgradeBrief($form);
    }

    /**
     * A brief built before the consent work: name and phone become required,
     * the company label still as seeded gains "(optional)", and a consent box
     * is added to the last step if there is none. Nothing else is touched.
     */
    private function upgradeBrief(Form $form): void
    {
        $form->fields()->whereIn('name', ['your_name', 'phone_number'])->update(['is_required' => true]);
        $form->fields()->where('name', 'company_or_brand')->where('label', 'Company or brand')->update(['label' => 'Company or brand (optional)']);
        if ($form->fields()->where('type', 'consent')->exists()) {
            return;
        }
        $fields = $form->fields()->get();
        $form->fields()->create([
            'label' => self::BRIEF_CONSENT['label'], 'name' => 'consent', 'type' => 'consent', 'is_required' => true,
            'sort_order' => (int) $fields->max('sort_order') + 1,
            'settings' => ['step' => $fields->max(fn ($f) => $f->step()) ?: 1] + array_diff_key(self::BRIEF_CONSENT, ['label' => 1]),
        ]);
    }

    /**
     * The design's navigation: one menu on every page (the header's, and the
     * Big Brand Finale's footer links). Menus a site already has are left
     * as they are, including the older home page and blog menus.
     */
    public const MENUS = [
        'Ekdanta main menu' => [['Home', '/'], ['Services', '/services'], ['Work', '/work'], ['About', '/about'], ['Blog', '/blog'], ['Contact', '/contact']],
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

    /** Settings → Branding: the e-mark as the favicon, when none is set. */
    /**
     * SEO Defaults → OG image: the share image (the Home hero), if none is set.
     * Its own copy, as an upload: removing it in Settings deletes that file,
     * never the Media Library's.
     */
    public function shareImage(): void
    {
        if (blank(setting('seo.og_image'))) {
            $file = base_path(self::ASSETS.'ekdanta-og-1200x630.jpg');
            app(SettingsService::class)->updateGroup('seo', ['og_image' => new UploadedFile($file, 'ekdanta-og-1200x630.jpg', 'image/jpeg', null, true)]);
        }
    }

    public function favicon(): void
    {
        if (blank(setting('branding.favicon'))) {
            $file = base_path(self::ASSETS.'e-mark.png');
            app(SettingsService::class)->updateGroup('branding', ['favicon' => new UploadedFile($file, 'favicon.png', 'image/png', null, true)]);
        }
    }

    /**
     * Settings → Business from the design; only values that are still empty
     * are filled. Office hours still holding the design's placeholder get the
     * real ones.
     */
    public function business(): void
    {
        $address = 'B-402–403, Bhatar Trade Center, near Bhatar Char Rasta, Uma Bhawan Society, Bhatar, Surat, Gujarat 395017';
        $values = [
            'company_name' => 'Ekdanta Branding and Marketing',
            'email' => 'info@ekdanta.in',
            'country_code' => '91',
            // docs/ekdanta-content/business.json → legal_entity (the GST registration).
            'gst' => '24GHNPD1228K1ZC',
            'legal_name' => 'Bhagat Dishant Dipesh',
            'trade_name' => 'Vedanta Advisor',
            'constitution' => 'Proprietorship',
            'registered_address' => 'Shop No. 403, 4th Floor, Bhatar Trade Center, B Building, Bhatar Char Rasta, near Shiv Timber Mart, Surat, Gujarat 395017',
            'phones' => [
                ['label' => 'All services', 'number' => '90994 17987', 'menu_label' => 'Call', 'whatsapp' => true],
                ['label' => 'All services', 'number' => '90994 17949', 'menu_label' => '', 'whatsapp' => false],
                ['label' => 'Sales', 'number' => '63544 33194', 'menu_label' => 'Sales', 'whatsapp' => false],
            ],
            'offices' => [
                ['name' => 'Surat', 'address' => $address, 'directions_url' => 'https://share.google/EAOxrObclZeY4QkQR', 'latitude' => '', 'longitude' => '', 'hours' => self::HOURS],
                ['name' => 'Ahmedabad', 'address' => '[Add Ahmedabad address]', 'directions_url' => '', 'latitude' => '', 'longitude' => '', 'hours' => ''],
            ],
        ];

        $missing = array_filter($values, fn ($value, string $key) => blank(setting("business.{$key}")), ARRAY_FILTER_USE_BOTH);
        $offices = setting_list('business.offices');
        if (! isset($missing['offices']) && ($offices[0]['hours'] ?? '') === '[Add office hours]') {
            $offices[0]['hours'] = self::HOURS;
            $missing['offices'] = $offices;
        }
        if ($missing !== []) {
            app(SettingsService::class)->updateGroup('business', $missing);
        }
    }

    /** Case studies, services, clients, reels, FAQs and team roles (database/data/ekdanta-collections.php); existing items only gain fields they lack. */
    public function collections(): void
    {
        $registry = app(CollectionRegistry::class);

        foreach (require database_path('data/ekdanta-collections.php') as $collection => $rows) {
            $defaults = $registry->find($collection)->defaults();
            foreach ($rows as $order => [$slug, $title, $data]) {
                $existing = CollectionItem::withTrashed()->where('collection', $collection)->where('slug', $slug)->first();
                if ($existing) {
                    // Fields added since it was seeded get their design value; nothing is overwritten.
                    $missing = array_diff_key(array_replace($defaults, $data), (array) $existing->data);
                    if ($missing !== [] && ! $existing->trashed()) {
                        $existing->update(['data' => (array) $existing->data + $missing]);
                    }

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
    /** Assets with their own key or description (docs/ekdanta-content/copy.json → alt_text): file name => [key, description]. */
    private const ASSET_INFO = [
        'ekdanta-og-1200x630' => ['ekdanta/og-default', 'Ekdanta — Aapki branding & marketing ke विघ्नहर्ता'],
        'founder-vaishali-hirpara' => ['ekdanta/founder-vaishali-hirpara', 'Vaishali Hirpara, founder of Ekdanta'],
        'founder-dishant-bhagat' => ['ekdanta/founder-dishant-bhagat', 'Dishant Bhagat, founder of Ekdanta'],
    ];

    public function media(): void
    {
        $folder = MediaFolder::query()->firstOrCreate(['name' => 'Ekdanta', 'parent_id' => null]);

        foreach (glob(base_path(self::ASSETS.'*.{png,jpg}'), GLOB_BRACE) as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if (! str_starts_with($name, 'client-logos-strip')) {
                [$key, $alt] = self::ASSET_INFO[$name] ?? ['ekdanta/'.$name, null];
                $this->store($file, $key, $folder->id, null, $alt);
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
        if ($media = Media::query()->where('key', $key)->first()) {
            if ($alt !== null && blank($media->alt)) {
                $media->forceFill(['alt' => $alt])->save(); // a description someone wrote stays
            }

            return;
        }

        $upload = new UploadedFile($path, $filename ?? basename($path), mime_content_type($path) ?: null, null, true);
        $media = app(MediaService::class)->upload($upload, $folderId, null);
        $media->forceFill(['key' => $key, 'alt' => $alt])->save();
    }
}

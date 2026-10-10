<?php

/*
 * The Ekdanta collections, copied from the design files (docs/ekdanta-design):
 * Case-Study CS, Final-Home P5/W/REELS, Services PIL/GR, Service-Detail S,
 * Contact FAQ, About ROLES, the two logo strips. Text in [brackets] is a
 * placeholder the design leaves for real content.
 *
 * Shape: collection key => list of [slug, title, data].
 */

$results = [
    ['label' => 'Reach', 'value' => '[ + ]', 'note' => 'Add the real number'],
    ['label' => 'Enquiries', 'value' => '[ + ]', 'note' => 'Add the real number'],
    ['label' => 'Engagement', 'value' => '[ + ]', 'note' => 'Add the real number'],
];
$case = fn (array $data) => $data + [
    'home_result' => '[Add one number from this campaign]',
    'result_line' => '[One line on what changed for the brand, in plain words.]',
    'results' => $results,
    'quote' => '[Client quote: what changed for their business, in their own words. Ask for permission to use their name and photo.]',
    'quote_name' => '[Name]',
    'quote_role' => '[Role]',
];

$items = [];

$items['case-studies'] = [
    ['trilok-infratech', 'Trilok Infratech', $case([
        'short_name' => 'Trilok Infratech', 'sector' => 'INFRASTRUCTURE AND PRECAST', 'industry' => 'Infrastructure and precast', 'colour' => '#0E2C54',
        'logo' => 'ekdanta/client-trilok-infratech', 'image' => 'ekdanta/case-trilok-infratech',
        'description' => 'A renowned precast infrastructure manufacturer with 20+ years of expertise, serving 100+ clients across India and international markets.',
        'brief' => 'Make a precast manufacturer with 20+ years of expertise look as solid online as its products, and speak to buyers in both Hindi and English.',
        'approach' => 'One consistent blue look across every post, site and factory reels, and campaigns written in both Hindi and English.',
        'made' => "Social media creatives\nSite and factory reels\nHindi and English campaigns",
        'facts' => [['value' => '20+', 'label' => 'years of expertise'], ['value' => '100+', 'label' => 'clients in India and abroad']],
    ])],
    ['yantra-design', 'Yantra Design', $case([
        'short_name' => 'Yantra Design', 'sector' => 'INDUSTRIAL MACHINERY', 'industry' => 'Industrial machinery', 'colour' => '#4A1212',
        'logo' => 'ekdanta/client-yantra-design', 'image' => 'ekdanta/case-yantra-design',
        'description' => "A leading CNC machine manufacturer delivering high-precision drilling solutions, with 250+ installations across India's engineering industries.",
        'brief' => 'Show what high-precision CNC drilling machines can do, in a way buyers in engineering industries understand at a glance.',
        'approach' => 'Bold red creatives that put each machine and its key specs front and centre, plus showcase posts and launch campaigns.',
        'made' => "Product creatives\nMachine showcase posts\nLaunch campaigns",
        'facts' => [['value' => '250+', 'label' => 'installations across India'], ['value' => 'CNC', 'label' => 'high-precision drilling machines']],
    ])],
    ['heaven-solar', 'Heaven Solar', $case([
        'short_name' => 'Heaven Solar', 'sector' => 'RENEWABLE ENERGY', 'industry' => 'Renewable energy', 'colour' => '#0C3E24',
        'logo' => 'ekdanta/client-heaven-solar', 'image' => 'ekdanta/case-heaven-solar',
        'description' => "Gujarat's No.1 solar energy company with 10,000+ installations, and an official Adani Solar channel partner.",
        'brief' => 'Turn festivals and offers into enquiries, with creatives in Gujarati and English for homes and businesses across Gujarat.',
        'approach' => 'Creatives in Gujarati and English, planned around festivals and limited-time offers.',
        'made' => "Gujarati and English creatives\nOffer campaigns\nFestival posts",
        'facts' => [['value' => '10,000+', 'label' => 'installations'], ['value' => 'Adani', 'label' => 'Solar channel partner']],
    ])],
    ['srk-dairy-farm-and-foods', 'SRK Dairy Farm and Foods', $case([
        'short_name' => 'SRK Dairy', 'sector' => 'DAIRY AND FMCG', 'industry' => 'Dairy and FMCG', 'colour' => '#48320A',
        'logo' => 'ekdanta/client-srk-dairy', 'image' => 'ekdanta/case-srk-dairy',
        'description' => 'A trusted dairy brand delivering farm-fresh premium ghee through superior quality and a strong distribution network.',
        'brief' => 'Make farm-fresh ghee look as premium as it tastes, with product photography and recipes people want to save.',
        'approach' => 'Warm product photography, recipe ideas and festive creatives that make the ghee the hero of every post.',
        'made' => "Product photography\nRecipe and festive creatives\nBrand campaigns",
        'facts' => [['value' => 'Ghee', 'label' => 'farm-fresh and premium'], ['value' => 'Wide', 'label' => 'distribution network']],
    ])],
    ['her-and-he-cosmetics', 'Her & He Cosmetics', $case([
        'short_name' => 'Her & He', 'sector' => 'SKINCARE', 'industry' => 'Skincare', 'colour' => '#2A282C',
        'logo' => 'ekdanta/client-her-he-cosmetics', 'image' => 'ekdanta/case-her-and-he',
        'description' => "India's growing unisex skincare brand from Surat, crafting effective formulas with complete transparency for everyday skin.",
        'brief' => 'Launch a unisex skincare brand with clean product shoots and model creatives that build trust from the first post.',
        'approach' => 'Clean product shoots and model creatives, with a launch post for each new formula.',
        'made' => "Product shoots\nModel creatives\nLaunch posts",
        'facts' => [['value' => 'Unisex', 'label' => 'skincare for everyday skin'], ['value' => 'Surat', 'label' => 'home of the brand']],
    ])],
    ['valleyrides', 'ValleyRides', $case([
        'short_name' => 'ValleyRides', 'sector' => 'ADVENTURE TOURISM', 'industry' => 'Adventure tourism', 'colour' => '#0C3A42',
        'logo' => 'ekdanta/client-valleyrides', 'image' => 'ekdanta/case-valleyrides',
        'description' => 'An adventure hospitality brand offering guided Himalayan treks, cozy stays and local cuisine in Sankri, Uttarakhand.',
        'brief' => 'Turn the Himalayan trails, stays and food into posts and reels that bring in trek bookings.',
        'approach' => 'Travel creatives and reels shot on the trail, with posts that point straight to bookings.',
        'made' => "Travel creatives\nReels from the trail\nBooking campaigns",
        'facts' => [['value' => 'Sankri', 'label' => 'Uttarakhand base camp'], ['value' => 'Treks', 'label' => 'stays and local cuisine']],
    ])],
];

$items['service-groups'] = [
    ['branding', 'Branding', ['hinglish' => 'Pehchaan jo yaad rahe.', 'word' => 'BRANDING', 'colour' => '#2EB45D', 'text_colour' => '#0D1410', 'accent' => '#0D1410', 'stroke' => 'rgba(13,20,16,0.35)', 'rule' => 'rgba(13,20,16,0.2)',
        'description' => 'We create distinctive brand identities that build recognition, trust and long-term value.',
        'short_description' => 'Identities that build recognition, trust and long-term value.', 'card_colour' => '#2EB45D', 'card_dot' => '#0D1410', 'badge' => '#0D1410', 'badge_text' => '#FFFFFF', 'deep_kicker' => '#0E3A22', 'deep_line' => '#0D1410', 'deep_text' => '#0E3A22']],
    ['digital', 'Digital', ['hinglish' => 'Dikho wahan, jahan customer hai.', 'word' => 'DIGITAL', 'colour' => '#0D1410', 'text_colour' => '#FFFFFF', 'accent' => '#2EB45D', 'stroke' => 'rgba(46,180,93,0.55)', 'rule' => 'rgba(255,255,255,0.15)',
        'description' => 'Data-driven digital strategies designed to attract the right audience, generate quality leads and accelerate growth.',
        'short_description' => 'Reach the right audience and bring in quality leads.', 'card_colour' => '#0D1410', 'card_dot' => '#2EB45D', 'badge' => '#2EB45D', 'badge_text' => '#0D1410', 'deep_kicker' => '#8FE3AE', 'deep_line' => '#2EB45D', 'deep_text' => '#BFD2C6']],
    ['growth', 'Growth', ['hinglish' => 'Enquiry se order tak.', 'word' => 'GROWTH', 'colour' => '#F4F6F1', 'text_colour' => '#0D1410', 'accent' => '#2EB45D', 'stroke' => 'rgba(30,142,71,0.4)', 'rule' => 'rgba(13,20,16,0.12)',
        'description' => 'We build systems that turn enquiries into qualified leads, and leads into long-term customers.',
        'short_description' => 'Turn enquiries into leads, and leads into customers.', 'card_colour' => '#FFFFFF', 'card_dot' => '#F2A230', 'badge' => '#F2A230', 'badge_text' => '#0D1410', 'deep_kicker' => '#1B8141', 'deep_line' => '#1E8E47', 'deep_text' => '#56605A']],
    ['films', 'Films', ['hinglish' => 'Kahani jo dil tak pahunche.', 'word' => 'FILMS', 'colour' => '#0A5A30', 'text_colour' => '#FFFFFF', 'accent' => '#F2A230', 'stroke' => 'rgba(255,255,255,0.35)', 'rule' => 'rgba(255,255,255,0.18)',
        'description' => 'Compelling visual content that captures attention, tells your story and strengthens your brand.',
        'short_description' => 'Photos, videos and films that tell your story.', 'card_colour' => '#0A5A30', 'card_dot' => '#8FE3AE', 'badge' => '#F2A230', 'badge_text' => '#0D1410', 'deep_kicker' => '#8FE3AE', 'deep_line' => '#F7B955', 'deep_text' => '#CFE8D7']],
];

// [group, full name, short name (Services menu), one-liner]
$grid = [
    ['branding', 'Brand strategy', 'Brand strategy', 'Who you serve, what you stand for and how you are different.'],
    ['branding', 'Brand identity', 'Brand identity', 'Name, logo and the personality your brand speaks with.'],
    ['branding', 'Visual identity', 'Visual identity', 'Colours, type and imagery that look like you everywhere.'],
    ['branding', 'Brand guidelines', 'Brand guidelines', 'One rulebook so every post, ad and print stays consistent.'],
    ['branding', 'Packaging design', 'Packaging design', 'Boxes, pouches and labels that sell on the shelf.'],
    ['branding', 'Brand positioning', 'Brand positioning', 'A clear place in your market that customers remember.'],
    ['digital', 'Social media management', 'Social media', 'Calendars, creatives and reels, posted for you every month.'],
    ['digital', 'Performance marketing', 'Performance marketing', 'Paid campaigns measured by leads and sales, not likes.'],
    ['digital', 'Meta ads', 'Meta ads', 'Facebook and Instagram ads, with creatives made in-house.'],
    ['digital', 'Lead generation', 'Lead generation', 'Forms, landing pages and campaigns that bring enquiries.'],
    ['digital', 'SEO', 'SEO', 'Get found on Google when people search for what you sell.'],
    ['digital', 'Website development', 'Website development', 'Fast, modern websites, built and maintained in-house.'],
    ['growth', 'Lead funnel strategy', 'Lead funnel strategy', 'The path a stranger takes to becoming an enquiry.'],
    ['growth', 'Sales funnel design', 'Sales funnel design', 'Clear steps from enquiry to quote to order.'],
    ['growth', 'Lead nurturing', 'Lead nurturing', "WhatsApp, call and email follow-ups that don't let leads go cold."],
    ['growth', 'Sales consulting', 'Sales consulting', 'Scripts, pricing and routines for your sales team.'],
    ['growth', 'CRM and app development', 'CRM and apps', 'Custom CRMs and apps to track every lead and order.'],
    ['growth', 'Conversion optimisation', 'Conversion optimisation', 'Small fixes to pages and offers that turn visitors into buyers.'],
    ['films', 'Brand and corporate films', 'Brand and corporate films', 'Your story on film, from factory floor to founder interview.'],
    ['films', 'Product photography', 'Product photography', 'Clean, scroll-stopping photos of every product.'],
    ['films', 'Product videos', 'Product videos', 'Short videos that show how your product works.'],
    ['films', 'Motion graphics', 'Motion graphics', 'Animated explainers, logo reveals and ad graphics.'],
    ['films', 'UGC content', 'UGC content', 'Real people using your product, made for ads and reels.'],
    ['films', 'Influencer and model shoots', 'Influencer shoots', 'Casting, styling and shoots with creators and models.'],
];

$rows = fn (array $pairs) => array_map(fn ($p) => ['title' => $p[0], 'text' => $p[1]], $pairs);

// The six services with their own page (Service-Detail S), keyed by slug.
$pages = [
    'social-media-management' => ['group' => 'digital', 'short_name' => 'Social media', 'lower_name' => 'social media', 'group_label' => 'DIGITAL', 'scene' => 'social',
        'hero_bg' => '#0D1410', 'hero_fg' => '#FFFFFF', 'hero_glow' => 'rgba(46,180,93,0.28)', 'hero_line' => 'rgba(255,255,255,0.35)', 'hero_accent' => '#2EB45D', 'hero_accent_fg' => '#0D1410',
        'promise' => 'Posts, reels and stories that keep your brand in front of the right people, every week.', 'good_for' => "Manufacturers\nD2C brands\nLocal businesses",
        'included' => $rows([['Monthly content calendar', 'Planned around your offers, festivals and launches.'], ['Creatives and carousels', "Designed in-house, in your brand's look."], ['Reels', 'Scripted, shot and edited by our team.'], ['Captions in your languages', 'English, Hindi or Gujarati, written for your buyers.'], ['Posting on schedule', 'We publish on time across your pages.'], ['Monthly report', "What worked, what didn't, and next month's plan."]]),
        'steps' => $rows([['Brand and audience call', 'We learn your products, customers and competitors.'], ['Calendar for approval', "You approve the month's plan before we create."], ['Create and publish', 'Design, shoots and edits, posted on schedule.'], ['Review every month', 'We look at the numbers and plan the next month.']]),
        'proof_title' => 'Feeds we run.', 'proof_cases' => 'trilok-infratech, heaven-solar, her-and-he-cosmetics', 'proof_placeholders' => [],
        'price_factors' => "How many posts and reels you need each month\nWhich platforms you want to be on\nWhether shoots are needed at your location\nThe languages for captions and creatives",
        'related' => 'meta-ads, product-shoots-and-reels, branding-and-packaging',
        'faqs' => [['Do you shoot the content too?', 'Yes. Our crew can shoot at your factory, store or site, or we can work with photos and videos you share.'], ['Do I approve posts before they go live?', 'Yes. You see the monthly calendar and the creatives before anything is posted.'], ['Which platforms do you manage?', '[Confirm: for example Instagram, Facebook and LinkedIn.]']]],
    'meta-ads' => ['group' => 'digital', 'short_name' => 'Meta ads', 'lower_name' => 'Meta ads', 'group_label' => 'DIGITAL · PERFORMANCE', 'scene' => 'ads',
        'hero_bg' => '#0E2440', 'hero_fg' => '#FFFFFF', 'hero_glow' => 'rgba(8,102,255,0.38)', 'hero_line' => 'rgba(255,255,255,0.35)', 'hero_accent' => '#FFFFFF', 'hero_accent_fg' => '#0D1410',
        'promise' => 'Facebook and Instagram ads that bring enquiries and orders, not just likes.', 'good_for' => "Lead generation\nOnline sales\nNew launches",
        'included' => $rows([['Pixel and tracking setup', 'So every enquiry and sale is counted properly.'], ['Audience and funnel plan', 'Who to reach first, and how to bring them back.'], ['Ad creatives', 'Static ads, reels and UGC, made in-house.'], ['Weekly testing', 'New hooks, offers and audiences, every week.'], ['Landing page fixes', 'Our developers fix pages that leak clicks.'], ['Monthly report and call', 'Spend, leads, cost per lead and next steps.']]),
        'steps' => $rows([['Account audit', 'We review your ad account, past ads and website.'], ['Tracking and structure', 'Pixel, events and campaigns set up properly.'], ['Creatives and testing', 'Several ads and audiences run side by side.'], ['Scale what works', 'Budget moves to the winners while we keep testing.']]),
        'proof_title' => "Campaigns we've run.", 'proof_cases' => 'heaven-solar, yantra-design, trilok-infratech', 'proof_placeholders' => [],
        'price_factors' => "Your monthly ad budget, paid directly to Meta\nHow many ad creatives you need each month\nWhether tracking or landing pages need fixing first\nHow many products, cities or audiences you target",
        'related' => 'social-media-management, website-development, crm-and-app-development',
        'faqs' => [['Is the ad budget part of your fee?', 'No. You pay Meta directly for ad spend. Our fee covers planning, creatives and management.'], ['Can you take over our current ad account?', 'Yes. You give us partner access in Meta Business Manager, and the account stays in your name.'], ['What is the minimum ad budget?', '[Confirm the minimum monthly ad budget you recommend.]']]],
    'branding-and-packaging' => ['group' => 'branding', 'short_name' => 'Branding', 'lower_name' => 'branding', 'group_label' => 'BRANDING', 'scene' => 'branding',
        'hero_bg' => '#2EB45D', 'hero_fg' => '#0D1410', 'hero_glow' => 'rgba(255,255,255,0.4)', 'hero_line' => 'rgba(13,20,16,0.4)', 'hero_accent' => '#0D1410', 'hero_accent_fg' => '#FFFFFF',
        'promise' => 'A name, logo, look and packaging that people remember and trust.', 'good_for' => "New brands\nBrand refresh\nProduct launches",
        'included' => $rows([['Brand strategy', 'Who you serve, what you stand for and how you are different.'], ['Logo and identity', 'A mark and a personality your brand speaks with.'], ['Colours and typography', 'A look that stays the same everywhere.'], ['Brand guidelines', 'One rulebook for every post, ad and print.'], ['Packaging design', 'Boxes, pouches and labels that sell on the shelf.'], ['Launch creatives', 'Posts and ads to introduce the new look.']]),
        'steps' => $rows([['Discovery', 'We learn your business, buyers and competitors.'], ['Strategy and direction', 'Positioning and two or three design routes to choose from.'], ['Design and refine', 'Logo, colours, type and packaging, refined with you.'], ['Guidelines and handover', 'All files and a simple rulebook for your team.']]),
        'proof_title' => "Brands we've shaped.", 'proof_cases' => '',
        'proof_placeholders' => $rows([['[Add a branding project]', 'Logo, colours and guidelines for a client brand.'], ['[Add a packaging project]', 'Boxes, pouches or labels you designed.'], ['[Add a brand refresh]', 'Before and after of a refreshed brand.']]),
        'price_factors' => "A new brand, or a refresh of an existing one\nHow many products or packaging variants you have\nWhether you need print-ready files or print support\nHow soon you want to launch",
        'related' => 'product-shoots-and-reels, social-media-management, website-development',
        'faqs' => [['Can you refresh our logo instead of starting over?', 'Yes. We can refresh what you have or start fresh, depending on what your brand needs.'], ['Do you also handle printing?', '[Confirm: do you coordinate printing, or hand over print-ready files?]'], ['How long does a branding project take?', '[Add a typical timeline.]']]],
    'website-development' => ['group' => 'digital', 'short_name' => 'Websites', 'lower_name' => 'websites', 'group_label' => 'DIGITAL · DEVELOPMENT', 'scene' => 'website',
        'hero_bg' => '#F4F6F1', 'hero_fg' => '#0D1410', 'hero_glow' => 'rgba(46,180,93,0.25)', 'hero_line' => 'rgba(13,20,16,0.25)', 'hero_accent' => '#0D1410', 'hero_accent_fg' => '#FFFFFF',
        'promise' => 'Fast, modern websites built and maintained in-house, made to turn visitors into enquiries.', 'good_for' => "Company websites\nProduct catalogues\nLanding pages",
        'included' => $rows([['Sitemap and content plan', 'Which pages you need and what each one says.'], ['Design', 'A look that matches your brand, made mobile-first.'], ['Development', 'Built in-house by our own developers.'], ['Admin panel', 'Edit pages, products and blogs yourself.'], ['SEO basics', 'Clean structure, speed and tags so Google can find you.'], ['Maintenance', 'Updates, backups and fixes after launch.']]),
        'steps' => $rows([['Plan the pages', 'Sitemap, content and features agreed together.'], ['Design', 'Home and key pages designed for your approval.'], ['Build and test', 'Developed, connected and tested on every screen size.'], ['Launch and maintain', 'We go live and keep the site updated.']]),
        'proof_title' => "Websites we've built.", 'proof_cases' => '',
        'proof_placeholders' => $rows([['[Add a website you built]', 'A screenshot of the home page and a link.'], ['[Add a second website]', 'Pick one with a clear before and after.'], ['[Add a landing page]', 'A page made for ads, with its result.']]),
        'price_factors' => "How many pages you need\nCustom features like catalogues, forms or payments\nWhether we write the content and shoot photos\nOngoing maintenance and hosting",
        'related' => 'meta-ads, crm-and-app-development, branding-and-packaging',
        'faqs' => [['Can we update the website ourselves?', 'Yes. We can add an admin panel so your team can edit pages, products and blog posts.'], ['Will it work well on phones?', 'Yes. Every site is designed for mobile first.'], ['Do you host the website too?', '[Confirm hosting and maintenance terms.]']]],
    'product-shoots-and-reels' => ['group' => 'films', 'short_name' => 'Shoots and reels', 'lower_name' => 'shoots and reels', 'group_label' => 'FILMS', 'scene' => 'shoots',
        'hero_bg' => '#0A5A30', 'hero_fg' => '#FFFFFF', 'hero_glow' => 'rgba(242,162,48,0.32)', 'hero_line' => 'rgba(255,255,255,0.4)', 'hero_accent' => '#F2A230', 'hero_accent_fg' => '#0D1410',
        'promise' => 'Photos, reels and films shot by our own crew, at your factory, store or site.', 'good_for' => "Product brands\nFactories and sites\nSocial media and ads",
        'included' => $rows([['Product photography', 'Clean photos of every product, for posts and listings.'], ['Reels and short videos', 'Made for Instagram, Facebook and ads.'], ['Brand and corporate films', 'Your story, from factory floor to founder.'], ['Model and influencer shoots', 'Casting, styling and direction handled for you.'], ['UGC videos', 'Real people using your product, made for ads.'], ['Editing and motion graphics', 'Cuts, captions, music and animated text.']]),
        'steps' => $rows([['Shoot plan', 'Shot list, script and location agreed with you.'], ['Shoot day', 'Our crew shoots on location or in a set-up.'], ['Edit', 'Cuts, colour, captions and music.'], ['Deliver', 'Every size you need, for posts, reels and ads.']]),
        'proof_title' => "Shoots we've done.", 'proof_cases' => 'srk-dairy-farm-and-foods, her-and-he-cosmetics, valleyrides', 'proof_placeholders' => [],
        'price_factors' => "How many products, locations or shoot days\nWhether models or creators are needed\nHow many final photos and videos you need\nTravel for outdoor or out-of-city shoots",
        'related' => 'social-media-management, meta-ads, branding-and-packaging',
        'faqs' => [['Do you shoot outside Surat?', 'Yes. We shoot on location, from factories and sites to Himalayan treks.'], ['Can you find models and creators?', 'Yes. We run casting calls for models, influencers and UGC creators.'], ['Who owns the photos and videos?', '[Confirm usage rights for the final files.]']]],
    'crm-and-app-development' => ['group' => 'growth', 'short_name' => 'CRM and apps', 'lower_name' => 'CRM and apps', 'group_label' => 'GROWTH', 'scene' => 'crm',
        'hero_bg' => '#1F2A44', 'hero_fg' => '#FFFFFF', 'hero_glow' => 'rgba(242,162,48,0.28)', 'hero_line' => 'rgba(255,255,255,0.35)', 'hero_accent' => '#F2A230', 'hero_accent_fg' => '#0D1410',
        'promise' => 'Custom CRMs and apps that track every lead, follow-up and order, built around how your team works.', 'good_for' => "Sales teams\nManufacturers and dealers\nGrowing businesses",
        'included' => $rows([['Lead capture', 'Enquiries from ads, website and calls in one place.'], ['Pipeline and reminders', 'Every lead has a stage and a next follow-up.'], ['Quotes and orders', 'Create and track quotes and orders in the system.'], ['Team roles and access', 'Everyone sees what they need, nothing more.'], ['Reports and dashboards', 'Leads, conversions and team performance at a glance.'], ['Support and updates', 'We keep improving it as your process grows.']]),
        'steps' => $rows([['Map your sales process', 'How a lead comes in, moves and closes today.'], ['Design the screens', 'Simple screens your team will actually use.'], ['Build and connect', 'Developed in-house and connected to your lead sources.'], ['Train your team', 'We set it up with your team and stay on call.']]),
        'proof_title' => "Systems we've built.", 'proof_cases' => '',
        'proof_placeholders' => $rows([['[Add a CRM you built]', 'A screenshot of the lead pipeline or dashboard.'], ['[Add an app you built]', 'What it does and who uses it.'], ['[Add a result]', 'For example, fewer missed follow-ups.']]),
        'price_factors' => "How many people will use it\nWhich lead sources and tools it connects to\nCustom modules like quotes, stock or dispatch\nMoving your existing data in",
        'related' => 'meta-ads, website-development, social-media-management',
        'faqs' => [['Can it connect to our ads and website forms?', '[Confirm which lead sources you connect, for example Meta lead ads and website forms.]'], ['Can our team use it on their phones?', '[Confirm mobile support.]'], ['Who owns the data?', '[Confirm data ownership and export terms.]']]],
];
$pageTitles = ['social-media-management' => 'Social media management', 'meta-ads' => 'Meta ads', 'branding-and-packaging' => 'Branding and packaging',
    'website-development' => 'Website development', 'product-shoots-and-reels' => 'Product shoots and reels', 'crm-and-app-development' => 'CRM and app development'];

$items['services'] = [];
$faqs = [];
foreach ($grid as [$group, $title, $short, $line]) {
    $slug = \Illuminate\Support\Str::slug($title);
    $page = $pages[$slug] ?? null;
    $data = ['group' => $group, 'short_name' => $short, 'one_liner' => $line, 'tile' => $slug, 'in_grid' => true, 'has_page' => $page !== null];
    $items['services'][] = [$slug, $title, $page === null ? $data : array_diff_key($page, ['faqs' => true]) + $data];
}
foreach ($pages as $slug => $page) {
    if (! in_array($pageTitles[$slug], array_column($grid, 1), true)) {
        $items['services'][] = [$slug, $pageTitles[$slug], array_diff_key($page, ['faqs' => true]) + ['in_grid' => false, 'has_page' => true, 'one_liner' => '']];
    }
    foreach ($page['faqs'] as $i => [$q, $a]) {
        $faqs[] = [$slug.'-'.($i + 1), $q, ['answer' => $a, 'group' => $slug]];
    }
}

$items['faqs'] = array_merge([
    ['fixed-packages', 'Do you have fixed packages?', ['group' => 'contact', 'answer' => 'No. We first understand your business and goals, then share a plan and quote made for you, so you only pay for what you need.']],
    ['industries', 'Which industries do you work with?', ['group' => 'contact', 'answer' => 'Manufacturing, food and beverage, retail and services. Our 45+ brands range from precast infrastructure and CNC machines to ghee, skincare and Himalayan treks.']],
    ['shoot-on-location', 'Can you shoot at our factory, store or site?', ['group' => 'contact', 'answer' => 'Yes. Our team shoots reels, product photos and brand films on location, like the site and factory videos we made for Trilok Infratech.']],
    ['only-social-media', 'Do you only do social media?', ['group' => 'contact', 'answer' => 'No. Everything your brand needs is under one roof: branding, digital marketing, lead and sales funnels, websites, CRM and films.']],
    ['where-based', 'Where are you based?', ['group' => 'contact', 'answer' => 'Our office is at Bhatar Trade Center in Surat. We also work with brands in Ahmedabad and across India.']],
    ['get-started', 'How do we get started?', ['group' => 'contact', 'answer' => 'Send us a WhatsApp, call sales on 63544 33194, or build your brief above. We understand your business first, then share a plan.']],
], $faqs);

$items['reels'] = [
    ['first-impression-matters', 'First impression matters', ['brand' => 'Cruisemax Sports', 'tag' => 'ATHLEISURE', 'cover' => 'ekdanta/reel-1']],
    ['vitamin-c-serum-20', 'Vitamin C Serum 20%', ['brand' => 'Her & He Cosmetics', 'tag' => 'SKINCARE', 'cover' => 'ekdanta/reel-2']],
    ['casting-call-zula-house', 'Looking for fresh faces and creative minds', ['brand' => 'The Zula House', 'tag' => 'CASTING CALL', 'cover' => 'ekdanta/reel-3']],
    ['advanced-uv-protection', 'Advanced UV protection', ['brand' => 'Cruisemax Sports', 'tag' => 'PRODUCT FEATURE', 'cover' => 'ekdanta/reel-4']],
    ['influencers-and-ugc-creators', 'Looking for influencers and UGC creators', ['brand' => 'Creator casting call', 'tag' => 'UGC AND INFLUENCERS', 'cover' => 'ekdanta/reel-5']],
];

$items['team-roles'] = array_map(fn ($r) => [\Illuminate\Support\Str::slug($r[0]), $r[0], ['description' => $r[1]]], [
    ['Brand strategists', 'Positioning, naming and the big idea.'],
    ['Designers', 'Logos, packaging, creatives and print.'],
    ['Photo and video crew', 'Shoots at your factory, store or site.'],
    ['Editors and motion designers', 'Reels, ad films and motion graphics.'],
    ['Performance marketers', 'Meta ads, Google, SEO and reports.'],
    ['Developers', 'Websites, CRMs and apps, built in-house.'],
    ['Sales consultants', 'Funnels, scripts and follow-up systems.'],
]);

// Clients: the design's logo order and its four shelves (CATS).
$industries = ['Manufacturing' => [0, 16], 'Food and beverage' => [16, 24], 'Retail' => [24, 32], 'Services' => [32, 44]];
$names = array_merge(...array_values(\Database\Seeders\EkdantaSiteSeeder::LOGOS));
$items['clients'] = [];
foreach ($names as $i => $name) {
    $industry = array_key_first(array_filter($industries, fn ($r) => $i >= $r[0] && $i < $r[1]));
    $slug = \Illuminate\Support\Str::slug($name);
    $items['clients'][] = [$slug, $name, ['logo' => 'ekdanta/client-'.$slug, 'industry' => $industry]];
}

return $items;

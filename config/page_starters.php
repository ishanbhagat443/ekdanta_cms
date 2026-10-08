<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Starter layouts
|--------------------------------------------------------------------------
| Pre-built page layouts offered when someone creates a page, and grouped
| into multi-page "site kits". A new user edits a finished page instead of
| staring at a blank one — the fastest route from install to a real site.
|
| Each starter is a list of sections, in order, with the same shape the
| builder saves: type + the desktop payload + optional style. Everything is
| ordinary CMS content afterwards, so a starter is a starting point, never a
| template the page stays tied to.
|
| Copy here is real, generic-business writing rather than lorem ipsum: it
| reads as a finished page, and it shows the user what belongs in each slot.
| Images are deliberately absent — the Media Library is empty on a fresh
| install, and an empty image frame is a clearer prompt than a broken one.
|
| Field keys must match the section type's schema (App\Services\SectionTypes).
*/

$cta = static fn (string $heading, string $text, string $label, string $url = '/contact-us'): array => [
    'type' => 'cta',
    'name' => 'Closing call to action',
    'data' => [
        'heading' => $heading,
        'subheading' => null,
        'text' => '<p>'.$text.'</p>',
        'alignment' => 'center',
        'buttons' => [
            ['label' => $label, 'url' => $url, 'style' => 'primary-pill'],
        ],
    ],
    'anim' => 'fade-up',
];

return [

    /*
    |----------------------------------------------------------------
    | Single pages
    |----------------------------------------------------------------
    */
    'pages' => [

        'blank' => [
            'label' => 'Blank page',
            'description' => 'An empty page. Add sections yourself.',
            'icon' => 'fas fa-file',
            'sections' => [],
        ],

        'home' => [
            'label' => 'Home',
            'description' => 'Hero, what you offer, proof, and a closing call to action.',
            'icon' => 'fas fa-house',
            'sections' => [
                [
                    'type' => 'hero',
                    'name' => 'Hero',
                    'data' => [
                        'eyebrow' => 'WELCOME',
                        'heading' => 'Work that speaks for itself',
                        'subheading' => 'Tell visitors what you do in one clear line. Keep it short enough to read at a glance.',
                        'height' => 'large',
                        'alignment' => 'center',
                        'overlay_opacity' => 45,
                        'buttons' => [
                            ['label' => 'Get in touch', 'url' => '/contact-us', 'style' => 'primary-pill'],
                            ['label' => 'See our work', 'url' => '#services', 'style' => 'outline-light'],
                        ],
                    ],
                    'anim' => 'fade-up',
                ],
                [
                    'type' => 'features',
                    'name' => 'What we do',
                    'data' => [
                        'heading' => 'What we do',
                        'subheading' => 'Three things worth knowing about working with us.',
                        'layout' => 'grid',
                        'items' => [
                            ['icon' => 'fas fa-compass-drafting', 'title' => 'Considered design', 'text' => 'Every project starts with the problem, not a template.'],
                            ['icon' => 'fas fa-gauge-high', 'title' => 'Built to last', 'text' => 'Fast, accessible, and easy to run once we hand it over.'],
                            ['icon' => 'fas fa-comments', 'title' => 'Straight answers', 'text' => 'Clear timelines, clear pricing, and one person to talk to.'],
                        ],
                    ],
                    'anim' => 'fade-up',
                    'stagger' => 90,
                ],
                [
                    'type' => 'counter',
                    'name' => 'By the numbers',
                    'data' => [
                        'heading' => 'By the numbers',
                        'animate' => true,
                        'counters' => [
                            ['number' => '12', 'suffix' => '+', 'label' => 'Years in business'],
                            ['number' => '240', 'suffix' => '', 'label' => 'Projects delivered'],
                            ['number' => '98', 'suffix' => '%', 'label' => 'Clients who return'],
                        ],
                    ],
                    'anim' => 'fade-up',
                    'stagger' => 80,
                ],
                [
                    'type' => 'testimonials',
                    'name' => 'What clients say',
                    'data' => [
                        'heading' => 'What clients say',
                        'carousel' => false,
                        'items' => [
                            ['name' => 'Priya Sharma', 'role' => 'Founder, Northline', 'quote' => 'They understood the brief in the first meeting and the site has paid for itself twice over.', 'rating' => 5],
                            ['name' => 'Daniel Okafor', 'role' => 'Operations, Kestrel', 'quote' => 'Everything arrived on the day they said it would. Rare, and worth a lot.', 'rating' => 5],
                        ],
                    ],
                    'anim' => 'fade-up',
                    'stagger' => 90,
                ],
                $cta('Ready to start?', 'Tell us what you have in mind and we will come back within one working day.', 'Start a conversation'),
            ],
        ],

        'about' => [
            'label' => 'About',
            'description' => 'Your story, the numbers behind it, and the people.',
            'icon' => 'fas fa-users',
            'sections' => [
                [
                    'type' => 'text-block',
                    'name' => 'Our story',
                    'data' => [
                        'heading' => 'We started with one question',
                        'subheading' => 'Why is good work so hard to buy?',
                        'body' => '<p>Write the short version of how the company began: who started it, what they were trying to fix, and what changed along the way. Two or three paragraphs is plenty — this is the page people skim before they email you.</p>'
                            .'<p>Say what you believe about the work. Specifics beat adjectives: name the standard you hold, the corner you refuse to cut, the kind of client you do your best work for.</p>',
                        'alignment' => 'left',
                        'width' => 'narrow',
                    ],
                    'anim' => 'fade-up',
                ],
                [
                    'type' => 'counter',
                    'name' => 'The numbers',
                    'data' => [
                        'heading' => 'Where we are today',
                        'animate' => true,
                        'counters' => [
                            ['number' => '12', 'suffix' => '+', 'label' => 'Years in business'],
                            ['number' => '18', 'suffix' => '', 'label' => 'People on the team'],
                            ['number' => '4', 'suffix' => '', 'label' => 'Countries served'],
                        ],
                    ],
                    'anim' => 'fade-up',
                    'stagger' => 80,
                ],
                [
                    'type' => 'team',
                    'name' => 'The team',
                    'data' => [
                        'heading' => 'The people you will work with',
                        'subheading' => 'Small team, no handoffs.',
                    ],
                    'anim' => 'fade-up',
                    'stagger' => 90,
                ],
                $cta('Want to work together?', 'We take on a handful of projects at a time so each one gets proper attention.', 'Get in touch'),
            ],
        ],

        'services' => [
            'label' => 'Services',
            'description' => 'What you offer, how it works, and the usual questions.',
            'icon' => 'fas fa-briefcase',
            'sections' => [
                [
                    'type' => 'text-block',
                    'name' => 'Intro',
                    'data' => [
                        'heading' => 'What we can do for you',
                        'body' => '<p>One paragraph on the kind of work you take on and who it suits. Be specific about what you are good at — saying no to the wrong project is what makes the right one go well.</p>',
                        'alignment' => 'center',
                        'width' => 'narrow',
                    ],
                    'anim' => 'fade-up',
                ],
                [
                    'type' => 'cards',
                    'name' => 'Services',
                    'data' => [
                        'heading' => 'How we help',
                        'columns' => '3',
                        'cards' => [
                            ['title' => 'Strategy', 'text' => 'Work out what to build and why, before anyone opens a design tool.', 'link_label' => 'Learn more', 'link_url' => '/contact-us'],
                            ['title' => 'Design', 'text' => 'Interfaces people understand without being taught.', 'link_label' => 'Learn more', 'link_url' => '/contact-us'],
                            ['title' => 'Build', 'text' => 'Fast, accessible sites you can run yourself afterwards.', 'link_label' => 'Learn more', 'link_url' => '/contact-us'],
                        ],
                    ],
                    'anim' => 'fade-up',
                    'stagger' => 90,
                ],
                [
                    'type' => 'process-steps',
                    'name' => 'How it works',
                    'data' => [
                        'heading' => 'How it works',
                        'subheading' => 'No surprises, at any stage.',
                        'items' => [
                            ['title' => 'Talk it through', 'text' => 'A call to understand the problem and whether we are the right fit.'],
                            ['title' => 'Agree the shape', 'text' => 'A written plan with scope, price and dates before any work starts.'],
                            ['title' => 'Build and review', 'text' => 'You see progress weekly and can change direction while it is cheap.'],
                            ['title' => 'Hand over', 'text' => 'You get the keys, the training, and a month of support.'],
                        ],
                    ],
                    'anim' => 'fade-up',
                    'stagger' => 100,
                ],
                [
                    'type' => 'faq',
                    'name' => 'Questions',
                    'data' => [
                        'heading' => 'Questions we get asked',
                        'schema_enabled' => true,
                        'items' => [
                            ['question' => 'How long does a project take?', 'answer' => 'Most sites take six to ten weeks from first call to launch. We will give you a date in writing before we start.'],
                            ['question' => 'What does it cost?', 'answer' => 'Projects usually land between two figures you should replace with your own. Every quote is fixed, so the number you agree is the number you pay.'],
                            ['question' => 'Can we make changes ourselves afterwards?', 'answer' => 'Yes. Everything is editable from the admin panel, and we train your team before handover.'],
                        ],
                    ],
                    'anim' => 'fade-up',
                ],
                $cta('Tell us about your project', 'A short message is enough to start. We will ask the right questions from there.', 'Get in touch'),
            ],
        ],

        'contact' => [
            'label' => 'Contact',
            'description' => 'How to reach you, plus a form that lands in Enquiries.',
            'icon' => 'fas fa-envelope',
            'sections' => [
                [
                    'type' => 'text-block',
                    'name' => 'Intro',
                    'data' => [
                        'heading' => 'Get in touch',
                        'body' => '<p>Tell us what you are working on. We read everything that comes in and reply within one working day.</p>',
                        'alignment' => 'center',
                        'width' => 'narrow',
                    ],
                    'anim' => 'fade-up',
                ],
                [
                    'type' => 'contact-details',
                    'name' => 'Contact details',
                    'data' => [
                        'heading' => 'Ways to reach us',
                        'card_shadow' => true,
                    ],
                    'anim' => 'fade-up',
                    'stagger' => 80,
                ],
                [
                    'type' => 'form',
                    'name' => 'Contact form',
                    'data' => [
                        'heading' => 'Send a message',
                        'subheading' => 'We will come back to you by email.',
                        'form_slug' => 'contact-form',
                    ],
                    'anim' => 'fade-up',
                ],
                [
                    'type' => 'map',
                    'name' => 'Where we are',
                    'data' => ['heading' => 'Find us'],
                    'anim' => 'fade-in',
                ],
            ],
        ],

        'landing' => [
            'label' => 'Landing page',
            'description' => 'One offer, one action. Built to convert.',
            'icon' => 'fas fa-bullseye',
            'sections' => [
                [
                    'type' => 'hero',
                    'name' => 'Offer',
                    'data' => [
                        'eyebrow' => 'LIMITED AVAILABILITY',
                        'heading' => 'The one thing this page is selling',
                        'subheading' => 'Say who it is for and what they get, in a sentence a stranger understands.',
                        'height' => 'large',
                        'alignment' => 'center',
                        'overlay_opacity' => 50,
                        'buttons' => [
                            ['label' => 'Claim your place', 'url' => '#signup', 'style' => 'primary-pill'],
                        ],
                    ],
                    'anim' => 'fade-up',
                ],
                [
                    'type' => 'features',
                    'name' => 'What you get',
                    'data' => [
                        'heading' => 'What you get',
                        'layout' => 'grid',
                        'items' => [
                            ['icon' => 'fas fa-circle-check', 'title' => 'Benefit, not feature', 'text' => 'Describe the outcome the reader cares about.'],
                            ['icon' => 'fas fa-circle-check', 'title' => 'Answer the doubt', 'text' => 'Name the reason people hesitate, then remove it.'],
                            ['icon' => 'fas fa-circle-check', 'title' => 'Make it concrete', 'text' => 'A number, a timeframe, a guarantee — something checkable.'],
                        ],
                    ],
                    'anim' => 'fade-up',
                    'stagger' => 90,
                ],
                [
                    'type' => 'testimonials',
                    'name' => 'Proof',
                    'data' => [
                        'heading' => 'People who already did this',
                        'items' => [
                            ['name' => 'Anita Rao', 'role' => 'Verified customer', 'quote' => 'I expected the usual runaround and got a straight answer instead.', 'rating' => 5],
                        ],
                    ],
                    'anim' => 'fade-up',
                ],
                [
                    'type' => 'faq',
                    'name' => 'Objections',
                    'data' => [
                        'heading' => 'Before you decide',
                        'schema_enabled' => true,
                        'items' => [
                            ['question' => 'What happens after I sign up?', 'answer' => 'Describe the very next step, exactly. Uncertainty is what stops people.'],
                            ['question' => 'Can I change my mind?', 'answer' => 'State the refund or cancellation terms in plain words.'],
                        ],
                    ],
                    'anim' => 'fade-up',
                ],
                $cta('Ready when you are', 'It takes two minutes and there is nothing to pay today.', 'Claim your place', '#signup'),
            ],
        ],

        'portfolio' => [
            'label' => 'Portfolio',
            'description' => 'A gallery of work with short write-ups.',
            'icon' => 'fas fa-images',
            'sections' => [
                [
                    'type' => 'text-block',
                    'name' => 'Intro',
                    'data' => [
                        'heading' => 'Selected work',
                        'body' => '<p>A line about the kind of work you show here, and what a visitor should look for in it.</p>',
                        'alignment' => 'center',
                        'width' => 'narrow',
                    ],
                    'anim' => 'fade-up',
                ],
                [
                    'type' => 'gallery',
                    'name' => 'Gallery',
                    'data' => ['heading' => 'Recent projects'],
                    'anim' => 'fade-up',
                    'stagger' => 70,
                ],
                [
                    'type' => 'cards',
                    'name' => 'Case studies',
                    'data' => [
                        'heading' => 'Case studies',
                        'columns' => '3',
                        'cards' => [
                            ['title' => 'Project name', 'text' => 'One line on the problem, one on the result. Numbers if you have them.', 'link_label' => 'Read the story', 'link_url' => '#'],
                            ['title' => 'Project name', 'text' => 'One line on the problem, one on the result. Numbers if you have them.', 'link_label' => 'Read the story', 'link_url' => '#'],
                            ['title' => 'Project name', 'text' => 'One line on the problem, one on the result. Numbers if you have them.', 'link_label' => 'Read the story', 'link_url' => '#'],
                        ],
                    ],
                    'anim' => 'fade-up',
                    'stagger' => 90,
                ],
                $cta('Have something similar in mind?', 'We are usually booking a month ahead.', 'Start a conversation'),
            ],
        ],

        'pricing' => [
            'label' => 'Pricing',
            'description' => 'Three tiers, then the questions people ask about money.',
            'icon' => 'fas fa-tags',
            'sections' => [
                [
                    'type' => 'text-block',
                    'name' => 'Intro',
                    'data' => [
                        'heading' => 'Simple pricing',
                        'body' => '<p>No hidden fees, no per-seat surprises. Pick the tier that matches where you are today — you can move later.</p>',
                        'alignment' => 'center',
                        'width' => 'narrow',
                    ],
                    'anim' => 'fade-up',
                ],
                [
                    'type' => 'cards',
                    'name' => 'Plans',
                    'data' => [
                        'heading' => 'Plans',
                        'columns' => '3',
                        'cards' => [
                            ['title' => 'Starter', 'text' => 'For a first site. Everything you need to be online and findable.', 'link_label' => 'Choose Starter', 'link_url' => '/contact-us'],
                            ['title' => 'Growth', 'text' => 'For a business that is scaling. Adds the parts you outgrow Starter for.', 'link_label' => 'Choose Growth', 'link_url' => '/contact-us'],
                            ['title' => 'Custom', 'text' => 'For anything that does not fit a box. We will quote it properly.', 'link_label' => 'Talk to us', 'link_url' => '/contact-us'],
                        ],
                    ],
                    'anim' => 'fade-up',
                    'stagger' => 90,
                ],
                [
                    'type' => 'faq',
                    'name' => 'Pricing questions',
                    'data' => [
                        'heading' => 'Questions about pricing',
                        'schema_enabled' => true,
                        'items' => [
                            ['question' => 'Are there setup fees?', 'answer' => 'Say yes or no plainly, and what the number is if yes.'],
                            ['question' => 'Can I change plan later?', 'answer' => 'Explain how and when, and whether anything is lost in the move.'],
                            ['question' => 'What if it is not right for me?', 'answer' => 'State the guarantee or trial period, and how someone claims it.'],
                        ],
                    ],
                    'anim' => 'fade-up',
                ],
                $cta('Not sure which fits?', 'Tell us the situation and we will point you at the right one — even if it is the cheapest.', 'Ask us'),
            ],
        ],

        'faq' => [
            'label' => 'FAQ',
            'description' => 'A searchable list of answers, marked up for search engines.',
            'icon' => 'fas fa-circle-question',
            'sections' => [
                [
                    'type' => 'text-block',
                    'name' => 'Intro',
                    'data' => [
                        'heading' => 'Frequently asked questions',
                        'body' => '<p>If the answer you need is not here, send us a message and we will add it.</p>',
                        'alignment' => 'center',
                        'width' => 'narrow',
                    ],
                    'anim' => 'fade-up',
                ],
                [
                    'type' => 'faq',
                    'name' => 'Questions',
                    'data' => [
                        'heading' => 'Answers',
                        'schema_enabled' => true,
                        'items' => [
                            ['question' => 'How do I get started?', 'answer' => 'Describe the first step and how long it takes.'],
                            ['question' => 'How long does delivery take?', 'answer' => 'Give a real range rather than "it depends".'],
                            ['question' => 'What do you need from me?', 'answer' => 'List what you ask the customer to provide, so nobody is caught out.'],
                            ['question' => 'How do I get support?', 'answer' => 'Say where to write and how quickly you answer.'],
                        ],
                    ],
                    'anim' => 'fade-up',
                ],
                $cta('Still stuck?', 'Ask us directly — a real person reads every message.', 'Ask a question'),
            ],
        ],

        'team' => [
            'label' => 'Team',
            'description' => 'The people, with a line about how you work.',
            'icon' => 'fas fa-user-group',
            'sections' => [
                [
                    'type' => 'text-block',
                    'name' => 'Intro',
                    'data' => [
                        'heading' => 'The people behind the work',
                        'body' => '<p>A short paragraph on how the team is put together and what it means for someone working with you.</p>',
                        'alignment' => 'center',
                        'width' => 'narrow',
                    ],
                    'anim' => 'fade-up',
                ],
                [
                    'type' => 'team',
                    'name' => 'Team grid',
                    'data' => ['heading' => 'Meet the team'],
                    'anim' => 'fade-up',
                    'stagger' => 90,
                ],
                [
                    'type' => 'features',
                    'name' => 'How we work',
                    'data' => [
                        'heading' => 'How we work',
                        'layout' => 'grid',
                        'items' => [
                            ['icon' => 'fas fa-handshake', 'title' => 'One point of contact', 'text' => 'You always know who to call.'],
                            ['icon' => 'fas fa-calendar-check', 'title' => 'Dates we keep', 'text' => 'If something slips you hear it from us first.'],
                            ['icon' => 'fas fa-lightbulb', 'title' => 'Opinions included', 'text' => 'We will tell you when we think there is a better way.'],
                        ],
                    ],
                    'anim' => 'fade-up',
                    'stagger' => 90,
                ],
                $cta('Want to join us?', 'We hire rarely, and always for attitude first.', 'See open roles', '/contact-us'),
            ],
        ],
    ],

    /*
    |----------------------------------------------------------------
    | Site kits — several pages created together
    |----------------------------------------------------------------
    | `menu` lists the slugs, in order, to add to the main navigation.
    */
    'kits' => [

        'small-business' => [
            'label' => 'Small business',
            'description' => 'Home, About, Services and Contact — the four pages most sites need.',
            'icon' => 'fas fa-store',
            'pages' => [
                ['starter' => 'home', 'title' => 'Home', 'slug' => 'home'],
                ['starter' => 'about', 'title' => 'About Us', 'slug' => 'about-us'],
                ['starter' => 'services', 'title' => 'Services', 'slug' => 'services'],
                ['starter' => 'contact', 'title' => 'Contact Us', 'slug' => 'contact-us'],
            ],
            'menu' => ['home', 'about-us', 'services', 'contact-us'],
        ],

        'portfolio-site' => [
            'label' => 'Portfolio',
            'description' => 'Home, Portfolio, About and Contact — for showing work.',
            'icon' => 'fas fa-palette',
            'pages' => [
                ['starter' => 'home', 'title' => 'Home', 'slug' => 'home'],
                ['starter' => 'portfolio', 'title' => 'Work', 'slug' => 'work'],
                ['starter' => 'about', 'title' => 'About', 'slug' => 'about'],
                ['starter' => 'contact', 'title' => 'Contact', 'slug' => 'contact'],
            ],
            'menu' => ['home', 'work', 'about', 'contact'],
        ],

        'service-business' => [
            'label' => 'Service business',
            'description' => 'Home, Services, Pricing, FAQ and Contact — for selling a service.',
            'icon' => 'fas fa-screwdriver-wrench',
            'pages' => [
                ['starter' => 'home', 'title' => 'Home', 'slug' => 'home'],
                ['starter' => 'services', 'title' => 'Services', 'slug' => 'services'],
                ['starter' => 'pricing', 'title' => 'Pricing', 'slug' => 'pricing'],
                ['starter' => 'faq', 'title' => 'FAQ', 'slug' => 'faq'],
                ['starter' => 'contact', 'title' => 'Contact', 'slug' => 'contact-us'],
            ],
            'menu' => ['home', 'services', 'pricing', 'faq', 'contact-us'],
        ],
    ],
];

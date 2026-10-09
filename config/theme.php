<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Theme Customizer schema
|--------------------------------------------------------------------------
| Single source of truth for the Theme Customizer. Each group renders as a
| tab in /admin/theme-customizer; each field renders by "type" and
| validates by "rules". Adding a field here is all that is needed — no
| migration, no view change. Values persist as flat "group.key" rows in
| theme_settings (mirrors config/settings.php exactly).
|
| Field types: text, color, select, number, range, textarea, code, image,
|              toggle, font (Google Fonts + local uploads)
*/

return [

    /*
    |----------------------------------------------------------------
    | Typography — one row per role, three responsive breakpoints
    |----------------------------------------------------------------
    | Each role gets: {role}_family, {role}_weight, {role}_size_desktop/
    | tablet/mobile, {role}_letter_spacing, {role}_line_height,
    | {role}_transform. Defaults match the brief exactly.
    */
    'typography' => [
        'label' => 'Typography',
        'icon' => 'fas fa-font',
        'roles' => [
            'section_heading' => ['label' => 'Section Heading', 'default_family' => 'Lato', 'default_size' => 42], // design scale (Home sections)
            // "Runiga" (as given in the brief) is not a published Google Font.
            // Defaults to a serif system stack until the admin uploads it
            // locally (Typography → Local Font Upload) or swaps to a real family.
            'main_heading' => ['label' => 'Main Heading (H1)', 'default_family' => 'Georgia, "Times New Roman", serif', 'default_size' => 68], // design 02 Hero.png scale (~86px @1920)
            'sub_heading' => ['label' => 'Sub Heading', 'default_family' => 'Poppins', 'default_size' => 22], // design 02 scale
            'body_text' => ['label' => 'Body Text', 'default_family' => 'Poppins', 'default_size' => 16],
            'paragraph' => ['label' => 'Paragraph', 'default_family' => 'Poppins', 'default_size' => 16],
            'buttons' => ['label' => 'Buttons', 'default_family' => 'Poppins', 'default_size' => 15],
            'navigation' => ['label' => 'Navigation', 'default_family' => 'Poppins', 'default_size' => 17], // design 01 Header.png scale
            'footer_text' => ['label' => 'Footer Text', 'default_family' => 'Poppins', 'default_size' => 14],
            'sidebar' => ['label' => 'Sidebar', 'default_family' => 'Poppins', 'default_size' => 14],
            'form_labels' => ['label' => 'Form Labels', 'default_family' => 'Poppins', 'default_size' => 14],
            'form_inputs' => ['label' => 'Form Inputs', 'default_family' => 'Poppins', 'default_size' => 15],
            'tables' => ['label' => 'Tables', 'default_family' => 'Poppins', 'default_size' => 14],
            'testimonials' => ['label' => 'Testimonials', 'default_family' => 'Poppins', 'default_size' => 16],
            'blog_content' => ['label' => 'Blog Content', 'default_family' => 'Poppins', 'default_size' => 16],
        ],
        // CSS custom property this role's font-family feeds (see resources/css/theme-vars stub in ThemeService).
        'weights' => ['300' => 'Light', '400' => 'Regular', '500' => 'Medium', '600' => 'SemiBold', '700' => 'Bold', '800' => 'ExtraBold'],
        'transforms' => ['none' => 'None', 'uppercase' => 'UPPERCASE', 'capitalize' => 'Capitalize', 'lowercase' => 'lowercase'],
        // Curated Google Fonts list shown in the picker (admin can still type any family name).
        'google_fonts' => [
            'Poppins', 'Lato', 'Playfair Display', 'Montserrat', 'Inter', 'Roboto', 'Open Sans',
            'Nunito', 'Raleway', 'Merriweather', 'Cormorant Garamond', 'Work Sans', 'DM Sans', 'Rubik',
        ],
    ],

    /*
    |----------------------------------------------------------------
    | Color Management
    |----------------------------------------------------------------
    */
    'colors' => [
        'label' => 'Colors',
        'icon' => 'fas fa-palette',
        'fields' => [
            // Starter palette — Deep Navy + Warm Gold. No gradients.
            'primary' => ['label' => 'Primary Color', 'default' => '#111827'],
            'secondary' => ['label' => 'Secondary Color', 'default' => '#4b5563'],
            'accent' => ['label' => 'Accent Color', 'default' => '#4b5563'],
            'success' => ['label' => 'Success', 'default' => '#198754'],
            'warning' => ['label' => 'Warning', 'default' => '#ffc107'],
            'danger' => ['label' => 'Danger', 'default' => '#dc3545'],
            'info' => ['label' => 'Info', 'default' => '#0dcaf0'],
            'dark' => ['label' => 'Dark', 'default' => '#0b0f19'],
            'light' => ['label' => 'Light', 'default' => '#f3f4f6'],
            'background' => ['label' => 'Background', 'default' => '#ffffff'],
            'section_background' => ['label' => 'Section Background', 'default' => '#f7f8fa'],
            'card_background' => ['label' => 'Card Background', 'default' => '#ffffff'],
            'button_bg' => ['label' => 'Button Background', 'default' => '#111827'],
            'button_text' => ['label' => 'Button Text', 'default' => '#ffffff'],
            'button_hover_bg' => ['label' => 'Button Hover Background', 'default' => '#4b5563'],
            'button_hover_text' => ['label' => 'Button Hover Text', 'default' => '#0b0f19'],
            'border_color' => ['label' => 'Border Color', 'default' => '#eceef1'],
            'text_color' => ['label' => 'Text Color', 'default' => '#2a3550'],
            'heading_color' => ['label' => 'Heading Color', 'default' => '#111827'],
            'footer_bg' => ['label' => 'Footer Background', 'default' => '#0b0f19'],
            'footer_text' => ['label' => 'Footer Text Color', 'default' => '#d8dee9'],
            'header_bg' => ['label' => 'Header Background', 'default' => '#ffffff'],
            'header_text' => ['label' => 'Header Text Color', 'default' => '#111827'],
            'link_color' => ['label' => 'Link Color', 'default' => '#111827'],
            'link_hover_color' => ['label' => 'Link Hover Color', 'default' => '#4b5563'],
        ],
    ],

    /*
    |----------------------------------------------------------------
    | Buttons
    |----------------------------------------------------------------
    */
    'buttons' => [
        'label' => 'Buttons',
        'icon' => 'fas fa-square',
        'fields' => [
            'primary_style' => ['label' => 'Primary Button Style', 'type' => 'select', 'default' => 'filled', 'options' => ['filled' => 'Filled', 'outline' => 'Outline', 'gradient' => 'Gradient', 'link' => 'Text Link']],
            'secondary_style' => ['label' => 'Secondary Button Style', 'type' => 'select', 'default' => 'outline', 'options' => ['filled' => 'Filled', 'outline' => 'Outline', 'gradient' => 'Gradient', 'link' => 'Text Link']],
            'border_radius' => ['label' => 'Border Radius (px)', 'type' => 'number', 'default' => 8, 'min' => 0, 'max' => 50],
            'padding_x' => ['label' => 'Horizontal Padding (px)', 'type' => 'number', 'default' => 24, 'min' => 0, 'max' => 80],
            'padding_y' => ['label' => 'Vertical Padding (px)', 'type' => 'number', 'default' => 12, 'min' => 0, 'max' => 40],
            'shadow' => ['label' => 'Shadow', 'type' => 'select', 'default' => 'none', 'options' => ['none' => 'None', 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large']],
            'hover_animation' => ['label' => 'Hover Animation', 'type' => 'select', 'default' => 'brighten', 'options' => ['none' => 'None', 'brighten' => 'Brighten', 'lift' => 'Lift', 'scale' => 'Scale', 'shrink' => 'Shrink']],
            'gradient_from' => ['label' => 'Gradient Start', 'type' => 'color', 'default' => '#111827'],
            'gradient_to' => ['label' => 'Gradient End', 'type' => 'color', 'default' => '#4b5563'],
            'icon_position' => ['label' => 'Icon Position', 'type' => 'select', 'default' => 'right', 'options' => ['none' => 'No Icon', 'left' => 'Left of Text', 'right' => 'Right of Text']],
            // Task 3: free-form CSS applied to every button the CMS renders
            // (theme buttons, Button Builder styles, legacy gradient CTAs).
            'custom_css' => ['label' => 'Button Custom CSS (applies to every CMS button, e.g. .btn { border-radius: 40px; })', 'type' => 'code', 'lang' => 'css'],
        ],
    ],

    /*
    |----------------------------------------------------------------
    | Spacing — per-breakpoint values
    |----------------------------------------------------------------
    */
    'spacing' => [
        'label' => 'Spacing',
        'icon' => 'fas fa-arrows-alt-v',
        'fields' => [
            'section_padding_desktop' => ['label' => 'Section Padding — Desktop (px)', 'type' => 'number', 'default' => 80, 'min' => 0, 'max' => 300],
            'section_padding_tablet' => ['label' => 'Section Padding — Tablet (px)', 'type' => 'number', 'default' => 56, 'min' => 0, 'max' => 300],
            'section_padding_mobile' => ['label' => 'Section Padding — Mobile (px)', 'type' => 'number', 'default' => 40, 'min' => 0, 'max' => 300],
            'container_width' => ['label' => 'Container Max Width (px)', 'type' => 'number', 'default' => 1320, 'min' => 960, 'max' => 1920],
            'content_width' => ['label' => 'Content Max Width (px)', 'type' => 'number', 'default' => 800, 'min' => 480, 'max' => 1600],
            'element_margin' => ['label' => 'Element Margin (px)', 'type' => 'number', 'default' => 24, 'min' => 0, 'max' => 120],
            'card_spacing' => ['label' => 'Card Spacing (px)', 'type' => 'number', 'default' => 24, 'min' => 0, 'max' => 120],
            'grid_gap_desktop' => ['label' => 'Grid Gap — Desktop (px)', 'type' => 'number', 'default' => 24, 'min' => 0, 'max' => 120],
            'grid_gap_tablet' => ['label' => 'Grid Gap — Tablet (px)', 'type' => 'number', 'default' => 16, 'min' => 0, 'max' => 120],
            'grid_gap_mobile' => ['label' => 'Grid Gap — Mobile (px)', 'type' => 'number', 'default' => 12, 'min' => 0, 'max' => 120],
            'container_padding_desktop' => ['label' => 'Container Padding — Desktop (px)', 'type' => 'number', 'default' => 24, 'min' => 0, 'max' => 120],
            'container_padding_tablet' => ['label' => 'Container Padding — Tablet (px)', 'type' => 'number', 'default' => 20, 'min' => 0, 'max' => 120],
            'container_padding_mobile' => ['label' => 'Container Padding — Mobile (px)', 'type' => 'number', 'default' => 16, 'min' => 0, 'max' => 120],
        ],
    ],

    /*
    |----------------------------------------------------------------
    | Border Radius — per surface
    |----------------------------------------------------------------
    */
    'radius' => [
        'label' => 'Border Radius',
        'icon' => 'fas fa-vector-square',
        'fields' => [
            'global' => ['label' => 'Global Radius (px)', 'type' => 'number', 'default' => 8, 'min' => 0, 'max' => 100],
            'buttons' => ['label' => 'Buttons (px)', 'type' => 'number', 'default' => 8, 'min' => 0, 'max' => 60],
            'cards' => ['label' => 'Cards (px)', 'type' => 'number', 'default' => 12, 'min' => 0, 'max' => 60],
            'inputs' => ['label' => 'Inputs (px)', 'type' => 'number', 'default' => 6, 'min' => 0, 'max' => 60],
            'images' => ['label' => 'Images (px)', 'type' => 'number', 'default' => 10, 'min' => 0, 'max' => 60],
            'sections' => ['label' => 'Sections (px)', 'type' => 'number', 'default' => 0, 'min' => 0, 'max' => 60],
            'tables' => ['label' => 'Tables (px)', 'type' => 'number', 'default' => 8, 'min' => 0, 'max' => 60],
        ],
    ],

    /*
    |----------------------------------------------------------------
    | Shadows — per surface
    |----------------------------------------------------------------
    */
    'shadows' => [
        'label' => 'Shadows',
        'icon' => 'fas fa-clone',
        'levels' => ['none' => 'None', 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large', 'xl' => 'Extra Large'],
        'fields' => [
            'cards' => ['label' => 'Cards', 'type' => 'select', 'default' => 'sm'],
            'buttons' => ['label' => 'Buttons', 'type' => 'select', 'default' => 'none'],
            'dropdowns' => ['label' => 'Dropdowns', 'type' => 'select', 'default' => 'md'],
            'images' => ['label' => 'Images', 'type' => 'select', 'default' => 'none'],
            'navigation' => ['label' => 'Navigation', 'type' => 'select', 'default' => 'sm'],
        ],
    ],

    /*
    |----------------------------------------------------------------
    | Animations
    |----------------------------------------------------------------
    */
    'animations' => [
        'label' => 'Animations',
        'icon' => 'fas fa-magic',
        // The Motion Library's entrance presets: one source of truth.
        'types' => \App\Support\SectionMotion::ENTRANCE,
        'fields' => [
            'enabled' => ['label' => 'Enable Animations', 'type' => 'toggle', 'default' => true,
                'hint' => 'Off = "Reduce motion" for every visitor: the whole site shows its final, still state, with no pinned scrolling and no loops.'],
            'default_type' => ['label' => 'Default Animation', 'type' => 'select', 'default' => 'none',
                'rules' => ['nullable', 'in:'.implode(',', array_keys(\App\Support\SectionMotion::ENTRANCE))],
                'hint' => 'The animation a newly added page section starts with. Each section can change it.'],
            'duration' => ['label' => 'Duration (ms)', 'type' => 'number', 'default' => 600, 'min' => 100, 'max' => 3000,
                'hint' => 'Starting speed for the default animation.'],
            'delay' => ['label' => 'Delay (ms)', 'type' => 'number', 'default' => 0, 'min' => 0, 'max' => 3000,
                'hint' => 'Starting delay for the default animation.'],
        ],
    ],

    /*
    |----------------------------------------------------------------
    | Custom CSS — per breakpoint
    |----------------------------------------------------------------
    */
    'custom_code' => [
        'label' => 'Custom CSS/JS',
        'icon' => 'fas fa-code',
        'fields' => [
            'css_variables' => ['label' => 'Global CSS Variables (one per line, e.g. --brand-accent: #4b5563)', 'type' => 'code', 'lang' => 'css'],
            'css_global' => ['label' => 'Global CSS', 'type' => 'code', 'lang' => 'css'],
            'css_desktop' => ['label' => 'Desktop CSS (min-width: 992px)', 'type' => 'code', 'lang' => 'css'],
            'css_tablet' => ['label' => 'Tablet CSS (768px–991px)', 'type' => 'code', 'lang' => 'css'],
            'css_mobile' => ['label' => 'Mobile CSS (max-width: 767px)', 'type' => 'code', 'lang' => 'css'],
            // Scripts run on every public page: Developer Mode only (see ThemeCustomizerController::update).
            'js_header' => ['label' => 'Header JS (before </head>)', 'type' => 'code', 'lang' => 'javascript', 'developer' => true],
            'js_footer' => ['label' => 'Footer JS (before </body>)', 'type' => 'code', 'lang' => 'javascript', 'developer' => true],
            'js_global' => ['label' => 'Global JS', 'type' => 'code', 'lang' => 'javascript', 'developer' => true],
        ],
    ],

    /*
    |----------------------------------------------------------------
    | Dark Mode
    |----------------------------------------------------------------
    */
    'dark_mode' => [
        'label' => 'Dark Mode',
        'icon' => 'fas fa-moon',
        'fields' => [
            'enabled' => ['label' => 'Enable Dark Mode Toggle', 'type' => 'toggle', 'default' => false],
            'background' => ['label' => 'Dark Background', 'default' => '#0f1420'],
            'section_background' => ['label' => 'Dark Section Background', 'default' => '#161c2c'],
            'card_background' => ['label' => 'Dark Card Background', 'default' => '#1c2333'],
            'text_color' => ['label' => 'Dark Text Color', 'default' => '#e5e7eb'],
            'heading_color' => ['label' => 'Dark Heading Color', 'default' => '#ffffff'],
            'border_color' => ['label' => 'Dark Border Color', 'default' => '#2a3247'],
        ],
    ],
];

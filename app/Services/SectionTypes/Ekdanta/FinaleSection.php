<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Every page's closing: "become a BIG BRAND!", the contact card and the
 * footer line (design: j-contact / m-contact). Phone numbers (grouped by
 * label), email and the first office's address come from Settings →
 * Business. Kept as one saved, synced
 * block ("Add a saved block"), so it is edited once; it replaces the site
 * footer on any page it is on.
 */
class FinaleSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'finale';
    }

    protected function anchor(): string
    {
        return 'contact';
    }

    public function label(): string
    {
        return 'Ekdanta · Big Brand Finale';
    }

    public function hidesFooter(): bool
    {
        return true;
    }

    protected function content(): array
    {
        return [
            $this->text('line_1', 'First line', "Now it's your turn to", 120),
            $this->text('line_2', 'Second line', 'become a', 60),
            $this->lines('big', 'Big words', "BIG\nBRAND!", 60),
            $this->image('logo', 'Logo in the card', 'ekdanta/logo-colour'),
            $this->text('logo_alt', 'Logo description', 'Ekdanta — Your Branding Partner', 120, false),
            $this->text('email_label', 'Email label', 'EMAIL', 40),
            $this->text('address_label', 'Address label', 'VISIT', 40),
            $this->rows('buttons', 'Buttons (the first is filled)', [
                $this->text('label', 'Label', null, 40),
                $this->link('link', 'Link'),
            ], [
                ['label' => 'WhatsApp us', 'link' => 'wa:'],
                ['label' => 'Get directions', 'link' => 'https://share.google/EAOxrObclZeY4QkQR'],
                ['label' => 'Instagram', 'link' => 'https://www.instagram.com/ekdanta.in/'],
            ], 4),
            $this->text('footer_left', 'Footer line, left ({year} = this year, {legal_line} = Settings → Business)', '© {year} Ekdanta Branding and Marketing, {legal_line} Surat and Ahmedabad.', 200),
            $this->text('footer_right', 'Footer line, right', 'Aapki branding & marketing ke विघ्नहर्ता', 200),
            ['key' => 'menu_id', 'label' => 'Footer: page links (a menu)', 'type' => 'menu', 'device' => false, 'rules' => ['nullable', 'integer', 'exists:menus,id'],
                'hint' => 'Wide screens show its links on the left of the footer; phones skip them (the menu is a tap away).'],
            $this->text('privacy_label', 'Footer: Privacy link text', 'Privacy Policy', 40),
            $this->link('privacy_link', 'Footer: Privacy link goes to', '/privacy-policy'),
            $this->text('terms_label', 'Footer: Terms link text', 'Terms of Service', 40),
            $this->link('terms_link', 'Footer: Terms link goes to', '/terms-of-service'),
            $this->text('cookie_label', 'Footer: "Cookie settings" button (brings the cookie bar back; empty = none)', 'Cookie settings', 40),
            $this->text('pause_label', '"Pause animations" button (empty = none)', 'Pause animations', 40),
        ];
    }

    /** Phones and tablets held upright: three footer lines (links, legal line, cities and tagline). */
    public function deviceDefaults(): array
    {
        return ['mobile' => [
            'footer_left' => '© {year} Ekdanta Branding and Marketing, {legal_line}',
            'footer_right' => 'Surat and Ahmedabad · Aapki branding & marketing ke विघ्नहर्ता',
        ]];
    }

    /** Screen fit: parts its automatic rule places wrongly (see SectionType::fitParts). */
    public function fitParts(): array
    {
        return [
            '[data-m="end-foot"]' => 'W',
        ];
    }
}

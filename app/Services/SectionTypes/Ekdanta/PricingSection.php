<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Services: no fixed packages. Generic package cards slide in, get stamped
 * and tumble away; a plan made for you rises in their place (design:
 * Services s-price).
 */
class PricingSection extends EkdantaSection
{
    public const ICONS = ['goal' => 'Target', 'calendar' => 'Calendar', 'budget' => 'Rupee', 'tools' => 'Wrench'];

    protected function slug(): string
    {
        return 'pricing';
    }

    protected function anchor(): string
    {
        return 's-price';
    }

    public function label(): string
    {
        return 'Ekdanta · Pricing (No Fixed Packages)';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'PRICING', 40),
            $this->text('heading', 'Heading, line 1', 'Har brand alag.', 80),
            $this->text('heading_2', 'Heading, line 2 (green)', 'Toh plan bhi alag.', 80),
            $this->lines('text', 'Text', "We don't sell fixed packages. After a free consultation, we send a clear monthly plan and quote built around what your brand actually needs.", 400),
            $this->text('factors_label', 'Small line above the list', 'YOUR QUOTE DEPENDS ON', 60),
            $this->rows('factors', 'What the quote depends on', [
                $this->select('icon', 'Icon', self::ICONS),
                $this->text('text', 'Text', null, 160),
            ], [
                ['icon' => 'goal', 'text' => 'Your goal: more leads, more sales or a stronger brand'],
                ['icon' => 'calendar', 'text' => 'How many posts, reels and shoots you need each month'],
                ['icon' => 'budget', 'text' => 'Your ad budget, which you pay directly to Meta or Google'],
                ['icon' => 'tools', 'text' => 'Whether your website or tracking needs work first'],
            ], 6),
            $this->text('button_label', 'Main button', 'Get a custom quote', 40),
            $this->link('button_link', 'Main button link', "wa:Hi Ekdanta, I'd like a custom quote for my brand."),
            $this->text('button_2_label', 'Second button', 'Build your brief', 40),
            $this->link('button_2_link', 'Second button link', '/contact#c-brief'),
            $this->lines('packages', 'Generic packages that tumble away (one per line)', "Basic\nStandard\nPremium", 200, false),
            $this->text('package_price', 'Their price', '₹ ?????', 20, false),
            $this->text('stamp', 'Stamp', 'NO FIXED PACKAGES', 40, false),
            $this->text('plan_label', 'Plan card: small line', 'YOUR PLAN', 40, false),
            $this->text('plan_tag', 'Plan card: tag', 'Example', 30, false),
            $this->lines('plan_title', 'Plan card: title', "Made for your brand,\nnot from a menu.", 120),
            $this->lines('plan_items', 'Plan card: chips (one per line)', "Brand identity\nMonthly reels\nMeta ads\nNew website\nProduct shoot\nLead follow-ups", 300, false),
            $this->text('quote_title', 'Plan card: quote line', 'One clear monthly quote', 60, false),
            $this->text('quote_note', 'Plan card: under the quote line', 'Sent after your free consultation', 80, false),
        ];
    }

    public function deviceDefaults(): array
    {
        return ['mobile' => [
            'text' => 'No fixed packages. After a free consultation, we send a clear monthly plan and quote built around what your brand needs.',
            'button_label' => 'Custom quote',
            'plan_title' => 'Made for your brand, not from a menu.',
        ]];
    }
}

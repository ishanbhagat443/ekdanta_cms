<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Contact: six ways to reach us. A green band slides down the list and a
 * card for each channel takes the stage (design: Contact c-channels).
 * Tapping a channel jumps to its card.
 */
class ContactChannelsSection extends EkdantaSection
{
    public const ICONS = ['wa' => 'WhatsApp', 'phone' => 'Phone', 'sales' => 'Briefcase', 'mail' => 'Email', 'pin' => 'Map pin', 'ig' => 'Instagram'];

    protected function slug(): string
    {
        return 'contact-channels';
    }

    protected function anchor(): string
    {
        return 'c-channels';
    }

    public function label(): string
    {
        return 'Ekdanta · Contact Channels';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'SIX WAYS TO REACH US', 60),
            $this->text('heading', 'Heading', "Pick one. We'll take it from there.", 120),
            $this->image('mark', 'Marker beside the list (desktop)', 'ekdanta/e-mark'),
            $this->rows('channels', 'Channels (one card each)', [
                $this->text('label', 'Name in the list', null, 40),
                $this->text('kicker', 'Card label (e.g. WHATSAPP)', null, 30),
                $this->text('value', 'Number, address or handle (rolls in; {phone:1}, {whatsapp} or {email} fill in from Settings → Business)', null, 30),
                $this->select('icon', 'Icon', self::ICONS),
                $this->lines('text', 'Card text', null, 300),
                $this->text('button', 'Button', null, 40),
                $this->link('button_link', 'Button link'),
                $this->text('button_2', 'Second button (optional)', null, 40),
                $this->link('button_2_link', 'Second button link'),
            ], [
                ['label' => 'WhatsApp', 'kicker' => 'WHATSAPP', 'value' => '{whatsapp}', 'icon' => 'wa', 'text' => "Message us a few lines about your business. Share photos, links or your current posts, and we'll reply right there.", 'button' => 'Open WhatsApp', 'button_link' => 'wa:', 'button_2' => '', 'button_2_link' => ''],
                ['label' => 'Call for any service', 'kicker' => 'ALL SERVICES', 'value' => '{phone:1}', 'icon' => 'phone', 'text' => 'Talk to our team about branding, content, ads, websites, shoots or anything else your brand needs.', 'button' => 'Call {phone:1}', 'button_link' => 'phone:1', 'button_2' => 'Or {phone:2}', 'button_2_link' => 'phone:2'],
                ['label' => 'Talk to sales', 'kicker' => 'SALES', 'value' => '{phone:3}', 'icon' => 'sales', 'text' => 'Talk to sales about a quote, a proposal or starting a new project with us.', 'button' => 'Call sales', 'button_link' => 'phone:3', 'button_2' => '', 'button_2_link' => ''],
                ['label' => 'Email', 'kicker' => 'EMAIL', 'value' => '{email}', 'icon' => 'mail', 'text' => "Send briefs, documents and anything you'd like in writing.", 'button' => 'Write an email', 'button_link' => 'email:', 'button_2' => '', 'button_2_link' => ''],
                ['label' => 'Visit our office', 'kicker' => 'OFFICE', 'value' => 'Bhatar, Surat', 'icon' => 'pin', 'text' => 'Meet us at Bhatar Trade Center in Surat. Call before you come, so the right person is there to meet you.', 'button' => 'Get directions', 'button_link' => 'office:1', 'button_2' => 'Call first', 'button_2_link' => 'phone:1'],
                ['label' => 'Instagram', 'kicker' => 'INSTAGRAM', 'value' => '@ekdanta.in', 'icon' => 'ig', 'text' => 'See our latest reels, behind-the-scenes and client stories. You can message us there too.', 'button' => 'Open Instagram', 'button_link' => 'https://www.instagram.com/ekdanta.in/', 'button_2' => '', 'button_2_link' => ''],
            ], 8),
        ];
    }
}

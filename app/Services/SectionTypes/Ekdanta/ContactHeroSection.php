<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Contact: a phone dials Ekdanta on its own and tilts away as the visitor
 * scrolls (design: Contact / M-Contact c-top). The number it dials is the
 * first one in Settings → Business → Phone Numbers.
 */
class ContactHeroSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'contact-hero';
    }

    protected function anchor(): string
    {
        return 'c-top';
    }

    public function label(): string
    {
        return 'Ekdanta · Contact Hero (Phone Dial)';
    }

    protected function content(): array
    {
        return [
            $this->text('crumb_home', 'Breadcrumb: first link', 'Home', 40),
            $this->link('crumb_home_link', 'Breadcrumb: first link goes to', '/'),
            $this->text('crumb_current', 'Breadcrumb: this page', 'Contact us', 60),
            $this->text('heading_1', 'Heading, line 1', 'Chalo,', 40),
            $this->text('heading_2', 'Heading, line 2 (light green)', 'baat karte', 40),
            $this->text('heading_3', 'Heading, line 3', 'hain.', 40),
            $this->lines('text', 'Text', 'Call, WhatsApp or drop by our Surat office. Every big brand starts with one conversation.', 400),
            $this->text('whatsapp_label', 'WhatsApp button', 'WhatsApp us', 60),
            $this->link('whatsapp_link', 'WhatsApp button link', 'wa:'),
            $this->text('call_label', 'Call button', 'Call sales: 63544 33194', 60),
            $this->link('call_link', 'Call button link', 'tel:+91 63544 33194'),
            $this->text('dial_label', 'Phone: line under the number', 'Ekdanta · Your Branding Partner', 60, false),
            $this->text('caller', 'Phone: name on the call screen', 'Ekdanta', 40, false),
            $this->text('calling', 'Phone: call status', 'Calling…', 40, false),
            $this->image('mark', 'Phone: picture on the call screen', 'ekdanta/e-mark'),
            $this->text('bubble_name', 'Chat bubble: name', 'Ekdanta', 40, false),
            $this->text('bubble_text', 'Chat bubble: message', 'Namaste! How can we help your brand?', 120, false),
            $this->text('hint', 'Scroll hint', 'SCROLL TO EXPLORE', 60),
        ];
    }

    public function deviceDefaults(): array
    {
        return ['mobile' => ['call_label' => 'Call sales']];
    }
}

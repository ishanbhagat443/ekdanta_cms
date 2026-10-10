<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Thank-you page: the "Sending" button turns into a tick, signal rings
 * spread and two status chips pop in; scrolling slides the chips apart
 * (design: Thank-You t-top). "Received …" shows the visitor's local time.
 */
class ThankYouHeroSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'thank-you-hero';
    }

    protected function anchor(): string
    {
        return 't-top';
    }

    public function label(): string
    {
        return 'Ekdanta · Thank-you Hero';
    }

    protected function content(): array
    {
        return [
            $this->text('sending', 'Button before it turns into a tick', 'Sending your enquiry', 60, false),
            $this->text('chip_1', 'Status chip, left', 'Enquiry received', 40),
            $this->text('chip_2', 'Status chip, right', 'Our team is notified', 40),
            $this->text('heading', 'Heading', 'Dhanyavaad!', 40),
            $this->text('line', 'Line under the heading', 'Your message has reached the Ekdanta team.', 120),
            $this->lines('text', 'Text', "We'll go through what you've shared and get back to you soon. Here's what happens next.", 300),
            $this->text('received', 'Received: label', 'Received', 30),
            $this->text('received_time', 'Received: with the visitor\'s time ({time})', 'today at {time}', 40),
            $this->text('received_fallback', 'Received: when the time is unknown', 'just now', 30),
            $this->text('whatsapp_label', 'WhatsApp button', 'Chat with us on WhatsApp', 60),
            $this->link('whatsapp_link', 'WhatsApp button link', 'wa:Hi Ekdanta, I just sent you an enquiry.'),
            $this->text('home_label', 'Second button', 'Back to home', 40),
            $this->link('home_link', 'Second button link', '/'),
            $this->text('next_label', 'Link at the bottom (desktop)', 'What happens next', 40),
            $this->link('next_link', 'Link at the bottom goes to', '#t-next'),
        ];
    }
}

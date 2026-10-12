<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Blog: weekly tips on WhatsApp. A paper plane flies to a phone and the
 * tips arrive one by one (design: Blog b-sub).
 */
class WhatsAppTipsSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'whatsapp-tips';
    }

    protected function anchor(): string
    {
        return 'b-sub';
    }

    public function label(): string
    {
        return 'Ekdanta · WhatsApp Tips';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'WEEKLY TIPS ON WHATSAPP', 60),
            $this->text('heading', 'Heading, line 1', 'Ek tip,', 40),
            $this->text('heading_2', 'Heading, line 2 (green)', 'har hafte.', 40),
            $this->lines('text', 'Text', 'One practical marketing tip on WhatsApp, every week. Short enough to read with your morning chai.', 300),
            $this->text('button_label', 'Button', 'Join on WhatsApp', 40),
            $this->link('button_link', 'Button link', 'wa:Hi Ekdanta, please add me to your weekly tips.'),
            $this->text('note', 'Small print', 'No spam. Leave any time by replying STOP.', 120),
            $this->text('phone_name', 'Phone: chat name', 'Ekdanta tips', 40, false),
            $this->text('phone_status', 'Phone: under the name', 'Broadcast', 40, false),
            $this->text('tip_label', 'Phone: tip label ({n})', 'TIP {n}', 20, false),
            $this->lines('tips', 'Phone: tips (one per line, four fit)', "Put your phone number in the first line of your Instagram bio.\nReply to every enquiry within the hour. Speed wins deals.\nOne reel shot at your factory beats ten stock photos.\nFestival posts sell better with an offer and a deadline.", 800, false),
            $this->image('mark', 'Phone: chat picture', 'ekdanta/e-mark'),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Thank-you page: save our number. The phone rings from an unknown number,
 * then shows Ekdanta once saved (design: Thank-You t-save). The numbers and
 * the contact file (/contact.vcf) come from Settings → Business.
 */
class SaveOurNumberSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'save-our-number';
    }

    protected function anchor(): string
    {
        return 't-save';
    }

    public function label(): string
    {
        return 'Ekdanta · Save Our Number';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'SAVE OUR NUMBER', 40),
            $this->text('heading', 'Heading, line 1', 'When we call,', 80),
            $this->text('heading_2', 'Heading, line 2 (green)', "you'll know it's us.", 80),
            $this->lines('text', 'Text', "Save Ekdanta in your contacts, so our call doesn't look like just another unknown number.", 300),
            $this->text('button_label', 'Button (downloads the contact card)', 'Save Ekdanta to contacts', 60),
            $this->text('phone_description', 'Phone picture description', 'A phone showing an incoming call from Ekdanta', 120, false),
            $this->text('incoming', 'Phone: top line', 'Incoming call', 40, false),
            $this->text('unknown', 'Phone: before saving', 'Unknown number', 40, false),
            $this->text('caller', 'Phone: after saving, name', 'Ekdanta', 40, false),
            $this->text('caller_line', 'Phone: after saving, line', 'Your branding partner', 60, false),
            $this->text('toast', 'Phone: saved message', 'Contact saved', 40, false),
            $this->text('decline', 'Phone: decline', 'Decline', 20, false),
            $this->text('accept', 'Phone: accept', 'Accept', 20, false),
            $this->image('mark', 'Phone: picture after saving', 'ekdanta/e-mark'),
        ];
    }
}

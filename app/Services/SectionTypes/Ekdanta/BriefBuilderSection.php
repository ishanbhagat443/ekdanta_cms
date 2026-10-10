<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

use App\Models\Form;
use Illuminate\Support\Facades\Schema;

/**
 * Contact: the brief, one step at a time, from a Forms module form
 * ("Ekdanta Brief"): its fields, steps, hidden fields and WhatsApp message
 * come from Forms; this section holds the words around them (design:
 * Contact c-brief). Sending saves the enquiry, then opens WhatsApp.
 */
class BriefBuilderSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'brief-builder';
    }

    protected function anchor(): string
    {
        return 'c-brief';
    }

    public function label(): string
    {
        return 'Ekdanta · Brief Builder';
    }

    protected function content(): array
    {
        return [
            ['key' => 'form', 'label' => 'Form (Forms module)', 'type' => 'select', 'options' => $this->forms(), 'default' => 'ekdanta-brief', 'device' => false,
                'rules' => ['nullable', 'string', 'exists:forms,slug'], 'hint' => 'Its fields, steps (each field\'s Step number) and WhatsApp message are set in Forms.'],
            $this->text('eyebrow', 'Small line above the heading', 'BUILD YOUR BRIEF', 40),
            $this->text('heading', 'Heading (rises word by word)', 'Tell us what you need.', 80),
            $this->lines('text', 'Text', 'Four quick steps, then your brief opens in WhatsApp, ready to send. No long forms, no waiting for a call back.', 400),
            $this->text('call_before', 'Call line: text', 'Prefer to talk? Call sales on', 80),
            $this->text('call_label', 'Call line: link', '63544 33194', 40),
            $this->link('call_link', 'Call line: link goes to', 'tel:+91 63544 33194'),
            $this->text('call_after', 'Call line: after the link', '.', 20),
            $this->text('bg_word', 'Outline words behind', 'शुभ आरंभ · Your brief · शुभ आरंभ', 80, false),
            $this->rows('steps', 'Steps (one per Step number in the form)', [
                $this->text('short', 'Name on the road', null, 20),
                $this->text('title', 'Question', null, 120),
                $this->text('hint', 'Hint under the question', null, 160),
            ], [
                ['short' => 'Needs', 'title' => 'What do you need help with?', 'hint' => 'Pick as many as you like.'],
                ['short' => 'Business', 'title' => 'What kind of business do you run?', 'hint' => 'Pick the closest one.'],
                ['short' => 'Timing', 'title' => 'When would you like to start?', 'hint' => 'No pressure. Just exploring is fine too.'],
                ['short' => 'Details', 'title' => 'Last step: how do we reach you?', 'hint' => ''],
            ], 10),
            $this->text('summary_label', 'Summary label (last step)', 'YOUR BRIEF', 40),
            $this->text('send_label', 'Send button', 'Send my brief on WhatsApp', 60),
            $this->text('back_label', 'Back button', 'Back', 30),
            $this->text('next_label', 'Next button', 'Next', 30),
            $this->text('counter', 'Step counter ({n} = this step, {total} = how many)', 'Step {n} of {total}', 40),
            $this->text('sent_title', 'After sending: big line', 'शुभ आरंभ!', 60),
            $this->text('restart_label', 'After sending: start again', 'Start another brief', 40),
            $this->image('mark', 'Walker on the road', 'ekdanta/e-mark'),
        ];
    }

    public function deviceDefaults(): array
    {
        return ['mobile' => ['text' => 'Four quick steps, then your brief opens in WhatsApp.', 'call_before' => 'Prefer to talk? Call']];
    }

    /** @return array<string, string> slug => name of active forms */
    private function forms(): array
    {
        return Schema::hasTable('forms') ? Form::query()->where('is_active', true)->orderBy('name')->pluck('name', 'slug')->all() : [];
    }
}

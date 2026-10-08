<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormSubmissionMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Form $form,
        public FormSubmission $submission,
    ) {
    }

    public function envelope(): Envelope
    {
        // Reply-to the visitor when the form captured an email address.
        // (loadMissing: queued mailables re-hydrate without relations.)
        $replyTo = [];
        foreach ($this->form->loadMissing('fields')->fields as $field) {
            if ($field->type === 'email' && ($email = $this->submission->valueFor($field->name)) !== '') {
                $replyTo[] = new Address($email);
                break;
            }
        }

        return new Envelope(
            subject: 'New submission — '.$this->form->name.' ('.setting('business.company_name', 'CMS').')',
            replyTo: $replyTo,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.form-submission');
    }
}

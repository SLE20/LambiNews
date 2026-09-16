<?php

namespace App\Mail;

use App\Models\NewsletterCampaign;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public NewsletterCampaign $campaign,
        public Subscriber $subscriber,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->campaign->subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter',
            with: [
                'campaign'       => $this->campaign,
                'articles'       => $this->campaign->articles(),
                'unsubscribeUrl' => route('newsletter.unsubscribe', $this->subscriber->token),
            ],
        );
    }

    /**
     * En-tête List-Unsubscribe : Gmail et Outlook affichent alors un lien
     * de désinscription natif, ce qui réduit fortement les signalements
     * pour spam.
     */
    public function headers(): \Illuminate\Mail\Mailables\Headers
    {
        return new \Illuminate\Mail\Mailables\Headers(
            text: [
                'List-Unsubscribe' => '<'.route('newsletter.unsubscribe', $this->subscriber->token).'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }
}

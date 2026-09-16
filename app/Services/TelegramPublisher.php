<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Publie les articles sur un canal Telegram.
 *
 * En Haïti, Telegram et WhatsApp portent autant de trafic que Facebook.
 * Telegram expose une API de diffusion gratuite ; WhatsApp non (les
 * canaux n'ont pas d'API publique), d'où les boutons de partage côté
 * lecteur plutôt qu'un envoi automatique.
 */
class TelegramPublisher
{
    private const API = 'https://api.telegram.org/bot';

    public function __construct(
        private readonly ?string $token = null,
        private readonly ?string $channel = null,
    ) {
    }

    public static function fromConfig(): self
    {
        return new self(
            config('services.telegram.bot_token'),
            config('services.telegram.channel_id'),
        );
    }

    public function isConfigured(): bool
    {
        return filled($this->token) && filled($this->channel);
    }

    /**
     * Envoie un article et renvoie l'identifiant du message Telegram.
     */
    public function publish(Article $article): int
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'Telegram n’est pas configuré : renseignez TELEGRAM_BOT_TOKEN '
                .'et TELEGRAM_CHANNEL_ID dans le fichier .env.'
            );
        }

        $caption = $this->caption($article);
        $photo   = $this->photoUrl($article);

        /*
         * Avec photo quand l'article en a une : un message illustré est
         * bien plus partagé. La légende est limitée à 1024 caractères par
         * l'API, contre 4096 pour un message texte.
         */
        $response = $photo
            ? $this->call('sendPhoto', [
                'chat_id'    => $this->channel,
                'photo'      => $photo,
                'caption'    => Str::limit($caption, 1000, '…'),
                'parse_mode' => 'HTML',
            ])
            : $this->call('sendMessage', [
                'chat_id'                  => $this->channel,
                'text'                     => Str::limit($caption, 4000, '…'),
                'parse_mode'               => 'HTML',
                'disable_web_page_preview' => false,
            ]);

        return (int) data_get($response, 'result.message_id', 0);
    }

    /** Légende HTML : titre, chapô, rubrique et lien. */
    private function caption(Article $article): string
    {
        $parts = [];

        if ($article->is_sponsored) {
            $parts[] = '🏷 <i>Contenu sponsorisé</i>';
        } elseif ($article->is_breaking) {
            $parts[] = '🔴 <b>DERNIÈRE MINUTE</b>';
        }

        $parts[] = '<b>'.e($article->title).'</b>';

        $excerpt = trim(strip_tags((string) ($article->excerpt ?: $article->content)));

        if ($excerpt !== '') {
            $parts[] = e(Str::limit($excerpt, 300));
        }

        if ($article->category) {
            $parts[] = '📌 '.e($article->category->name);
        }

        $parts[] = route('articles.show', $article->slug);

        return implode("\n\n", $parts);
    }

    private function photoUrl(Article $article): ?string
    {
        // On réutilise la carte 1200×630 : bon format pour Telegram aussi.
        return blank($article->featured_image) ? null : $article->og_image_url;
    }

    /** @return array<string, mixed> */
    private function call(string $method, array $payload): array
    {
        $response = Http::timeout(30)
            ->retry(2, 500)
            ->asForm()
            ->post(self::API.$this->token.'/'.$method, $payload);

        if (! $response->successful() || $response->json('ok') !== true) {
            throw new RuntimeException('Telegram '.$method.' : '.$response->body());
        }

        return (array) $response->json();
    }
}

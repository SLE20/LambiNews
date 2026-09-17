<?php

namespace App\View\Components;

use App\Models\SiteSetting;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Invitation à rejoindre le canal Telegram :
 *
 *     <x-telegram-cta />                 carte (colonne)
 *     <x-telegram-cta variant="inline" /> bandeau (fin d'article)
 *
 * L'adresse vient des réglages ; sans adresse, rien ne s'affiche.
 */
class TelegramCta extends Component
{
    public string $url;

    public function __construct(public string $variant = 'card')
    {
        $this->url = SiteSetting::get('telegram_url', '');
    }

    public function shouldRender(): bool
    {
        return str_starts_with($this->url, 'https://');
    }

    public function render(): View
    {
        return view('components.telegram-cta');
    }
}

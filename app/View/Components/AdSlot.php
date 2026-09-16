<?php

namespace App\View\Components;

use App\Models\Ad;
use App\Services\AdServer;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Emplacement publicitaire.
 *
 *     <x-ad-slot position="sidebar_top" />
 *
 * Ne rend rien du tout si aucune bannière n’est programmée : pas de cadre
 * vide ni de « Votre publicité ici » sur le site public.
 */
class AdSlot extends Component
{
    public ?Ad $ad = null;

    public function __construct(
        public string $position,
        public ?string $class = null,
    ) {
        $this->ad = app(AdServer::class)->pick($position);
    }

    public function shouldRender(): bool
    {
        return $this->ad !== null;
    }

    public function render(): View
    {
        return view('components.ad-slot');
    }
}

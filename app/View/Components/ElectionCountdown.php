<?php

namespace App\View\Components;

use App\Models\ElectionEvent;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Encart « prochaine échéance électorale » :
 *
 *     <x-election-countdown />
 *
 * Ne s'affiche que si une échéance vérifiée est publiée.
 */
class ElectionCountdown extends Component
{
    public ?ElectionEvent $event;

    public function __construct()
    {
        $this->event = ElectionEvent::next();
    }

    public function shouldRender(): bool
    {
        return $this->event !== null;
    }

    public function render(): View
    {
        return view('components.election-countdown');
    }
}

<?php

namespace App\View\Components;

use App\Models\Fundraiser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Encart « campagne de financement » :
 *
 *     <x-featured-fundraiser />
 *
 * Met en avant une campagne ouverte — celle qui est épinglée en
 * priorité, sinon la plus avancée. On ne montre jamais une campagne
 * close : un appel aux dons qui ne peut plus rien recevoir décourage
 * plus qu'il ne rapporte.
 */
class FeaturedFundraiser extends Component
{
    public ?Fundraiser $fundraiser = null;

    public function __construct()
    {
        $this->fundraiser = Fundraiser::query()
            ->where('status', Fundraiser::STATUS_ACTIVE)
            ->where(fn (Builder $q) => $q
                ->whereNull('starts_at')->orWhereDate('starts_at', '<=', now()->toDateString()))
            ->where(fn (Builder $q) => $q
                ->whereNull('ends_at')->orWhereDate('ends_at', '>=', now()->toDateString()))
            ->orderByDesc('is_featured')
            ->orderByDesc('raised_amount')
            ->latest()
            ->first();
    }

    public function shouldRender(): bool
    {
        return $this->fundraiser !== null;
    }

    public function render(): View
    {
        return view('components.featured-fundraiser');
    }
}

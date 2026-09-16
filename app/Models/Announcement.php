<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use CrudTrait;

    public const STATUS_PENDING   = 'pending_payment';
    public const STATUS_PAID      = 'paid';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_EXPIRED   = 'expired';

    /** Durée d’affichage, en jours. */
    public const DISPLAY_DAYS = 30;

    protected $fillable = [
        'reference',
        'slug',
        'type',
        'title',
        'body',
        'photo',
        'requester_name',
        'requester_email',
        'requester_phone',
        'amount',
        'currency',
        'status',
        'moderation_note',
        'paypal_order_id',
        'paypal_capture_id',
        'paid_at',
        'published_at',
        'expires_at',
        'ip_hash',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'paid_at'      => 'datetime',
            'published_at' => 'datetime',
            'expires_at'   => 'date',
        ];
    }

    /**
     * Catégories d’annonces et tarif en dollars.
     *
     * Modifier un tarif ici ne change rien aux annonces déjà payées : le
     * montant réglé est figé sur chaque ligne.
     *
     * @return array<string, array{label: string, price: float, intro: string}>
     */
    public static function catalogue(): array
    {
        return [
            'deces' => [
                'label' => 'Avis de décès',
                'price' => 25.00,
                'intro' => 'Annoncer le décès d’un proche et les détails des funérailles.',
            ],
            'remerciement' => [
                'label' => 'Remerciements',
                'price' => 20.00,
                'intro' => 'Remercier publiquement ceux qui vous ont soutenu.',
            ],
            'messe' => [
                'label' => 'Messe de souvenir',
                'price' => 20.00,
                'intro' => 'Annoncer une messe anniversaire ou de suffrage.',
            ],
            'felicitations' => [
                'label' => 'Félicitations',
                'price' => 15.00,
                'intro' => 'Diplôme, promotion, mariage, naissance.',
            ],
            'anniversaire' => [
                'label' => 'Anniversaire',
                'price' => 15.00,
                'intro' => 'Souhaiter un anniversaire à quelqu’un.',
            ],
            'business' => [
                'label' => 'Annonce commerciale',
                'price' => 30.00,
                'intro' => 'Ouverture, promotion, recrutement, service.',
            ],
        ];
    }

    /** @return array<string, string> */
    public static function typeOptions(): array
    {
        return array_map(fn (array $row) => $row['label'], self::catalogue());
    }

    public static function priceFor(string $type): ?float
    {
        return self::catalogue()[$type]['price'] ?? null;
    }

    protected static function booted(): void
    {
        static::creating(function (Announcement $announcement): void {
            if (blank($announcement->reference)) {
                $announcement->reference = 'AN-'.Str::upper(Str::random(10));
            }

            if (blank($announcement->slug)) {
                $announcement->slug = Str::slug(
                    Str::limit($announcement->title, 60, '')
                ).'-'.Str::lower(Str::random(6));
            }
        });
    }

    /** Annonces payées, relues et encore dans leur période d’affichage. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(fn (Builder $q) => $q
                ->whereNull('expires_at')
                ->orWhereDate('expires_at', '>=', now()->toDateString()));
    }

    public function getTypeLabel(): string
    {
        return self::catalogue()[$this->type]['label'] ?? $this->type;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'Paiement en attente',
            self::STATUS_PAID      => 'Payée — à relire',
            self::STATUS_PUBLISHED => 'Publiée',
            self::STATUS_REJECTED  => 'Refusée',
            self::STATUS_EXPIRED   => 'Expirée',
            default                => $this->status,
        };
    }

    public function getFormattedAmount(): string
    {
        return number_format((float) $this->amount, 2).' '.$this->currency;
    }
}

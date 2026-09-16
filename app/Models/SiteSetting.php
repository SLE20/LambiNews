<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Réglages éditables depuis l'administration.
 *
 * Ce qui relève de l'identité du média (nom, slogan, réseaux, description
 * par défaut) vit ici plutôt que dans le code : le changer ne doit pas
 * demander un déploiement. Les secrets (clés PayPal, jeton Telegram)
 * restent dans .env, où ils ne risquent pas de fuiter par une capture
 * d'écran de l'administration.
 */
class SiteSetting extends Model
{
    use CrudTrait;

    protected $table = 'site_settings';

    protected $fillable = ['key', 'value', 'label', 'hint', 'group', 'type', 'position'];

    private const CACHE_KEY = 'site_settings.all';

    protected static function booted(): void
    {
        // Le cache doit tomber dès qu'un réglage change.
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * Toutes les valeurs, indexées par clé.
     *
     * Ne pas nommer cette méthode all() : elle entrerait en conflit avec
     * Model::all($columns) et provoquerait une erreur fatale au
     * chargement de la classe.
     *
     * @return array<string, string>
     */
    public static function values(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn () => static::query()->pluck('value', 'key')->all()
        );
    }

    public static function get(string $key, string $default = ''): string
    {
        $value = static::values()[$key] ?? null;

        return ($value === null || $value === '') ? $default : (string) $value;
    }

    public function getGroupLabel(): string
    {
        return match ($this->group) {
            'identite' => 'Identité du média',
            'seo'      => 'Référencement',
            'reseaux'  => 'Réseaux sociaux',
            default    => $this->group,
        };
    }
}

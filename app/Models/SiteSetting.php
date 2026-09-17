<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Throwable;

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

    protected $fillable = ['key', 'value', 'label', 'hint', 'group', 'type', 'position', 'is_secret'];

    protected $casts = ['is_secret' => 'boolean'];

    private const CACHE_KEY = 'site_settings.all';

    protected static function booted(): void
    {
        // Chiffre à l'écriture, mais seulement si la valeur a changé :
        // sinon on rechiffrerait un texte déjà chiffré.
        static::saving(function (SiteSetting $setting): void {
            if ($setting->is_secret && $setting->isDirty('value') && filled($setting->value)) {
                $setting->value = Crypt::encryptString((string) $setting->value);
            }
        });

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

    /**
     * Valeur d'un réglage sensible, déchiffrée.
     *
     * Les secrets sont chiffrés avec APP_KEY : une copie de la base
     * volée ne livre pas les clés de paiement.
     */
    public static function secret(string $key, string $default = ''): string
    {
        $stored = static::values()[$key] ?? null;

        if (blank($stored)) {
            return $default;
        }

        try {
            return (string) Crypt::decryptString((string) $stored);
        } catch (Throwable) {
            // Valeur saisie avant le chiffrement, ou APP_KEY changée.
            return (string) $stored;
        }
    }

    /** Aperçu masqué, pour l'administration. */
    public function maskedValue(): string
    {
        if (blank($this->value)) {
            return '—';
        }

        if (! $this->is_secret) {
            return \Illuminate\Support\Str::limit((string) $this->value, 70);
        }

        $clear = self::secret($this->key);

        return strlen($clear) <= 8
            ? '••••••••'
            : substr($clear, 0, 4).str_repeat('•', 12).substr($clear, -4);
    }

    public function getGroupLabel(): string
    {
        return match ($this->group) {
            'identite' => 'Identité du média',
            'seo'      => 'Référencement',
            'reseaux'  => 'Réseaux sociaux',
            'paiement' => 'Paiement (PayPal)',
            'elections' => 'Élections',
            default    => $this->group,
        };
    }
}

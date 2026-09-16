<?php

namespace App\Services;

use GdImage;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Fabrique des vignettes JPEG à partir des images téléversées.
 *
 * La rédaction envoie des PNG de deux à trois mégaoctets. Tels quels,
 * neuf articles en page d'accueil représentent près de 20 Mo — sur une
 * connexion mobile haïtienne, la page ne s'affiche jamais. Une vignette
 * JPEG de 800 px pèse environ 80 Ko, soit trente fois moins.
 *
 * Les vignettes sont produites à la demande puis mises en cache : la
 * première visite paie la conversion, les suivantes lisent un fichier.
 */
class Thumbnailer
{
    /** Largeurs proposées, celles utilisées dans les srcset. */
    public const WIDTHS = [400, 800, 1200];

    private const CACHE_DIR = 'thumbs';

    private const QUALITY = 78;

    /** Chemin de la vignette, relatif au disque "public". */
    public static function cachePath(string $source, int $width): string
    {
        return self::CACHE_DIR.'/'.$width.'/'.sha1($source).'.jpg';
    }

    public static function nearestWidth(int $requested): int
    {
        foreach (self::WIDTHS as $width) {
            if ($requested <= $width) {
                return $width;
            }
        }

        return (int) max(self::WIDTHS);
    }

    /**
     * Renvoie le JPEG de la vignette, en le produisant si besoin.
     * Null si l'image source est absente ou illisible.
     */
    public function render(string $source, int $width): ?string
    {
        $width = self::nearestWidth($width);
        $path  = self::cachePath($source, $width);
        $disk  = Storage::disk('public');

        if ($disk->exists($path)) {
            return (string) $disk->get($path);
        }

        if (! $disk->exists($source)) {
            return null;
        }

        $jpeg = $this->resize($disk->path($source), $width);

        if ($jpeg === null) {
            return null;
        }

        $disk->put($path, $jpeg);

        return $jpeg;
    }

    /** Redimensionne en conservant les proportions, sans jamais agrandir. */
    private function resize(string $absolutePath, int $width): ?string
    {
        try {
            $info = @getimagesize($absolutePath);

            if ($info === false) {
                return null;
            }

            $source = match ($info[2]) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($absolutePath),
                IMAGETYPE_PNG  => @imagecreatefrompng($absolutePath),
                IMAGETYPE_GIF  => @imagecreatefromgif($absolutePath),
                IMAGETYPE_WEBP => @imagecreatefromwebp($absolutePath),
                default        => false,
            };

            if (! $source instanceof GdImage) {
                return null;
            }

            $srcW = imagesx($source);
            $srcH = imagesy($source);

            $targetW = min($width, $srcW);
            $targetH = (int) round($srcH * ($targetW / $srcW));

            $canvas = imagecreatetruecolor($targetW, $targetH);

            /*
             * Fond blanc : un PNG transparent aplati en JPEG donnerait
             * sinon un fond noir.
             */
            imagefilledrectangle(
                $canvas, 0, 0, $targetW, $targetH,
                (int) imagecolorallocate($canvas, 255, 255, 255)
            );

            imagecopyresampled(
                $canvas, $source,
                0, 0, 0, 0,
                $targetW, $targetH, $srcW, $srcH
            );

            imagedestroy($source);

            ob_start();
            imagejpeg($canvas, null, self::QUALITY);
            $jpeg = (string) ob_get_clean();

            imagedestroy($canvas);

            return $jpeg;
        } catch (Throwable) {
            return null;
        }
    }
}

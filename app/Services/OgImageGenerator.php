<?php

namespace App\Services;

use App\Models\Article;
use GdImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Génère l’image de partage (Open Graph) en 1200×630, le format attendu
 * par Facebook, WhatsApp, LinkedIn et X pour afficher un grand aperçu.
 *
 * La carte reprend la photo de l’article, assombrie vers le bas, avec le
 * logo « Lambi News », la rubrique et le titre.
 *
 * Utilise GD plutôt qu’Imagick : l’extension est présente partout et
 * l’hébergement mutualisé ne garantit pas la seconde.
 */
class OgImageGenerator
{
    public const WIDTH = 1200;

    public const HEIGHT = 630;

    /** Répertoire de cache, relatif au disque "public". */
    private const CACHE_DIR = 'og';

    /** Or de la charte (--primary). */
    private const GOLD = [216, 169, 34];

    private const BLACK = [8, 8, 8];

    /**
     * Empreinte de la carte. Dès que le titre, la photo ou la rubrique
     * changent, l’URL change aussi : les réseaux sociaux régénèrent alors
     * leur aperçu au lieu de resservir l’ancien.
     */
    public static function version(Article $article): string
    {
        // Volontairement basée sur ce qui est *dessiné* sur la carte, et
        // non sur updated_at : une simple correction dans le corps du texte
        // ne doit pas invalider l’aperçu déjà mis en cache par Facebook.
        return substr(sha1(implode('|', [
            $article->title,
            $article->featured_image,
            $article->category?->name,
        ])), 0, 10);
    }

    /** Chemin du fichier en cache, relatif au disque "public". */
    public static function cachePath(Article $article, string $version): string
    {
        return self::CACHE_DIR.'/'.$article->slug.'-'.$version.'.jpg';
    }

    /**
     * Renvoie le contenu JPEG de la carte, en le générant si nécessaire.
     */
    public function render(Article $article): string
    {
        $version = self::version($article);
        $path    = self::cachePath($article, $version);
        $disk    = Storage::disk('public');

        if ($disk->exists($path)) {
            return (string) $disk->get($path);
        }

        $jpeg = $this->compose(
            $this->sourceImagePath($article),
            $article->category?->name,
            $article->title
        );

        $disk->put($path, $jpeg);
        $this->forgetOldVersions($article, $version);

        return $jpeg;
    }

    /**
     * Carte par défaut du site, pour l’accueil et les pages sans photo.
     */
    public function renderDefault(): string
    {
        return $this->compose(
            null,
            null,
            'Le citoyen au cœur de l’information'
        );
    }

    /**
     * Chemin absolu de la photo de l’article, ou null si elle est absente
     * ou illisible.
     */
    private function sourceImagePath(Article $article): ?string
    {
        if (blank($article->featured_image)) {
            return null;
        }

        $disk = Storage::disk('public');

        return $disk->exists($article->featured_image)
            ? $disk->path($article->featured_image)
            : null;
    }

    /**
     * Assemble la carte : fond, voile dégradé, logo, rubrique, titre.
     */
    private function compose(?string $imagePath, ?string $section, string $headline): string
    {
        $canvas = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagefilledrectangle(
            $canvas, 0, 0, self::WIDTH, self::HEIGHT,
            $this->color($canvas, self::BLACK)
        );

        // La mise en page se calcule de bas en haut, avant tout tracé :
        // le voile doit couvrir exactement la zone occupée par le texte.
        $layout = $this->layout($headline);

        $this->drawBackground($canvas, $imagePath);
        $this->drawScrim($canvas, $layout['scrimStart']);
        $this->drawBrand($canvas, $section, $layout['brandBaseline']);
        $this->drawHeadline($canvas, $layout);
        $this->drawAccentBar($canvas);

        ob_start();
        imagejpeg($canvas, null, 88);
        $jpeg = (string) ob_get_clean();

        imagedestroy($canvas);

        return $jpeg;
    }

    /**
     * Photo recadrée pour remplir le cadre, ou fond dégradé de marque.
     */
    private function drawBackground(GdImage $canvas, ?string $imagePath): void
    {
        if ($imagePath !== null && $this->drawPhoto($canvas, $imagePath)) {
            return;
        }

        // Fond de marque : dégradé sombre.
        for ($y = 0; $y < self::HEIGHT; $y++) {
            $shade = (int) round(8 + (26 * ($y / self::HEIGHT)));
            $line  = $this->color($canvas, [$shade, $shade, max(8, $shade - 4)]);
            imageline($canvas, 0, $y, self::WIDTH, $y, $line);
        }
    }

    /**
     * Dessine la photo en « cover » : elle remplit 1200×630 sans déformation.
     */
    private function drawPhoto(GdImage $canvas, string $path): bool
    {
        try {
            $info = @getimagesize($path);

            if ($info === false) {
                return false;
            }

            $photo = match ($info[2]) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
                IMAGETYPE_PNG  => @imagecreatefrompng($path),
                IMAGETYPE_GIF  => @imagecreatefromgif($path),
                IMAGETYPE_WEBP => @imagecreatefromwebp($path),
                default        => false,
            };

            if (! $photo instanceof GdImage) {
                return false;
            }

            $srcW  = imagesx($photo);
            $srcH  = imagesy($photo);
            $scale = max(self::WIDTH / $srcW, self::HEIGHT / $srcH);

            // Fenêtre source centrée, au ratio 1200×630.
            $cropW = (int) round(self::WIDTH / $scale);
            $cropH = (int) round(self::HEIGHT / $scale);
            $cropX = (int) round(($srcW - $cropW) / 2);
            $cropY = (int) round(($srcH - $cropH) / 2);

            imagecopyresampled(
                $canvas, $photo,
                0, 0, $cropX, $cropY,
                self::WIDTH, self::HEIGHT, $cropW, $cropH
            );

            imagedestroy($photo);

            // Assombrit légèrement pour que le texte reste lisible.
            imagefilter($canvas, IMG_FILTER_BRIGHTNESS, -16);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Calcule la mise en page du bloc texte, de bas en haut : taille du
     * titre, lignes, position du logo et hauteur du voile.
     *
     * @return array{lines: array<int, string>, fontSize: int, lineHeight: int,
     *               firstBaseline: int, brandBaseline: int, scrimStart: int}
     */
    private function layout(string $headline): array
    {
        $maxWidth = self::WIDTH - 128;
        $maxLines = 3;
        $font     = $this->font('playfair');

        // Réduit la taille jusqu’à ce que le titre tienne en trois lignes.
        $fontSize = 56;
        $lines    = $this->wrap($headline, $font, $fontSize, $maxWidth);

        while (count($lines) > $maxLines && $fontSize > 36) {
            $fontSize -= 4;
            $lines = $this->wrap($headline, $font, $fontSize, $maxWidth);
        }

        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $lines[$maxLines - 1] = rtrim($lines[$maxLines - 1], " .,;:").'…';
        }

        $lineHeight    = (int) round($fontSize * 1.26);
        $lastBaseline  = self::HEIGHT - 66;
        $firstBaseline = $lastBaseline - ((count($lines) - 1) * $lineHeight);

        // Le logo se place au-dessus de la première ligne du titre.
        $brandBaseline = $firstBaseline - $fontSize - 30;

        return [
            'lines'         => $lines,
            'fontSize'      => $fontSize,
            'lineHeight'    => $lineHeight,
            'firstBaseline' => $firstBaseline,
            'brandBaseline' => $brandBaseline,
            'scrimStart'    => max(0, $brandBaseline - 150),
        ];
    }

    /**
     * Voile dégradé sur le bas de l’image, dessiné ligne à ligne.
     *
     * Il devient quasi opaque sous le logo pour que le titre reste lisible
     * même sur une photo claire ou chargée.
     */
    private function drawScrim(GdImage $canvas, int $start): void
    {
        $span = max(1, self::HEIGHT - $start);

        for ($y = $start; $y < self::HEIGHT; $y++) {
            // Progression quadratique : voile discret en haut, dense en bas.
            $ratio = ($y - $start) / $span;
            $alpha = (int) round(127 - (127 * min(1.0, $ratio ** 1.15)));

            if ($alpha >= 127) {
                continue;
            }

            $shade = imagecolorallocatealpha(
                $canvas, self::BLACK[0], self::BLACK[1], self::BLACK[2], $alpha
            );
            imageline($canvas, 0, $y, self::WIDTH, $y, $shade);
        }
    }

    /** Logo « LAMBI NEWS » et, à sa droite, la rubrique. */
    private function drawBrand(GdImage $canvas, ?string $section, int $y): void
    {
        $gold = $this->color($canvas, self::GOLD);

        $width = $this->drawTracked(
            $canvas, 'LAMBI NEWS', $this->font('dmsans'), 32, 64, $y, $gold, 3.0
        );

        if (blank($section)) {
            return;
        }

        $x = 64 + $width + 26;

        // Séparateur vertical entre le logo et la rubrique.
        imagefilledrectangle(
            $canvas, $x, $y - 24, $x + 1, $y + 5,
            imagecolorallocatealpha($canvas, 255, 255, 255, 80)
        );

        $this->drawTracked(
            $canvas,
            Str::upper(Str::limit($section, 26, '')),
            $this->font('dmsans'),
            25,
            $x + 22,
            $y,
            imagecolorallocatealpha($canvas, 255, 255, 255, 18),
            2.0
        );
    }

    /**
     * Titre de l’article, positionné par layout().
     *
     * @param array{lines: array<int, string>, fontSize: int, lineHeight: int,
     *              firstBaseline: int} $layout
     */
    private function drawHeadline(GdImage $canvas, array $layout): void
    {
        $font  = $this->font('playfair');
        $white = $this->color($canvas, [255, 255, 255]);

        foreach ($layout['lines'] as $i => $line) {
            imagettftext(
                $canvas,
                $layout['fontSize'],
                0,
                64,
                $layout['firstBaseline'] + ($i * $layout['lineHeight']),
                $white,
                $font,
                $line
            );
        }
    }

    /** Filet doré tout en bas, rappel de la charte. */
    private function drawAccentBar(GdImage $canvas): void
    {
        imagefilledrectangle(
            $canvas, 0, self::HEIGHT - 9, self::WIDTH, self::HEIGHT,
            $this->color($canvas, self::GOLD)
        );
    }

    /**
     * Écrit un texte en espaçant les lettres (GD ne gère pas l’approche).
     * Renvoie la largeur totale dessinée.
     */
    private function drawTracked(
        GdImage $canvas,
        string $text,
        string $font,
        int $size,
        int $x,
        int $y,
        int $color,
        float $tracking
    ): int {
        $cursor = $x;

        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $char) {
            imagettftext($canvas, $size, 0, (int) round($cursor), $y, $color, $font, $char);
            $cursor += $this->textWidth($char, $font, $size) + $tracking;
        }

        return (int) round($cursor - $tracking - $x);
    }

    /**
     * Découpe un texte en lignes qui tiennent dans la largeur donnée.
     *
     * @return array<int, string>
     */
    private function wrap(string $text, string $font, int $size, int $maxWidth): array
    {
        $words = preg_split('/\s+/u', trim($text)) ?: [];
        $lines = [];
        $line  = '';

        foreach ($words as $word) {
            $candidate = $line === '' ? $word : $line.' '.$word;

            if ($line === '' || $this->textWidth($candidate, $font, $size) <= $maxWidth) {
                $line = $candidate;

                continue;
            }

            $lines[] = $line;
            $line    = $word;
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }

    /** Largeur d’un texte rendu avec la police et la taille données. */
    private function textWidth(string $text, string $font, int $size): float
    {
        $box = imagettfbbox($size, 0, $font, $text);

        if ($box === false) {
            return 0.0;
        }

        return max($box[2], $box[4]) - min($box[0], $box[6]);
    }

    /** @param array{0:int,1:int,2:int} $rgb */
    private function color(GdImage $canvas, array $rgb): int
    {
        return (int) imagecolorallocate($canvas, $rgb[0], $rgb[1], $rgb[2]);
    }

    /** Chemin absolu d’une police embarquée. */
    private function font(string $name): string
    {
        return resource_path('fonts/'.$name.'.ttf');
    }

    /**
     * Supprime les cartes des versions précédentes de l’article.
     */
    private function forgetOldVersions(Article $article, string $keep): void
    {
        $disk = Storage::disk('public');
        $keepName = $article->slug.'-'.$keep.'.jpg';

        foreach ($disk->files(self::CACHE_DIR) as $file) {
            $name = basename($file);

            if (Str::startsWith($name, $article->slug.'-') && $name !== $keepName) {
                $disk->delete($file);
            }
        }
    }
}

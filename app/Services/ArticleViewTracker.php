<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArticleViewTracker
{
    /**
     * Durée pendant laquelle plusieurs consultations
     * du même article sont considérées comme une seule vue.
     */
    private const DUPLICATE_DELAY_MINUTES = 30;

    public function record(Article $article, Request $request): void
    {
        if (! $this->shouldRecord($article, $request)) {
            return;
        }

        $userAgent = Str::limit(
            (string) $request->userAgent(),
            1024,
            ''
        );

        $sessionId = $request->hasSession()
            ? $request->session()->getId()
            : null;

        $ip = (string) $request->ip();

        $ipHash = hash_hmac(
            'sha256',
            $ip,
            (string) config('app.key')
        );

        $visitorHash = hash_hmac(
            'sha256',
            $ip.'|'.$userAgent.'|'.$sessionId,
            (string) config('app.key')
        );

        $alreadyRecorded = ArticleView::query()
            ->where('article_id', $article->id)
            ->where('visitor_hash', $visitorHash)
            ->where(
                'viewed_at',
                '>=',
                now()->subMinutes(self::DUPLICATE_DELAY_MINUTES)
            )
            ->exists();

        if ($alreadyRecorded) {
            return;
        }

        DB::transaction(function () use (
            $article,
            $request,
            $visitorHash,
            $sessionId,
            $ipHash,
            $userAgent
        ): void {
            ArticleView::create([
                'article_id' => $article->id,
                'visitor_hash' => $visitorHash,
                'session_id' => $sessionId,
                'ip_hash' => $ipHash,
                'referrer' => $this->getReferrer($request),
                'user_agent' => $userAgent ?: null,
                'device_type' => $this->detectDeviceType($userAgent),
                'browser' => $this->detectBrowser($userAgent),
                'operating_system' => $this->detectOperatingSystem($userAgent),
                'country_code' => null,
                'city' => null,
                'viewed_at' => now(),
            ]);

            /*
             * Compteur incrémenté via le query builder brut : l’increment()
             * d’Eloquent met aussi « updated_at » à jour, ce qui ferait
             * passer chaque simple lecture pour une modification de
             * l’article (dateModified, lastmod du sitemap, aperçus sociaux).
             */
            DB::table('articles')
                ->where('id', $article->id)
                ->increment('views_count');
        });
    }

    private function shouldRecord(
        Article $article,
        Request $request
    ): bool {
        if (! $article->exists) {
            return false;
        }

        if ($request->method() !== 'GET') {
            return false;
        }

        if ($this->isBot((string) $request->userAgent())) {
            return false;
        }

        /*
         * On ne comptabilise normalement que les articles publiés.
         * Si votre statut utilise un autre nom, adaptez "published".
         */
        if (
            isset($article->status) &&
            $article->status !== 'published'
        ) {
            return false;
        }

        if (
            isset($article->published_at) &&
            $article->published_at?->isFuture()
        ) {
            return false;
        }

        return true;
    }

    private function isBot(string $userAgent): bool
    {
        if ($userAgent === '') {
            return true;
        }

        return (bool) preg_match(
            '/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|'
            .'whatsapp|telegrambot|discordbot|linkedinbot|preview|'
            .'lighthouse|pagespeed|headlesschrome|uptimerobot|monitoring/i',
            $userAgent
        );
    }

    private function getReferrer(Request $request): ?string
    {
        $referrer = trim((string) $request->headers->get('referer'));

        if ($referrer === '') {
            return null;
        }

        return Str::limit($referrer, 2048, '');
    }

    private function detectDeviceType(string $userAgent): string
    {
        if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            return 'tablet';
        }

        if (
            preg_match(
                '/mobile|iphone|ipod|android.*mobile|windows phone/i',
                $userAgent
            )
        ) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function detectBrowser(string $userAgent): ?string
    {
        return match (true) {
            preg_match('/Edg\//i', $userAgent) === 1 => 'Edge',
            preg_match('/OPR\//i', $userAgent) === 1 => 'Opera',
            preg_match('/Firefox\//i', $userAgent) === 1 => 'Firefox',
            preg_match('/Chrome\//i', $userAgent) === 1 => 'Chrome',
            preg_match('/Safari\//i', $userAgent) === 1 => 'Safari',
            default => null,
        };
    }

    private function detectOperatingSystem(string $userAgent): ?string
    {
        return match (true) {
            preg_match('/Windows/i', $userAgent) === 1 => 'Windows',
            preg_match('/Android/i', $userAgent) === 1 => 'Android',
            preg_match('/iPhone|iPad|iPod/i', $userAgent) === 1 => 'iOS',
            preg_match('/Mac OS X|Macintosh/i', $userAgent) === 1 => 'macOS',
            preg_match('/Linux/i', $userAgent) === 1 => 'Linux',
            default => null,
        };
    }
}
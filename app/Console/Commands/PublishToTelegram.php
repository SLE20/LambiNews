<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\TelegramPublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Diffuse sur Telegram les articles publiés qui n'y sont pas encore.
 */
class PublishToTelegram extends Command
{
    protected $signature = 'telegram:publish
        {--limit=3 : Articles par passage}
        {--max-age=48 : Ne rien publier de plus ancien, en heures}';

    protected $description = 'Publie les nouveaux articles sur le canal Telegram';

    public function handle(TelegramPublisher $telegram): int
    {
        if (! $telegram->isConfigured()) {
            $this->warn('Telegram n’est pas configuré — rien à faire.');

            return self::SUCCESS;
        }

        /*
         * La fenêtre d'ancienneté évite qu'une première activation ne
         * déverse d'un coup toutes les archives sur le canal.
         */
        $articles = Article::query()
            ->published()
            ->with('category')
            ->whereNull('telegram_posted_at')
            ->where('published_at', '>=', now()->subHours((int) $this->option('max-age')))
            ->orderBy('published_at')
            ->limit(max(1, (int) $this->option('limit')))
            ->get();

        if ($articles->isEmpty()) {
            $this->info('Aucun article à diffuser.');

            return self::SUCCESS;
        }

        foreach ($articles as $article) {
            try {
                $messageId = $telegram->publish($article);

                // Query builder brut : ne pas toucher updated_at.
                DB::table('articles')->where('id', $article->id)->update([
                    'telegram_message_id' => $messageId ?: null,
                    'telegram_posted_at'  => now(),
                ]);

                $this->info('Publié : '.$article->title);
            } catch (Throwable $e) {
                Log::error('Telegram — échec pour l’article '.$article->id.' : '.$e->getMessage());
                $this->error($article->title.' : '.$e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}

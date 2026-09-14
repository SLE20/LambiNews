<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class PublishScheduledArticles extends Command
{
    protected $signature = 'articles:publish-scheduled';

    protected $description = 'Publier les articles programmés arrivés à échéance';

    public function handle(): int
    {
        $publishedCount = Article::query()
            ->where('status', 'scheduled')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->update([
                'status' => 'published',
                'updated_at' => now(),
            ]);

        $this->info(
            $publishedCount.' article(s) programmé(s) publié(s).'
        );

        return self::SUCCESS;
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->text('value')->nullable();

            // Pour l'affichage dans l'administration.
            $table->string('label');
            $table->string('hint')->nullable();
            $table->string('group', 40)->default('general');
            // text | textarea | url | email | image
            $table->string('type', 20)->default('text');
            $table->unsignedSmallInteger('position')->default(0);

            $table->timestamps();

            $table->index(['group', 'position']);
        });

        $now = now();

        $rows = [
            ['site_name', 'Lambi News', 'Nom du site', 'Utilisé dans le titre des pages et les partages.', 'identite', 'text', 10],
            ['tagline', 'Le citoyen au cœur de l’information', 'Slogan', 'Affiché après le nom du site sur l’accueil.', 'identite', 'text', 20],
            ['default_description', 'Lambi News vous informe sur l’actualité nationale et internationale.', 'Description par défaut', 'Sert de méta-description quand une page n’en a pas.', 'seo', 'textarea', 10],
            ['contact_email', 'info.lambinews@gmail.com', 'Courriel de contact', 'Publié dans les données structurées.', 'identite', 'email', 30],
            ['facebook_url', 'https://www.facebook.com/lambinews/', 'Page Facebook', null, 'reseaux', 'url', 10],
            ['instagram_url', 'https://www.instagram.com/info.lambinews/', 'Compte Instagram', null, 'reseaux', 'url', 20],
            ['tiktok_url', 'https://www.tiktok.com/@lambinews', 'Compte TikTok', null, 'reseaux', 'url', 30],
            ['twitter_handle', '', 'Identifiant X (Twitter)', 'Sans le @. Renseigné, il apparaît dans les cartes de partage.', 'reseaux', 'text', 40],
            ['telegram_url', '', 'Canal Telegram', 'Adresse publique du canal, ex. https://t.me/lambinews', 'reseaux', 'url', 50],
            ['whatsapp_url', '', 'Canal WhatsApp', 'Lien d’invitation du canal.', 'reseaux', 'url', 60],
        ];

        DB::table('site_settings')->insert(array_map(
            fn (array $r) => [
                'key'        => $r[0],
                'value'      => $r[1],
                'label'      => $r[2],
                'hint'       => $r[3],
                'group'      => $r[4],
                'type'       => $r[5],
                'position'   => $r[6],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $rows
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fundraisers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('summary')->nullable();
            $table->text('story')->nullable();
            $table->string('cover_image')->nullable();

            // Bénéficiaire : qui reçoit l'argent, dit clairement au public.
            $table->string('beneficiary')->nullable();

            $table->decimal('goal_amount', 12, 2);
            $table->string('currency', 3)->default('USD');

            /*
             * Total collecté, tenu à jour à chaque contribution confirmée.
             * Recalculable depuis les contributions en cas de doute.
             */
            $table->decimal('raised_amount', 12, 2)->default(0);
            $table->unsignedInteger('contributions_count')->default(0);

            // draft | active | closed
            $table->string('status', 20)->default('draft');
            $table->boolean('is_featured')->default(false);

            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'is_featured']);
        });

        Schema::create('fundraiser_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundraiser_id')->constrained()->cascadeOnDelete();

            $table->string('reference', 32)->unique();

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');

            $table->string('donor_name')->nullable();
            $table->string('donor_email')->nullable();
            $table->string('donor_phone')->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_anonymous')->default(false);

            // paypal | moncash
            $table->string('provider', 20)->default('paypal');
            // pending | completed | failed | cancelled
            $table->string('status', 20)->default('pending');

            $table->string('provider_order_id')->nullable()->index();
            $table->string('provider_capture_id')->nullable();
            $table->string('checkout_url', 500)->nullable();

            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['fundraiser_id', 'status']);
        });

        // Réglages WalCash Pay, à côté de ceux de PayPal.
        $now = now();

        DB::table('site_settings')->insert(array_map(
            fn (array $r) => [
                'key' => $r[0], 'value' => $r[1], 'label' => $r[2], 'hint' => $r[3],
                'group' => 'paiement', 'type' => $r[4], 'position' => $r[5],
                'is_secret' => $r[6], 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                ['walcash_mode', 'test', 'Mode WalCash Pay (MonCash)', 'test pour les essais, live pour encaisser réellement.', 'select', 40, false],
                ['walcash_test_key', '', 'Clé API test (sk_test_…)', 'Paiements simulés, sans argent réel.', 'text', 50, true],
                ['walcash_live_key', '', 'Clé API live (sk_live_…)', 'Encaissement réel. Ne jamais la publier.', 'text', 60, true],
                ['walcash_webhook_secret', '', 'Secret du webhook (whsec_…)', 'À créer dans le tableau de bord WalCash, en enregistrant l’adresse https://lambinews.com/webhooks/walcash.', 'text', 70, true],
            ]
        ));
    }

    public function down(): void
    {
        DB::table('site_settings')->whereIn('key', [
            'walcash_mode', 'walcash_test_key', 'walcash_live_key', 'walcash_webhook_secret',
        ])->delete();

        Schema::dropIfExists('fundraiser_contributions');
        Schema::dropIfExists('fundraisers');
    }
};

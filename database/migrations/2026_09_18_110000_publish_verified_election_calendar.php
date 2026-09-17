<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Calendrier électoral vérifié le 17 septembre 2026 sur les sources du
 * CEP :
 *  - calendrier officiel 2026-2027 (note de presse du 27 juillet 2026) ;
 *  - note du 2 septembre 2026 (étapes de l'inscription des candidats) ;
 *  - note de presse 60 du 16 septembre 2026 (délai jusqu'au 9 octobre).
 *
 * La publication de la liste préliminaire (29 septembre) reste en
 * brouillon : la note 60 la rend intenable et le CEP n'a pas encore
 * annoncé de nouvelle date.
 */
return new class extends Migration
{
    private const CALENDAR = 'https://cephaiti.ht/publication-officielle-du-calendrier-electoral-2026-2027/';
    private const NOTE_SEPT_2 = 'https://cephaiti.ht/prolongation-de-la-periode-dinscription-des-candidats/';
    private const NOTE_60 = 'https://cephaiti.ht/elementor-16515/';

    public function up(): void
    {
        $now = now();
        $verified = '2026-09-17';

        $set = function (string $title, array $values) use ($now, $verified): ?int {
            $id = DB::table('election_events')->where('title', $title)->value('id');
            if ($id) {
                DB::table('election_events')->where('id', $id)->update($values + [
                    'verified_on' => $verified, 'is_published' => true, 'updated_at' => $now,
                ]);
            }

            return $id;
        };

        $add = function (array $values) use ($now, $verified): int {
            return DB::table('election_events')->insertGetId($values + [
                'verified_on' => $verified, 'is_published' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        };

        $set('Publication du décret électoral', [
            'title'        => 'Publication du décret électoral',
            'starts_on'    => '2026-06-02', 'ends_on' => null, 'status' => 'done',
            'description'  => 'Le CEP poursuit des discussions avec les acteurs politiques et le gouvernement en vue d’une modification du décret (note de presse 60 du 16 septembre 2026).',
            'source_label' => 'CEP — Décret électoral du 2 juin 2026',
            'source_url'   => 'https://cephaiti.ht/decret-electoral-du-2-juin-2026/',
        ]);

        $set('Inscription des électeurs', [
            'starts_on'    => '2026-07-20', 'ends_on' => '2026-10-13', 'status' => 'ongoing',
            'description'  => 'Lancée le 20 juillet 2026 dans neuf départements ; le département de l’Ouest n’était pas concerné par ce lancement.',
            'source_label' => 'CEP — calendrier électoral 2026-2027 (27 juillet 2026)',
            'source_url'   => self::CALENDAR,
        ]);

        $online = $set('Dépôt des candidatures (période prolongée)', [
            'title'        => 'Inscription en ligne des candidats',
            'starts_on'    => '2026-08-20', 'ends_on' => '2026-10-09', 'status' => 'ongoing',
            'description'  => 'Le calendrier initial prévoyait l’inscription des candidats du 20 août au 2 octobre. La note du 2 septembre l’a prolongée et a fixé l’inscription en ligne au 20 septembre ; la note 60 du 16 septembre accorde un délai jusqu’au 9 octobre.',
            'source_label' => 'CEP — note de presse 60 (16 septembre 2026)',
            'source_url'   => self::NOTE_60,
        ]);

        $pieces = $set('Dépôt des pièces aux BED et aux BEC', [
            'title'        => 'Dépôt des pièces requises aux BED et aux BEC',
            'starts_on'    => '2026-09-13', 'ends_on' => '2026-10-09', 'status' => 'ongoing',
            'description'  => 'Prévu du 13 au 22 septembre par la note du 2 septembre, prolongé jusqu’au 9 octobre par la note 60.',
            'source_label' => 'CEP — note de presse 60 (16 septembre 2026)',
            'source_url'   => self::NOTE_60,
        ]);

        // Historique réel, daté du jour de la décision du CEP.
        $history = [
            [$online, '2026-09-02 12:00:00', '2026-08-20', '2026-10-02', '2026-08-20', '2026-09-20'],
            [$online, '2026-09-16 12:00:00', '2026-08-20', '2026-09-20', '2026-08-20', '2026-10-09'],
            [$pieces, '2026-09-16 12:00:00', '2026-09-13', '2026-09-22', '2026-09-13', '2026-10-09'],
        ];
        foreach ($history as [$id, $at, $os, $oe, $ns, $ne]) {
            if ($id) {
                DB::table('election_event_revisions')->insert([
                    'election_event_id' => $id, 'changed_at' => $at,
                    'old_starts_on' => $os, 'old_ends_on' => $oe,
                    'new_starts_on' => $ns, 'new_ends_on' => $ne,
                    'old_status' => 'ongoing', 'new_status' => 'ongoing',
                ]);
            }
        }

        DB::table('election_events')->where('title', 'Publication de la liste préliminaire des candidats')->update([
            'description'  => 'À revoir : la note 60 du 16 septembre prolonge l’inscription et le dépôt des pièces jusqu’au 9 octobre. Le CEP n’a pas encore publié de nouvelle date pour cette étape.',
            'source_label' => 'CEP — note du 2 septembre 2026',
            'source_url'   => self::NOTE_SEPT_2,
            'is_published' => false,
            'updated_at'   => $now,
        ]);

        $set('Campagne électorale', [
            'title'        => 'Campagne électorale du premier tour',
            'starts_on'    => '2026-10-05', 'ends_on' => '2026-12-12', 'status' => 'upcoming',
            'source_label' => 'CEP — calendrier électoral 2026-2027 (27 juillet 2026)',
            'source_url'   => self::CALENDAR,
        ]);

        $set('Premier tour — présidentielle et législatives', [
            'title'        => 'Premier tour — présidentielle, législatives et ratification populaire',
            'starts_on'    => '2026-12-13', 'ends_on' => null, 'status' => 'upcoming',
            'description'  => 'Le CEP souligne que le respect de ces échéances dépend d’un climat sécuritaire acceptable et des financements nécessaires.',
            'source_label' => 'CEP — calendrier électoral 2026-2027 (27 juillet 2026)',
            'source_url'   => self::CALENDAR,
        ]);

        $cep = ['status' => 'upcoming', 'source_label' => 'CEP — calendrier électoral 2026-2027 (27 juillet 2026)', 'source_url' => self::CALENDAR];
        $add($cep + ['title' => 'Publication des listes électorales', 'starts_on' => '2026-11-13', 'ends_on' => null, 'description' => 'Trente jours avant le scrutin.']);
        $add($cep + ['title' => 'Résultats préliminaires du premier tour', 'starts_on' => '2026-12-19', 'ends_on' => null, 'description' => 'Présidentielle et législatives.']);
        $add($cep + ['title' => 'Résultats définitifs du premier tour', 'starts_on' => '2027-01-09', 'ends_on' => null, 'description' => 'Après la période de contestation ouverte le 22 décembre 2026.']);
        $add($cep + ['title' => 'Second tour et élections des collectivités territoriales', 'starts_on' => '2027-02-21', 'ends_on' => null, 'description' => 'Second tour de la présidentielle et des législatives.']);
    }

    public function down(): void
    {
        DB::table('election_events')->whereIn('title', [
            'Publication des listes électorales',
            'Résultats préliminaires du premier tour',
            'Résultats définitifs du premier tour',
            'Second tour et élections des collectivités territoriales',
        ])->delete();
        DB::table('election_events')->update(['is_published' => false]);
    }
};

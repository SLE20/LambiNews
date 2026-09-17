<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Contenu de départ de l'espace « Élections ».
 *
 * - Les acteurs : textes explicatifs de la rédaction, publiés.
 * - Les structures politiques : liste officielle du CEP (août 2026),
 *   avec leur numéro de campagne. Source primaire, publiée.
 * - Le questionnaire : thèmes proposés aux partis, modifiables.
 * - Le calendrier : échéances annoncées par le CEP, créées en
 *   brouillon. Une date électorale ne se publie qu'après vérification
 *   par la rédaction sur la source officielle.
 * - Les chiffres ONI : réglages vides tant qu'ils ne sont pas relevés.
 */
return new class extends Migration
{
    private const CEP = 'https://cephaiti.ht/';

    public function up(): void
    {
        $now = now();

        /* ---------------- Acteurs ---------------- */
        $actors = [
            ['🏛', 'Conseil électoral provisoire (CEP)', 'cep',
             'L’institution chargée d’organiser les élections et d’en proclamer les résultats.',
             'Le CEP fixe le calendrier, enregistre les partis et les candidats, accrédite les observateurs, organise le scrutin, dépouille et publie les résultats. Il tranche aussi une partie des contestations à travers ses bureaux du contentieux.',
             "Publier et tenir à jour le calendrier électoral\nEnregistrer les partis, groupements et candidatures\nOuvrir les centres d’inscription et de vote\nAccréditer observateurs et journalistes\nOrganiser le scrutin et le dépouillement\nProclamer les résultats et juger les contestations",
             "Avant le scrutin : cadre, calendrier, inscriptions\nLe jour du vote : bureaux, dépouillement\nAprès le vote : résultats et contentieux",
             "Respect des délais annoncés\nPublication des décisions et des procès-verbaux\nÉgalité de traitement entre les candidats",
             'Site officiel du CEP', self::CEP],
            ['🗳', 'Électeurs', 'electeurs',
             'Les citoyennes et citoyens inscrits, dont le vote donne sa légitimité au scrutin.',
             'Pour voter, un citoyen doit être inscrit au registre électoral et détenir sa carte d’identification. Il vote au centre où il est inscrit.',
             "S’inscrire au registre électoral dans les délais\nVérifier son centre de vote\nS’informer sur les candidats et les programmes\nVoter le jour du scrutin",
             "Période d’inscription\nCampagne électorale\nJour du vote",
             "Accès aux centres d’inscription dans toutes les communes\nSécurité des déplacements le jour du vote\nInformation claire sur les démarches",
             'Office national d’identification (ONI)', 'https://oni.gouv.ht/'],
            ['🎙', 'Candidats', 'candidats',
             'Les personnes qui briguent un mandat de président, de sénateur ou de député.',
             'Un candidat dépose un dossier auprès des bureaux électoraux, présenté par un parti, un groupement ou à titre indépendant selon les règles du décret électoral. Une fois agréé, il fait campagne dans la période prévue.',
             "Déposer un dossier complet dans les délais\nRespecter les règles de campagne et de financement\nAccepter ou contester les résultats selon la procédure",
             "Dépôt et analyse des candidatures\nCampagne électorale\nContentieux après le scrutin",
             "Conditions d’éligibilité\nTransparence du financement de campagne\nCampagne sans violence",
             'Site officiel du CEP', self::CEP],
            ['🚩', 'Partis et groupements politiques', 'partis-politiques',
             'Les organisations qui présentent des candidats et portent des programmes.',
             'Les structures politiques agréées par le CEP reçoivent un numéro de campagne et peuvent présenter des candidats. Elles désignent aussi des mandataires pour suivre le vote dans les bureaux.',
             "Se faire enregistrer et agréer par le CEP\nPrésenter des candidatures\nFormer et déployer des mandataires\nPublier un programme",
             "Enregistrement des structures\nCampagne électorale\nJour du vote et dépouillement",
             "Clarté des programmes\nPrésence de mandataires dans les bureaux\nRespect du code de conduite",
             'CEP — structures politiques agréées', 'https://cephaiti.ht/wp-content/uploads/2026/08/STRUCTURES-NUMEROS.pdf'],
            ['🤝', 'Société civile', 'societe-civile',
             'Associations, églises, syndicats et observateurs qui informent et surveillent le processus.',
             'Les organisations de la société civile mènent l’éducation civique, observent l’inscription et le vote, et publient leurs constats. Leurs observateurs doivent être accrédités par le CEP.',
             "Éducation civique des électeurs\nObservation électorale accréditée\nSignalement des irrégularités\nPlaidoyer pour l’inclusion",
             "Tout au long du cycle électoral",
             "Indépendance vis-à-vis des partis\nMéthodes d’observation publiées",
             'Site officiel du CEP', self::CEP],
            ['✈️', 'Diaspora', 'diaspora',
             'Les Haïtiennes et Haïtiens vivant à l’étranger, attachés au débat national.',
             'La diaspora pèse dans la vie économique et politique du pays. Les modalités de sa participation au vote dépendent des règles fixées pour chaque scrutin : il faut les vérifier auprès du CEP.',
             "Suivre les règles de participation applicables\nS’informer sur les candidats\nSoutenir l’information et l’observation",
             "Débat public et campagne",
             "Clarté des règles de participation depuis l’étranger",
             'Site officiel du CEP', self::CEP],
            ['🌐', 'Partenaires internationaux', 'partenaires-internationaux',
             'Organisations et pays qui apportent un appui technique, financier ou des observateurs.',
             'Les partenaires internationaux peuvent financer une partie des opérations, fournir une expertise technique ou envoyer des missions d’observation, à l’invitation des autorités haïtiennes.',
             "Appui technique et financier\nMissions d’observation\nRapports publics",
             "Préparation des opérations\nJour du vote\nPublication des rapports",
             "Respect de la souveraineté des décisions électorales\nTransparence des financements",
             null, null],
            ['🏢', 'Gouvernement', 'gouvernement',
             'L’exécutif, qui publie le décret électoral et fournit les moyens matériels du scrutin.',
             'Le gouvernement publie le décret électoral, dégage le budget, et coordonne la sécurité du processus avec la Police nationale. Il ne décide pas des résultats.',
             "Publier le cadre légal du scrutin\nFinancer les opérations électorales\nAssurer la sécurité avec la PNH",
             "Préparation du scrutin\nJour du vote",
             "Décaissement du budget dans les délais\nSécurité des centres et du matériel",
             'Le Moniteur', null],
        ];

        foreach ($actors as $i => [$icon, $name, $slug, $summary, $role, $resp, $moments, $watch, $srcLabel, $srcUrl]) {
            DB::table('electoral_actors')->insert([
                'name' => $name, 'slug' => $slug, 'icon' => $icon, 'summary' => $summary,
                'role' => $role, 'responsibilities' => $resp, 'moments' => $moments,
                'watch_points' => $watch, 'source_label' => $srcLabel, 'source_url' => $srcUrl,
                'position' => $i + 1, 'is_published' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        /* ---------------- Structures politiques (CEP) ---------------- */
        $parties = json_decode(file_get_contents(database_path('data/cep-structures-politiques-2026.json')), true);

        foreach ($parties as $party) {
            $base = Str::slug($party['acronym'] ?: $party['name']) ?: 'structure';
            DB::table('political_parties')->insert([
                'name'            => $party['name'],
                'slug'            => $base.'-'.$party['number'],
                'acronym'         => $party['acronym'] ?: null,
                'campaign_number' => $party['number'],
                'kind'            => 'structure',
                'is_published'    => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }

        /* ---------------- Questionnaire ---------------- */
        $questions = [
            ['Sécurité', 'Quelles mesures prendrez-vous dans les six premiers mois pour rétablir la sécurité dans les zones contrôlées par les gangs ?'],
            ['Économie', 'Comment comptez-vous créer des emplois et soutenir la production nationale ?'],
            ['Éducation', 'Quelle est votre priorité pour l’école publique et l’accès à l’éducation ?'],
            ['Santé', 'Comment améliorer l’accès aux soins de santé en dehors de Port-au-Prince ?'],
            ['Justice', 'Quelles réformes proposez-vous pour lutter contre l’impunité et la corruption ?'],
            ['Diaspora', 'Quelle place donnez-vous à la diaspora dans la vie politique et économique du pays ?'],
        ];
        foreach ($questions as $i => [$theme, $question]) {
            DB::table('party_questions')->insert([
                'theme' => $theme, 'question' => $question, 'position' => $i + 1,
                'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        /* ---------------- Calendrier (brouillons à vérifier) ---------------- */
        $events = [
            ['Publication du décret électoral', '2026-06-02', null, 'done', 'Le Moniteur'],
            ['Inscription des électeurs', '2026-07-20', null, 'ongoing', 'CEP — ouverture des centres d’inscription'],
            ['Dépôt des candidatures (période prolongée)', '2026-08-20', '2026-10-14', 'ongoing', 'CEP — prolongation de l’inscription des candidats'],
            ['Dépôt des pièces aux BED et aux BEC', '2026-09-13', '2026-09-22', 'ongoing', 'CEP — dépôt des pièces et déclarations de candidature'],
            ['Publication de la liste préliminaire des candidats', '2026-09-29', null, 'upcoming', 'CEP'],
            ['Campagne électorale', '2026-10-05', '2026-12-12', 'upcoming', 'CEP — calendrier électoral'],
            ['Premier tour — présidentielle et législatives', '2026-12-13', null, 'upcoming', 'CEP — calendrier électoral'],
        ];
        foreach ($events as [$title, $start, $end, $status, $src]) {
            DB::table('election_events')->insert([
                'title' => $title, 'starts_on' => $start, 'ends_on' => $end, 'status' => $status,
                'source_label' => $src, 'source_url' => self::CEP,
                'verified_on' => null, 'is_published' => false,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        /* ---------------- Réglages ---------------- */
        $settings = [
            ['oni_women', 'Inscrits ONI — femmes', 'Nombre publié par l’ONI. Laisser vide pour masquer le bloc.', 10],
            ['oni_men', 'Inscrits ONI — hommes', 'Nombre publié par l’ONI.', 20],
            ['oni_checked_on', 'Chiffres ONI relevés le', 'Date de consultation, ex. 2026-08-03.', 30],
            ['oni_source_url', 'Lien vers les statistiques de l’ONI', 'Page officielle où vérifier les chiffres.', 40],
            ['cep_stats_url', 'Tableau statistique du CEP (intégré)', 'Laisser vide pour ne pas l’afficher.', 50],
        ];
        foreach ($settings as [$key, $label, $hint, $pos]) {
            DB::table('site_settings')->insert([
                'key' => $key,
                'value' => $key === 'cep_stats_url' ? 'https://stats.cephaiti.ht/' : ($key === 'oni_source_url' ? 'https://oni.gouv.ht/' : ''),
                'label' => $label, 'hint' => $hint, 'group' => 'elections', 'type' => 'text',
                'position' => $pos, 'is_secret' => false,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('site_settings')->where('group', 'elections')->delete();
        DB::table('election_events')->delete();
        DB::table('party_questions')->delete();
        DB::table('political_parties')->delete();
        DB::table('electoral_actors')->delete();
    }
};

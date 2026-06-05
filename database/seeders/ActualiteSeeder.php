<?php

namespace Database\Seeders;

use App\Models\Actualite;
use Illuminate\Database\Seeder;

class ActualiteSeeder extends Seeder
{
    public function run(): void
    {
        $actualites = [
            [
                'title' => 'Revalorisation des pensions civiles 2026',
                'description' => 'Le gouvernement annonce une revalorisation de 8 % des pensions civiles à compter du 1er juillet 2026, bénéficiant à plus de 45 000 retraités.',
                'content_text' => '<p>Le Ministère de l\'Économie et des Finances a officialisé ce mardi la revalorisation des pensions civiles de 8 % à compter du premier juillet 2026. Cette mesure s\'inscrit dans le cadre du plan de modernisation de la protection sociale des fonctionnaires retraités.</p><p>Les bénéficiaires sont invités à se connecter à leur espace personnel pour consulter le nouveau montant de leur pension et télécharger l\'attestation de revalorisation.</p>',
                'category' => 'Réforme',
                'posted_in' => 'Direction des Pensions Civiles',
                'published' => true,
                'created_at' => now()->subDays(3),
            ],
            [
                'title' => 'Nouvelle procédure de demande de pension en ligne',
                'description' => 'Dès le 15 juin 2026, tous les fonctionnaires pourront soumettre leur demande de mise à la retraite entièrement en ligne via le portail officiel.',
                'content_text' => '<p>La Direction des Pensions Civiles lance la dématérialisation complète du processus de demande de pension. À partir du 15 juin 2026, il ne sera plus nécessaire de se déplacer aux guichets pour déposer un dossier.</p><p>Pour en bénéficier, créez votre compte sur ce portail, remplissez le formulaire de demande de pension et joignez les pièces justificatives numérisées. Votre dossier sera traité dans un délai de 30 jours ouvrables.</p>',
                'category' => 'Services en ligne',
                'posted_in' => 'Direction des Pensions Civiles',
                'published' => true,
                'created_at' => now()->subDays(10),
            ],
            [
                'title' => 'Campagne de mise à jour des fiches biométriques',
                'description' => 'Une campagne nationale de renouvellement des fiches biométriques des retraités est lancée du 1er au 30 juin 2026 dans tous les départements.',
                'content_text' => '<p>Afin de lutter contre la fraude aux prestations et de maintenir à jour les registres de l\'État, la Direction des Pensions Civiles organise une campagne de collecte biométrique sur tout le territoire national.</p><p>Les retraités sont priés de se présenter au bureau des pensions de leur département munis de leur CIN valide et de leur code de pension entre le 1er et le 30 juin 2026.</p>',
                'category' => 'Campagne',
                'posted_in' => 'Direction des Pensions Civiles',
                'published' => true,
                'created_at' => now()->subDays(20),
            ],
            [
                'title' => 'Publication du rapport annuel 2025',
                'description' => 'Le rapport annuel d\'activité 2025 de la Direction des Pensions Civiles est désormais disponible en téléchargement dans la section Médiathèque.',
                'content_text' => '<p>La Direction des Pensions Civiles a publié son rapport annuel d\'activité pour l\'exercice 2025. Ce document retrace les principales réalisations de l\'institution : traitement des dossiers, évolution des effectifs des bénéficiaires, réformes menées et perspectives pour 2026.</p><p>Le rapport est accessible librement dans la section Médiathèque du portail.</p>',
                'category' => 'Publication',
                'posted_in' => 'Direction des Pensions Civiles',
                'published' => true,
                'created_at' => now()->subDays(35),
            ],
            [
                'title' => 'Fermeture exceptionnelle des guichets — Fête nationale',
                'description' => 'Les guichets de la Direction des Pensions Civiles seront fermés le 18 mai 2026 à l\'occasion de la Fête du Drapeau. Reprise normale le 19 mai.',
                'content_text' => '<p>En raison de la célébration de la Fête du Drapeau, les guichets d\'accueil de la Direction des Pensions Civiles seront exceptionnellement fermés le samedi 18 mai 2026. Les services reprennent leurs activités normalement le lundi 19 mai.</p><p>Les démarches en ligne restent accessibles 24h/24 via ce portail.</p>',
                'category' => 'Information',
                'posted_in' => 'Direction des Pensions Civiles',
                'published' => true,
                'created_at' => now()->subDays(50),
            ],
        ];

        foreach ($actualites as $data) {
            Actualite::updateOrCreate(['title' => $data['title']], $data);
        }
    }
}

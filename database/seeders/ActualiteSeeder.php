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
                'title'        => 'Publication de l’avis de liquidation n° 4 — février 2026',
                'description'  => 'La Direction de la Pension Civile informe les pensionnaires de la publication de l’avis de liquidation n° 4, pris en faveur de certains agents publics.',
                'content_text' => "La Direction de la Pension Civile porte à la connaissance des intéressés que l’avis de liquidation n° 4 a été publié le 10 février 2026.\n\nLes agents publics concernés sont invités à consulter le document officiel dans la rubrique Publications, ou à se présenter aux bureaux de la Direction munis de leur pièce d’identité afin de vérifier leur inscription.\n\nPour toute réclamation, un délai de trente (30) jours est ouvert à compter de la date de publication.",
                'category'     => 'Avis de liquidation',
                'posted_in'    => 'Site web',
                'published'    => true,
                'created_at'   => now()->subDays(8),
                'image'        => 'images/carousel/KEV_6804.jpg',
            ],
            [
                'title'        => 'Calendrier de paiement des pensions — 3e trimestre 2026',
                'description'  => 'Le calendrier de versement des pensions civiles pour le troisième trimestre 2026 est désormais disponible.',
                'content_text' => "Afin de permettre aux pensionnaires de mieux planifier leurs démarches, la Direction de la Pension Civile publie le calendrier indicatif des versements pour juillet, août et septembre 2026.\n\nLes paiements interviennent généralement en début de mois, selon les disponibilités du Trésor public. Les pensionnaires ayant choisi le virement bancaire sont priés de s’assurer que leurs coordonnées bancaires sont à jour.\n\nEn cas de non-réception, contactez le service des paiements ou rapprochez-vous de votre direction départementale.",
                'category'     => 'Paiement',
                'posted_in'    => 'Site web',
                'published'    => true,
                'created_at'   => now()->subDays(18),
                'image'        => 'images/carousel/KEV_7157.jpg',
            ],
            [
                'title'        => 'Rappel : certificat de vie et preuve d’existence',
                'description'  => 'Les pensionnaires doivent renouveler leur certificat de vie dans les délais impartis afin d’éviter toute interruption de paiement.',
                'content_text' => "Conformément aux dispositions en vigueur, tout pensionnaire est tenu de produire périodiquement un certificat de vie (preuve d’existence).\n\nCe document peut être déposé en ligne via l’espace personnel, ou remis aux guichets de la Direction et des directions départementales. Un justificatif d’identité en cours de validité est exigé.\n\nLe non-renouvellement dans les délais peut entraîner la suspension temporaire du versement de la pension jusqu’à régularisation du dossier.",
                'category'     => 'Démarches',
                'posted_in'    => 'Site web',
                'published'    => true,
                'created_at'   => now()->subDays(27),
                'image'        => 'images/carousel/KEV_7055.jpg',
            ],
            [
                'title'        => 'Campagne d’information : vos droits à la retraite',
                'description'  => 'Un guide pratique rappelle les droits des fonctionnaires et des futurs retraités au titre de la pension civile.',
                'content_text' => "La Direction de la Pension Civile lance une campagne d’information destinée aux agents en fin de carrière et aux pensionnaires déjà liquidés.\n\nLe document « Vos droits à la retraite » synthétise les conditions d’éligibilité, les pièces à fournir, les modalités de calcul prévues par le décret du 9 octobre 2015, ainsi que les recours disponibles.\n\nDes séances d’orientation seront également organisées dans plusieurs directions départementales. Le calendrier sera communiqué prochainement.",
                'category'     => 'Information',
                'posted_in'    => 'Site web',
                'published'    => true,
                'created_at'   => now()->subDays(41),
                'image'        => 'images/carousel/KEV_7043.jpg',
            ],
            [
                'title'        => 'Ouverture des demandes de transfert de banque en ligne',
                'description'  => 'Les pensionnaires peuvent désormais demander un changement de compte bancaire directement depuis leur espace personnel.',
                'content_text' => "Afin de simplifier les démarches, le service de transfert bancaire est désormais accessible en ligne.\n\nAprès connexion à l’espace personnel, sélectionnez le type de demande « Transfert de banque », renseignez les nouvelles coordonnées et joignez un relevé d’identité bancaire récent.\n\nLe traitement s’effectue selon le circuit habituel. Un accusé de réception est envoyé par notification dès le dépôt du dossier.",
                'category'     => 'Services en ligne',
                'posted_in'    => 'Site web',
                'published'    => true,
                'created_at'   => now()->subDays(55),
                'image'        => 'images/carousel/KEV_6984.jpg',
            ],
            [
                'title'        => 'Avis rectificatif n° 2 — janvier 2026',
                'description'  => 'Un avis rectificatif a été publié afin de corriger certaines mentions figurant dans un avis de liquidation antérieur.',
                'content_text' => "La Direction de la Pension Civile informe le public de la publication de l’avis rectificatif n° 2, en date du 6 janvier 2026.\n\nCet avis vise à rectifier des erreurs matérielles constatées dans un avis de liquidation précédent. Les personnes concernées sont priées de consulter le document officiel et, le cas échéant, de se présenter au service compétent pour mise à jour de leur dossier.\n\nLe document est disponible dans la rubrique Publications du site.",
                'category'     => 'Communiqué',
                'posted_in'    => 'Journal officiel',
                'published'    => true,
                'created_at'   => now()->subDays(72),
                'image'        => 'images/carousel/KEV_7117.jpg',
            ],
        ];

        foreach ($actualites as $data) {
            $createdAt = $data['created_at'];
            $imagePath = $data['image'];
            unset($data['created_at'], $data['image']);

            $actualite = Actualite::updateOrCreate(
                ['title' => $data['title']],
                $data
            );

            $actualite->created_at = $createdAt;
            $actualite->updated_at = $createdAt;
            $actualite->save();

            $existing = $actualite->images()->first();
            if ($existing) {
                $existing->update(['image_path' => $imagePath]);
            } else {
                $actualite->images()->create(['image_path' => $imagePath]);
            }
        }
    }
}

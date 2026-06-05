<?php

namespace Database\Seeders;

use App\Models\FaqItem;
use Illuminate\Database\Seeder;

class FaqItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // From original hardcoded FAQ view (commit e0abce6)
            [
                'question'     => 'Qui peut bénéficier de la Pension Civile ?',
                'answer'       => 'La Pension Civile est destinée aux fonctionnaires ayant réalisé le nombre d\'années de service requis et remplissant les conditions légales prévues.',
                'category'     => 'eligibilite',
                'order_column' => 1,
                'published'    => true,
            ],
            [
                'question'     => 'Quels documents sont nécessaires pour déposer une demande ?',
                'answer'       => 'Les principaux documents sont : une pièce d\'identité, les états de service, le dernier bulletin de salaire, et le formulaire officiel dûment rempli.',
                'category'     => 'dossier',
                'order_column' => 2,
                'published'    => true,
            ],
            [
                'question'     => 'Comment est calculée la Pension Civile ?',
                'answer'       => 'Le calcul se base sur la moyenne des 60 meilleurs mois de salaire, conformément au décret du 09 octobre 2015.',
                'category'     => 'calcul',
                'order_column' => 3,
                'published'    => true,
            ],
            [
                'question'     => 'À quelle fréquence la pension est-elle versée ?',
                'answer'       => 'Les pensions sont généralement versées mensuellement selon le calendrier établi par le MEF.',
                'category'     => 'paiement',
                'order_column' => 4,
                'published'    => true,
            ],
            [
                'question'     => 'Que faire si aucune réponse ne correspond à ma question ?',
                'answer'       => 'Vous pouvez nous contacter directement via le formulaire de contact disponible sur le portail ou vous rendre à nos bureaux. Un agent de la Direction sera disponible pour vous assister.',
                'category'     => 'acces',
                'order_column' => 5,
                'published'    => true,
            ],
            // Portal-specific questions
            [
                'question'     => 'Comment puis-je soumettre une demande de pension de retraite ?',
                'answer'       => 'Connectez-vous à votre espace personnel, accédez à la section « Mes demandes » et cliquez sur « Nouvelle demande ». Sélectionnez le type « Demande de pension de retraite », remplissez le formulaire et joignez les pièces justificatives requises. Une fois soumise, vous pouvez suivre l\'avancement de votre dossier en temps réel.',
                'category'     => 'demarches',
                'order_column' => 6,
                'published'    => true,
            ],
            [
                'question'     => 'Quels documents sont nécessaires pour constituer un dossier de pension ?',
                'answer'       => 'Les pièces généralement requises sont : une copie de la CIN (carte d\'identité nationale), le NIF, le NINU, un relevé de carrière délivré par le ministère employeur, un certificat médical (pour invalidité), et un acte de mariage ou de naissance selon le type de pension demandé. La liste complète dépend du type de demande.',
                'category'     => 'dossier',
                'order_column' => 7,
                'published'    => true,
            ],
            [
                'question'     => 'Quel est le délai de traitement d\'un dossier de pension ?',
                'answer'       => 'Le délai moyen de traitement est de 30 à 60 jours ouvrables à compter de la réception d\'un dossier complet. Ce délai peut varier selon la complexité du dossier et la charge des services. Vous serez notifié à chaque étape du traitement via votre espace personnel.',
                'category'     => 'demarches',
                'order_column' => 8,
                'published'    => true,
            ],
            [
                'question'     => 'Comment obtenir une attestation de pension ?',
                'answer'       => 'Depuis votre espace personnel, soumettez une demande d\'attestation de pension. Le document vous sera délivré après vérification de votre dossier. Vous pourrez le télécharger directement depuis la plateforme une fois disponible.',
                'category'     => 'demarches',
                'order_column' => 9,
                'published'    => true,
            ],
            [
                'question'     => 'Qu\'est-ce que la preuve d\'existence et quand dois-je la fournir ?',
                'answer'       => 'La preuve d\'existence est un certificat annuel confirmant que le pensionnaire est toujours en vie. Elle est exigée une fois par an pour maintenir le versement de la pension. Un rappel vous sera envoyé par notification avant l\'échéance.',
                'category'     => 'paiement',
                'order_column' => 10,
                'published'    => true,
            ],
            [
                'question'     => 'Comment modifier mes coordonnées bancaires pour le versement de ma pension ?',
                'answer'       => 'Soumettez une demande de transfert bancaire depuis votre espace personnel en indiquant les nouvelles coordonnées. Un agent de la Direction vérifiera la demande avant de mettre à jour votre dossier. Le changement prend effet au prochain cycle de paiement.',
                'category'     => 'paiement',
                'order_column' => 11,
                'published'    => true,
            ],
            [
                'question'     => 'Que faire si ma pension n\'a pas été versée ce mois-ci ?',
                'answer'       => 'Vérifiez d\'abord que votre preuve d\'existence est à jour et que vos coordonnées bancaires sont correctes dans votre profil. Si tout est en ordre, contactez la Direction via le formulaire de contact ou soumettez une demande en ligne. Un agent prendra en charge votre signalement dans les meilleurs délais.',
                'category'     => 'paiement',
                'order_column' => 12,
                'published'    => true,
            ],
            [
                'question'     => 'Un fonctionnaire décédé — comment ses ayants droit peuvent-ils demander la pension de réversion ?',
                'answer'       => 'Les ayants droit (conjoint ou enfants) doivent créer un compte de type « Pensionnaire » et soumettre une demande de pension de réversion en joignant l\'acte de décès, l\'acte de mariage ou de naissance, et les pièces d\'identité du demandeur. Le dossier sera traité par les services compétents.',
                'category'     => 'dossier',
                'order_column' => 13,
                'published'    => true,
            ],
            [
                'question'     => 'Comment suivre l\'état de ma demande ?',
                'answer'       => 'Connectez-vous à votre espace personnel et accédez à « Mes demandes ». Chaque dossier affiche son statut en temps réel (Brouillon, Soumise, En traitement, Complément requis, Approuvée, etc.). Vous recevez également des notifications à chaque changement de statut.',
                'category'     => 'demarches',
                'order_column' => 14,
                'published'    => true,
            ],
            [
                'question'     => 'Puis-je soumettre une demande sans compte sur la plateforme ?',
                'answer'       => 'La plupart des demandes nécessitent un compte. Cependant, la demande de rencontre (vidéoconférence avec la Direction) est accessible sans authentification depuis la page d\'accueil du portail.',
                'category'     => 'acces',
                'order_column' => 15,
                'published'    => true,
            ],
        ];

        foreach ($items as $data) {
            FaqItem::firstOrCreate(['question' => $data['question']], $data);
        }
    }
}

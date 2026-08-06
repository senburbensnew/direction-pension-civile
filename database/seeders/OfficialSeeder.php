<?php

namespace Database\Seeders;

use App\Models\Official;
use Illuminate\Database\Seeder;

class OfficialSeeder extends Seeder
{
    public function run(): void
    {
        $officials = [
            [
                'slug'       => 'ministre',
                'role'       => 'Le Ministre',
                'nom'        => 'Serge Gabriel COLLIN',
                'sexe'       => 'M',
                'photo'      => 'images/ministre.png',
                'biographie' => "M. Serge Gabriel COLLIN est Ministre de l'Économie et des Finances. Il conduit les orientations stratégiques du ministère en matière de finances publiques, de modernisation de l'administration et de protection sociale des agents de l'État.\n\nSous son autorité, la Direction de la Pension Civile poursuit le renforcement d'un système de retraite plus fiable, plus transparent et plus accessible aux fonctionnaires, retraités et ayants droit.",
                'discours'   => "Chers compatriotes, chers collaborateurs,\n\nLa protection des droits à pension des fonctionnaires constitue un pilier essentiel de la solidarité nationale et de la crédibilité de l'État employeur. Notre engagement est d'assurer une gestion rigoureuse, moderne et humaine de la pension civile.\n\nJe réaffirme la volonté du Ministère de l'Économie et des Finances d'accompagner la Direction de la Pension Civile dans l'amélioration continue de ses services, la digitalisation des procédures et le respect des délais de traitement.\n\nEnsemble, poursuivons cet effort au service des retraités, des actifs et de leurs familles.\n\nSerge Gabriel COLLIN\nMinistre de l'Économie et des Finances",
                'citation'   => 'La dignité des retraités est le reflet de la responsabilité de l\'État.',
                'order'      => 1,
            ],
            [
                'slug'       => 'directeur-general',
                'role'       => 'Directeur Général',
                'nom'        => 'Jean Bouco JEAN JACQUES',
                'sexe'       => 'M',
                'photo'      => 'images/directricehhh.jpg',
                'biographie' => null,
                'discours'   => "Chers collaborateurs, chers partenaires institutionnels,\n\nAu sein du Ministère de l'Économie et des Finances, nous avons la responsabilité stratégique d'assurer la stabilité et la pérennité du système de pension civile, afin de garantir aux fonctionnaires et retraités une sécurité financière digne et fiable. Cette mission exige rigueur, anticipation et engagement envers la modernisation de nos services.\n\nSous la direction éclairée du Ministre, nous poursuivons la mise en œuvre de réformes indispensables pour renforcer la gestion des pensions civiles, améliorer la transparence des opérations et garantir un versement régulier et sécurisé des droits des bénéficiaires.\n\nJe salue l'engagement et le professionnalisme des équipes du ministère, qui œuvrent quotidiennement à assurer le bien-être de nos fonctionnaires retraités et la fiabilité de notre système de pension. Ensemble, nous continuerons à bâtir un ministère moderne et efficace, garantissant à chaque agent le respect et la sécurité de ses droits.\n\nRespectueusement,\nJean Bouco JEAN JACQUES\nDirecteur Général — Ministère de l'Économie et des Finances",
                'citation'   => 'Une administration forte repose sur la rigueur, la transparence et la vision.',
                'order'      => 2,
            ],
            [
                'slug'       => 'directeur',
                'role'       => 'Directrice Générale',
                'nom'        => 'Esther Musac JEUDY',
                'sexe'       => 'F',
                'photo'      => 'images/directrice.jpg',
                'biographie' => "Mme Esther Musac JEUDY est une professionnelle reconnue de l'administration publique, dotée d'une solide expérience dans la gestion institutionnelle, la coordination stratégique et la modernisation des services administratifs. Son parcours est marqué par un engagement constant en faveur de l'efficacité, de la transparence et de l'amélioration continue du service public.\n\nAvant d'être nommée Directrice Générale de la Direction de la Pension Civile, elle a occupé plusieurs fonctions de responsabilité au sein de l'administration, où elle s'est distinguée par sa rigueur, son leadership positif et sa capacité à piloter des réformes structurantes.\n\nÀ la tête de la Direction de la Pension Civile, Mme JEUDY œuvre pour renforcer un système de retraite plus efficace, plus transparent et plus proche des usagers. Elle place l'humain au cœur de chaque décision et veille au respect des droits des retraités et de leurs familles.",
                'discours'   => "Chers collègues, partenaires et bénéficiaires,\n\nLa Direction de la Pension Civile joue un rôle essentiel dans la reconnaissance des services rendus par nos fonctionnaires. Chaque jour, nous œuvrons pour garantir une gestion efficace, transparente et équitable des pensions, afin d'assurer la sérénité de nos retraités et de leurs ayants droit.\n\nNotre engagement est d'améliorer continuellement nos processus, en modernisant nos outils et en simplifiant les démarches pour offrir un service rapide et accessible à tous. Nous mettons un point d'honneur à accompagner chaque bénéficiaire avec professionnalisme et empathie.\n\nGrâce à nos efforts conjugués, nous continuerons à bâtir un système de pension plus juste, plus efficace et plus humain. Votre confiance et votre collaboration sont essentielles à la réussite de cette mission.\n\nAvec mes salutations respectueuses,\nEsther Musac JEUDY\nDirectrice Générale — Direction de la Pension Civile",
                'citation'   => "Servir avec dévouement, c'est assurer l'avenir de ceux qui ont bâti notre nation.",
                'order'      => 3,
            ],
        ];

        foreach ($officials as $data) {
            Official::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}

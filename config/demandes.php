<?php

return [
    'demande_adhesion' => [
        'documents' => [
            'multiple' => [],
            'single' => [
                'profile_photo' => [
                    'label'     => 'Photo',
                    'multiple'  => false,
                    'min_files' => 1,
                    'max_files' => 1,
                ],
            ],
        ],
    ],

    'demande_pension' => [
        'documents' => [

            'multiple' => [

                'career_certificates' => [
                    'label'     => 'Certificat de carrière',
                    'multiple'  => true,
                    'min_files' => 1,
                    'max_files' => 0,
                ],

                'marriage_certificates' => [
                    'label'     => 'Acte de mariage',
                    'multiple'  => true,
                    'min_files' => 0, // optionnel
                    'max_files' => 2, // 0 = illimité
                ],

                'birth_certificates' => [
                    'label'     => 'Acte de naissance',
                    'multiple'  => true,
                    'min_files' => 2,
                    'max_files' => 2,
                ],

                'tax_id_numbers' => [
                    'label'     => 'Numéro d’identification fiscale',
                    'multiple'  => true,
                    'min_files' => 2,
                    'max_files' => 2,
                ],

                'photos' => [
                    'label'     => 'Photos d’identité',
                    'multiple'  => true,
                    'min_files' => 2,
                    'max_files' => 2,
                ],
            ],

            'single' => [

                'monitor_copy' => [
                    'label'     => 'Copie du moniteur',
                    'multiple'  => false,
                    'min_files' => 0,
                    'max_files' => 1,
                ],

                'medical_certificate' => [
                    'label'     => 'Certificat médical',
                    'multiple'  => false,
                    'min_files' => 0,
                    'max_files' => 1,
                ],

                'check_stub' => [
                    'label'     => 'Bulletin de paie',
                    'multiple'  => false,
                    'min_files' => 1,
                    'max_files' => 1,
                ],

                'divorce_certificate' => [
                    'label'     => 'Acte de divorce',
                    'multiple'  => false,
                    'min_files' => 0,
                    'max_files' => 1,
                ],
            ],
        ],
    ],

    'demande_pension_reversion' => [
        'documents' => [
            'multiple' => [
                'acte_deces' => [
                    'label'     => 'acte_deces',
                    'multiple'  => true,
                    'min_files' => 2,
                    'max_files' => 2,
                ],
                'photos_identites' => [
                    'label'     => 'photos_identites',
                    'multiple'  => true,
                    'min_files' => 2,
                    'max_files' => 2,
                ],
                'attestations_scolaires' => [
                    'label'     => 'attestations_scolaires',
                    'multiple'  => true,
                    'min_files' => 0,
                    'max_files' => 2,
                ],
            ],

            'single' => [
                'certificat_carriere' => [
                    'label'     => 'certificat_carriere',
                    'multiple'  => false,
                    'min_files' => 1,
                    'max_files' => 1,
                ],
                'certificat_non_dissolution' => [
                    'label'     => 'certificat_non_dissolution',
                    'multiple'  => false,
                    'min_files' => 1,
                    'max_files' => 1,
                ],
                'carte_pension' => [
                    'label'     => 'carte_pension',
                    'multiple'  => false,
                    'min_files' => 1,
                    'max_files' => 1,
                ],
                'souche_cheque' => [
                    'label'     => 'souche_cheque',
                    'multiple'  => false,
                    'min_files' => 1,
                    'max_files' => 1,
                ],
                'extrait_acte_mariage' => [
                    'label'     => 'extrait_acte_mariage',
                    'multiple'  => false,
                    'min_files' => 1,
                    'max_files' => 1,
                ],
                'extrait_acte_naissance' => [
                    'label'     => 'extrait_acte_naissance',
                    'multiple'  => false,
                    'min_files' => 1,
                    'max_files' => 1,
                ],
                'matricule_fiscal' => [
                    'label'     => 'matricule_fiscal',
                    'multiple'  => false,
                    'min_files' => 1,
                    'max_files' => 1,
                ],
                'carte_electorale' => [
                    'label'     => 'carte_electorale',
                    'multiple'  => false,
                    'min_files' => 1,
                    'max_files' => 1,
                ],
                'pv_tutelle' => [
                    'label'     => 'pv_tutelle',
                    'multiple'  => false,
                    'min_files' => 0,
                    'max_files' => 1,
                ],
                'certificat_medical' => [
                    'label'     => 'certificat_medical',
                    'multiple'  => false,
                    'min_files' => 0,
                    'max_files' => 1,
                ],
                'certificat_medical' => [
                    'label'     => 'certificat_medical',
                    'multiple'  => false,
                    'min_files' => 0,
                    'max_files' => 1,
                ],
                'copie_moniteur' => [
                    'label'     => 'certificat_medical',
                    'multiple'  => false,
                    'min_files' => 0,
                    'max_files' => 1,
                ],
            ],
        ],
    ],

    'demande_etat_carriere' => [
        'documents' => [
            'multiple' => [
                'bulletins_salaire' => [
                    'label'     => 'bulletins_salaire',
                    'multiple'  => true,
                    'min_files' => 1,
                    'max_files' => 0,
                ],
                'documents_carriere' => [
                    'label'     => 'documents_carriere',
                    'multiple'  => true,
                    'min_files' => 1,
                    'max_files' => 0,
                ],
            ],

            'single' => [
                'lettre_nomination' => [
                    'label'     => 'lettre_nomination',
                    'multiple'  => false,
                    'min_files' => 1,
                    'max_files' => 1,
                ],
                'acte_mariage_acte_deces' => [
                    'label'     => 'acte_mariage_acte_deces',
                    'multiple'  => false,
                    'min_files' => 0,
                    'max_files' => 1,
                ],
            ],
        ],
    ],

    // Disk to use for file storage
    'disk' => 'public',
];
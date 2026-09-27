<?php

return [
    'slot_minutes' => 15,

    'start' => '14:00',
    'end' => '16:00',

    'slots_per_agent_per_day' => 8,

    'agent_role' => 'agent_rdv',
    'validator_role' => 'validateur_rdv',

    'visio' => [
        'activate_minutes_before' => 15,
        'keep_open_minutes_after' => 15,
        'jitsi_domain' => env(
            'RDV_JITSI_DOMAIN',
            'meet.jit.si'
        ),
    ],

    'reminder' => [
        'days_before' => 1,
        'at' => '08:00',
        'service_label' => 'Formalités',
        'channel' => 'appel_telephonique',
    ],
];
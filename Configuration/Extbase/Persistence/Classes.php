<?php

declare(strict_types=1);

return [
    \Innologi\Appointments\Domain\Model\FormField::class => [
        'properties' => [
            'sorting' => [
                'fieldName' => 'sorting',
            ],
        ],
    ],
    \Innologi\Appointments\Domain\Model\Address::class => [
        'tableName' => 'tt_address',
        'properties' => [
            'socialSecurityNumber' => [
                'fieldName' => 'tx_appointments_social_security_number',
            ],
            'creationProgress' => [
                'fieldName' => 'tx_appointments_creation_progress',
            ],
        ],
    ],
    \Innologi\Appointments\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
    ],
    \Innologi\Appointments\Domain\Model\FrontendUserGroup::class => [
        'tableName' => 'fe_groups',
    ],
];

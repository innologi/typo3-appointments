<?php

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Appointments',
    'Agenda',
    [
        \Innologi\Appointments\Controller\AgendaController::class => 'showMonth, showWeeks, none',
    ],
    // non-cacheable actions
    [],
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Appointments',
    'List',
    [
        \Innologi\Appointments\Controller\AppointmentController::class => 'list, show, new1, new2, processNew, simpleProcessNew, create, edit, update, delete, free, none',
    ],
    // non-cacheable actions
    [
        \Innologi\Appointments\Controller\AppointmentController::class => 'list, new1, new2, processNew, simpleProcessNew, create, edit, update, delete, free',
    ],
);

// create a cache specifically the date/time slots
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['appointments_slots'])
    || !is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['appointments_slots'])
) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['appointments_slots'] = [
        'options' => [
            'defaultLifetime' => 3600,
            'compression' => extension_loaded('zlib'),
        ],
        'groups' => ['pages', 'all'],
    ];
}

#$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \Innologi\Appointments\Hooks\Tcemain::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Core\Imaging\IconFactory::class]['overrideIconOverlay'][] = \Innologi\Appointments\Hooks\IconFactoryHook::class;

//add scheduler tasks
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Innologi\Appointments\Task\CleanUpTask::class] = [
    'extension' => 'Appointments',
    'title' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_be.xml:tx_appointments_task_cleanup.name',
    'description' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_be.xml:tx_appointments_task_cleanup.description',
    'additionalFields' => \Innologi\Appointments\Task\CleanUpTaskAdditionalFieldProvider::class,
];

<?php

defined('TYPO3_MODE') or die();

// add plugin flexform csh
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tt_content.pi_flexform.appointments_agenda.list',
    'EXT:appointments/Resources/Private/Language/locallang_csh_flexform_agenda.xml',
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tt_content.pi_flexform.appointments_list.list',
    'EXT:appointments/Resources/Private/Language/locallang_csh_flexform_list.xml',
);

// add task csh
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_appointments_csh_task_clean_up',
    'EXT:appointments/Resources/Private/Language/locallang_csh_task_clean_up.xml',
);

// add tca csh
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_appointments_domain_model_appointment',
    'EXT:appointments/Resources/Private/Language/locallang_csh_tx_appointments_domain_model_appointment.xml',
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_appointments_domain_model_agenda',
    'EXT:appointments/Resources/Private/Language/locallang_csh_tx_appointments_domain_model_agenda.xml',
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_appointments_domain_model_type',
    'EXT:appointments/Resources/Private/Language/locallang_csh_tx_appointments_domain_model_type.xml',
);
// @LOW it seems inline records CSH doesn't get displayed in T3 v8.7(.4), even though the mouse icon shows there is CSH
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_appointments_domain_model_formfield',
    'EXT:appointments/Resources/Private/Language/locallang_csh_tx_appointments_domain_model_formfield.xml',
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_appointments_domain_model_formfieldvalue',
    'EXT:appointments/Resources/Private/Language/locallang_csh_tx_appointments_domain_model_formfieldvalue.xml',
);

#@LOW is there a native datepicker [6.1+]?
#@LOW add icon for sysfolder <http://buzz.typo3.org/people/steffen-kamper/article/new-icons-for-my-pages/>
#@LOW add plugin preview <http://buzz.typo3.org/people/steffen-kamper/article/render-custom-preview-from-extension/>
#@TODO _______Warning for lack of support unfinished-list, removed by script checking sessionStorage support
#@TODO _______Folding of message-box unfinished-list
#@TODO _______Remove all button which marks the entire unfinished-list as DELETED, with a confirm box
#@TODO _______icon/message indicating incomplete data, per unfinished appointment, removed by script checking sessionStorage data
#@TODO __make messages dismissable
#@TODO __only show required legenda if there are required fields
#@TODO __add validation options to Address
#@LOW look at / replace Resources/Public/Icons
#@LOW unittesting
#@TODO currently, month and day names are taken from locallang. I should see if the php locale can be changed to the typo3 locale in order to rely on the Intl php ext and/or f:format.date
#@LOW use localization $arguments parameter
#@LOW consider using crop VH in Agenda views (not available in 4.5)
#@TODO default templates aren't tableless
#@LOW what about labelless edit/delete buttons? -->
#@TODO use data attributes instead in frontend, once I can go HTML5-only

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_appointments_domain_model_appointment');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_appointments_domain_model_agenda');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_appointments_domain_model_type');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_appointments_domain_model_formfield');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_appointments_domain_model_formfieldvalue');

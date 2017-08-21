<?php
defined('TYPO3_MODE') or die();

// add plugin flexform csh
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'tt_content.pi_flexform.appointments_agenda.list',
	'EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang_csh_flexform_agenda.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'tt_content.pi_flexform.appointments_list.list',
	'EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang_csh_flexform_list.xml'
);

// add task csh
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'tx_appointments_csh_task_clean_up',
	'EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang_csh_task_clean_up.xml'
);

// add tca csh
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'tx_appointments_domain_model_appointment',
	'EXT:appointments/Resources/Private/Language/locallang_csh_tx_appointments_domain_model_appointment.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'tx_appointments_domain_model_agenda',
	'EXT:appointments/Resources/Private/Language/locallang_csh_tx_appointments_domain_model_agenda.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'tx_appointments_domain_model_type',
	'EXT:appointments/Resources/Private/Language/locallang_csh_tx_appointments_domain_model_type.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'tx_appointments_domain_model_formfield',
	'EXT:appointments/Resources/Private/Language/locallang_csh_tx_appointments_domain_model_formfield.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'tx_appointments_domain_model_formfieldvalue',
	'EXT:appointments/Resources/Private/Language/locallang_csh_tx_appointments_domain_model_formfieldvalue.xml'
);

#@LOW is there a native datepicker [6.1+]?
#@TODO _document address-problems in 4.5: no cascade remove from FE
#@LOW add icon for sysfolder <http://buzz.typo3.org/people/steffen-kamper/article/new-icons-for-my-pages/>
#@LOW add plugin preview <http://buzz.typo3.org/people/steffen-kamper/article/render-custom-preview-from-extension/>
#@LOW see if utilizing errorAction (forward()?) for time-related errors is an option
#@TODO _______Warning for lack of support unfinished-list, removed by script checking sessionStorage support
#@TODO _______Folding of message-box unfinished-list
#@TODO _______Remove all button which marks the entire unfinished-list as DELETED, with a confirm box
#@TODO _______icon/message indicating incomplete data, per unfinished appointment, removed by script checking sessionStorage data
#@TODO __make messages dismissable
#@TODO __make * = required only visible when there are required fields
#@TODO __add validation options to Address
#@FIX do Manual
#@LOW look at / replace Resources/Public/Icons
#@LOW unittesting
#@TODO currently, month and day names are taken from locallang. I should see if the php locale can be changed to the typo3 locale in order to rely on strftime and/or f:format.date
#@LOW use localization $arguments parameter

//<http://docs.typo3.org/typo3cms/ExtbaseFluidBook/b-ExtbaseReference/Index.html>
#@TODO _the above link also mentions something about recordType use in a different chapter, that might be of use for address..


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_appointments_domain_model_appointment');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_appointments_domain_model_agenda');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_appointments_domain_model_type');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_appointments_domain_model_formfield');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_appointments_domain_model_formfieldvalue');


//set overlay icons
if (TYPO3_MODE === 'BE') {
	$GLOBALS['TBE_STYLES']['spriteIconApi']['spriteIconRecordOverlayNames']['tx_appointments_unfinished'] = 'status-overlay-missing';
	$GLOBALS['TBE_STYLES']['spriteIconApi']['spriteIconRecordOverlayNames']['tx_appointments_expired'] = 'status-overlay-deleted';
	array_unshift($GLOBALS['TBE_STYLES']['spriteIconApi']['spriteIconRecordOverlayPriorities'],'tx_appointments_expired','tx_appointments_unfinished');
}
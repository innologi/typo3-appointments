<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Agenda',
	'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang_be.xml:tx_appointments_plugin_agenda_title'
);

Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'List',
	'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang_be.xml:tx_appointments_plugin_list_title'
);

$pluginSignatureStart = str_replace('_','',$_EXTKEY) . '_';
$pluginSignature = $pluginSignatureStart . 'agenda';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_agenda.xml');
t3lib_extMgm::addLLrefForTCAdescr('tt_content.pi_flexform.'.$pluginSignature.'.list', 'EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang_csh_flexform_agenda.xml');
$pluginSignature = $pluginSignatureStart . 'list';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_list.xml');
t3lib_extMgm::addLLrefForTCAdescr('tt_content.pi_flexform.'.$pluginSignature.'.list', 'EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang_csh_flexform_list.xml');
#@TODO maak de New.html if condition voor timeslots in dateslots netter! (inline)
#@TODO make jslib get @ warnunload optional
#@FIXME expand warnunload to expire the appointment on leave page
#@FIXME sessions?
#@SHOULD make messages dismissable
#@SHOULD add icons to messages
#@SHOULD make * = required only visible when there are required fields
#@FIXME do Manual
#@SHOULD look at / replace Resources/Public/Icons
#@SHOULD unittesting?
#@TODO currently, month and day names are taken from locallang. I should see if the php locale can be changed to the typo3 locale in order to rely on strftime and/or f:format.date
#@TODO in hoeverre zijn mijn models entity of valueObject?
#@TODO fluid kan {settings} benaderen? :O
#@FIXME F5 op new appointment pagina = NOOOOOOO weg timeslot + time choice ALS ZE AL BEZET WAREN.. dus niet als verlopen. hmm
#@TODO wat gebeurt er als je bij edit de datum wijzigt?
#@TODO kunnen we datumkeuze niet gewoon nog steeds beschikbaar houden zodra je dateFirst doet?
#@SHOULD replace the following in the appointment list template once required TYPO3 version is upped to 4.7 or higher:
//{v:math.sum(a:'{v:math.product(a:appointment.type.hoursMutable,b:3600)}',b:appointment.crdate)}
//with:
//{appointment.type.hoursMutable -> v:math.product(b:3600) -> v:math.sum(b:appointment.crdate)}

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Appointment Scheduler');

t3lib_extMgm::addLLrefForTCAdescr('tx_appointments_domain_model_appointment', 'EXT:appointments/Resources/Private/Language/locallang_csh_tx_appointments_domain_model_appointment.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_appointments_domain_model_appointment');
$TCA['tx_appointments_domain_model_appointment'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment',
		'label' => 'begin_time',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'default_sortby' => 'ORDER BY begin_time DESC',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		#'requestUpdate' => 'type',
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'tx_appointments_creation_progress' => 'creation_progress', #@TODO add to enable fields with hook $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_page.php']['addEnableColumns']
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/Appointment.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_appointments_domain_model_appointment.gif'
	),
);

t3lib_extMgm::addLLrefForTCAdescr('tx_appointments_domain_model_agenda', 'EXT:appointments/Resources/Private/Language/locallang_csh_tx_appointments_domain_model_agenda.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_appointments_domain_model_agenda');
$TCA['tx_appointments_domain_model_agenda'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/Agenda.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_appointments_domain_model_agenda.gif'
	),
);

t3lib_extMgm::addLLrefForTCAdescr('tx_appointments_domain_model_type', 'EXT:appointments/Resources/Private/Language/locallang_csh_tx_appointments_domain_model_type.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_appointments_domain_model_type');
$TCA['tx_appointments_domain_model_type'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/Type.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_appointments_domain_model_type.gif'
	),
);

t3lib_extMgm::addLLrefForTCAdescr('tx_appointments_domain_model_formfield', 'EXT:appointments/Resources/Private/Language/locallang_csh_tx_appointments_domain_model_formfield.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_appointments_domain_model_formfield');
$TCA['tx_appointments_domain_model_formfield'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'sorting',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'requestUpdate' => 'field_type',
		'hideTable' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/FormField.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_appointments_domain_model_formfield.gif'
	),
);

#t3lib_div::loadTCA('tt_address');
#@SHOULD make it set a type as soon as IRRE supports it
#if (!isset($TCA['tt_address']['ctrl']['type'])) {
#	$TCA['tt_address']['ctrl']['type'] = 'tx_extbase_type';
#}
#$TCA['tt_address']['columns'][$TCA['tt_address']['ctrl']['type']]['config']['items'][] = array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tt_address.tx_extbase_type.Tx_Appointments_Address','Tx_Appointments_Address');
#$TCA['tt_address']['types']['Tx_Appointments_Address']['showitem'] = $TCA['tt_address']['types']['1']['showitem'];
#$TCA['tt_address']['types']['Tx_Appointments_Address']['showitem'] .= ',--div--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address,';
#$TCA['tt_address']['types']['Tx_Appointments_Address']['showitem'] = 'first_name, middle_name, last_name, name, gender, birthday, email, address, zip, city';
t3lib_extMgm::addTCAcolumns('tt_address', array(
	'tx_appointments_social_security_number' => array (
		'exclude' => 0,
		'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.social_security_number',
		'config' => array (
			'type' => 'input',
			'size' => 25,
			'max' => 255,
			'eval' => 'trim'
		)
	),
), 1);
t3lib_extMgm::addTCAcolumns('tt_address', array(
	'tx_appointments_creation_progress' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.creation_progress',
		'config' => array (
			'type' => 'none',
		)
	),
), 1);
t3lib_extMgm::addToAllTCAtypes('tt_address','tx_appointments_social_security_number');

t3lib_extMgm::addLLrefForTCAdescr('tx_appointments_domain_model_formfieldvalue', 'EXT:appointments/Resources/Private/Language/locallang_csh_tx_appointments_domain_model_formfieldvalue.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_appointments_domain_model_formfieldvalue');
$TCA['tx_appointments_domain_model_formfieldvalue'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfieldvalue',
		'label' => 'form_field',
		'label_alt' => 'value',
		'label_alt_force' => TRUE,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'sorting',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'hideTable' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/FormFieldValue.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_appointments_domain_model_formfieldvalue.gif'
	),
);

## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder

//set overlay icons
if (TYPO3_MODE === 'BE') {
	$TBE_STYLES['spriteIconApi']['spriteIconRecordOverlayNames']['tx_appointments_unfinished'] = 'status-overlay-missing';
	$TBE_STYLES['spriteIconApi']['spriteIconRecordOverlayNames']['tx_appointments_expired'] = 'status-overlay-deleted';
	array_unshift($TBE_STYLES['spriteIconApi']['spriteIconRecordOverlayPriorities'],'tx_appointments_expired','tx_appointments_unfinished');
}
?>
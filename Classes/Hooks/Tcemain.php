<?php
namespace Innologi\Appointments\Hooks;
/***************************************************************
 *  Copyright notice
*
*  (c) 2012 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 3 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
use Innologi\Appointments\Domain\Model\FormField;
/**
 * TCA PostProcess FormFieldValue
 *
 * Inspired by tt_address.
 *
 * FormFieldValue:
 * Currently UNUSED, only useful when we want time properties for appointment
 * to be adjusted automatically @ updates on formfieldvalues.
 *
 * Appointment:
 * Currently UNUSED, becomes useful when we take control of end_time, end_reserved and begin_reserved
 * out of the hands of BE-users. But do we really want to? Because there are other domain models
 * which influence these properties as well, and some of them are problematic. (i.e. when deleting an influentual inline record)
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tcemain {
	#@LOW what about email/calendar? that would be a good use of these hooks
	/**
	 * Automatically corrects time properties of parent appointment.
	 *
	 * @param string $action Record action
	 * @param string $table Domain model table
	 * @param integer $uid Uid of record
	 * @param array $propertyArray Reference to the changes of the record
	 * @param object $parentObj Parent object
	 * @return void
	 */
	public function processDatamap_postProcessFieldArray($action, $table, $uid, &$propertyArray, $parentObj) {
		// formfieldvalue
		if ($table === 'tx_appointments_domain_model_formfieldvalue') {
			if ($action === 'new' || (
					$action === 'update' && (
						isset($propertyArray['value']) || isset($propertyArray['form_field']) || isset($propertyArray['hidden'])
					)
				)
			) {
				$oldValue = 0;
				$addValue = TRUE;

				if ($action === 'update') {
					//on update, the $propertyArray only contains altered fields, and parent object doesn't contain the appointment property
					$ffVal = $this->getRecord($table,$uid);
					$oldValue = intval($ffVal['value']) * 60;
					$oldFormField = $ffVal['form_field'];
					$ffVal = array_merge($ffVal,$propertyArray);
				} else {
					$ffVal = $propertyArray;
					$oldFormField = $ffVal['form_field'];
					#@LOW has no appointment id, kinda useless until it does huh?
				}

				$table = 'tx_appointments_domain_model_formfield';
				$formField = $this->getRecord($table,$ffVal['form_field']);
				$function = intval($formField['function']);
				$addTimeId = FormField::FUNCTION_ADDTIME;

				if ($oldFormField !== $ffVal['form_field']) { //has formfield changed?
					$formFieldOld = $this->getRecord($table,$oldFormField);
					$functionOld = intval($formFieldOld['function']);
					if ($functionOld === $addTimeId && $function !== $addTimeId) { //went from an addtime formfield
						$addValue = FALSE; //should only substract previous value
					} elseif($functionOld !== $addTimeId && $function === $addTimeId) { //went to an addtime formfield
						$oldValue = 0; //previous value is not important
					} elseif ($functionOld !== $addTimeId && $function !== $addTimeId) { //neither was an addtime formfield
						return; //no influence on time
					}
				}

				//hiding a formfieldvalue should substract value
				if (intval($ffVal['hidden']) === 1) {
					$addValue = FALSE;
				} elseif (intval($ffVal['hidden']) === 0) { //unhiding should add value
					$addValue = TRUE;
					$oldValue = 0;
				}

				//if current formfield isn't addtime, still allow substraction
				if ($function === $addTimeId || !$addValue) {
					$table = 'tx_appointments_domain_model_appointment';
					$appointment = $this->getRecord($table,$ffVal['appointment']);
					$add = ($addValue ? (intval($ffVal['value']) * 60) : 0) - $oldValue;
					$updateValues = array(
							'end_time' => $appointment['end_time'] + $add,
							'end_reserved' => $appointment['end_reserved'] + $add
					);
					$this->updateRecord($table,$ffVal['appointment'],$updateValues);
				}
			}
			#@LOW a delete doesn't come by here.. find out how to catch that one

			// appointment
		} elseif ($table === 'tx_appointments_domain_model_appointment' && (
			$action === 'new' || (
				$action === 'update' && isset($propertyArray['begin_time'])
			)
		)) {
			if ($action === 'update') {
				//on update, the $propertyArray only contains altered fields
				$appointment = $this->getRecord($table,$uid,$parentObj);
				$appointment = array_merge($appointment,$propertyArray);
			} else {
				$appointment = $propertyArray;
			}

			#@LOW postprocess of type is required as well
			#@LOW postprocess of formfield is required as well
			$type = $this->getRecord('tx_appointments_domain_model_type',$appointment['type']);
			$reserveBlock = $type['between_minutes'] * 60;
			$defaultDuration = $type['default_duration'] * 60;

			#@LOW do check here on whether time is allowed?

			$propertyArray['begin_reserved'] = $appointment['begin_time'] - $reserveBlock;
			$propertyArray['end_time'] = $appointment['begin_time'] + $defaultDuration;
			$propertyArray['end_reserved'] = $propertyArray['end_time'] + $reserveBlock;
		}
	}

	/**
	 * Gets a record from the parent object or the database.
	 *
	 * @param string $table Domain model table
	 * @param integer $uid Uid of record
	 * @param object $parentObject Parent object
	 * @return array
	 */
	protected function getRecord($table, $uid, $parentObj = NULL) {
		if ($parentObj !== NULL && isset($parentObj->datamap[$table][$uid])) {
			return $parentObj->datamap[$table][$uid];
		}
		$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$table,'uid = '.$uid);
		return $row[0];
	}

	/**
	 * Updates a record.
	 *
	 * @param string $table Domain model table
	 * @param integer $uid Uid of record
	 * @param array $values Values to update in format $field=>$value
	 * @return boolean
	 */
	protected function updateRecord($table, $uid, $values) {
		return $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,'uid = '.$uid,$values);
	}

}
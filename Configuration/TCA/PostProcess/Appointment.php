<?php

/***************************************************************
 *  Copyright notice
*
*  (c) 2012 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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

/**
 * TCA PostProcess Appointment
 *
 * Inspired by tt_address.
 *
 * Currently UNUSED, becomes useful when we take control of end_time, end_reserved and begin_reserved
 * out of the hands of BE-users. But do we really want to? Because there are other domain models
 * which influence these properties as well, and some of them are problematic. (i.e. when deleting an influentual inline record)
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Configuration_TCA_PostProcess_Appointment {
	#@SHOULD what about email/calendar? that would be a good use of these hooks
   	/**
	 * Automatically corrects time properties based on begin_time.
	 *
	 * @param string $action Record action
	 * @param string $table Domain model table
	 * @param integer $uid Uid of record
	 * @param array $propertyArray Reference to the changes of the record
	 * @param object $parentObj Parent object
	 * @return void
	 */
	public function processDatamap_postProcessFieldArray($action, $table, $uid, &$propertyArray, $parentObj) {

		if ($table === 'tx_appointments_domain_model_appointment' && (
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

			#@SHOULD postprocess of type is required as well
			#@SHOULD postprocess of formfield is required as well
			$type = $this->getRecord('tx_appointments_domain_model_type',$appointment['type']);
			$reserveBlock = $type['between_minutes'] * 60;
			$defaultDuration = $type['default_duration'] * 60;

			#@SHOULD do check here on whether time is allowed?

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

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_address/class.tx_ttaddress_compat.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_address/class.tx_ttaddress_compat.php']);
}

?>
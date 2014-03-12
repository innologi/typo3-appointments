<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
 * Timer Viewhelper
 *
 * Creates a timer for the appointment's reserved timeslot,
 * displaying the remaining minutes and seconds in format 8:45
 *
 * <span class="reservation-timer">
 * 		<a:timer appointment="{appointment}" timerMinutes="{settings.freeSlotInMinutes}" format="timer|minutes|seconds"/>
 * </span>
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_ViewHelpers_TimerViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Render timer
	 *
	 * @param integer $timerMinutes Total number of minutes the timer normally
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment
	 * @param string $format timer|minutes|seconds
	 * @return string
	 */
	public function render($timerMinutes = 1, Tx_Appointments_Domain_Model_Appointment $appointment = NULL, $format = "timer") {
		if ($appointment === NULL) {
			$appointment = $this->renderChildren();
			if (!$appointment instanceof Tx_Appointments_Domain_Model_Appointment) {
				// @LOW throw exception
				return;
			}
		}
		switch ($format) {
			case 'timer':
				$timer = Tx_Appointments_Utility_GeneralUtility::getAppointmentTimer(
					$appointment, (int) $timerMinutes
				);
				break;
			case 'minutes':
				$seconds = Tx_Appointments_Utility_GeneralUtility::getTimerRemainingSeconds(
					$appointment, (int) $timerMinutes
				);
				$max = ceil($seconds / 60);
				$timer = '~' . ($max > 1 ? '1-' : '') . $max;
				break;
			case 'seconds':
				$timer = Tx_Appointments_Utility_GeneralUtility::getTimerRemainingSeconds(
					$appointment, (int) $timerMinutes
				);
				break;
		}

		return $timer;
	}

}
?>
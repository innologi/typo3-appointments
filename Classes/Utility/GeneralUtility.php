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
 * General Utility class
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Utility_GeneralUtility {

	/**
	 * Returns remaining seconds of the timer of the appointment's timeslot.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment
	 * @param integer $timerMinutes
	 * @return integer
	 */
	public static function getTimerRemainingSeconds(Tx_Appointments_Domain_Model_Appointment $appointment, $timerMinutes = 1) {
		// default number of seconds remaining before timeslot is freed
		$remainingSeconds = $timerMinutes * 60;
		$secondsBusy = time() - $appointment->getCrdate();
		$remainingSeconds = $remainingSeconds - $secondsBusy;

		if ($remainingSeconds < 0) {
			$remainingSeconds = 0;
		}

		// store it for later convenience
		$appointment->setRemainingSeconds($remainingSeconds);
		return $remainingSeconds;
	}

	/**
	 * Returns string representation of the appointment's timeslot timer.
	 * e.g. 8:45
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment
	 * @param integer $timerMinutes
	 * @return string
	 */
	public static function getAppointmentTimer(Tx_Appointments_Domain_Model_Appointment $appointment, $timerMinutes = 1) {
		$remainingSeconds = $appointment->getRemainingSeconds();
		if ($remainingSeconds === NULL) {
			$remainingSeconds = self::getTimerRemainingSeconds($appointment, $timerMinutes);
		}

		$timer = floor($remainingSeconds / 60) . ':' . date('s', $remainingSeconds);
		return $timer;
	}

}
?>
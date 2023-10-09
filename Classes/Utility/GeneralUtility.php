<?php
namespace Innologi\Appointments\Utility;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use Innologi\Appointments\Domain\Model\Appointment;
/**
 * General Utility class
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class GeneralUtility {

	/**
	 * Returns remaining seconds of the timer of the appointment's timeslot.
	 *
	 * @param Appointment $appointment
	 * @param integer $timerMinutes
	 * @return integer
	 */
	public static function getTimerRemainingSeconds(Appointment $appointment, $timerMinutes = 1) {
		// default number of seconds remaining before timeslot is freed
		$remainingSeconds = $timerMinutes * 60;
		$secondsBusy = time() - $appointment->getReservationTime();
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
	 * @param Appointment $appointment
	 * @param integer $timerMinutes
	 * @return string
	 */
	public static function getAppointmentTimer(Appointment $appointment, $timerMinutes = 1) {
		$remainingSeconds = $appointment->getRemainingSeconds();
		if ($remainingSeconds === NULL) {
			$remainingSeconds = self::getTimerRemainingSeconds($appointment, $timerMinutes);
		}

		$timer = floor($remainingSeconds / 60) . ':' . date('s', $remainingSeconds);
		return $timer;
	}

	/**
	 * Strips designated GET parameters from any URL
	 * and returns the result.
	 *
	 * Parameters are considered case-insensitive.
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return string
	 */
	public static function stripGetParameters(string $url, array $parameters) {
		foreach ($parameters as $parameter) {
			$pos = strpos(strtolower($url), strtolower($parameter . '='));
			if ($pos !== FALSE) {
				$endPos = strpos($url, '&', $pos);
				if ($endPos === FALSE) {
					$start = $pos - 1;
					$url = substr_replace($url, '', $start);
				} else {
					$start = $pos;
					$length = ($endPos - $pos ) + 1;
					$url = substr_replace($url, '', $start, $length);
				}
			}
		}
		return $url;
	}

	/**
	 * Wraps a GET parameter with the expected extension plugin prefix
	 * and returns the result.
	 *
	 * @param string $parameter
	 * @param string $extensionKey
	 * @param string $pluginName
	 * @return string
	 */
	public static function wrapGetParameter($parameter, $extensionKey, $pluginName) {
		// e.g. tx_appointments_list[$parameter]
		$parameter = 'tx_' . $extensionKey . '_' . $pluginName . '[' . $parameter . ']';
		return strtolower($parameter);
	}

	/**
	 * Splits any URL by its parameters, sorts the resulting
	 * array and then returns it.
	 *
	 * @param string $url
	 * @return array
	 */
	public static function splitUrlAndSortInArray($url) {
		$parts = explode('?', $url);
		// more than 1 element?
		if (isset($parts[1])) {
			$parameters = explode('&', array_pop($parts));
			$parts = array_merge($parts, $parameters);
			sort($parts);
		}
		return $parts;
	}

}
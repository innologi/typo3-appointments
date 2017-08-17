<?php
namespace Innologi\Appointments\ViewHelpers;
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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use Innologi\Appointments\Domain\Model\Appointment;
use Innologi\Appointments\Utility\GeneralUtility;
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
class TimerViewHelper extends AbstractViewHelper {

	/**
	 * Render timer
	 *
	 * @param integer $timerMinutes Total number of minutes the timer normally
	 * @param Appointment $appointment The appointment
	 * @param string $format timer|minutes|seconds
	 * @return string
	 */
	public function render($timerMinutes = 1, Appointment $appointment = NULL, $format = "timer") {
		if ($appointment === NULL) {
			$appointment = $this->renderChildren();
			if (!$appointment instanceof Appointment) {
				// @LOW throw exception
				return;
			}
		}
		switch ($format) {
			case 'timer':
				$timer = GeneralUtility::getAppointmentTimer(
					$appointment, (int) $timerMinutes
				);
				break;
			case 'minutes':
				$seconds = GeneralUtility::getTimerRemainingSeconds(
					$appointment, (int) $timerMinutes
				);
				$max = ceil($seconds / 60);
				$timer = '~' . ($max > 1 ? '1-' : '') . $max;
				break;
			case 'seconds':
				$timer = GeneralUtility::getTimerRemainingSeconds(
					$appointment, (int) $timerMinutes
				);
				break;
		}

		return $timer;
	}

}
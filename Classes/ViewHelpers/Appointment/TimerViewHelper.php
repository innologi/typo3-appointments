<?php

namespace Innologi\Appointments\ViewHelpers\Appointment;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use Innologi\Appointments\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Timer Viewhelper
 *
 * Creates a timer for the appointment's reserved timeslot,
 * displaying the remaining minutes and seconds in format 8:45
 *
 * <span class="reservation-timer">
 * 		<a:appointment.timer appointment="{appointment}" timerMinutes="{settings.freeSlotInMinutes}" format="timer|minutes|seconds"/>
 * </span>
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TimerViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeChildren = true;

    /**
     * @var boolean
     */
    protected $escapeOutput = true;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('appointment', Appointment::class, 'The appointment to show a timer for.', true);
        $this->registerArgument('timerMinutes', 'integer', 'The number of minutes it takes for a timeslot to be freed again.', false, 1);
        $this->registerArgument('format', 'string', 'The type of timer to show: timer|minutes|seconds', false, 'timer');
    }

    /**
     * @return boolean
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        /** @var Appointment $appointment */
        $appointment = $arguments['appointment'];
        $timerMinutes = (int) $arguments['timerMinutes'];
        $timer = '';

        switch ($arguments['format']) {
            case 'timer':
                $timer = GeneralUtility::getAppointmentTimer(
                    $appointment,
                    $timerMinutes,
                );
                break;
            case 'minutes':
                $seconds = GeneralUtility::getTimerRemainingSeconds(
                    $appointment,
                    $timerMinutes,
                );
                $max = ceil($seconds / 60);
                $timer = '~' . ($max > 1 ? '1-' : '') . $max;
                break;
            case 'seconds':
                $timer = GeneralUtility::getTimerRemainingSeconds(
                    $appointment,
                    $timerMinutes,
                );
                break;
            default:
                // @LOW throw exception
        }

        return $timer;
    }
}

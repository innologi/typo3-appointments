<?php

namespace Innologi\Appointments\ViewHelpers\Appointment;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Is Mutable Viewhelper
 *
 * Determines whether an appointment falls within its "mutable hours", effectively allowing changes.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IsMutableViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('appointment', Appointment::class, 'The appointment to check if it is still mutable.', true);
        $this->registerArgument(
            name: 'time',
            type: 'integer',
            description: 'Timestamp to evaluate with.',
            required: false,
            defaultValue: GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp'),
        );
    }

    /**
     * @return boolean
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        /** @var Appointment $appointment */
        $appointment = $arguments['appointment'];
        $mutableEndTime = ($appointment->getType()->getHoursMutable() * 3600) + $appointment->getReservationTime();
        return $arguments['time'] < $mutableEndTime;
    }
}

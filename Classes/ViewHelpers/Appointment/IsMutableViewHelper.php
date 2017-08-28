<?php
namespace Innologi\Appointments\ViewHelpers\Appointment;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use Innologi\Appointments\Domain\Model\Appointment;
/**
 * Strip Property Index Viewhelper
 *
 * Strips ObjectStorage indexes used in propertypaths of our form fields.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class IsMutableViewHelper extends AbstractViewHelper {
	use CompileWithRenderStatic;

	/**
	 * @var boolean
	 */
	protected $escapeChildren = TRUE;

	/**
	 * @var boolean
	 */
	protected $escapeOutput = TRUE;

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('appointment', Appointment::class, 'The appointment to check if it is still mutable.', TRUE);
		$this->registerArgument('time', 'integer', 'Timestamp to evaluate with.', FALSE, $GLOBALS['EXEC_TIME']);
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 */
	public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		/** @var Appointment $appointment */
		$appointment = $arguments['appointment'];
		$mutableEndTime = ($appointment->getType()->getHoursMutable() * 3600) + $appointment->getCrdate();
		return $arguments['time'] < $mutableEndTime;
	}

}
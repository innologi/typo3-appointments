<?php
namespace Innologi\Appointments\Task;
/***************************************************************
 *  Copyright notice
*
*  (c) 2013 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Extbase\Scheduler\Task;
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * Clean Up Scheduler Task
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class CleanUpTask extends Task {

	/**
	 * Age
	 *
	 * @var integer
	 */
	protected $age;

	/**
	 * Returns age
	 *
	 * @return integer
	 */
	public function getAge() {
		return $this->age;
	}

	/**
	 * Sets age
	 *
	 * @param integer $age
	 * @return void
	 */
	public function setAge($age) {
		$this->age = $age;
	}

	/**
	 * Executes task business logic
	 *
	 * @return	boolean		True on success, false on failure
	 */
	public function execute() {
		$businessLogic = GeneralUtility::makeInstance(CleanUpTaskLogic::class, $this->age);
		return $businessLogic->execute();
	}

}

?>
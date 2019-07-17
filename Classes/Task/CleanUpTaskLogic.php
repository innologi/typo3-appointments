<?php
namespace Innologi\Appointments\Task;
/***************************************************************
 *  Copyright notice
*
*  (c) 2013-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use Innologi\Appointments\Core\BootstrapTask;
use Innologi\Appointments\Domain\Repository\AppointmentRepository;
/**
 * CleanUp Scheduler Task business logic
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class CleanUpTaskLogic extends BootstrapTask {

	/**
	 * Age
	 *
	 * @var integer
	 */
	protected $age;

	/**
	 * appointmentRepository
	 *
	 * @var AppointmentRepository
	 */
	protected $appointmentRepository;

	/**
	 * __construct
	 *
	 * @param integer $age
	 * @return void
	 */
	public function __construct($age) {
		$parameters = array();
		parent::__construct('Innologi', 'appointments', 'cleanuptask', $parameters);
		$this->initRepositories();
		$this->age = $age;
	}

	/**
	 * Initialize repositories (DI doesn't work here)
	 *
	 * @return void
	 */
	protected function initRepositories() {
		$this->appointmentRepository = $this->objectManager->get(AppointmentRepository::class);
	}

	/**
	 * Execute business logic
	 *
	 * @return boolean
	 */
	public function execute() {
		$expiredAppointments = $this->appointmentRepository->findExpiredByAge($this->age);
		foreach ($expiredAppointments as $appointment) {
			$this->appointmentRepository->remove($appointment);
		}

		return TRUE;
	}

}
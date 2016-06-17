<?php

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

/**
 * CleanUp Scheduler Task business logic
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Task_CleanUpTaskLogic extends Tx_Appointments_Core_BootstrapTask {

	/**
	 * Age
	 *
	 * @var integer
	 */
	protected $age;

	/**
	 * appointmentRepository
	 *
	 * @var Tx_Appointments_Domain_Repository_AppointmentRepository
	 */
	protected $appointmentRepository;

	/**
	 * formFieldValueRepository
	 *
	 * @var Tx_Appointments_Domain_Repository_FormFieldValueRepository
	 */
	protected $formFieldValueRepository;

	/**
	 * __construct
	 *
	 * @param integer $age
	 * @throws Exception
	 * @return void
	 */
	public function __construct($age) {
		$parameters = array();
		parent::__construct('appointments', 'cleanuptask', $parameters);
		$this->initRepositories();
		$this->age = $age;
	}

	/**
	 * Initialize repositories (DI doesn't work here)
	 *
	 * @return void
	 */
	protected function initRepositories() {
		$this->appointmentRepository = $this->objectManager->get('Tx_Appointments_Domain_Repository_AppointmentRepository');
		$this->formFieldValueRepository = $this->objectManager->get('Tx_Appointments_Domain_Repository_FormFieldValueRepository');
	}

	/**
	 * Execute business logic
	 *
	 * @return boolean
	 */
	public function execute() {
		$expiredAppointments = $this->appointmentRepository->findExpiredByAge($this->age);
		foreach ($expiredAppointments as $appointment) {
			$appointment instanceof Tx_Appointments_Domain_Model_Appointment;
			$this->appointmentRepository->remove($appointment);
		}

		//we need to delete these manually because of the cascade remove problem
		$orphanedFormFieldValues = $this->formFieldValueRepository->findOrphaned();
		foreach ($orphanedFormFieldValues as $formFieldValue) {
			$this->formFieldValueRepository->remove($formFieldValue);
		}

		return TRUE;
	}

}
?>
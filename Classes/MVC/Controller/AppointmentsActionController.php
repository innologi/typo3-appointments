<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
 * Appointments Action Controller.
 *
 * Replacement for the Extbase Action Controller for inheritance by the
 * domain controllers. It unites all appointments-specific code that is
 * to be shared between all domain controllers.
 *
 * Provides a (necessary) try and catch construction for resolving
 * controller arguments from the database.
 *
 * Also provides a united error messaging feature in initializeAction(),
 * containing all necessary checks like those agenda or feUser related.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_MVC_Controller_AppointmentsActionController extends Tx_Appointments_MVC_Controller_SettingsOverrideController {

	/**
	 * agendaRepository
	 *
	 * @var Tx_Appointments_Domain_Repository_AgendaRepository
	 */
	protected $agendaRepository;

	/**
	 * appointmentRepository
	 *
	 * @var Tx_Appointments_Domain_Repository_AppointmentRepository
	 */
	protected $appointmentRepository;

	/**
	 * typeRepository
	 *
	 * @var Tx_Appointments_Domain_Repository_TypeRepository
	 */
	protected $typeRepository;

	/**
	 * @var Tx_Appointments_Service_UserService
	 */
	protected $userService;

	/**
	 * @var Tx_Appointments_Domain_Service_SlotService
	 */
	protected $slotService;

	/**
	 * Logged in frontend user
	 *
	 * @var Tx_Appointments_Domain_Model_FrontendUser
	 */
	protected $feUser;

	/**
	 * Agenda
	 *
	 * @var Tx_Appointments_Domain_Model_Agenda
	 */
	protected $agenda;

	/**
	 * Indicates if user needs to be logged in
	 *
	 * Can be overridden by extending domain controllers
	 *
	 * @var boolean
	 */
	protected $requireLogin = TRUE;

	/**
	 * injectAgendaRepository
	 *
	 * @param Tx_Appointments_Domain_Repository_AgendaRepository $agendaRepository
	 * @return void
	 */
	public function injectAgendaRepository(Tx_Appointments_Domain_Repository_AgendaRepository $agendaRepository) {
		$this->agendaRepository = $agendaRepository;
	}

	/**
	 * injectAppointmentRepository
	 *
	 * @param Tx_Appointments_Domain_Repository_AppointmentRepository $appointmentRepository
	 * @return void
	 */
	public function injectAppointmentRepository(Tx_Appointments_Domain_Repository_AppointmentRepository $appointmentRepository) {
		$this->appointmentRepository = $appointmentRepository;
	}

	/**
	 * injectTypeRepository
	 *
	 * @param Tx_Appointments_Domain_Repository_TypeRepository $typeRepository
	 * @return void
	 */
	public function injectTypeRepository(Tx_Appointments_Domain_Repository_TypeRepository $typeRepository) {
		$this->typeRepository = $typeRepository;
	}

	/**
	 * Injects the User Service
	 *
	 * @param Tx_Appointments_Service_UserService $userService
	 * @return void
	 */
	public function injectUserService(Tx_Appointments_Service_UserService $userService) {
		$this->userService = $userService;
	}


	/**
	 * Injects the Slot Service
	 *
	 * @param Tx_Appointments_Domain_Service_SlotService $slotService
	 * @return void
	 */
	public function injectSlotService(Tx_Appointments_Domain_Service_SlotService $slotService) {
		$this->slotService = $slotService;
	}

	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * Sets some prerequisite variables. If it fails because of any error related to these,
	 * it will set appropriate error messages and redirect to the appropriate action.
	 *
	 * @return void
	 */
	protected function initializeAction() {
		if ($this->actionMethodName !== 'noneAction') {
			$errors = array();

			//is user logged in as required?
			$this->feUser = $this->userService->getCurrentUser();
			if ($this->requireLogin && !$this->feUser) {
				$errors[] = Tx_Extbase_Utility_Localization::translate('tx_appointments.login_error', $this->extensionName);
			}

			//is an agenda record set?
			$this->agenda = $this->agendaRepository->findByUid($this->settings['agendaUid']);
			if ($this->agenda === NULL) {
				$errors[] = Tx_Extbase_Utility_Localization::translate('tx_appointments.no_agenda', $this->extensionName);
			}

			//errors!
			if (!empty($errors)) {
				foreach ($errors as $flashMessage) {
					$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);
				}
				$this->forward('none');
			}

		}
	}

	/**
	 * action none
	 *
	 * If the plugin is supposed to do nothing but present flash messages.
	 *
	 * Note that you should FORWARD, not REDIRECT, to this action,
	 * or we would need conditions and a redirect here as well.
	 *
	 * @return void
	 */
	public function noneAction() { }

	/**
	 * Gets types according to settings.
	 *
	 * @return Tx_Extbase_Persistence_QueryResultInterface
	 */
	protected function getTypes() {
		$superUser = $this->userService->isInGroup($this->settings['suGroup']);
		$this->view->assign('superUser', $superUser);

		$typeArray = t3lib_div::trimExplode(',', $this->settings['appointmentTypeList'], 1);
		$types = empty($typeArray) ? $this->typeRepository->findAll($superUser) : $this->typeRepository->findIn($typeArray,$superUser);
		if ($types->valid()) {
			//types found
			return $types;
		} else {
			//no types found
			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments.no_types', $this->extensionName);
			$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);
			$this->forward('none');
		}
	}

	/**
	 * Maps arguments delivered by the request object to the local controller arguments.
	 *
	 * Try and catch construction makes sure a controller argument which no longer exists
	 * in the database, doesn't produce a full stop. It catches it, and produces a flashMessage.
	 *
	 * This concerns f.e. an object that was deleted in TCA or FE or by task. An appointment
	 * in the making which expired but wasn't deleted yet, will still be retrievable.
	 *
	 * @return void
	 */
	protected function mapRequestArgumentsToControllerArguments() {
		$objectDeleted = FALSE;

		try {
			parent::mapRequestArgumentsToControllerArguments();
		} catch (Tx_Extbase_MVC_Exception_InvalidArgumentValue $e) { #@TODO I should syslog these as well
			$objectDeleted = TRUE;
		} catch (Tx_Extbase_Property_Exception_TargetNotFound $e) {
			$objectDeleted = TRUE;
		} catch (Tx_Appointments_MVC_Exception_PropertyDeleted $e) {
			//in case not the original argument, but one of its object-properties no longer exist, try to redirect to the appropriate action
			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments.appointment_property_deleted', $this->extensionName);
			$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);

			$redirectTo = 'list';
			$arguments = array();
			if ($this->request->hasArgument($argumentName)) {
				$appointment = $this->request->getArgument('appointment'); //get from request, as controller argument mapping was just disrupted
				if (isset($appointment['__identity'])) { //getting the entire array would also require the hmac property, we only need uid
					$arguments['appointment'] = $appointment['__identity'];
					//sending to the appropriate form will regenerate missing objects #@TODO in ALL cases? needs more testing (specifically .address & .formFieldValues and lazy vs non-lazy)
					switch ($this->actionMethodName) {
						case 'createAction':
							$redirectTo = 'new2';
							break;
						case 'updateAction':
							$redirectTo = 'edit';
					}
				}
			}
			$this->redirect($redirectTo,NULL,NULL,$arguments);
		}

		if ($objectDeleted) {
			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments.appointment_no_longer_available', $this->extensionName); #@TODO __the message doesn't cover cases where the appointment was not finished
			$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);
			$this->redirect('list');
		}
	}

}
?>
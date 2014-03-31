<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2014 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
class Tx_Appointments_MVC_Controller_ActionController extends Tx_Appointments_MVC_Controller_SettingsOverrideController {

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

			//no errors? initialize slotService as it is used in most actions
			$this->slotService->initialize(
					$this->extensionName,
					intval($this->settings['freeSlotInMinutes']),
					intval($this->settings['shiftSlotPerInterval'])
			);
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
	 * @return array
	 */
	protected function getTypes() {
		$superUser = $this->userService->isInGroup($this->settings['suGroup']);
		$this->view->assign('superUser', $superUser);

		#$this->typeUidArray = t3lib_div::trimExplode(',', $this->settings['appointmentTypeList'], 1); #@TODO _need to reuse this as showTypes/allowTypes or something
		#$types = empty($this->typeUidArray) ? $this->typeRepository->findAll($superUser) : $this->typeRepository->findIn($this->typeUidArray,$superUser);
		$types = $superUser ? $this->agenda->getTypes()->toArray() : $this->typeRepository->findIn($this->agenda->getTypes()->toArray())->toArray();
		if (!empty($types)) {
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
		$propertyDeleted = FALSE;

		try {
			parent::mapRequestArgumentsToControllerArguments();
		} catch (Tx_Extbase_MVC_Exception_InvalidArgumentValue $e) {
			$objectDeleted = TRUE;
		} catch (Tx_Extbase_Property_Exception_TargetNotFound $e) {
			$objectDeleted = TRUE;
		} catch (Tx_Appointments_MVC_Exception_PropertyDeleted $e) {
			$propertyDeleted = TRUE;
		} catch (t3lib_error_Exception $e) {
			$propertyDeleted = TRUE;
		}

		if ($objectDeleted) {
			t3lib_div::sysLog('An appointment disappeared while an feuser tried to interact with it: ' . $e->getMessage(),
				$this->extensionName, t3lib_div::SYSLOG_SEVERITY_NOTICE);

			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments.appointment_no_longer_available', $this->extensionName); #@TODO __the message doesn't cover cases where the appointment was not finished
			#@TODO deprecated, see add() for replacement
			$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);
			$this->redirect('list');
		}
		if ($propertyDeleted) {
			t3lib_div::sysLog('An appointment is missing a property which was most likely deleted by a backend user: ' . $e->getMessage(),
				$this->extensionName, t3lib_div::SYSLOG_SEVERITY_ERROR);

			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments.appointment_property_deleted', $this->extensionName);
			$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);

			//in case not the original argument, but one of its object-properties no longer exist, try to redirect to the appropriate action
			$redirectTo = 'list';
			$arguments = array();
			$argumentName = 'appointment';
			if ($this->request->hasArgument($argumentName)) {
				$appointment = $this->request->getArgument($argumentName); //get from request, as controller argument mapping was just disrupted
				if (isset($appointment['__identity'])) { //getting the entire array would also require the hmac property, we only need uid
					$arguments[$argumentName] = $appointment['__identity'];
					//sending to the appropriate form will regenerate missing objects (but not their values)
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
	}

	/**
	 * Adds the needed validators to the Arguments:
	 *
	 * - Validators checking the data type from the @param annotation
	 * - Custom validators specified with validate annotations.
	 * - Model-based validators (validate annotations in the model)
	 * - Custom model validator classes
	 *
	 * This override works around the 6.2-bug where it no longer supports
	 * dontvalidate for the deprecatedPropertyMapper when a matching
	 * Domain Model validator is present.
	 *
	 * @return void
	 */
	protected function initializeActionMethodValidators() {
		if (version_compare(TYPO3_branch, '6.2', '<')) {
			parent::initializeActionMethodValidators();
		} else {
			// @deprecated since Extbase 1.4.0, will be removed two versions after Extbase 6.1

			$parameterValidators = $this->validatorResolver->buildMethodArgumentsValidatorConjunctions(get_class($this), $this->actionMethodName);
			$dontValidateAnnotations = array();

			$methodTagsValues = $this->reflectionService->getMethodTagsValues(get_class($this), $this->actionMethodName);
			if (isset($methodTagsValues['dontvalidate'])) {
				$dontValidateAnnotations = $methodTagsValues['dontvalidate'];
			}

			foreach ($this->arguments as $argument) {
				$validator = $parameterValidators[$argument->getName()];
				if (array_search('$' . $argument->getName(), $dontValidateAnnotations) === FALSE) {
					$baseValidatorConjunction = $this->validatorResolver->getBaseValidatorConjunction($argument->getDataType());
					if ($baseValidatorConjunction !== NULL) {
						$validator->addValidator($baseValidatorConjunction);
					}
					// moved this INSIDE the if, instead of outside
					$argument->setValidator($validator);
				}
			}
		}
	}

}
?>
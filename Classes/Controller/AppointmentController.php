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
 * Appointment Controller
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Controller_AppointmentController extends Tx_Appointments_MVC_Controller_SettingsOverrideController {

	/**
	 * appointmentRepository
	 *
	 * @var Tx_Appointments_Domain_Repository_AppointmentRepository
	 */
	protected $appointmentRepository;

	/**
	 * agendaRepository
	 *
	 * @var Tx_Appointments_Domain_Repository_AgendaRepository
	 */
	protected $agendaRepository;

	/**
	 * typeRepository
	 *
	 * @var Tx_Appointments_Domain_Repository_TypeRepository
	 */
	protected $typeRepository;

	/**
	 * @var Tx_Appointments_Domain_Service_SlotService
	 */
	protected $slotService;

	/**
	 * @var Tx_Appointments_Service_EmailService
	 */
	protected $emailService;

	/**
	 * @var Tx_Appointments_Service_UserService
	 */
	protected $userService;

	/**
	 * Logged in frontend user
	 *
	 * @var Tx_Extbase_Domain_Model_FrontendUser
	 */
	protected $feUser;

	/**
	 * Agenda
	 *
	 * @var Tx_Appointments_Domain_Model_Agenda
	 */
	protected $agenda;

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
	 * injectAgendaRepository
	 *
	 * @param Tx_Appointments_Domain_Repository_AgendaRepository $agendaRepository
	 * @return void
	 */
	public function injectAgendaRepository(Tx_Appointments_Domain_Repository_AgendaRepository $agendaRepository) {
		$this->agendaRepository = $agendaRepository;
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
	 * Injects the Slot Service
	 *
	 * @param Tx_Appointments_Domain_Service_SlotService $slotService
	 * @return void
	 */
	public function injectSlotService(Tx_Appointments_Domain_Service_SlotService $slotService) {
		$this->slotService = $slotService;
	}

	/**
	 * Injects the Email Service
	 *
	 * @param Tx_Appointments_Service_EmailService $emailService
	 * @return void
	 */
	public function injectEmailService(Tx_Appointments_Service_EmailService $emailService) {
		$emailService->setExtensionName($this->extensionName);
		$this->emailService = $emailService;
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
			if (!$this->feUser) {
				$errors[] = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.login_error', $this->extensionName);
			}

			//is an agenda record set?
			$this->agenda = $this->agendaRepository->findByUid($this->settings['agendaUid']);
			if ($this->agenda === NULL) {
				$errors[] = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.no_agenda', $this->extensionName);
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
	 * action list
	 *
	 * Shows the list of future appointments of the logged-in user
	 *
	 * @return void
	 */
	public function listAction() {
		//turns out getting the user id is not enough: not all fe_users are of the correct record_type
		$appointments = $this->appointmentRepository->findByAgendaAndFeUser($this->agenda,$this->feUser,new DateTime());
		$this->view->assign('appointments', $appointments);

		//users can only edit/delete appointments when the appointment type's mutable hours hasn't passed yet
		//a superuser can ALWAYS mutate, so 'now = 0' fixes that
		$now = $this->userService->isInGroup($this->settings['suGroup']) ? 0 : time();
		$this->view->assign('now', $now);
	}

	/**
	 * action show
	 *
	 * Certain conditions get to show more data (i.e. being superuser and/or owner)
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment to show
	 * @dontvalidate $appointment
	 * @return void
	 */
	public function showAction(Tx_Appointments_Domain_Model_Appointment $appointment) {
		#@TODO you should be able to return to an agenda view if the plugin came from there but a list plugin is n/a .. or is that even possible?
		//check if current user is member of the superuser group
		$superUser = $this->userService->isInGroup($this->settings['suGroup']);
		$showMore = TRUE;
		if ($superUser) { //we're not using vhs viewhelpers for this, because we need to set $showMore anyway and a viewhelper-alternative is overkill
			//su can edit any appointment that hasn't started yet
			$endTime = $appointment->getBeginReserved()->getTimestamp();
		} elseif ($this->feUser->getUid() == $appointment->getFeUser()->getUid()) { //check if current user is the owner of the appointment
			//non-su can edit his own appointment if hoursMutable hasn't passed yet
			$endTime = $appointment->getType()->getHoursMutable() * 3600 + $appointment->getCrdate();
		} else { //if not su nor owner, limit rights
			$showMore = FALSE;
			$endTime = 0;
		}
		$mutable = time() < $endTime;

		$this->view->assign('showMore', $showMore);
		$this->view->assign('mutable', $mutable); //edit / delete
		$this->view->assign('superUser', $superUser);
		$this->view->assign('appointment', $appointment);
	}

	/**
	 * action new1
	 *
	 * Part 1 of a multi-step form. This action by itself creats a multi-step form as well.
	 * This action sets all fields that are required before timeslot-reservation, step by step.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment that's being created
	 * @param string $dateFirst The timestamp that should be set before a type was already chosen
	 * @dontvalidate $appointment
	 * @return void
	 */
	public function new1Action(Tx_Appointments_Domain_Model_Appointment $appointment = NULL, $dateFirst = NULL) {
		//find types
		$types = $this->getTypes();
		#@SHOULD in a seperate action that forwards/redirects or not.. consider the extra overhead, it's probably not worth it
		if (isset($dateFirst[0])) { //overrides in case an appointment-date is picked through agenda
			//removes types that can't produce timeslots on the dateFirst date
			$beginTime = new DateTime();
			$beginTime->setTimestamp($dateFirst);
			$types = $this->limitTypesByDate($types, $this->agenda, clone $beginTime);
			if (!empty($types)) {
				if ($appointment === NULL) { #@TODO this is ALWAYS NULL, currently. But what if we allow appointment in the datefirst link? Can it be done? If so, we should have an ELSE too
					$appointment = new Tx_Appointments_Domain_Model_Appointment();
					$appointment->setType(current($types));
					$appointment->setBeginTime($beginTime);
				}
			} else {
				//no types available on chosen time, so no appointments either.
				//the condition also functions as a check for a valid dateFirst
				$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_create_no_types', $this->extensionName);
				$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);
			}
		}

		if ($appointment !== NULL) {
			//type chosen! (or dateFirst)

			$freeSlotInMinutes = intval($this->settings['freeSlotInMinutes']);
			$dateSlots = $this->slotService->getDateSlots($appointment->getType(), $this->agenda, $freeSlotInMinutes); #@TODO can we throw error somewhere when this one is empty, about the type not having any available timeslots?
			$this->view->assign('dateSlots', $dateSlots);

			if ($appointment->getBeginTime() !== NULL) {
				//date chosen! (or dateFirst)

				//an impossible type/date combo will result in $timeSlots == FALSE, so the user can't continue without re-picking
				$timeSlots = $this->slotService->getTimeSlots($dateSlots,$appointment);
				$this->view->assign('timeSlots', $timeSlots);
			}
		}

		$this->view->assign('types', $types);
		$this->view->assign('appointment', $appointment);
		$this->view->assign('step', 1);
	}

	/**
	 * action new2
	 *
	 * Part 2 (or actually 4) of a multi-step form. This action requires to be preceded by the
	 * processNew action. It builds the form used to fill out the entire appointment details,
	 * and displays a timer for the timeslot reservation.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment that's being created
	 * @dontvalidate $appointment
	 * @return void
	 */
	public function new2Action(Tx_Appointments_Domain_Model_Appointment $appointment) {
		$this->appointmentRepository->update($appointment); //necessary to retain fieldvalues of validation-error-returned appointments

		//limit the available types by the already chosen timeslot
		$types = $this->limitTypesByAppointment($this->getTypes(),$appointment);

		//dummy dateslots are never empty, no error checking necessary
		$dateSlots = $this->slotService->getDummyDateSlot(clone $appointment->getBeginTime());
		$timeSlots = $this->slotService->getTimeSlots($dateSlots,$appointment);

		//add message that depends on creation-progress
		$this->addTimerMessage($appointment);

		$formFieldValues = $appointment->getFormFieldValues();
		$formFields = clone $appointment->getType()->getFormFields(); //formFields is modified for this process but not to persist, hence clone
		$formFieldValues = $this->addMissingFormFields($formFields,$formFieldValues);

		//adding the formFieldValues already will get them persisted too soon, empty and unused, so we're assigning them separately from $appointment
		$this->view->assign('formFieldValues', $formFieldValues);
		$this->view->assign('timeSlots', $timeSlots);
		$this->view->assign('dateSlots', $dateSlots);
		$this->view->assign('types', $types);
		$this->view->assign('appointment', $appointment);
		$warnUnloadText = str_replace('$1',
				Tx_Extbase_Utility_Localization::translate('tx_appointments_list.submit_new', $this->extensionName),
				Tx_Extbase_Utility_Localization::translate('tx_appointments_list.warn_unload', $this->extensionName)
		);
		$this->view->assign('warnUnloadText', $warnUnloadText);
		$this->view->assign('step', 2); #@TODO check of het template niet wat meer van dit variabel af kan hangen zonder dat ik meerdere forms hoef te definiëren
	}

	/**
	 * action processType
	 *
	 * Part 2 (or actually 4) of a multi-step form. This action requires to be preceded by the
	 * processNew action. It builds the form used to fill out the entire appointment details,
	 * and displays a timer for the timeslot reservation.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment that's being created
	 * @param Tx_Appointments_Domain_Model_Type $type The type to change to
	 * @dontvalidate $appointment
	 * @dontvalidate $type
	 * @return void
	 */
	public function simpleProcessNewAction(Tx_Appointments_Domain_Model_Appointment $appointment, Tx_Appointments_Domain_Model_Type $type = NULL) {
		#@TODO reset previous type and correct func args & doc
		$this->appointmentRepository->update($appointment);
		$this->slotService->resetStorageObject($appointment->getType(),$this->agenda); //necessary to persist changes to the available timeslots
		$arguments = array(
				'appointment' => $appointment
		);
		$this->redirect('new2',NULL,NULL,$arguments);
	}

	/**
	 * action processNew
	 *
	 * This part of the multi-step form performs some preliminary time-related validation checks,
	 * adds missing properties, persists / refreshes timeslot reservations, and then redirects to
	 * the appropriate action.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment that's being created
	 * @param boolean $crossAppointment Indicates an overlap caused by $appointment
	 * @dontvalidate $appointment
	 * @return void
	 */
	public function processNewAction(Tx_Appointments_Domain_Model_Appointment $appointment, $crossAppointment = '') {
		$appointment->setAgenda($this->agenda);
		$appointment->setFeUser($this->feUser);


		$freeSlotInMinutes = intval($this->settings['freeSlotInMinutes']);
		if ($this->slotService->isTimeSlotAllowed($appointment,$freeSlotInMinutes)) {
			$this->calculateTimes($appointment); //set the remaining DateTime properties of appointment
			$timerStart = FALSE;


			//when a validation error ensues, we don't want the unfinished appointment being re-added, hence the check
			if ($appointment->_isNew()) {
				$this->appointmentRepository->add($appointment);
				$timerStart = TRUE;
			} else {


				//expired appointments should be refreshed
				if ($appointment->getCreationProgress() === Tx_Appointments_Domain_Model_Appointment::EXPIRED) { //it's possible to get here when expired and the appointment no longer exists, thus throwing an exception #@TODO caught by.. ? SettingsOverride? I don't remember!
					//.. unless a crossAppointment check returned true
					if ($crossAppointment) { //indicates there is an overlap caused by this appointment's own add-time formfield
						#@TODO __can we indicate how much time overlaps?
						$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_create_crosstime', $this->extensionName);
						$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);
						#@TODO __mark the time(-related) field(s) like a validation error
					} else {
						//checks whether the timeslot was changed or not
						$cleanBeginTime = $appointment->_getCleanProperty('beginTime');
						if ($cleanBeginTime instanceof DateTime && $cleanBeginTime->getTimestamp() !== $appointment->getBeginTime()->getTimestamp()) {
							$timerStart = TRUE;
						} else {
							//messages for the same timeslot again (refresh)
							$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_timerrefresh', $this->extensionName);
							$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::INFO);
						}
						$appointment->setCreationProgress(Tx_Appointments_Domain_Model_Appointment::UNFINISHED); #@TODO __cleanup task for expired records?
					}
				}


				$this->appointmentRepository->update($appointment);
			}
			$this->slotService->resetStorageObject($appointment->getType(),$this->agenda); //necessary to persist changes to the available timeslots


			if ($timerStart) {
				//message for a new timeslot
				$flashMessage = str_replace('$1', $freeSlotInMinutes,
						Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_timerstart', $this->extensionName)
				);
				$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::INFO);
			}


			$action = 'new2';
		} else {
			//chosen timeslot is not allowed
			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.timeslot_not_allowed', $this->extensionName);
			$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);

			$action = 'new1';
			#@TODO __mark the time field like a validation error
		}


		//send to appropriate action, with changed $appointment
		$arguments = array(
				'appointment' => $appointment
		);
		$this->redirect($action,NULL,NULL,$arguments);
	}

	/**
	 * action create
	 *
	 * Notice that the appointment isn't added but updated. This is because the processNewAction
	 * will have already added an unfinished appointment. We just need to change its creation_progress flag.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment to create
	 * @return void
	 */
	public function createAction(Tx_Appointments_Domain_Model_Appointment $appointment) {
		$this->calculateTimes($appointment); //times can be influenced by formfields

		#@TODO __think hard about what should and shouldn't happen when someone submits an expired appointment, because we have to carefully consider the consequences of having NO isTimeSlotAllowed in cases the firstAvailableTime > timeslot LONG since choosing the timeslot
		//as a safety measure, first check if there are appointments which occupy time which this one claims
		//this is necessary in case another appointment is created or edited before this one is saved.
		//isTimeSlotAllowed() does not suffice by itself, because of formfields that add time and can cause overlap
		if ($this->crossAppointments($appointment)) { //an appointment was found that makes the current one's times not possible
			//updating it as expired so the fields get saved while not blocking any appointment that might have caused crossAppointment to be TRUE
			$appointment->setCreationProgress(Tx_Appointments_Domain_Model_Appointment::EXPIRED);
			$this->appointmentRepository->update($appointment);
			//not resetting the storage object just yet because this one still has a chance regaining his prematurely ended reservation
			$arguments = array(
				'appointment' => $appointment,
				'crossAppointment' => TRUE
			);
			$this->redirect('processNew',NULL,NULL,$arguments);
		} else {
			$appointment->setCreationProgress(Tx_Appointments_Domain_Model_Appointment::FINISHED);
			$this->appointmentRepository->update($appointment);
			$this->slotService->resetStorageObject($appointment->getType(),$this->agenda); //persist changes in timeslots, in case they were freed up for some reason

			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_create_success', $this->extensionName);
			$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::OK);

			$this->performMailingActions('create',$appointment);

			$this->redirect('list');
		}
	}

	/**
	 * action edit
	 *
	 * $appointment should not be validated, because changes to the extension or some editing in TCA might cause
	 * validation errors, and we can't fix those in FE if editAction isn't allowed. Validation is done in updateAction anyway.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment to edit
	 * @param string $changedDate Changed date
	 * @dontvalidate $appointment
	 * @return void
	 */
	public function editAction(Tx_Appointments_Domain_Model_Appointment $appointment, $changedDate = NULL) {
		$superUser = $this->userService->isInGroup($this->settings['suGroup']);

		$formFieldValues = $appointment->getFormFieldValues();
		$formFields = clone $appointment->getType()->getFormFields(); //formFields is modified for this process but not to persist, hence clone
		//it's possible to delete relevant formfieldvalues in TCA, so we'll re-add them here if that's the case
		$formFieldValues = $this->addMissingFormFields($formFields,$formFieldValues);
		#@TODO create the possibility to differentiate between shown and allowed-to-create types, so that we can encourage big changes to appointment types to happen through a copy instead, which will allow as to preserve old appointments in full glory

		//if the date was changed, reflect it on the form but don't persist it yet
		if ($changedDate !== NULL) {
			$appointment->setBeginTime(new DateTime($changedDate)); #@SHOULD couldn't we do it this way with dateFirst either? Ymd instead of timestamp so we can use construct
			$appointment->_memorizeCleanState('beginTime'); //makes sure it isn't persisted automatically
		}
		$freeSlotInMinutes = intval($this->settings['freeSlotInMinutes']); #@SHOULD is 0 supported everywhere? it should be, but I think I left a <1 check somewhere. Also timer messages should react to 0
		$dateSlots = $this->slotService->getDateSlotsIncludingCurrent($appointment,$freeSlotInMinutes,TRUE);
		$timeSlots = $this->slotService->getTimeSlots($dateSlots,$appointment);

		$this->view->assign('dateSlots', $dateSlots);
		$this->view->assign('timeSlots', $timeSlots);
		$this->view->assign('appointment', $appointment);
		//adding the formFieldValues already will get them persisted too soon, empty and unused, so we're assigning them separately from $appointment
		$this->view->assign('formFieldValues', $formFieldValues);
		$this->view->assign('superUser', $superUser);
	}

	/**
	 * action update
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment to update
	 * @return void
	 */
	public function updateAction(Tx_Appointments_Domain_Model_Appointment $appointment) {
		$this->calculateTimes($appointment); //times can be influenced by formfields

		#@TODO betekent calculateTimes nu niet dat hij altijd als modified wordt geregistreerd?
		//as a safety measure, first check if there are appointments which occupy time which this one claims
		//this is necessary in case another appointment is created or edited before this one is saved
		if ($this->crossAppointments($appointment)) {
			//an appointment was found that makes the current one's times not possible
			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_update_crosstime', $this->extensionName);
			$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);
			$this->forward('edit'); #@TODO __mark add-time fields?
		} else {
			$this->appointmentRepository->update($appointment);
			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_update_success', $this->extensionName);
			$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::OK);

			$this->slotService->resetStorageObject($appointment->getType(),$appointment->getAgenda()); //persist changes in timeslots, in case they were freed up for some reason

			$this->performMailingActions('update',$appointment);

			$this->redirect('list');
		}
	}

	/**
	 * action delete
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment to delete
	 * @dontvalidate $appointment
	 * @return void
	 */
	public function deleteAction(Tx_Appointments_Domain_Model_Appointment $appointment) {
		$this->appointmentRepository->remove($appointment);
		$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_delete_success', $this->extensionName);
		$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::OK);

		$this->slotService->resetStorageObject($appointment->getType(),$appointment->getAgenda()); //persist changes in timeslots

		$this->performMailingActions('delete',$appointment);

		$this->redirect('list');
	}

	/**
	 * action free
	 *
	 * When an unfinished appointment is started, one is allowed to free up the chosen timeslot.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment's time to free up
	 * @dontvalidate $appointment
	 * @return void
	 */
	public function freeAction(Tx_Appointments_Domain_Model_Appointment $appointment) {
		//set it to expired to free up the timeslot, but still pass along the appointment so that it may be reconstituted in the same session
		$appointment->setCreationProgress(Tx_Appointments_Domain_Model_Appointment::EXPIRED); #@TODO __try to include the appointment formfields with this form somehow, so that we can save it, and then display them in a disabled form
		$this->appointmentRepository->update($appointment);
		$this->slotService->resetStorageObject($appointment->getType(),$this->agenda); //persist changes in timeslots

		$arguments = array(
				'appointment' => $appointment
		);
		$this->redirect('new1',NULL,NULL,$arguments);
	}




	/**
	 * There are cases when FormFieldValues are missing from an appointment, f.e. when the appointment is brand new,
	 * or by deleting relevant FormFieldValues in TCA. This causes FE to not render those FormFields at all
	 * @ that specific appointment, thus would also prevent setting them in the edit action. This method will make
	 * sure an appointment's FormFieldValues storage contains one for each (missing) FormField.
	 *
	 * Attaching a formFieldValue to an existing appointment storage (edit action), would be persisted before the
	 * appointment itself was updated, while the relevant form and update action would persist it yet again as a
	 * different field. To prevent persisting the first, this method creates and returns a new storage.
	 *
	 * Because the new formFieldValue is already added to the original storage during an attempted update/create, a
	 * validation error would cause the fields to retain their values in the subsequent edit/new action.
	 *
	 * Also, the explicit sorting values of FormFields are used here to re-arrange the FormFieldValues.
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_Appointments_Domain_Model_FormField> $formFields
	 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_Appointments_Domain_Model_FormFieldValue> $formFieldValues
	 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_Appointments_Domain_Model_FormFieldValue>
	 */
	protected function addMissingFormFields(Tx_Extbase_Persistence_ObjectStorage $formFields, Tx_Extbase_Persistence_ObjectStorage $formFieldValues) {
		$items = array();
		$order = array();

		//formfieldvalues already available
		foreach ($formFieldValues as $formFieldValue) {
			$formField = $formFieldValue->getFormField();
			if ($formField !== NULL) {
				$uid = $formField->getUid();
				$items[$uid] = $formFieldValue; //I'd prefer $items[$sorting] = $formFieldValue, but the sorting value can be messed with to cause duplicate keys
				$order[$uid] = $formField->getSorting();
				if (isset($formFields[$formField])) {
					$formFields->detach($formField);
				}
			} else {
				//the formfield was removed at some point, so should its value be
				$formFieldValues->detach($formFieldValue); //this is the original storage, so this is persisted
			}
		}

		$formFields = $formFields->toArray(); //formFields is lazy, a count on a lazy objectstorage will give the wrong number if a detach took place
		if (count($formFields)) {
			$newStorage = new Tx_Extbase_Persistence_ObjectStorage();

			//formfieldvalues to add
			foreach ($formFields as $formField) {
				$uid = $formField->getUid();
				$formFieldValue = new Tx_Appointments_Domain_Model_FormFieldValue();
				$formFieldValue->setFormField($formField);
				$items[$uid] = $formFieldValue;
				$order[$uid] = $formField->getSorting();
			}

			//NOTE: extbase will set sorting value to the currently arranged order, when persisted
			natsort($order);
			foreach($order as $uid=>$sorting) {
				$newStorage->attach($items[$uid]);
			}

			//newStorage will not contain formfieldvalues which belonged to formfield that have been removed
			return $newStorage;
		}

		return $formFieldValues;
	}

	/**
	 * Calculates the time-properties of an appointments, and sets them.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment of which to calculate the time properties
	 * @return void
	 */
	protected function calculateTimes(Tx_Appointments_Domain_Model_Appointment $appointment) {
		$timestamp = $appointment->getBeginTime()->getTimestamp();
		$type = $appointment->getType();

		$reserveBlock = $type->getBetweenMinutes() * 60;
		$appointment->setBeginReserved(new DateTime(strftime("%Y-%m-%d %H:%M:%S",$timestamp-$reserveBlock)));

		$defaultDuration = $type->getDefaultDuration() * 60;
		$timestamp += $defaultDuration;

		//some formfields can set extra time
		$formFieldValues = $appointment->getFormFieldValues();
		foreach($formFieldValues as $formFieldValue) {
			$formField = $formFieldValue->getFormField();
			if ($formField->getFunction() === Tx_Appointments_Domain_Model_FormField::FUNCTION_ADDTIME) {
				$fieldType = $formField->getFieldType();
				$value = $formFieldValue->getValue();
				switch ($fieldType) {
					case Tx_Appointments_Domain_Model_FormField::TYPE_TEXTLARGE:
					case Tx_Appointments_Domain_Model_FormField::TYPE_TEXTSMALL:
						$timestamp += intval($value) * 60;
						break;
					case Tx_Appointments_Domain_Model_FormField::TYPE_SELECT:
						#@TODO moet mogelijk zijn met de timeAdd optie
					case Tx_Appointments_Domain_Model_FormField::TYPE_BOOLEAN:
						#@TODO moet mogelijk zijn met de timeAdd optie
				}
			}
		}
		$appointment->setEndTime(new DateTime(strftime("%Y-%m-%d %H:%M:%S",$timestamp)));
		$appointment->setEndReserved(new DateTime(strftime("%Y-%m-%d %H:%M:%S",$timestamp+$reserveBlock)));
	}

	/**
	 * Adds the timer message for a currently reserved (or expired) timeslot.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment Appointment which uses the timeslot
	 * @return void
	 */
	protected function addTimerMessage(Tx_Appointments_Domain_Model_Appointment $appointment) {
		//calculate remaining seconds
		$freeSlotInMinutes = intval($this->settings['freeSlotInMinutes']);
		$remainingSeconds = $freeSlotInMinutes * 60; //default number of seconds remaining before timeslot is freed
		$secondsBusy = time() - $appointment->getCrdate();
		$remainingSeconds = $remainingSeconds - $secondsBusy;

		//when the appointment was flagged 'expired' in the current pagehit, (e.g. page refresh)
		//this $appointment reference might not yet be up to date, so we have to check
		//$remainingSeconds < 0 for those specific cases
		if ($remainingSeconds < 0 && $appointment->getCreationProgress() === Tx_Appointments_Domain_Model_Appointment::UNFINISHED) {
			$appointment->setCreationProgress(Tx_Appointments_Domain_Model_Appointment::EXPIRED);
			$appointment->_memorizePropertyCleanState('creationProgress'); //if we don't register EXPIRED as clean state, setting it to unfinished later won't be recognized by persistence!
		}

		//inform of timer
		if ($appointment->getCreationProgress() === Tx_Appointments_Domain_Model_Appointment::UNFINISHED) {
			$flashMessage = str_replace(
					'$1',
					'<span class="reservation-timer">' . floor($remainingSeconds/60) . ':' . date('s',$remainingSeconds) . '</span>',
					Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_timer', $this->extensionName)
			);
			$flashHeader = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_timer_header', $this->extensionName);
			$flashState = t3lib_FlashMessage::INFO;
		} else { //warn of expiration
			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_expired', $this->extensionName);
			$flashHeader = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_expired_header', $this->extensionName);
			$flashState = t3lib_FlashMessage::WARNING; #@TODO __transform automatically with javascript
		}
		$this->flashMessageContainer->add($flashMessage,$flashHeader,$flashState);
	}

	/**
	 * Checks to see if an appointment's time properties are taken up by any other appointment.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment to check
	 * @return boolean
	 */
	protected function crossAppointments(Tx_Appointments_Domain_Model_Appointment $appointment) {
		$crossAppointments = $this->appointmentRepository->findCrossAppointments($appointment);
		return !empty($crossAppointments);
	}

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
			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.no_types', $this->extensionName);
			$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);
			$this->forward('none');
		}
	}

	/**
	 * Limits the allowed types based on available timeslots on the given DateTime.
	 *
	 * @param Iterator|Array $types Previous types result
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda Agenda to check
	 * @param DateTime $dateTime DateTime to get dateslot for
	 * @return array Contains types that have an available timeslot
	 */
	protected function limitTypesByDate($types, Tx_Appointments_Domain_Model_Agenda $agenda, DateTime $dateTime) {
		$newTypes = array();
		$freeSlotInMinutes = intval($this->settings['freeSlotInMinutes']);
		foreach ($types as $type) {
			$slotStorage = $this->slotService->getSingleDateSlot($type, $agenda, $freeSlotInMinutes, clone $dateTime);
			if ($slotStorage->valid()) { //returns true only if it contains at least one valid dateslot
				$newTypes[] = $type;
			}
		}

		return $newTypes;
	}

	/**
	 * Limits the allowed types based on appointment properties.
	 *
	 * @param Iterator|Array $types Previous types result
	 * @param Tx_Appointments_Domain_Model_Appointment $excludeAppointment Appointment to exclude in available timeslot calculation
	 * @return array Contains types that have an available timeslot
	 */
	protected function limitTypesByAppointment($types, Tx_Appointments_Domain_Model_Appointment $appointment) {
		$newTypes = array();
		$freeSlotInMinutes = intval($this->settings['freeSlotInMinutes']);
		$timeSlotKey = $appointment->getBeginTime()->format(Tx_Appointments_Domain_Service_SlotService::TIMESLOT_KEY_FORMAT);

		foreach ($types as $type) {
			$slotStorage = $this->slotService->getSingleDateSlotIncludingCurrent($appointment, $freeSlotInMinutes, $type);
			$timeSlots = $this->slotService->getTimeSlots($slotStorage,$appointment);
			if ($timeSlots && isset($timeSlots[$timeSlotKey])) {
				$newTypes[] = $type;
			}
		}

		return $newTypes;
	}

	/**
	 * Performs all mailing actions appropriate to $action.
	 *
	 * @param string $action The email action [create / update / delete]
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment to inform about
	 */
	protected function performMailingActions($action,Tx_Appointments_Domain_Model_Appointment $appointment) {
		$this->emailService->setControllerContext($this->controllerContext); //can't be done @ injection because controllerContext won't be initialized yet

		if (!$this->emailService->sendAction($action,$appointment)) {
			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.email_error', $this->extensionName);
			$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);
		}
	}

}
?>
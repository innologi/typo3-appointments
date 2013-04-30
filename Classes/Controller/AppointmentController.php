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
	protected $agendaRepository; #@SHOULD what's this one used for again?

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
	protected $feUser = NULL;

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
	 * Sets the logged in user and if none, sends the user to LoginError
	 *
	 * @return void
	 */
	protected function initializeAction() {
		$this->feUser = $this->userService->getCurrentUser();
		if (!$this->feUser && $this->actionMethodName !== 'loginErrorAction') {
			//message to logged out users is taken care of by listAction
			$this->redirect('loginError');
		}
	}

	/**
	 * action login-error
	 *
	 * The plugin requires a logged in frontend user. This action presents
	 * the code to be executed when there is no logged in frontend user.
	 *
	 * @return void
	 */
	public function loginErrorAction() {
		$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.login_error', $this->extensionName);
		$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);
	}

	/**
	 * action list
	 *
	 * Shows the list of future appointments of the logged-in user
	 *
	 * @return void
	 */
	public function listAction() {
		$agendaUid = $this->settings['agendaUid'];
		$agenda = $this->agendaRepository->findByUid($agendaUid);
		$this->view->assign('agenda', $agenda);

		if ($agenda !== NULL && $this->feUser) { #@TODO remove the feUser check when a noLoginAction is here
			//turns out getting the user id is not enough: not all fe_users are of the correct record_type
			$appointments = $this->appointmentRepository->findByAgendaAndFeUser($agenda,$this->feUser,new DateTime());
			$this->view->assign('appointments', $appointments);
			//users can only edit/delete appointments when the appointment type's mutable hours hasn't passed yet
			//a superuser can ALWAYS mutate, so now = 0 fixes that
			$superUser = $this->userService->isInGroup($this->settings['suGroup']);
			$this->view->assign('now', $superUser ? 0 : time());
		} else {
			#@TODO error flash on no agenda
		}
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
	 * action new
	 *
	 * This is a multi-step action, returning an appointment with new properties that indicate each next step.
	 * In the final step, the appointment is already added as unfinished. Unfinished appointments
	 * will keep the timeslot occupied until either cleared manually or expired automatically, or until finished.
	 *
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda the appointment belongs to
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment that's being created
	 * @param string $buildCreate Indicates that the actual/final appointment creation form should be build
	 * @param string $dateFirst
	 * @dontvalidate $appointment
	 * @return void
	 */
	public function newAction(Tx_Appointments_Domain_Model_Agenda $agenda, Tx_Appointments_Domain_Model_Appointment $appointment = NULL, $buildCreate = '', $dateFirst = NULL) {
		$superUser = $this->userService->isInGroup($this->settings['suGroup']);

		#@TODO see if we can get rid of $buildCreate  when newAction is split
		$buildCreate = ($buildCreate === '1') ? TRUE : FALSE;

		//find types
		$typeArray = t3lib_div::trimExplode(',', $this->settings['appointmentTypeList'], 1);
		$types = empty($typeArray) ? $this->typeRepository->findAll($superUser) : $this->typeRepository->findIn($typeArray,$superUser);
		$freeSlotInMinutes = intval($this->settings['freeSlotInMinutes']);

		if (isset($dateFirst[0])) { //overrides in case an appointment-date is picked through agenda
			//removes types that can't produce timeslots on the dateFirst date
			$types = $this->limitTypesByTime($types, $agenda, $dateFirst); #@TODO cache?
			if (!empty($types)) {
				$type = $appointment === NULL ? current($types) : $appointment->getType();
				$beginTime = $dateFirst; //this is only useful for the next block if appointment === NULL, so it never overwrites an already picked beginTime
				$dateSlots = $this->slotService->getSingleDateSlot($type, $agenda, $freeSlotInMinutes, $dateFirst); //takes it from the storage set by limitTypes
			} else {
				//no types available on chosen time, so no appointments either
				$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_create_no_types', $this->extensionName);
				$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);
			}
		}

		if ($appointment !== NULL) {
			//step 1 reached

			$type = $appointment->getType();

			#@TODO doc after other TODOs
			if (!isset($dateSlots)) { //helps to avoid overwriting an override
				if ($buildCreate && $appointment->getBeginTime() !== NULL) {
					$dateSlots = $this->slotService->getSingleDateSlot($type,$agenda,$freeSlotInMinutes,$appointment->getBeginTime()->getTimestamp()); #@TODO this circumvents the problem where there are no timeslots available when F5-ing, but is it efficient?
					if ($dateSlots->count() === 0) { #@FIXME THIS IS A TERRIBLE TEMPORARY WORKAROUND, FIX IT (also, timer refresh automatically is NOT ALLOWED)
						$temp = new Tx_Appointments_Domain_Model_DateSlot();
						$temp->setTimestamp($appointment->getBeginTime()->getTimestamp());
						$temp->setKey($appointment->getBeginTime()->format(Tx_Appointments_Domain_Service_SlotService::DATESLOT_KEY_FORMAT));
						$temp->setDayName($appointment->getBeginTime()->format('l'));
						$dayShort = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.day_s'.$appointment->getBeginTime()->format('N'), $this->extensionName);
						$temp->setLabel($dayShort . ' ' . $appointment->getBeginTime()->format('d-m-Y'));
						$dateSlots->attach($temp);
					}
				} else {
					$dateSlots = $this->slotService->getDateSlots($type, $agenda, $freeSlotInMinutes); #@TODO can we throw error somewhere when this one is empty?
				}
			}

			$beginTime = $appointment->getBeginTime();
			if ($beginTime !== NULL) {
				//step 2 reached

				if (($timeSlots = $this->slotService->getTimeSlots($dateSlots,$appointment)) !== FALSE) {
					if ($buildCreate) {
						//step 3 reached

						//indicates if the appointment should be treated as new for the user
						if ($appointment->_isNew() || $appointment->getRefresh()) {
							$appointment->setCreationProgress(Tx_Appointments_Domain_Model_Appointment::UNFINISHED);
							$appointment->setRefresh(FALSE);

							//message indicating reservation time start
							$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_create_timerstart', $this->extensionName);
							$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::INFO);
						}

						if ($this->slotService->isTimeSlotAllowed($timeSlots,$appointment) !== FALSE) { #@TODO I don't think this is useful anymore :P
							//limit the still available types by the already chosen timeslot
							$excludeAppointment = $appointment->_isNew() ? 0 : $appointment->getUid();
							#@FIXME why does it show Test when the date is out of range for Test?
							$types = $this->limitTypesByTime($types, $agenda, $beginTime->getTimestamp(), $excludeAppointment); #@TODO cache?

							$appointment->setAgenda($agenda);

							$formFieldValues = $appointment->getFormFieldValues();
							$formFieldArray = $type->getFormFields()->toArray();
							if ($formFieldValues->count() !== count($formFieldArray)) { //a check here, because on validation error, they'll already exist
								//fields that did not yet exist will get an UID @ a manual persist
								$formFieldValues = $this->addMissingFormFields($formFieldArray,$formFieldValues);
								#$appointment->setFormFieldValues($formFieldValues); #@TODO do this or dont? does it have advantages?
							}
							//adding the formFieldValues already will get them persisted too soon, empty and unused, so we're assigning them separately from $appointment
							$this->view->assign('formFieldValues', $formFieldValues);

							if ($appointment->getFeUser() === NULL) { //a check because on validation error, it'll already exist
								$appointment->setFeUser($this->feUser);
							}

							$this->calculateTimes($appointment); //set the remaining DateTime properties
							$remainingSeconds = $freeSlotInMinutes * 60; //number of seconds remaining before timeslot is freed

							//when a validation error ensues, we don't want the unfinished appointment being re-added, hence the check
							if ($appointment->_isNew()) { #@TODO check all the inline doc around these changes
								$this->appointmentRepository->add($appointment);
							} else {
								//recalculate remainingSeconds
								$secondsBusy = time() - $appointment->getCrdate();
								$remainingSeconds = $remainingSeconds - $secondsBusy; #@SHOULD we make this a (transient?) property of class appointment?

								//when the appointment was flagged 'expired' in the current pagehit,
								//this $appointment reference might not yet be up to date,
								//so we have to check $remainingSeconds < 0 for those specific cases #@TODO check if this is still the case when we have a split newAction
								if ($remainingSeconds < 0 && $appointment->getCreationProgress() !== Tx_Appointments_Domain_Model_Appointment::EXPIRED) {
									$appointment->setCreationProgress(Tx_Appointments_Domain_Model_Appointment::EXPIRED);
									$appointment->_memorizePropertyCleanState('creationProgress'); //if we don't register EXPIRED as clean state, setting it to unfinished later won't be recognized by persistence!
								}

								#@TODO kan ik net zoals change type niet een change time en change date hierin verwerken?
								#@SHOULD split up in functions!
								//expired appointments should be either refreshed and/or notified of timneslot-related problems
								if ($appointment->getCreationProgress() === Tx_Appointments_Domain_Model_Appointment::EXPIRED) { //it's possible to get here when expired and the appointment no longer exists, thus throwing an exception #@TODO caught by.. ?
									if ($this->crossAppointments($appointment)) { //make sure the timeslot is still available
										//an appointment was found that makes the current one's time not possible to retain
										$flashMessage = str_replace('$1',$freeSlotInMinutes,
												Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_create_crosstime', $this->extensionName)
										);
										$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);
										#@TODO misschien de vrijmaak knop los van een wijzig knop doen, en de wijzig knoppen via ajax o.i.d. doorsturen?
										#@TODO test wat er precies gebeurt bij het wijzigen van de type met compleet verschillende formfields
									} else {
										//timer refreshed message
										$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_create_timerrefresh', $this->extensionName);
										$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::INFO);
									}
									#@TODO should this be here? what happens @ crossAppointments? I don't think the timer should be refreshed, but it probably is..
									$appointment->setCreationProgress(Tx_Appointments_Domain_Model_Appointment::UNFINISHED); #@TODO cleanup task for expired records?
									$remainingSeconds = $freeSlotInMinutes * 60;
								}
								$this->appointmentRepository->update($appointment);
							}
							$this->slotService->resetStorageObject($type,$agenda); //necessary to persist changes to the available timeslots

							//remaining time message
							$flashMessage = str_replace(
									'$1',
									'<span class="reservation-timer">' . floor($remainingSeconds/60) . ':' . date('s',$remainingSeconds) . '</span>',
									Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_create_timer', $this->extensionName)
							);
							$flashHeader = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_create_timer_header', $this->extensionName);
							$this->flashMessageContainer->add($flashMessage,$flashHeader,t3lib_FlashMessage::INFO);
						} else {
							#@TODO error: not allowed
						}
					}
					$this->view->assign('timeSlots', $timeSlots);
				} else {
					#@TODO error: no timeslots
				}
			}
			$this->view->assign('dateSlots', $dateSlots);
		}

		$this->view->assign('types', $types);
		$this->view->assign('appointment', $appointment);
		$this->view->assign('agenda', $agenda);
		$this->view->assign('buildCreate', $buildCreate);
		$this->view->assign('dateFirst', $dateFirst);
		$this->view->assign('superUser', $superUser);
	}

	/**
	 * action create
	 *
	 * We need $agenda here in case of a validation error sending us back to newAction.
	 *
	 * Notice that the appointment isn't added but updated. This is because the newAction
	 * will have already added an unfinished appointment. We just need to change its creation_progress flag.
	 *
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda the appointment belongs to
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment to create
	 * @return void
	 */
	public function createAction(Tx_Appointments_Domain_Model_Agenda $agenda, Tx_Appointments_Domain_Model_Appointment $appointment) {
		$this->calculateTimes($appointment);

		//as a safety measure, first check if there are appointments which occupy time which this one claims
		//this is necessary in case the unfinished appointment was already expired due to a timeout
		if ($this->crossAppointments($appointment)) { #@TODO wellicht kunnen we dit in een speciale action stoppen die vervolgens forward of redirect, zo hoeft het niet dubbel gechecked worden als deze true is
			//an appointment was found that makes the current one's times not possible
			$this->forward('new'); //seems to work similar to a validation error #@SHOULD mark the time field like a validation error
		} else {
			$appointment->setCreationProgress(Tx_Appointments_Domain_Model_Appointment::FINISHED);
			$this->appointmentRepository->update($appointment);
			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_create_success', $this->extensionName);
			$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::OK);

			$this->slotService->resetStorageObject($appointment->getType(),$agenda); //persist changes in timeslots, in case they were freed up for some reason

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
		$formFieldArray = $appointment->getType()->getFormFields()->toArray(); #@TODO perhaps using an objectstorage here will help us fix the bug below, in combination with a foreach explicitly checking if the utilized formfields exist in this storage
		//it's possible to delete relevant formfieldvalues in TCA, so we'll re-add them here if that's the case
		if ($formFieldValues->count() !== count($formFieldArray)) { #@FIXME bug: if we replace the 5 existing formfields with 5 new ones, addMissingFormFields isn't being called to fix things
			$formFieldValues = $this->addMissingFormFields($formFieldArray,$formFieldValues); #@SHOULD using conditions in template now to fix the above issue, but if solved here, we probably don't need them
			#@TODO create the possibility to differentiate between shown and allowed-to-create types, so that we can encourage big changes to appointment types to happen through a copy instead, which will allow as to preserve old appointments in full glory
		}

		//if the date was changed, reflect it on the form but don't persist it yet
		if ($changedDate !== NULL) {
			$appointment->setBeginTime(new DateTime($changedDate));
			$appointment->_memorizeCleanState('beginTime'); //makes sure it isn't persisted automatically
		}
		$dateSlots = $this->slotService->getDateSlotsIncludingCurrent($appointment);
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
		#@TODO is er een manier om alleen te updaten als $appointment veranderd is ten opzichte van voorheen?
		//as a safety measure, first check if there are appointments which occupy time which this one claims
		//this is necessary in case another appointment is created or edited before this one is saved
		$this->calculateTimes($appointment);
		if ($this->crossAppointments($appointment)) {
			//an appointment was found that makes the current one's times not possible
			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_update_crosstime', $this->extensionName);
			$this->flashMessageContainer->add($flashMessage,'',t3lib_FlashMessage::ERROR);
			$this->forward('edit'); //hopefully acts like a validation error #@TODO compare with changes @ create
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
		#@FIXME bug: causes problems when address was deleted before!
		$this->performMailingActions('delete',$appointment);

		$this->redirect('list');
	}

	/**
	 * action free
	 *
	 * When an unfinished appointment is started, one is allowed to free up the chosen timeslot.
	 *
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda the appointment belongs to
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment's time to free up
	 * @param string $dateFirst
	 * @dontvalidate $appointment
	 * @return void
	 */
	public function freeAction(Tx_Appointments_Domain_Model_Agenda $agenda, Tx_Appointments_Domain_Model_Appointment $appointment = NULL, $dateFirst = NULL) {
		$arguments = array( //arguments will provide newAction with previously chosen parameters
				'agenda' => $agenda
		);

		if ($appointment !== NULL) {
			$type = $appointment->getType();
			//set it to expired to free up the timeslot, but still pass along the appointment so that it may be reconstituted in the same session
			$appointment->setCreationProgress(Tx_Appointments_Domain_Model_Appointment::EXPIRED);
			$appointment->setRefresh(TRUE);
			$this->appointmentRepository->update($appointment);
			$this->slotService->resetStorageObject($type,$agenda); //persist changes in timeslots
			$arguments['appointment'] = $appointment;
		}

		if (isset($dateFirst[0])) {
			#@TODO the whole dateFirst bit is an ugly hack, should really do it more properly after the new newAction is created
			$arguments['dateFirst'] = $dateFirst;
		}

		$this->redirect('new',NULL,NULL,$arguments); #@TODO test if $arguments is necessary
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
	 * @param Array $formFieldArray
	 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_Appointments_Domain_Model_FormFieldValue> $formFieldValues
	 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_Appointments_Domain_Model_FormFieldValue>
	 */
	protected function addMissingFormFields($formFieldArray, Tx_Extbase_Persistence_ObjectStorage $formFieldValues) {
		$items = array();
		$order = array();
		$newStorage = new Tx_Extbase_Persistence_ObjectStorage(); #@SHOULD clone instead?
		$formFieldValues = $formFieldValues->toArray();

		//formfieldvalues already available
		foreach ($formFieldValues as $formFieldValue) {
			$formField = $formFieldValue->getFormField();
			if ($formField !== NULL) { //it's possible a formfield was deleted at some point
				$uid = $formField->getUid();
				$items[$uid] = $formFieldValue;
			}
		}

		//formfieldvalues to add
		foreach ($formFieldArray as $formField) { #@SHOULD experiment with this still being an objectstorage, a clone perhaps
			$uid = $formField->getUid();
			if (!isset($items[$uid])) {
				$formFieldValue = new Tx_Appointments_Domain_Model_FormFieldValue();
				$formFieldValue->setFormField($formField);
				$items[$uid] = $formFieldValue;
			}
			$order[$uid] = $formField->getSorting(); //I'd prefer $items[$sorting] = $formFieldValue, but the sorting value can be messed with to cause duplicate keys
		}

		//NOTE: extbase will set sorting value to the currently arranged order, when persisted
		natsort($order);
		foreach($order as $uid=>$sorting) {
			$newStorage->attach($items[$uid]);
		}

		//newStorage will not contain formfieldvalues which belonged to formfield that have been removed
		return $newStorage;
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
	 * Checks to see if an appointment's time properties are taken up by any other appointment.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment to check
	 * @return boolean
	 */
	protected function crossAppointments(Tx_Appointments_Domain_Model_Appointment $appointment) {
		$crossAppointments = $this->appointmentRepository->findCrossAppointments($appointment);
		return !empty($crossAppointments); #@TODO re-check if a cross appointment is caught and presented on FE the way I really really want it
	}

	/**
	 * Limits the allowed types based on available timeslots on the given timestamp.
	 *
	 * @param Iterator|Array $types Previous types result
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda Agenda to check
	 * @param string $timestamp Timestamp to get dateslot for
	 * @param integer $excludeAppointment UID of appointment to exclude in available timeslot calculation
	 * @return array Contains types that have an available timeslot
	 */
	protected function limitTypesByTime($types, Tx_Appointments_Domain_Model_Agenda $agenda, $timestamp, $excludeAppointment = 0) {
		$newTypes = array();
		foreach ($types as $type) {
			$slotStorage = $this->slotService->getSingleDateSlot($type, $agenda, intval($this->settings['freeSlotInMinutes']), $timestamp, $excludeAppointment);
			if ($slotStorage->valid()) { //returns true only if it contains at least one valid dateslot
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
		$emailService->setControllerContext($this->controllerContext); //can't be done @ injection because controllerContext won't be initialized yet

		$this->emailService->sendEmailAction($action,$appointment); #@TODO add message on success and fail? (maybe sys_log?)
		$this->emailService->sendCalendarAction($action,$appointment); #@TODO add message on success and fail?
	}

}
?>
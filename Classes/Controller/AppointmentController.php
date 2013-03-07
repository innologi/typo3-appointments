<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
	 * frontendUserRepository
	 *
	 * @var Tx_Extbase_Domain_Repository_FrontendUserRepository
	 */
	protected $frontendUserRepository;

	/**
	 * frontendUserGroupRepository
	 *
	 * @var Tx_Extbase_Domain_Repository_FrontendUserGroupRepository
	 */
	protected $frontendUserGroupRepository;

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
	 * injectFrontendUserRepository
	 *
	 * @param Tx_Extbase_Domain_Repository_FrontendUserRepository $frontendUserRepository
	 * @return void
	 */
	public function injectFrontendUserRepository(Tx_Extbase_Domain_Repository_FrontendUserRepository $frontendUserRepository) {
		$this->frontendUserRepository = $frontendUserRepository;
	}

	/**
	 * injectFrontendUserGroupRepository
	 *
	 * @param Tx_Extbase_Domain_Repository_FrontendUserGroupRepository $frontendUserGroupRepository
	 * @return void
	 */
	public function injectFrontendUserGroupRepository(Tx_Extbase_Domain_Repository_FrontendUserGroupRepository $frontendUserGroupRepository) {
		$this->frontendUserGroupRepository = $frontendUserGroupRepository;
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
	 * action list
	 *
	 * Shows the list of future appointments of the logged-in user
	 *
	 * @return void
	 */
	public function listAction() {
		global $TSFE;

		$agendaUid = $this->settings['agendaUid'];
		$agenda = $this->agendaRepository->findByUid($agendaUid);
		$this->view->assign('agenda', $agenda);

		$feUser = NULL;
		if ($TSFE->fe_user) {
			//turns out getting the id is not enough: not all fe_users are of the correct record_type
			$feUserUid = $TSFE->fe_user->user['uid'];
			$feUser = $this->frontendUserRepository->findByUid($feUserUid);
			//get superuser group
			$suGroup = $this->frontendUserGroupRepository->findByUid($this->settings['suGroup']);
		}

		if ($agenda !== NULL && $feUser !== NULL) {
			$appointments = $this->appointmentRepository->findByAgendaAndFeUser($agenda,$feUser,new DateTime());
			$this->view->assign('appointments', $appointments);
			//users can only edit/delete appointments when the appointment type's mutable hours hasn't passed yet
			//a superuser can ALWAYS mutate, so now = 0 fixes that
			$this->view->assign('now', $feUser->getUsergroup()->contains($suGroup) ? 0 : time());
		} else {
			#@TODO error flash on no agenda
		}
	}

	/**
	 * action show
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment to show
	 * @dontvalidate $appointment
	 * @return void
	 */
	public function showAction(Tx_Appointments_Domain_Model_Appointment $appointment) {
		global $TSFE;
		$this->view->assign('appointment', $appointment);
		$showMore = FALSE;
		$mutable = FALSE;
		$superUser = FALSE;
		#@TODO you should be able to return to an agenda view if the plugin came from there but a list plugin is n/a .. or is that even possible?

		if ($TSFE->fe_user) {
			$feUser = $this->frontendUserRepository->findByUid($TSFE->fe_user->user['uid']);

			//check if current user is member of the superuser group
			$suGroup = $this->frontendUserGroupRepository->findByUid($this->settings['suGroup']);
			if ($feUser->getUsergroup()->contains($suGroup)) {
				//we're not using vhs viewhelpers for this, because we need to set $showMore anyway and a viewhelper-alternative is overkill
				$superUser = TRUE;
				$showMore = TRUE;
				$endTime = $appointment->getBeginReserved()->getTimestamp();
				$mutable = time() < $endTime; //su can edit any appointment that hasn't started yet
			} elseif ($feUser->getUid() == $appointment->getFeUser()->getUid()) { //check if current user is the owner of the appointment, so that we may do a 'mutable' check
				$showMore = TRUE;
				$endTime = $appointment->getType()->getHoursMutable() * 60 * 60 + $appointment->getCrdate();
				$mutable = time() < $endTime;
			}

			//certain conditions get to show more data (i.e. being superuser and/or owner)
			$this->view->assign('showMore', $showMore);
			$this->view->assign('mutable', $mutable); //edit / delete
			$this->view->assign('superUser', $superUser);
		}
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
	 * @param Tx_Appointments_Domain_Model_Type $type appointment.type substitute
	 * @param integer $beginTime appointment.beginTime substitute
	 * @dontvalidate $appointment
	 * @dontvalidate $type
	 * @return void
	 */
	public function newAction(Tx_Appointments_Domain_Model_Agenda $agenda, Tx_Appointments_Domain_Model_Appointment $appointment = NULL, $buildCreate = NULL, $dateFirst = NULL, Tx_Appointments_Domain_Model_Type $type = NULL, $beginTime = NULL) {
		//is user superuser?
		#@TODO look at the template.. are all those hidden properties really necessary?
		global $TSFE; #@TODO move to a service/helper class?
		$superUser = FALSE;
		if ($TSFE->fe_user) {
			$feUser = $this->frontendUserRepository->findByUid($TSFE->fe_user->user['uid']);
			$suGroup = $this->frontendUserGroupRepository->findByUid($this->settings['suGroup']);
			if ($feUser->getUsergroup()->contains($suGroup)) {
				$superUser = TRUE;
			}
		}

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
				$this->flashMessageContainer->add($flashMessage);
			}
		}

		//if an appointment isn't set yet, but substitute values are, we can skip the first steps anyway
		if ($appointment === NULL && $beginTime !== NULL && $type !== NULL) {
			$appointment = new Tx_Appointments_Domain_Model_Appointment();
			$appointment->setBeginTime(new DateTime(strftime("%Y-%m-%d %H:%M:%S",$beginTime)));
			$appointment->setType($type);
			//we're now at the same result as after the first two steps
		}

		if ($appointment !== NULL) {
			//step 1 reached

			$type = $appointment->getType();

			if (!isset($dateSlots)) { //helps to avoid overwriting an override
				$dateSlots = $this->slotService->getDateSlots($type, $agenda, $freeSlotInMinutes); #@TODO can we throw error somewhere when this one is empty?
			}

			$beginTime = $appointment->getBeginTime();
			if ($beginTime !== NULL) {
				//step 2 reached

				if (($timeSlots = $this->slotService->getTimeSlots($dateSlots,$appointment)) !== FALSE) {
					if ($buildCreate !== NULL) { #@TODO all the earlier buttons will not save fields that are set in the final form, what can be done about it?
						//step 3 reached

						if ($this->slotService->isTimeSlotAllowed($timeSlots,$appointment) !== FALSE) {
							//limit the still available types by the already chosen timeslot
							$excludeAppointment = $appointment->_isNew() ? 0 : $appointment->getUid();
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
								$appointment->setFeUser($feUser);
							}

							$this->calculateTimes($appointment); //set the remaining DateTime properties

							//when a validation error ensues, we don't want the unfinished appointment being re-added, hence the check
							if ($appointment->_isNew()) { #@TODO check all the inline doc around these changes
								$this->appointmentRepository->add($appointment);
								#@TODO message
								#$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_new_creation', $this->extensionName);
								#$this->flashMessageContainer->add($flashMessage);
							} else {
								#@TODO kan ik net zoals change type niet een change time en change date hierin verwerken?
								#@SHOULD split up in functions!
								#@TODO doc
								if ($appointment->getCreationProgress() === Tx_Appointments_Domain_Model_Appointment::EXPIRED) { //it's possible to get here while $freeSlotInMinutes has expired and the appointment no longer exists, thus throwing an exception
									if ($this->crossAppointments($appointment)) { //make sure the timeslot is still available
										//an appointment was found that makes the current one's times not possible
										$flashMessage = str_replace('$1',$freeSlotInMinutes,
												Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_create_crosstime', $this->extensionName)
										);
										$this->flashMessageContainer->add($flashMessage);

										$appointment->setCreationProgress(Tx_Appointments_Domain_Model_Appointment::UNFINISHED); #@TODO cleanup task for expired records?
										#@TODO werkt nog niet helemaal zoals ik wil: je moet de tijd eerst vrijgeven voor je verder kunt kiezen wat betekent dat je weer de waarden verliest
										#@TODO dus misschien de vrijmaak knop los van een wijzig knop doen, en de wijzig knoppen via ajax o.i.d. doorsturen?
										#@TODO test wat er precies gebeurt bij het wijzigen van de type met compleet verschillende formfields
									}
									$appointment->setCreationProgress(Tx_Appointments_Domain_Model_Appointment::UNFINISHED);
									#@TODO message about refreshed freeSlotInMinutes?
								}
								$this->appointmentRepository->update($appointment);
							}
							$this->slotService->resetStorageObject($type,$agenda); //necessary to persist changes to the available timeslots
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
		if ($this->crossAppointments($appointment)) { #@TODO wellicht kunnen we dit in een speciale action stoppen die vervolgens forward of redirect
			//an appointment was found that makes the current one's times not possible
			$this->forward('new'); //seems to work similar to a validation error #@SHOULD mark the time field like a validation error
		} else {
			$appointment->setCreationProgress(Tx_Appointments_Domain_Model_Appointment::FINISHED);
			$this->appointmentRepository->update($appointment);
			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_create_success', $this->extensionName);
			$this->flashMessageContainer->add($flashMessage);

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
		//is user superuser?
		global $TSFE;
		$superUser = FALSE;
		if ($TSFE->fe_user) {
			$feUser = $this->frontendUserRepository->findByUid($TSFE->fe_user->user['uid']);
			$suGroup = $this->frontendUserGroupRepository->findByUid($this->settings['suGroup']);
			if ($feUser->getUsergroup()->contains($suGroup)) {
				$superUser = TRUE;
			}
		}

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
			$this->flashMessageContainer->add($flashMessage);
			$this->forward('edit'); //hopefully acts like a validation error #@TODO compare with changes @ create
		} else {
			$this->appointmentRepository->update($appointment);
			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_update_success', $this->extensionName);
			$this->flashMessageContainer->add($flashMessage);

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
		$this->flashMessageContainer->add($flashMessage);

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

		#@FIXME misschien moet ik beginTime property bij deze maar onnodig maken: geen select?

		if ($appointment !== NULL) {
			$type = $appointment->getType();
			#@FIXME instead of removing it, I should look at how I solve editAction and create a new multi-stepped newAction which looks at submitbutton for free/create action while using a single form
			$this->appointmentRepository->remove($appointment); #@SHOULD consider alternatives, updating saves unnecessary increments of uid .. but then crdate is not renewed. I guess this is a pro-argument for using tstamp instead of crdate? OR EXPIRE IT AND MOVE FROM THERE!
			$this->slotService->resetStorageObject($type,$agenda); //persist changes in timeslots
			$arguments['type'] = $type;
			$arguments['beginTime'] = $appointment->getBeginTime()->getTimestamp();
		}

		if (isset($dateFirst[0])) {
			#@TODO the whole dateFirst bit is an ugly hack, should really do it more properly after the new newAction is created
			$arguments['dateFirst'] = $dateFirst;
		}

		$this->redirect('new',NULL,NULL,$arguments);
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
		return !empty($crossAppointments);
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

	#@TODO doc
	protected function performMailingActions($action,$appointment) {
		$this->emailService->setControllerContext($this->controllerContext);

		$this->emailService->sendEmailAction($action,$appointment); #@TODO add message on success and fail? (maybe sys_log?)
		$this->emailService->sendCalendarAction($action,$appointment); #@TODO add message on success and fail?
	}

}
?>
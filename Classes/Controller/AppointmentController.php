<?php

namespace Innologi\Appointments\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use Innologi\Appointments\Domain\Model\{Agenda, Appointment, FormField, FormFieldValue};
use Innologi\Appointments\Domain\Service\SlotService;
use Innologi\Appointments\Mvc\Controller\ActionController;
use Innologi\Appointments\Mvc\Exception\EarlyResponseThrowable;
use Innologi\Appointments\Utility\GeneralUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Appointment Controller
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AppointmentController extends ActionController
{
    /**
     * @var \Innologi\Appointments\Service\EmailService
     */
    protected $emailService;

    /**
     * Injects the Email Service
     */
    public function injectEmailService(\Innologi\Appointments\Service\EmailService $emailService)
    {
        $emailService->setExtensionName($this->extensionName);
        $this->emailService = $emailService;
    }

    /**
     * This method is to ensure that only an appointment owner or superuser can mutate the given appointment.
     * This method is to be used on any method that attempts or implies a mutate action. Because we're caching
     * the show action, this will prevent any users sharing the usergroups, and therefore cache, can change
     * or delete anyone else's appointment.
     *
     * @throws EarlyResponseThrowable
     */
    protected function validateMutateAttempt(Appointment $appointment)
    {
        if (!($this->feUser->getUid() === $appointment->getFeUser()->getUid() || $this->userService->isInGroup($this->settings['suGroup']))) {
            $this->addFlashMessage(LocalizationUtility::translate('tx_appointments_list.no_mutate', $this->extensionName), '', FlashMessage::ERROR, true);
            throw new EarlyResponseThrowable($this->redirect('list'));
        }
    }

    /**
     * Initialize Process New Action
     */
    protected function initializeProcessNewAction()
    {
        if (isset($this->request->getArgument('appointment')['__identity'])) {
            $this->validateRequest();
        }
    }

    /**
     * Initialize Create Action
     */
    protected function initializeCreateAction()
    {
        $this->validateRequest();
    }

    /**
     * Initialize Update Action
     */
    protected function initializeUpdateAction()
    {
        $this->validateRequest();
    }

    /**
     * Initialize Delete Action
     */
    protected function initializeDeleteAction()
    {
        $this->validateRequest();
    }

    /**
     * @see \Innologi\Appointments\Mvc\Controller\ActionController::initializeAction()
     */
    protected function initializeAction()
    {
        // doing this in the appropriate initialize methods is too late, so..
        $this->disableRequireLogin([
            'list',
            'show',
        ]);
        parent::initializeAction();
        if ($this->arguments->hasArgument('appointment')) {
            /** @var \TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration $propertyMappingConfiguration */
            $propertyMappingConfiguration = $this->arguments['appointment']->getPropertyMappingConfiguration();
            $propertyMappingConfiguration->forProperty('beginTime')->setTypeConverterOption(DateTimeConverter::class, DateTimeConverter::CONFIGURATION_DATE_FORMAT, $this->request->hasArgument('expectDate') ? SlotService::DATESLOT_KEY_FORMAT : SlotService::TIMESLOT_KEY_FORMAT);
            // @TODO needs to be configurable!
            $propertyMappingConfiguration->forProperty('address.birthday')->setTypeConverterOption(DateTimeConverter::class, DateTimeConverter::CONFIGURATION_DATE_FORMAT, 'd-m-Y');
        }
    }

    /**
     * action list
     *
     * Shows the list of future appointments of the logged-in user
     *
     * Note that we cannot cache this page! Otherwise, users in the same usergroup will end up
     * seeing other people's lists
     */
    public function listAction(): ResponseInterface
    {
        if ($this->feUser !== false) {
            $types = $this->getTypes(); // we need to include types in case a type was hidden or deleted, or we get all sorts of errors
            $appointments = $this->appointmentRepository->findPersonalList($this->agenda, $types, $this->feUser, false, new \DateTime());
            # @LOW enable through flexform?
            $unfinishedAppointments = $this->settings['allowResume'] ? $this->appointmentRepository->findPersonalList($this->agenda, $types, $this->feUser, true, new \DateTime()) : [];
            // users can only edit/delete appointments when the appointment type's mutable hours hasn't passed yet
            // a superuser can ALWAYS mutate, so 'now = 0' fixes that
            $now = $this->userService->isInGroup($this->settings['suGroup']) ? 0 : time();
            $this->view->assign('now', $now);
        } else {
            $appointments = [];
            $unfinishedAppointments = [];
        }
        $this->view->assign('appointments', $appointments); # @TODO _can we create an undo link for cancelling? consider the consequences for the emailactions
        $this->view->assign('unfinishedAppointments', $unfinishedAppointments);
        return $this->htmlResponse();
    }

    /**
     * action show
     *
     * Certain conditions get to show more data (i.e. being superuser and/or owner)
     *
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointment The appointment to show
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("appointment")
     */
    public function showAction(Appointment $appointment): ResponseInterface
    {
        // limited rights by default
        $showMore = false;
        $endTime = 0;

        if ($this->feUser) {
            // check if current user is member of the superuser group
            $superUser = $this->userService->isInGroup($this->settings['suGroup']);
            if ($superUser) { // we're not using vhs viewhelpers for this, because we need to set $showMore anyway and a viewhelper-alternative is overkill
                // su can edit any appointment that hasn't started yet
                $endTime = $appointment->getBeginReserved()->getTimestamp();
                $showMore = true;
            } elseif ($this->feUser->getUid() == $appointment->getFeUser()->getUid()) { // check if current user is the owner of the appointment
                // non-su can edit his own appointment if hoursMutable hasn't passed yet
                $endTime = $appointment->getType()->getHoursMutable() * 3600 + $appointment->getReservationTime();
                $showMore = true;
            }
        }
        $mutable = time() < $endTime;
        $sanitizedFormFieldValues = $this->sanitizeFormFieldValues($appointment->getFormFieldValues()->toArray());

        $this->view->assign('showMore', $showMore);
        $this->view->assign('mutable', $mutable); // edit / delete
        $this->view->assign('superUser', $superUser);
        $this->view->assign('appointment', $appointment);
        $this->view->assign('formFieldValues', $sanitizedFormFieldValues);
        return $this->htmlResponse();
    }

    /**
     * action new1
     *
     * Part 1 of a multi-step form. This action by itself creats a multi-step form as well.
     * This action sets all fields that are required before timeslot-reservation, step by step.
     *
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointment The appointment that's being created
     * @param string $dateFirst The timestamp that should be set before a type was already chosen
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("appointment")
     */
    public function new1Action(Appointment $appointment = null, $dateFirst = null): ResponseInterface
    {
        // find types
        $types = $this->getTypes();
        # @LOW in a seperate action that forwards/redirects or not.. consider the extra overhead, it's probably not worth it
        if (isset($dateFirst[0])) { // overrides in case an appointment-date is picked through agenda
            // removes types that can't produce timeslots on the dateFirst date
            $beginTime = new \DateTime();
            $beginTime->setTimestamp($dateFirst);
            $types = $this->limitTypesByDate($types, $this->agenda, clone $beginTime);
            if (!empty($types)) {
                $appointment = new Appointment();
                $appointment->setType(current($types));
                $appointment->setBeginTime($beginTime);
            } else {
                // no types available on chosen time, so no appointments either.
                // the condition also functions as a check for a valid dateFirst
                $flashMessage = LocalizationUtility::translate('tx_appointments_list.appointment_create_no_types', $this->extensionName);
                $this->addFlashMessage($flashMessage, '', FlashMessage::ERROR);
                // $appointment == NULL
            }
        }

        if ($appointment !== null) {
            // type chosen! (or dateFirst)

            $dateSlots = $this->slotService->getDateSlots($appointment->getType(), $this->agenda); # @TODO can we throw error somewhere when this one is empty, about the type not having any available timeslots?
            $this->view->assign('dateSlots', $dateSlots);

            if ($appointment->getBeginTime() !== null) {
                // date chosen! (or dateFirst)

                $timeSlots = $this->slotService->getTimeSlots($dateSlots, $appointment);
                $this->view->assign('timeSlots', $timeSlots); // an impossible type/date combo will result in $timeSlots == FALSE, so the user can't continue without re-picking

                // will show disabledform, so requires formFieldValues to be assigned
                if (!$appointment->_isNew()) {
                    $formFieldValues = $appointment->getFormFieldValues();
                    $formFields = clone $appointment->getType()->getFormFields(); // formFields is modified for this process but not to persist, hence clone
                    $formFieldValues = $this->addMissingFormFields($formFields, $formFieldValues);
                    // adding the formFieldValues already will get them persisted too soon, empty and unused, so we're assigning them separately from $appointment
                    $this->view->assign('formFieldValues', $formFieldValues);
                }
            }
        }

        $this->view->assign('types', $types);
        $this->view->assign('appointment', $appointment);
        return $this->htmlResponse();
    }

    /**
     * action new2
     *
     * Part 2 (or actually 4) of a multi-step form. This action requires to be preceded by the
     * processNew action. It builds the form used to fill out the entire appointment details,
     * and displays a timer for the timeslot reservation.
     *
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointment The appointment that's being created
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("appointment")
     */
    public function new2Action(Appointment $appointment): ResponseInterface
    {
        // limit the available types by the already chosen timeslot
        $types = $this->limitTypesByAppointment($this->getTypes(), $appointment);

        // dummy dateslots are never empty, no error checking necessary
        $dateSlots = $this->slotService->getDummyDateSlot(clone $appointment->getBeginTime());
        $timeSlots = $this->slotService->getTimeSlots($dateSlots, $appointment);

        // add message that depends on creation-progress
        $this->addTimerMessage($appointment);

        $formFieldValues = $appointment->getFormFieldValues();
        $formFields = clone $appointment->getType()->getFormFields(); // formFields is modified for this process but not to persist, hence clone
        $formFieldValues = $this->addMissingFormFields($formFields, $formFieldValues);

        // adding the formFieldValues already will get them persisted too soon, empty and unused, so we're assigning them separately from $appointment
        $this->view->assign('formFieldValues', $formFieldValues);
        $this->view->assign('timeSlots', $timeSlots);
        $this->view->assign('dateSlots', $dateSlots);
        $this->view->assign('types', $types);
        $this->view->assign('appointment', $appointment);
        return $this->htmlResponse();
    }

    /**
     * action processType
     *
     * Part 2 (or actually 4) of a multi-step form. This action requires to be preceded by the
     * processNew action. It builds the form used to fill out the entire appointment details,
     * and displays a timer for the timeslot reservation.
     *
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointment The appointment that's being created
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("appointment")
     */
    public function simpleProcessNewAction(Appointment $appointment): ResponseInterface
    {
        $this->validateMutateAttempt($appointment);

        $this->appointmentRepository->update($appointment);
        $arguments = [
            'appointment' => $appointment,
        ];
        return $this->redirect('new2', null, null, $arguments);
    }

    /**
     * action processNew
     *
     * This part of the multi-step form performs some preliminary time-related validation checks,
     * adds missing properties, persists / refreshes timeslot reservations, and then redirects to
     * the appropriate action.
     *
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointment The appointment that's being created
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("appointment")
     */
    public function processNewAction(Appointment $appointment): ResponseInterface
    {
        $arguments = [];
        $appointment->setAgenda($this->agenda);
        $appointment->setFeUser($this->feUser);

        if ($this->slotService->isTimeSlotAllowed($appointment)) {
            $this->calculateTimes($appointment); // set the remaining DateTime properties of appointment
            $timerStart = false;
            $action = 'new2';
            $arguments['appointment'] = $appointment;

            // when a validation error ensues, we don't want the unfinished appointment being re-added, hence the check
            if ($appointment->_isNew()) {
                // sets reservations time etc
                $appointment->setCreationProgress(Appointment::UNFINISHED);
                // currently, persist happens within add
                // because $appointment is used as argument @ redirect() and thus to be serialized by uriBuilder (which requires an uid)
                // we NEED it to be persisted. Of course, the real reason is that we want to reserve the timeslots from the start
                $this->appointmentRepository->add($appointment);
                $timerStart = true;
            } else {
                // expired appointments should be refreshed
                if ($appointment->getCreationProgress() === Appointment::EXPIRED) { // it's possible to get here when expired and the appointment no longer exists, thus throwing an exception #@TODO caught by.. ? SettingsOverride? I don't remember!
                    // checks whether the timeslot was changed or not
                    $cleanBeginTime = $appointment->_getCleanProperty('beginTime');
                    if ($cleanBeginTime instanceof \DateTime && $cleanBeginTime->getTimestamp() !== $appointment->getBeginTime()->getTimestamp()) {
                        $timerStart = true;
                    } else {
                        // messages for the same timeslot again (refresh)
                        $flashMessage = LocalizationUtility::translate('tx_appointments_list.appointment_timerrefresh', $this->extensionName);
                        $this->addFlashMessage($flashMessage, '', FlashMessage::INFO);
                    }
                    $appointment->setCreationProgress(Appointment::UNFINISHED);
                }

                $this->appointmentRepository->update($appointment);
            }

            if ($timerStart) {
                $freeSlotInMinutes = (int) $this->settings['freeSlotInMinutes']; # @LOW is 0 supported everywhere? it should be, but I think I left a <1 check somewhere. Also timer messages should react to 0
                // message for a new timeslot
                $flashMessage = str_replace('$1', $freeSlotInMinutes, LocalizationUtility::translate('tx_appointments_list.appointment_timerstart', $this->extensionName));
                $this->addFlashMessage($flashMessage, '', FlashMessage::INFO);
            }
        } else {
            $action = 'new1'; // if a timeslot is not allowed, we'll need to force the user to pick a new one
            $flashMessage = LocalizationUtility::translate('tx_appointments_list.timeslot_not_allowed', $this->extensionName);
            $this->addFlashMessage($flashMessage, '', FlashMessage::ERROR);

            // not adding appointment as argument prevents a uriBuilder exception @ redirect() if appointment wasn't persisted yet..
            if (!$appointment->_isNew()) { // .. but since we're not redirecting if this condition returns TRUE, there's no need for it here anyway
                $this->failTimeValidation($action);
            }
            // if appointment wasn't persisted, there is no validation error to apply as there only a type-form #@TODO _couldn't we also pass along a timestamp through the dateFirst mechanism then? that would include the date and time fields again..
        }

        // send to appropriate action
        return $this->redirect($action, null, null, $arguments);
    }

    /**
     * action create
     *
     * Notice that the appointment isn't added but updated. This is because the processNewAction
     * will have already added an unfinished appointment. We just need to change its creation_progress flag.
     *
     * Also, there is no isTimeSlotAllowed check here. So in theory, it is possible for one to have his
     * timeslot expire AND have firstAvailableTime passed, while keeping his session alive and not refreshing
     * his timeslot by submitting an invalid appointment over and over. We might need to fix that, but for
     * now the cleanup task will prevent excessive time-differences over firstAvailableTime.
     *
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointment The appointment to create
     * @TYPO3\CMS\Extbase\Annotation\Validate(param="appointment", validator="Innologi\Appointments\Domain\Validator\AppointmentValidator")
     */
    public function createAction(Appointment $appointment): ResponseInterface
    {
        $timeFields = $this->calculateTimes($appointment); // times can be influenced by formfields
        # @FIX _there is no check whether timeslotisallowed, which is good for the firstavailabletime, but what about maxPerDays and all that?
        // as a safety measure, first check if there are appointments which occupy time which this one claims
        // this is necessary in case another appointment is created or edited before this one is saved.
        // isTimeSlotAllowed() does not suffice by itself, because of formfields that add time and can cause overlap
        if (($overlap = $this->crossAppointments($appointment)) !== false) { // an appointment was found that makes the current one's times not possible
            // updating it as expired so the fields get saved while not blocking any appointment that might have caused crossAppointment to be TRUE
            $appointment->setCreationProgress(Appointment::EXPIRED);
            $this->appointmentRepository->update($appointment, false); // not resetting the storage object just yet because this one still has a chance regaining his prematurely ended reservation

            $this->processOverlapInfo($overlap, $appointment);
            $this->failTimeValidation('new2', 4075013371337, $timeFields);
        }

        $appointment->setCreationProgress(Appointment::FINISHED);
        $this->appointmentRepository->update($appointment);

        $flashMessage = LocalizationUtility::translate('tx_appointments_list.appointment_create_success', $this->extensionName);
        $this->addFlashMessage($flashMessage, '', FlashMessage::OK);

        $this->performMailingActions('create', $appointment);

        return $this->redirect($this->settings['redirectAfterSave'], null, null, ($this->settings['redirectAfterSave'] == 'show') ? [
            'appointment' => $appointment,
        ] : null);
    }

    /**
     * action edit
     *
     * $appointment should not be validated, because changes to the extension or some editing in TCA might cause
     * validation errors, and we can't fix those in FE if editAction isn't allowed. Validation is done in updateAction anyway.
     *
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointment The appointment to edit
     * @param string $changedDate Changed date
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("appointment")
     */
    public function editAction(Appointment $appointment, $changedDate = null): ResponseInterface
    {
        $this->validateMutateAttempt($appointment);
        $superUser = $this->userService->isInGroup($this->settings['suGroup']);

        $formFieldValues = $appointment->getFormFieldValues();
        $formFields = clone $appointment->getType()->getFormFields(); // formFields is modified for this process but not to persist, hence clone
        // it's possible to delete relevant formfieldvalues in TCA, so we'll re-add them here if that's the case
        $formFieldValues = $this->addMissingFormFields($formFields, $formFieldValues);
        # @TODO create the possibility to differentiate between shown and allowed-to-create types, so that we can encourage big changes to appointment types to happen through a copy instead, which will allow as to preserve old appointments in full glory

        // if the date was changed, reflect it on the form but don't persist it yet
        if ($changedDate !== null) {
            $appointment->setBeginTime($appointment->getBeginTime()->setDate(substr($changedDate, 0, 4), substr($changedDate, 4, 2), substr($changedDate, 6, 2))); # @LOW couldn't we do it this way with dateFirst either? Ymd instead of timestamp so we can use construct
            $appointment->_memorizeCleanState('beginTime'); // makes sure it isn't persisted automatically
        }
        $dateSlots = $this->slotService->getDateSlotsIncludingCurrent($appointment, true);
        $timeSlots = $this->slotService->getTimeSlots($dateSlots, $appointment);

        $this->view->assign('dateSlots', $dateSlots);
        $this->view->assign('timeSlots', $timeSlots);
        $this->view->assign('appointment', $appointment);
        // adding the formFieldValues already will get them persisted too soon, empty and unused, so we're assigning them separately from $appointment
        $this->view->assign('formFieldValues', $formFieldValues);
        $this->view->assign('superUser', $superUser);
        return $this->htmlResponse();
    }

    /**
     * action update
     *
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointment The appointment to update
     * @TYPO3\CMS\Extbase\Annotation\Validate(param="appointment", validator="Innologi\Appointments\Domain\Validator\AppointmentValidator")
     */
    public function updateAction(Appointment $appointment): ResponseInterface
    {
        $this->validateMutateAttempt($appointment);
        $timeFields = $this->calculateTimes($appointment); // times can be influenced by formfields

        # @TODO betekent calculateTimes nu niet dat hij altijd als modified wordt geregistreerd?
        // as a safety measure, first check if there are appointments which occupy time which this one claims
        // this is necessary in case another appointment is created or edited before this one is saved
        if (($overlap = $this->crossAppointments($appointment)) !== false) {
            // an appointment was found that makes the current one's times not possible
            $this->processOverlapInfo($overlap, $appointment);
            $this->failTimeValidation('edit', 4075013371337, $timeFields);
        }

        $this->appointmentRepository->update($appointment);
        $flashMessage = LocalizationUtility::translate('tx_appointments_list.appointment_update_success', $this->extensionName);
        $this->addFlashMessage($flashMessage, '', FlashMessage::OK);

        $this->performMailingActions('update', $appointment);

        return $this->redirect($this->settings['redirectAfterSave'], null, null, ($this->settings['redirectAfterSave'] == 'show') ? [
            'appointment' => $appointment,
        ] : null);
    }

    /**
     * action delete
     *
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointment The appointment to delete
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("appointment")
     */
    public function deleteAction(Appointment $appointment): ResponseInterface
    {
        $this->validateMutateAttempt($appointment);

        $this->appointmentRepository->remove($appointment);
        $flashMessage = LocalizationUtility::translate('tx_appointments_list.appointment_delete_success', $this->extensionName);
        $this->addFlashMessage($flashMessage, '', FlashMessage::OK);

        $this->performMailingActions('delete', $appointment);

        return $this->redirect('list');
    }

    /**
     * action free
     *
     * When an unfinished appointment is started, one is allowed to free up the chosen timeslot.
     *
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointment The appointment's time to free up
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("appointment")
     */
    public function freeAction(Appointment $appointment): ResponseInterface
    {
        // set it to expired to free up the timeslot, but still pass along the appointment so that it may be reconstituted in the same session
        $appointment->setCreationProgress(Appointment::EXPIRED);
        $this->appointmentRepository->update($appointment);

        $flashMessage = LocalizationUtility::translate('tx_appointments_list.appointment_free_success', $this->extensionName);
        $this->addFlashMessage($flashMessage, '', FlashMessage::INFO);

        $arguments = [
            'appointment' => $appointment,
        ];
        return $this->redirect('new1', null, null, $arguments);
    }

    /**
     * There are cases when FormFieldValues are missing from an appointment, f.e.
     * when the appointment is brand new,
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
     * @return ObjectStorage
     */
    protected function addMissingFormFields(ObjectStorage $formFields, ObjectStorage $formFieldValues)
    { # @TODO _can we once again check if this doesn't just readd everything? There were some artifacts last time I debugged this
        $items = [];
        $order = [];

        // formfieldvalues already available
        foreach ($formFieldValues as $formFieldValue) {
            $formField = $formFieldValue->getFormField();
            if ($formField !== null) {
                // note that this way, a formfield that isn't part of the current type will simply not be shown, NOR REMOVED!
                // if we allow type-changes in edit, this will prove useful, but what is the consequence in TCA? #@TODO got to check this out and see if we need some damagecontrol
                if (isset($formFields[$formField])) {
                    $uid = $formField->getUid();
                    $items[$uid] = $formFieldValue; // I'd prefer $items[$sorting] = $formFieldValue, but the sorting value can be messed with to cause duplicate keys
                    $order[$uid] = $formField->getSorting();
                    $formFields->detach($formField);
                }
            } else {
                // the formfield was removed at some point, so should its value
                $formFieldValues->detach($formFieldValue); // this is the original storage, so this is persisted
            }
        }

        $formFields = $formFields->toArray(); // formFields is lazy, a count on a lazy objectstorage will give the wrong number if a detach took place
        if (count($formFields)) {
            // formfieldvalues to add
            foreach ($formFields as $formField) {
                $uid = $formField->getUid();
                $formFieldValue = new FormFieldValue();
                $formFieldValue->setFormField($formField);
                $items[$uid] = $formFieldValue;
                $order[$uid] = $formField->getSorting();
            }
        }

        if (!empty($order)) {
            $newStorage = new ObjectStorage();
            // NOTE: extbase will set sorting value to the currently arranged order, when persisted
            natsort($order);
            foreach ($order as $uid => $sorting) {
                $newStorage->attach($items[$uid]);
            }
            // newStorage will not contain formfieldvalues which belonged to formfield that have been removed
            return $newStorage;
        }

        return $formFieldValues;
    }

    /**
     * Calculates the time-properties of an appointments, and sets them.
     *
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointment The appointment of which to calculate the time properties
     * @return array Contains the formfields of the addtime fields
     */
    protected function calculateTimes(Appointment $appointment)
    {
        $timeFields = [];
        $dateTime = clone $appointment->getBeginTime();
        $type = $appointment->getType();
        $unit = ' minutes';

        $reserveBlock = $type->getBetweenMinutes();
        $beginReserved = clone $dateTime;
        $appointment->setBeginReserved($beginReserved->modify('-' . $reserveBlock . $unit));

        $dateTime->modify('+' . $type->getDefaultDuration() . $unit);

        // some formfields can set extra time
        $formFieldValues = $appointment->getFormFieldValues();
        foreach ($formFieldValues as $formFieldValue) {
            $formField = $formFieldValue->getFormField();
            if ($formField->getFunction() === FormField::FUNCTION_ADDTIME) {
                $timeFields[] = $formField;
                $fieldType = $formField->getFieldType();
                $value = $formFieldValue->getValue();
                switch ($fieldType) {
                    case FormField::TYPE_TEXTLARGE:
                    case FormField::TYPE_TEXTSMALL:
                        $dateTime->modify('+' . intval($value) . $unit); # @LOW _add a validator-choice with a customizable max?
                        break;
                    case FormField::TYPE_RADIO:
                    case FormField::TYPE_SELECT:
                    case FormField::TYPE_BOOLEAN:
                        # @TODO moet mogelijk zijn met de timeAdd optie
                }
            }
        }
        $appointment->setEndTime(clone $dateTime);
        $appointment->setEndReserved($dateTime->modify('+' . $reserveBlock . $unit));

        return $timeFields;
    }

    /**
     * Adds the timer message for a currently reserved (or expired) timeslot.
     *
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointment Appointment which uses the timeslot
     */
    protected function addTimerMessage(Appointment $appointment)
    {
        // get remaining seconds
        $remainingSeconds = GeneralUtility::getTimerRemainingSeconds($appointment, (int) $this->settings['freeSlotInMinutes']);

        // when the appointment was flagged 'expired' in the current pagehit, (e.g. page refresh)
        // this $appointment reference might not yet be up to date, so we have to check
        // $remainingSeconds === 0 for those specific cases
        if ($remainingSeconds === 0 && $appointment->getCreationProgress() === Appointment::UNFINISHED) {
            $appointment->setCreationProgress(Appointment::EXPIRED);
            $appointment->_memorizePropertyCleanState('creationProgress'); // if we don't register EXPIRED as clean state, setting it to unfinished later won't be recognized by persistence!
        }

        // inform of timer
        if ($appointment->getCreationProgress() === Appointment::UNFINISHED) {
            // we use HTML in this flash message, but we'd need a custom VH which I'm not going to do for this single case, so:
            $messages = [
                \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(FlashMessage::class, LocalizationUtility::translate('tx_appointments_list.appointment_timer', $this->extensionName), LocalizationUtility::translate('tx_appointments_list.appointment_timer_header', $this->extensionName), FlashMessage::INFO, true),
            ];

            $timerMessage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(FlashMessageRendererResolver::class)->resolve()->render($messages);

            $this->view->assign('timerMessage', str_replace('$1', '<span class="reservation-timer">' . GeneralUtility::getAppointmentTimer($appointment) . '</span>', (string) $timerMessage));
        } else { // warn of expiration
            $this->addFlashMessage(LocalizationUtility::translate('tx_appointments_list.appointment_expired', $this->extensionName), LocalizationUtility::translate('tx_appointments_list.appointment_expired_header', $this->extensionName), FlashMessage::WARNING);
            $this->view->assign('expired', 1); // for free-time button
        }
    }

    /**
     * Checks to see if an appointment's time properties are taken up by any other appointment.
     *
     * If an overlap is found, returns an array with at least 1 of 2 possible elements with key:
     * - begin => the amount of seconds the beginTime overlaps the closest (earlier) appointment
     * - end => the amount of seconds the endTime overlaps the closest (later) appointment
     *
     * Currently unused are the following, disabled keys:
     * - changeTimeSlot => boolean whether the timeslot NEEDS to be changed
     *
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointment The appointment to check
     * @return mixed Array on overlap, boolean FALSE on no overlap
     */
    protected function crossAppointments(Appointment $appointment)
    {
        $crossAppointments = $this->appointmentRepository->findCrossAppointments($appointment);
        if (!empty($crossAppointments)) {
            $beginTimeDiff = [];
            $endTimeDiff = [];

            $beginTime = $appointment->getBeginTime()->getTimestamp();
            $endTime = $appointment->getEndTime()->getTimestamp();
            $beginReserved = $appointment->getBeginReserved()->getTimestamp();
            $endReserved = $appointment->getEndReserved()->getTimestamp();
            foreach ($crossAppointments as $ca) {
                $cbt = $ca->getBeginTime()->getTimestamp();
                $cet = $ca->getEndTime()->getTimestamp();
                $cbr = $ca->getBeginReserved()->getTimestamp();
                $cer = $ca->getEndReserved()->getTimestamp();
                if ($beginTime >= $cbt) { // any appointment overlap UNTIL (AND INCLUDING, as timeslot will need to be changed anyway) beginTime
                    // we add the difference of BOTH possible overlaps, so that we can later get the largest
                    // difference inbetween different reserved-blocks in case of different appointment types
                    if ($beginReserved < $cet) {
                        $beginTimeDiff[] = $cet - $beginReserved;
                    }
                    if ($beginTime < $cer) {
                        $beginTimeDiff[] = $cer - $beginTime;
                    }
                } else { // any appointment overlap AFTER beginTime
                    // reversed logic of 'until beginTime'
                    if ($endReserved > $cbt) {
                        $endTimeDiff[] = $endReserved - $cbt;
                    }
                    if ($endTime < $cbr) {
                        $endTimeDiff[] = $endTime - $cbr;
                    }
                }
            }

            # @LOW _consider adding the appointment(s) that is conflicting to the overlapArray, so we have more details for the overlapInfo
            $overlapArray = [# 'changeTimeSlot' => FALSE //indicates whether we're absolutely sure the user NEEDS to change the timeslot
            ];
            // set the largest values in the array
            if (isset($beginTimeDiff[0])) {
                rsort($beginTimeDiff); // sets the largest difference (between appointments and INBETWEEN reserved-times) @ pos 0
                $overlapArray['begin'] = $beginTimeDiff[0];
                # $overlapArray['changeTimeSlot'] = TRUE;
            }
            if (isset($endTimeDiff[0])) { // do the same for endTime
                rsort($endTimeDiff);
                $overlapArray['end'] = $endTimeDiff[0];
                # @LOW clean up? forcing a new1 action can make the original time disappear if it was no longer available, which might confuse the user
                # if (!$overlapArray['changeTimeSlot']) { //if not yet TRUE, let it depend on whether the diff is larger than time added by (formfields)
                # $appointmentTime = ($endTime - $beginTime);
                # $appointmentTimeVariable = $appointmentTime - ($appointment->getType()->getDefaultDuration() * 60);
                # if ($endTimeDiff[0] > $appointmentTimeVariable) {
                # $overlapArray['changeTimeSlot'] = TRUE;
                # }
                # }
            }

            return $overlapArray;
        }
        return false;
    }

    /**
     * Limits the allowed types based on available timeslots on the given DateTime.
     *
     * @param \Iterator|array $types Previous types result
     * @param \Innologi\Appointments\Domain\Model\Agenda $agenda Agenda to check
     * @param \DateTime $dateTime DateTime to get dateslot for
     * @return array Contains types that have an available timeslot
     */
    protected function limitTypesByDate($types, Agenda $agenda, \DateTime $dateTime)
    {
        $newTypes = [];
        foreach ($types as $type) {
            $slotStorage = $this->slotService->getSingleDateSlot($type, $agenda, clone $dateTime);
            if ($slotStorage->valid()) { // returns true only if it contains at least one valid dateslot
                $newTypes[] = $type;
            }
        }

        return $newTypes;
    }

    /**
     * Limits the allowed types based on appointment properties.
     *
     * @param \Iterator|array $types Previous types result
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointment Appointment to exclude in available timeslot calculation
     * @return array Contains types that have an available timeslot
     */
    protected function limitTypesByAppointment($types, Appointment $appointment)
    {
        $newTypes = [];
        $timeSlotKey = $appointment->getBeginTime()->format(SlotService::TIMESLOT_KEY_FORMAT);

        foreach ($types as $type) {
            $slotStorage = $this->slotService->getSingleDateSlotIncludingCurrent($appointment, $type);
            $timeSlots = $this->slotService->getTimeSlots($slotStorage, $appointment);
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
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointment The appointment to inform about
     */
    protected function performMailingActions($action, Appointment $appointment)
    {
        if (!$this->emailService->sendAction($action, $appointment)) {
            $flashMessage = LocalizationUtility::translate('tx_appointments_list.email_error', $this->extensionName);
            $this->addFlashMessage($flashMessage, '', FlashMessage::ERROR);
        }
    }

    /**
     * Fails validation manually based on time-related fields.
     * It then forwards to requested $action.
     *
     * @param string $action The action to forward to
     * @param integer $errorCode The errorcode
     * @param array $timeFields Contains formfield uids of time-related formfields
     * @see getReferringRequest()
     * @throws EarlyResponseThrowable
     */
    protected function failTimeValidation($action = 'new1', $errorCode = 407501337, array $timeFields = null)
    {
        $errorMsg = 'Time-related validation error.';

        $validationResults = new \TYPO3\CMS\Extbase\Error\Result();
        $appointmentResult = $validationResults->forProperty('appointment');

        // this marks the beginTime fields (date / time), and adds the validation error message to it
        $appointmentResult->forProperty('beginTime')->addError(new \TYPO3\CMS\Extbase\Validation\Error($errorMsg, $errorCode));

        // marks all the time-related formfieldvalues
        if ($timeFields !== null && !empty($timeFields)) {
            $formFieldValuesResult = $appointmentResult->forProperty('formFieldValues');
            /** @var \Innologi\Appointments\Domain\Model\FormField $formField */
            foreach ($timeFields as $formField) {
                $formFieldValuesResult->forProperty('i' . $formField->getUid() . '.value')->addError(new \TYPO3\CMS\Extbase\Validation\Error($errorMsg, $errorCode, [], $formField->getLabel()));
            }
        }

        // merge with any other outstanding validation results
        $validationResults->merge($this->arguments->validate());

        // forward request
        $originalRequest = clone $this->request;
        $this->request->setOriginalRequest($originalRequest);
        $this->request->setOriginalRequestMappingResults($validationResults);
        throw new EarlyResponseThrowable(ForwardResponse($action));
    }

    /**
     * Process overlap-info.
     *
     * Currently only appends related messages.
     *
     * @param array $overlapInfo As returned by crossAppointments()
     * @param Appointment $appointment The appointment that is overlapping
     * @see self::crossAppointments()
     */
    protected function processOverlapInfo(array $overlapInfo, Appointment $appointment)
    {
        $messageParts = '';

        if (isset($overlapInfo['begin'])) {
            $messageParts .= LocalizationUtility::translate('tx_appointments_list.crosstime_begin', $this->extensionName, [
                $appointment->getBeginTime()->format('H:i'),
                $overlapInfo['begin'] / 60,
            ]);
        }
        if (isset($overlapInfo['end'])) {
            $messageParts .= LocalizationUtility::translate('tx_appointments_list.crosstime_end', $this->extensionName, [
                $appointment->getEndTime()->format('H:i'),
                $overlapInfo['end'] / 60,
            ]);
        }

        $this->addFlashMessage(LocalizationUtility::translate('tx_appointments_list.crosstime_info', $this->extensionName, [
            $messageParts,
        ]), LocalizationUtility::translate('tx_appointments_list.crosstime_title', $this->extensionName), FlashMessage::ERROR);
    }

    /**
     * Sanitizes the formfieldvalues by checking if:
     * - its formfield even exists, to prevent errors @ view
     * - if there is an enabler, that it matches the assigned value
     *
     * @param array $formFieldValues Original formfieldvalues
     * @return array Sanitized formfieldvalues
     */
    protected function sanitizeFormFieldValues(array $formFieldValues)
    {
        $sanitized = [];
        $enable = [];
        $indexMap = [];

        foreach ($formFieldValues as $index => $value) {
            $formField = $value->getFormField();
            // dont add incomplete formfields
            if ($formField !== null) {
                $enableField = $formField->getEnableField();
                if ($enableField !== null) {
                    // register enabler-data
                    $enable[$index] = [
                        'id' => $enableField->getUid(),
                        'value' => $formField->getEnableValue(),
                    ];
                }
                $sanitized[$index] = $value;
                $indexMap[$formField->getUid()] = $index;
            }
        }

        foreach ($enable as $index => $enableData) {
            $id = $enableData['id'];
            if (isset($indexMap[$id])) {
                $enablerIndex = $indexMap[$id];
                $enabler = $sanitized[$enablerIndex];
                $enablerValue = $enabler->getValue();
                if ($enablerValue === $enableData['value']) {
                    continue;
                }
            }
            unset($sanitized[$index]);
        }

        return $sanitized;
    }
}

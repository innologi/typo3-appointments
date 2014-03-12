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
 * Appointment domain model
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Domain_Model_Appointment extends Tx_Extbase_DomainObject_AbstractEntity {

	//creation progress constants
	const FINISHED = 0; //appointment finalized
	const UNFINISHED = 1; //appointment not yet finalized AND occupying a timeslot
	const EXPIRED = 2; //appointment not yet finalized but NOT occupying a timeslot

	/**
	 * Creation timestamp
	 *
	 * @var integer
	 */
	protected $crdate;

	/**
	 * State of creation
	 *
	 * @var integer
	 */
	protected $creationProgress = self::UNFINISHED;

	/**
	 * Indicated if the original beginTime was changed
	 *
	 * @var boolean
	 * @transient
	 */
	protected $changedTime = FALSE;

	/**
	 * Start time
	 *
	 * @var DateTime
	 * @validate DateTime
	 */
	protected $beginTime;

	/**
	 * End time
	 *
	 * @var DateTime
	 */
	protected $endTime;

	/**
	 * Start reserved
	 *
	 * @var DateTime
	 */
	protected $beginReserved;

	/**
	 * End time
	 *
	 * @var DateTime
	 */
	protected $endReserved;

	/**
	 * Notes
	 *
	 * @var string
	 */
	protected $notes;

	/**
	 * Notes SU
	 *
	 * @var string
	 */
	protected $notesSu;

	/**
	 * Type which this Appointment belongs to
	 *
	 * @var Tx_Appointments_Domain_Model_Type
	 * @validate NotEmpty
	 * @lazy
	 */
	protected $type;
	#@LOW should see if this is still the case in 6.2, once we raise dependency version
	/**
	 * Form field values associated with this appointment
	 *
	 * 1. Cannot cascade remove this, because Extbase is anal about deleting these then,
	 * if changed from the parentObject, which I do. Also, Extbase isn't as extensible
	 * as I hoped: creating an alternative to ObjectStorage with a slightly different
	 * implementation of its methods would have circumvented the issue gracefully, but
	 * Extbase has its behaviour around ObjectStorages determined by hardcoded strings
	 * that represent classnames, which can of course be changed from within this extension,
	 * but needs a terrible amount of overrides of Extbase (and Fluid!) Core classes that
	 * I'm not willing to make, due to the increased chance of breaking at each version
	 * change.
	 *
	 * As an alternative to cascade remove, the records that are connected to deleted
	 * appointments will be deleted by the GC scheduler task.
	 *
	 * 2. Can be lazy, because the objectStorage is ONLY manipulated by form.
	 *
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Appointments_Domain_Model_FormFieldValue>
	 * @validate Tx_Appointments_Domain_Validator_ObjectStorageValidator(clearErrors=1)
	 * @lazy
	 */
	protected $formFieldValues; #@LOW create an extbase feature suggestion and patch to remedy the objectstorage behaviour with instanceof checks
	#@LOW test and see if making this an array would resolve the fluid issue of directly addressing an object (e.g. formFieldValues.189.value or formFieldValues._189.value)

	/**
	 * FormFieldValues that are set as sending-email-address
	 *
	 * @transient
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Appointments_Domain_Model_FormFieldValue>
	 */
	protected $emailFormFieldValues;

	/**
	 * Name and address information
	 *
	 * @var Tx_Appointments_Domain_Model_Address
	 * @cascade remove
	 * @lazy
	 */
	protected $address;

	/**
	 * User who created this appointment
	 *
	 * @var Tx_Appointments_Domain_Model_FrontendUser
	 * @lazy
	 */
	protected $feUser;

	/**
	 * Agenda in which this appointment was made
	 *
	 * @var Tx_Appointments_Domain_Model_Agenda
	 * @validate NotEmpty
	 * @lazy
	 */
	protected $agenda;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		//Do not remove the next line: It would break the functionality
		$this->initStorageObjects();
	}

	/**
	 * Initializes all Tx_Extbase_Persistence_ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		/**
		 * Do not modify this method!
		 * It will be rewritten on each save in the extension builder
		 * You may modify the constructor of this class instead
		 */
		$this->formFieldValues = new Tx_Extbase_Persistence_ObjectStorage();
	}


	/**
	 * Returns the creation timestamp
	 *
	 * @return integer $crdate
	 */
	public function getCrdate() {
		return $this->crdate;
	}

	#@LOW make these chainable?
	/**
	 * Returns the creationProgress flag
	 *
	 * @return integer $creationProgress
	 */
	public function getCreationProgress() {
		return $this->creationProgress;
	}

	/**
	 * Sets the creationProgress flag
	 *
	 * @param integer $creationProgress
	 * @return void
	 */
	public function setCreationProgress($creationProgress) {
		$this->creationProgress = $creationProgress;
		$address = $this->address;
		if ($address !== NULL) {
			$address->setCreationProgress($creationProgress);
		}
		if ($creationProgress === self::UNFINISHED) {
			$this->crdate = time();
		}
	}

	/**
	 * Returns changedTime
	 *
	 * @return boolean $changedTime
	 */
	public function getChangedTime() {
		return $this->changedTime;
	}

	/**
	 * Sets changedTime
	 *
	 * @param boolean $changedTime
	 * @return void
	 */
	public function setChangedTime($changedTime) {
		$this->changedTime = $changedTime;
	}

	/**
	 * Returns the beginTime
	 *
	 * @return DateTime $beginTime
	 */
	public function getBeginTime() {
		return $this->beginTime;
	}

	/**
	 * Sets the beginTime
	 *
	 * @param DateTime $beginTime
	 * @return void
	 */
	public function setBeginTime($beginTime) {
		$this->beginTime = $beginTime;
	}

	/**
	 * Returns the endTime
	 *
	 * @return DateTime $endTime
	 */
	public function getEndTime() {
		return $this->endTime;
	}

	/**
	 * Sets the endTime
	 *
	 * @param DateTime $endTime
	 * @return void
	 */
	public function setEndTime($endTime) {
		$this->endTime = $endTime;
	}

	/**
	 * Returns the beginReserved
	 *
	 * @return DateTime $beginReserved
	 */
	public function getBeginReserved() {
		return $this->beginReserved;
	}

	/**
	 * Sets the beginReserved
	 *
	 * @param DateTime $beginReserved
	 * @return void
	 */
	public function setBeginReserved($beginReserved) {
		$this->beginReserved = $beginReserved;
	}

	/**
	 * Returns the endReserved
	 *
	 * @return DateTime $endReserved
	 */
	public function getEndReserved() {
		return $this->endReserved;
	}

	/**
	 * Sets the endReserved
	 *
	 * @param DateTime $endReserved
	 * @return void
	 */
	public function setEndReserved($endReserved) {
		$this->endReserved = $endReserved;
	}

	/**
	 * Returns the notes
	 *
	 * @return string $notes
	 */
	public function getNotes() {
		return $this->notes;
	}

	/**
	 * Sets the notes
	 *
	 * @param string $notes
	 * @return void
	 */
	public function setNotes($notes) {
		$this->notes = $notes;
	}

	/**
	 * Returns the notes SU
	 *
	 * @return string $notesSu
	 */
	public function getNotesSu() {
		return $this->notesSu;
	}

	/**
	 * Sets the notes SU
	 *
	 * @param string $notesSu
	 * @return void
	 */
	public function setNotesSu($notesSu) {
		$this->notesSu = $notesSu;
	}

	/**
	 * Returns the type
	 *
	 * @return Tx_Appointments_Domain_Model_Type $type
	 */
	public function getType() {
		$this->noLazy($this->type);
		return $this->type;
	}

	/**
	 * Sets the type
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type
	 * @return void
	 */
	public function setType(Tx_Appointments_Domain_Model_Type $type) {
		$this->type = $type;
	}

	/**
	 * Adds a FormFieldValue
	 *
	 * @param Tx_Appointments_Domain_Model_FormFieldValue $formFieldValue
	 * @return void
	 */
	public function addFormFieldValue(Tx_Appointments_Domain_Model_FormFieldValue $formFieldValue) {
		$this->formFieldValues->attach($formFieldValue);
	}

	/**
	 * Removes a FormFieldValue
	 *
	 * @param Tx_Appointments_Domain_Model_FormFieldValue $formFieldValueToRemove The FormFieldValue to be removed
	 * @return void
	 */
	public function removeFormFieldValue(Tx_Appointments_Domain_Model_FormFieldValue $formFieldValueToRemove) {
		$this->formFieldValues->detach($formFieldValueToRemove);
	}

	/**
	 * Returns the formFieldValues
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage $formFieldValues
	 */
	public function getFormFieldValues() {
		return $this->formFieldValues;
	}

	/**
	 * Sets the formFieldValues
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_Appointments_Domain_Model_FormFieldValue> $formFieldValues
	 * @return void
	 */
	public function setFormFieldValues(Tx_Extbase_Persistence_ObjectStorage $formFieldValues) {
		$this->formFieldValues = $formFieldValues;
	}

	/**
	 * Returns the emailFormFieldValues
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage $emailFormFieldValues
	 */
	public function getEmailFormFieldValues() {
		if ($this->emailFormFieldValues === NULL) {
			$this->setEmailFormFieldValues($this->formFieldValues);
		}
		return $this->emailFormFieldValues;
	}

	/**
	 * Sets the emailFormFieldValues, filtered from $formFieldValues
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_Appointments_Domain_Model_FormFieldValue> $formFieldValues
	 * @return void
	 */
	public function setEmailFormFieldValues(Tx_Extbase_Persistence_ObjectStorage $formFieldValues) {
		$this->emailFormFieldValues = new Tx_Extbase_Persistence_ObjectStorage();
		foreach ($formFieldValues as $formFieldValue) {
			$formField = $formFieldValue->getFormField();
			if ($formField->getFunction() === Tx_Appointments_Domain_Model_FormField::FUNCTION_EMAIL) {
				$fieldType = $formField->getFieldType();
				if (
					$fieldType === Tx_Appointments_Domain_Model_FormField::TYPE_TEXTLARGE
					|| $fieldType === Tx_Appointments_Domain_Model_FormField::TYPE_TEXTSMALL
				) {
					$this->emailFormFieldValues->attach($formFieldValue);
				}
			}
		}
	}

	/**
	 * Returns the address
	 *
	 * @return Tx_Appointments_Domain_Model_Address $address
	 */
	public function getAddress() {
		$this->noLazy($this->address);
		return $this->address;
	}

	/**
	 * Sets the address
	 *
	 * @param Tx_Appointments_Domain_Model_Address $address
	 * @return void
	 */
	public function setAddress(Tx_Appointments_Domain_Model_Address $address) {
		$this->address = $address;
	}

	/**
	 * Returns the agenda
	 *
	 * @return Tx_Appointments_Domain_Model_Agenda $agenda
	 */
	public function getAgenda() {
		$this->noLazy($this->agenda);
		return $this->agenda;
	}

	/**
	 * Sets the agenda
	 *
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda
	 * @return void
	 */
	public function setAgenda(Tx_Appointments_Domain_Model_Agenda $agenda) {
		$this->agenda = $agenda;
	}

	/**
	 * Returns the feUser
	 *
	 * @return Tx_Appointments_Domain_Model_FrontendUser feUser
	 */
	public function getFeUser() {
		return $this->feUser;
	}

	/**
	 * Sets the feUser
	 *
	 * @param Tx_Appointments_Domain_Model_FrontendUser $feUser
	 * @return Tx_Appointments_Domain_Model_FrontendUser feUser
	 */
	public function setFeUser(Tx_Appointments_Domain_Model_FrontendUser $feUser) {
		$this->feUser = $feUser;
	}

	/**
	 * A check for lazy objects, and converts them to their real type.
	 *
	 * Useful when the objects are not addressed in all cases, but still in plenty
	 *
	 * @param mixed $property Defined by reference because we're replacing the original reference
	 */
	protected function noLazy(&$property) { #@TODO when is this really necessary?
		if (is_object($property) && $property instanceof Tx_Extbase_Persistence_LazyLoadingProxy) {
			$property = $property->_loadRealInstance();
		}
	}

}
?>
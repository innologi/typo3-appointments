<?php
namespace Innologi\Appointments\Domain\Model;
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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
/**
 * Appointment domain model
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Appointment extends AbstractEntity {

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
	 * Remaining seconds on chosen timeslot
	 *
	 * @var integer
	 * @extensionScannerIgnoreLine
	 * @transient
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
	 */
	protected $remainingSeconds = NULL;

	/**
	 * Start time
	 *
	 * @var \DateTime
	 * @extensionScannerIgnoreLine
	 * @validate NotEmpty,DateTime
	 * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
	 * @TYPO3\CMS\Extbase\Annotation\Validate("DateTime")
	 */
	protected $beginTime;

	/**
	 * End time
	 *
	 * @var \DateTime
	 */
	protected $endTime;

	/**
	 * Start reserved
	 *
	 * @var \DateTime
	 */
	protected $beginReserved;

	/**
	 * End time
	 *
	 * @var \DateTime
	 */
	protected $endReserved;

	/**
	 * Notes
	 *
	 * @var string
	 */
	protected $notes = '';

	/**
	 * Notes SU
	 *
	 * @var string
	 */
	protected $notesSu = '';

	/**
	 * Type which this Appointment belongs to
	 *
	 * @var \Innologi\Appointments\Domain\Model\Type
	 * @extensionScannerIgnoreLine
	 * @validate NotEmpty
	 * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
	 * @extensionScannerIgnoreLine
	 * @lazy
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
	 */
	protected $type;

	/**
	 * Form field values associated with this appointment
	 *
	 * Couldn't be cascade remove in 4.5-4.7, because Extbase attempted to remove them
	 * upon changing content via parentObj. But doesn't seem to be an issue on T3v8.
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Appointments\Domain\Model\FormFieldValue>
	 * @extensionScannerIgnoreLine
	 * @cascade remove
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
	 * @extensionScannerIgnoreLine
	 * @lazy
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
	 */
	protected $formFieldValues;

	/**
	 * FormFieldValues that are set as sending-email-address
	 *
	 * @extensionScannerIgnoreLine
	 * @transient
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Appointments\Domain\Model\FormFieldValue>
	 */
	protected $emailFormFieldValues;

	/**
	 * Name and address information
	 *
	 * Validation is done through AppointmentValidator
	 *
	 * @var \Innologi\Appointments\Domain\Model\Address
	 * @extensionScannerIgnoreLine
	 * @cascade remove
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
	 */
	protected $address;

	/**
	 * User who created this appointment
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
	 * @extensionScannerIgnoreLine
	 * @lazy
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
	 */
	protected $feUser;

	/**
	 * Agenda in which this appointment was made
	 *
	 * @var \Innologi\Appointments\Domain\Model\Agenda
	 * @extensionScannerIgnoreLine
	 * @validate NotEmpty
	 * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
	 * @extensionScannerIgnoreLine
	 * @lazy
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
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
	 * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		/**
		 * Do not modify this method!
		 * It will be rewritten on each save in the extension builder
		 * You may modify the constructor of this class instead
		 */
		$this->formFieldValues = new ObjectStorage();
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
	 * Returns remainingSeconds
	 *
	 * @return integer $remainingSeconds
	 */
	public function getRemainingSeconds() {
		return $this->remainingSeconds;
	}

	/**
	 * Sets remainingSeconds
	 *
	 * @param integer $remainingSeconds
	 * @return void
	 */
	public function setRemainingSeconds($remainingSeconds) {
		$this->remainingSeconds = $remainingSeconds;
	}

	/**
	 * Returns the beginTime
	 *
	 * @return \DateTime $beginTime
	 */
	public function getBeginTime() {
		return $this->beginTime;
	}

	/**
	 * Sets the beginTime
	 *
	 * @param \DateTime $beginTime
	 * @return void
	 */
	public function setBeginTime($beginTime) {
		$this->beginTime = $beginTime;
	}

	/**
	 * Returns the endTime
	 *
	 * @return \DateTime $endTime
	 */
	public function getEndTime() {
		return $this->endTime;
	}

	/**
	 * Sets the endTime
	 *
	 * @param \DateTime $endTime
	 * @return void
	 */
	public function setEndTime($endTime) {
		$this->endTime = $endTime;
	}

	/**
	 * Returns the beginReserved
	 *
	 * @return \DateTime $beginReserved
	 */
	public function getBeginReserved() {
		return $this->beginReserved;
	}

	/**
	 * Sets the beginReserved
	 *
	 * @param \DateTime $beginReserved
	 * @return void
	 */
	public function setBeginReserved($beginReserved) {
		$this->beginReserved = $beginReserved;
	}

	/**
	 * Returns the endReserved
	 *
	 * @return \DateTime $endReserved
	 */
	public function getEndReserved() {
		return $this->endReserved;
	}

	/**
	 * Sets the endReserved
	 *
	 * @param \DateTime $endReserved
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
	 * @return \Innologi\Appointments\Domain\Model\Type
	 */
	public function getType() {
		$this->noLazy($this->type);
		return $this->type;
	}

	/**
	 * Sets the type
	 *
	 * @param \Innologi\Appointments\Domain\Model\Type $type
	 * @return void
	 */
	public function setType(Type $type) {
		$this->type = $type;
	}

	/**
	 * Adds a FormFieldValue
	 *
	 * @param \Innologi\Appointments\Domain\Model\FormFieldValue $formFieldValue
	 * @return void
	 */
	public function addFormFieldValue(FormFieldValue $formFieldValue) {
		$this->formFieldValues->attach($formFieldValue);
	}

	/**
	 * Removes a FormFieldValue
	 *
	 * @param \Innologi\Appointments\Domain\Model\FormFieldValue $formFieldValueToRemove The FormFieldValue to be removed
	 * @return void
	 */
	public function removeFormFieldValue(FormFieldValue $formFieldValueToRemove) {
		$this->formFieldValues->detach($formFieldValueToRemove);
	}

	/**
	 * Returns the formFieldValues
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getFormFieldValues() {
		return $this->formFieldValues;
	}

	/**
	 * Sets the formFieldValues
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $formFieldValues
	 * @return void
	 */
	public function setFormFieldValues(ObjectStorage $formFieldValues) {
		$this->formFieldValues = $formFieldValues;
	}

	/**
	 * Returns the emailFormFieldValues
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
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
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Appointments\Domain\Model\FormFieldValue> $formFieldValues
	 * @return void
	 */
	public function setEmailFormFieldValues(ObjectStorage $formFieldValues) {
		$this->emailFormFieldValues = new ObjectStorage();
		foreach ($formFieldValues as $formFieldValue) {
			$formField = $formFieldValue->getFormField();
			if ($formField->getFunction() === FormField::FUNCTION_EMAIL) {
				$fieldType = $formField->getFieldType();
				if (
					$fieldType === FormField::TYPE_TEXTLARGE
					|| $fieldType === FormField::TYPE_TEXTSMALL
				) {
					$this->emailFormFieldValues->attach($formFieldValue);
				}
			}
		}
	}

	/**
	 * Returns the address
	 *
	 * @return \Innologi\Appointments\Domain\Model\Address
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * Sets the address
	 *
	 * @param \Innologi\Appointments\Domain\Model\Address $address
	 * @return void
	 */
	public function setAddress(Address $address) {
		$this->address = $address;
	}

	/**
	 * Returns the agenda
	 *
	 * @return \Innologi\Appointments\Domain\Model\Agenda
	 */
	public function getAgenda() {
		$this->noLazy($this->agenda);
		return $this->agenda;
	}

	/**
	 * Sets the agenda
	 *
	 * @param \Innologi\Appointments\Domain\Model\Agenda $agenda
	 * @return void
	 */
	public function setAgenda(Agenda $agenda) {
		$this->agenda = $agenda;
	}

	/**
	 * Returns the feUser
	 *
	 * @return \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
	 */
	public function getFeUser() {
		return $this->feUser;
	}

	/**
	 * Sets the feUser
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $feUser
	 * @return void
	 */
	public function setFeUser(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $feUser) {
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
		if (is_object($property) && $property instanceof LazyLoadingProxy) {
			$property = $property->_loadRealInstance();
		}
	}

}
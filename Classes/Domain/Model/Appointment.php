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
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Appointment domain model
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Appointment extends AbstractEntity
{
    //creation progress constants
    public const FINISHED = 0; //appointment finalized
    public const UNFINISHED = 1; //appointment not yet finalized AND occupying a timeslot
    public const EXPIRED = 2; //appointment not yet finalized but NOT occupying a timeslot

    /**
     * State of creation
     *
     * @var integer
     */
    protected $creationProgress = self::UNFINISHED;

    /**
     * @var integer
     */
    protected $reservationTime;

    /**
     * Remaining seconds on chosen timeslot
     *
     * @var integer
     * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
     */
    protected $remainingSeconds = null;

    /**
     * Start time
     *
     * @var \DateTime
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
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
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
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $formFieldValues;

    /**
     * FormFieldValues that are set as sending-email-address
     *
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
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $address;

    /**
     * User who created this appointment
     *
     * @var \Innologi\Appointments\Domain\Model\FrontendUser
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $feUser;

    /**
     * Agenda in which this appointment was made
     *
     * @var \Innologi\Appointments\Domain\Model\Agenda
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $agenda;

    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties.
     */
    protected function initStorageObjects()
    {
        /**
         * Do not modify this method!
         * It will be rewritten on each save in the extension builder
         * You may modify the constructor of this class instead
         */
        $this->formFieldValues = new ObjectStorage();
    }

    /**
     * Returns the start of the reservation timer
     *
     * @return integer
     */
    public function getReservationTime()
    {
        return $this->reservationTime;
    }

    #@LOW make these chainable?
    /**
     * Returns the creationProgress flag
     *
     * @return integer
     */
    public function getCreationProgress()
    {
        return $this->creationProgress;
    }

    /**
     * Sets the creationProgress flag
     *
     * @param integer $creationProgress
     */
    public function setCreationProgress($creationProgress)
    {
        $this->creationProgress = $creationProgress;
        $address = $this->address;
        if ($address !== null) {
            $address->setCreationProgress($creationProgress);
        }
        if ($creationProgress === self::UNFINISHED) {
            $this->reservationTime = time();
        }
    }

    /**
     * Returns remainingSeconds
     *
     * @return integer
     */
    public function getRemainingSeconds()
    {
        return $this->remainingSeconds;
    }

    /**
     * Sets remainingSeconds
     *
     * @param integer $remainingSeconds
     */
    public function setRemainingSeconds($remainingSeconds)
    {
        $this->remainingSeconds = $remainingSeconds;
    }

    /**
     * Returns the beginTime
     *
     * @return \DateTime
     */
    public function getBeginTime()
    {
        return $this->beginTime;
    }

    /**
     * Sets the beginTime
     *
     * @param \DateTime $beginTime
     */
    public function setBeginTime($beginTime)
    {
        $this->beginTime = $beginTime;
    }

    /**
     * Returns the endTime
     *
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Sets the endTime
     *
     * @param \DateTime $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * Returns the beginReserved
     *
     * @return \DateTime
     */
    public function getBeginReserved()
    {
        return $this->beginReserved;
    }

    /**
     * Sets the beginReserved
     *
     * @param \DateTime $beginReserved
     */
    public function setBeginReserved($beginReserved)
    {
        $this->beginReserved = $beginReserved;
    }

    /**
     * Returns the endReserved
     *
     * @return \DateTime
     */
    public function getEndReserved()
    {
        return $this->endReserved;
    }

    /**
     * Sets the endReserved
     *
     * @param \DateTime $endReserved
     */
    public function setEndReserved($endReserved)
    {
        $this->endReserved = $endReserved;
    }

    /**
     * Returns the notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Sets the notes
     *
     * @param string $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * Returns the notes SU
     *
     * @return string
     */
    public function getNotesSu()
    {
        return $this->notesSu;
    }

    /**
     * Sets the notes SU
     *
     * @param string $notesSu
     */
    public function setNotesSu($notesSu)
    {
        $this->notesSu = $notesSu;
    }

    /**
     * Returns the type
     *
     * @return \Innologi\Appointments\Domain\Model\Type
     */
    public function getType()
    {
        $this->noLazy($this->type);
        return $this->type;
    }

    /**
     * Sets the type
     */
    public function setType(Type $type)
    {
        $this->type = $type;
    }

    /**
     * Adds a FormFieldValue
     */
    public function addFormFieldValue(FormFieldValue $formFieldValue)
    {
        $this->formFieldValues->attach($formFieldValue);
    }

    /**
     * Removes a FormFieldValue
     *
     * @param \Innologi\Appointments\Domain\Model\FormFieldValue $formFieldValueToRemove The FormFieldValue to be removed
     */
    public function removeFormFieldValue(FormFieldValue $formFieldValueToRemove)
    {
        $this->formFieldValues->detach($formFieldValueToRemove);
    }

    /**
     * Returns the formFieldValues
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getFormFieldValues()
    {
        return $this->formFieldValues;
    }

    /**
     * Sets the formFieldValues
     */
    public function setFormFieldValues(ObjectStorage $formFieldValues)
    {
        $this->formFieldValues = $formFieldValues;
    }

    /**
     * Returns the emailFormFieldValues
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getEmailFormFieldValues()
    {
        if ($this->emailFormFieldValues === null) {
            $this->setEmailFormFieldValues($this->formFieldValues);
        }
        return $this->emailFormFieldValues;
    }

    /**
     * Sets the emailFormFieldValues, filtered from $formFieldValues
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Appointments\Domain\Model\FormFieldValue> $formFieldValues
     */
    public function setEmailFormFieldValues(ObjectStorage $formFieldValues)
    {
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
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets the address
     */
    public function setAddress(Address $address)
    {
        $this->address = $address;
    }

    /**
     * Returns the agenda
     *
     * @return \Innologi\Appointments\Domain\Model\Agenda
     */
    public function getAgenda()
    {
        $this->noLazy($this->agenda);
        return $this->agenda;
    }

    /**
     * Sets the agenda
     */
    public function setAgenda(Agenda $agenda)
    {
        $this->agenda = $agenda;
    }

    /**
     * Returns the feUser
     *
     * @return \Innologi\Appointments\Domain\Model\FrontendUser
     */
    public function getFeUser()
    {
        return $this->feUser;
    }

    /**
     * Sets the feUser
     */
    public function setFeUser(\Innologi\Appointments\Domain\Model\FrontendUser $feUser)
    {
        $this->feUser = $feUser;
    }

    /**
     * A check for lazy objects, and converts them to their real type.
     *
     * Useful when the objects are not addressed in all cases, but still in plenty
     *
     * @param mixed $property Defined by reference because we're replacing the original reference
     */
    protected function noLazy(&$property) #@TODO when is this really necessary?
    {
        if (is_object($property) && $property instanceof LazyLoadingProxy) {
            $property = $property->_loadRealInstance();
        }
    }
}

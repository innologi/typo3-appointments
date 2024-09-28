<?php

namespace Innologi\Appointments\Domain\Model\Agenda;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use Innologi\Appointments\Domain\Model\Appointment;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Agenda Date
 *
 * A non-persisted entity for easy use in views.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Date extends AbstractEntity
{
    /**
     * Classes for agenda use
     *
     * @var string
     */
    protected $agendaClasses = '';

    /**
     * Number of day in month
     *
     * @var string
     */
    protected $dayNumber;

    /**
     * Short name of month
     *
     * @var string
     */
    protected $monthShort;

    /**
     * Timestamp
     *
     * @var integer
     */
    protected $timestamp;

    /**
     * Is a holiday
     *
     * @var boolean
     */
    protected $isHoliday = false;

    /**
     * Is today
     *
     * @var boolean
     */
    protected $isToday = false;

    /**
     * Allows creation of new appointments
     *
     * @var boolean
     */
    protected $allowCreate = false;

    /**
     * Appointments
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Appointments\Domain\Model\Appointment>
     */
    protected $appointments;

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
        $this->appointments = new ObjectStorage();
    }

    /**
     * Returns the Agenda Classes
     *
     * @return string
     */
    public function getAgendaClasses()
    {
        return $this->agendaClasses;
    }

    /**
     * Sets the Agenda Classes
     *
     * @param string $agendaClasses
     */
    public function setAgendaClasses($agendaClasses): void
    {
        $this->agendaClasses = $agendaClasses;
    }

    /**
     * Adds a single Agenda Class
     *
     * @param string $agendaClass
     */
    public function addAgendaClass($agendaClass): void
    {
        $this->agendaClasses .= ' ' . $agendaClass;
    }

    /**
     * Returns the Day Number
     *
     * @return string
     */
    public function getDayNumber()
    {
        return $this->dayNumber;
    }

    /**
     * Sets the Day Number
     *
     * @param string $dayNumber
     */
    public function setDayNumber($dayNumber): void
    {
        $this->dayNumber = $dayNumber;
    }

    // @TODO monthShort is a representation choice.. just make it a month property and get the string from locallang in the template
    /**
     * Returns the month short
     *
     * @return string
     */
    public function getMonthShort()
    {
        return $this->monthShort;
    }

    /**
     * Sets the month short
     *
     * @param string $monthShort
     */
    public function setMonthShort($monthShort): void
    {
        $this->monthShort = $monthShort;
    }

    /**
     * Returns the timestamp
     *
     * @return integer
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Sets the timestamp
     *
     * @param integer $timestamp
     */
    public function setTimestamp($timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Returns the Is Holiday
     *
     * @return boolean
     */
    public function getIsHoliday()
    {
        return $this->isHoliday;
    }

    /**
     * Sets the Is Holiday
     *
     * @param boolean $isHoliday
     */
    public function setIsHoliday($isHoliday): void
    {
        $this->isHoliday = $isHoliday;
        if ($isHoliday) {
            $this->addAgendaClass('holiday');
        }
    }

    /**
     * Returns IsToday
     *
     * @return boolean
     */
    public function getIsToday()
    {
        return $this->isToday;
    }

    /**
     * Sets IsToday
     *
     * @param boolean $isToday
     */
    public function setIsToday($isToday): void
    {
        $this->isToday = $isToday;
        if ($isToday) {
            $this->addAgendaClass('current');
        }
    }

    /**
     * Returns allowCreate
     *
     * @return boolean
     */
    public function getAllowCreate()
    {
        return $this->allowCreate;
    }

    /**
     * Sets allowCreate
     *
     * @param boolean $allowCreate
     */
    public function setAllowCreate($allowCreate): void
    {
        $this->allowCreate = $allowCreate;
    }

    /**
     * Adds an appointment
     */
    public function addAppointment(Appointment $appointment): void
    {
        $this->appointments->attach($appointment);
    }

    /**
     * Removes an appointment
     *
     * @param \Innologi\Appointments\Domain\Model\Appointment $appointmentToRemove The Appointment to be removed
     */
    public function removeAppointment(Appointment $appointmentToRemove): void
    {
        $this->appointments->detach($appointmentToRemove);
    }

    /**
     * Returns the appointments
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getAppointments()
    {
        return $this->appointments;
    }

    /**
     * Sets the appointments
     */
    public function setAppointments(ObjectStorage $appointments): void
    {
        $this->appointments = $appointments;
    }
}

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

/**
 * Type domain model
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Type extends AbstractEntity
{
    /**
     * Name of type
     *
     * @var string
     */
    protected $name; //validate NotEmpty

    /**
     * Superuser only?
     *
     * @var boolean
     */
    protected $superuserOnly = false;

    /**
     * Exclusive availability?
     *
     * @var boolean
     */
    protected $exclusiveAvailability = false;
    #@LOW imagine a different approach to type-binding:
    /*
     * - set sub-types inline in agenda record
     * - sub-type consists of type record, name, and agenda-specific settings like:
     * exclusiveAvailability, SuperUser-only
     * - this way, you can define a type to be inherited by sub-types, include it
     * multiple times, and give each a different role which are all supposed to be
     * similar in conditions and formfields
     * - if alternate slotstorage/caching works, all sub-types could retrieve from
     * a single type-cache
     * - a change in a type would echo through ALL sub-types, however, if you wish
     * to change a single sub-type, you could make it inherit a different type
     * without influencing other sub-types or removing its appointments
     * - if you don't want the implications to echo through the appointments,
     * you would still require a new sub-type, just like you would currently
     * require a new type
     * - in the plugin you would set which of the sub-types to show, and which
     * can be created. This way, if you create a replacement sub-type with the same
     * name as the old, you can set the old to show only, and this way leave
     * every old appointment intact
     * - an update script would be required to set the relations between sub-types
     * (which the script would create for you) and agenda's, reading the relevant
     * values from type and writing them into the sub-types
     * - as long as the update script remains, the old table columns of type will
     * also remain in the sql so they don't get deleted before you could run the
     * update script
     *
     * Would that increase flexibility and usability?
     * Would that also increase configuration complexity?
     * Is that trade-off acceptable?
     * Or is it rather useless as the current situation works just fine?
     */

    /**
     * @var boolean
     */
    protected $dontBlockTypes = false;

    /**
     * @var boolean
     */
    protected $dontRestrictTypeCounts = false;

    /**
     * Default duration
     *
     * @var integer
     */
    protected $defaultDuration; //validate Integer

    /**
     * Earliest possible time to make appointments on Mondays
     *
     * @var string
     */
    protected $startTimeMonday;

    /**
     * Earliest possible time to make appointments on Tuesdays
     *
     * @var string
     */
    protected $startTimeTuesday;

    /**
     * Earliest possible time to make appointments on Wednesdays
     *
     * @var string
     */
    protected $startTimeWednesday;

    /**
     * Earliest possible time to make appointments on Thursdays
     *
     * @var string
     */
    protected $startTimeThursday;

    /**
     * Earliest possible time to make appointments on Fridays
     *
     * @var string
     */
    protected $startTimeFriday;

    /**
     * Earliest possible time to make appointments on Saturdays
     *
     * @var string
     */
    protected $startTimeSaturday;

    /**
     * Earliest possible time to make appointments on Sundays
     *
     * @var string
     */
    protected $startTimeSunday;

    /**
     * Latest possible time to make appointments on Mondays
     *
     * @var string
     */
    protected $stopTimeMonday;

    /**
     * Latest possible time to make appointments on Tuesdays
     *
     * @var string
     */
    protected $stopTimeTuesday;

    /**
     * Latest possible time to make appointments on Wednesdays
     *
     * @var string
     */
    protected $stopTimeWednesday;

    /**
     * Latest possible time to make appointments on Thursdays
     *
     * @var string
     */
    protected $stopTimeThursday;

    /**
     * Latest possible time to make appointments on Fridays
     *
     * @var string
     */
    protected $stopTimeFriday;

    /**
     * Latest possible time to make appointments on Saturdays
     *
     * @var string
     */
    protected $stopTimeSaturday;

    /**
     * Latest possible time to make appointments on Sundays
     *
     * @var string
     */
    protected $stopTimeSunday;

    /**
     * If enabled, does not allow appointments on days marked as holiday in the agenda record.
     *
     * @var boolean
     */
    protected $excludeHolidays = false;

    /**
     * Number of appointments allowed on Mondays
     *
     * @var integer
     */
    protected $maxAmountMonday; //validate NumberRange(startRange=0,endRange=1440)

    /**
     * Number of appointments allowed on Tuesdays
     *
     * @var integer
     */
    protected $maxAmountTuesday; //validate NumberRange(startRange=0,endRange=1440)

    /**
     * Number of appointments allowed on Wednesdays
     *
     * @var integer
     */
    protected $maxAmountWednesday; //validate NumberRange(startRange=0,endRange=1440)

    /**
     * Number of appointments allowed on Thursdays
     *
     * @var integer
     */
    protected $maxAmountThursday; //validate NumberRange(startRange=0,endRange=1440)

    /**
     * Number of appointments allowed on Fridays
     *
     * @var integer
     */
    protected $maxAmountFriday; //validate NumberRange(startRange=0,endRange=1440)

    /**
     * Number of appointments allowed on Saturdays
     *
     * @var integer
     */
    protected $maxAmountSaturday; //validate NumberRange(startRange=0,endRange=1440)

    /**
     * Number of appointments allowed on Sundays
     *
     * @var integer
     */
    protected $maxAmountSunday; //validate NumberRange(startRange=0,endRange=1440)

    /**
     * Number of minutes between allowed appointment time-slots on Mondays
     *
     * @var integer
     */
    protected $minuteIntervalMonday; //validate NumberRange(startRange=1,endRange=1440)

    /**
     * Number of minutes between allowed appointment time-slots on Tuesdays
     *
     * @var integer
     */
    protected $minuteIntervalTuesday; //validate NumberRange(startRange=1,endRange=1440)

    /**
     * Number of minutes between allowed appointment time-slots on Wednesdays
     *
     * @var integer
     */
    protected $minuteIntervalWednesday; //validate NumberRange(startRange=1,endRange=1440)

    /**
     * Number of minutes between allowed appointment time-slots on Thursdays
     *
     * @var integer
     */
    protected $minuteIntervalThursday; //validate NumberRange(startRange=1,endRange=1440)

    /**
     * Number of minutes between allowed appointment time-slots on Fridays
     *
     * @var integer
     */
    protected $minuteIntervalFriday; //validate NumberRange(startRange=1,endRange=1440)

    /**
     * Number of minutes between allowed appointment time-slots on Saturdays
     *
     * @var integer
     */
    protected $minuteIntervalSaturday; //validate NumberRange(startRange=1,endRange=1440)

    /**
     * Number of minutes between allowed appointment time-slots on Sundays
     *
     * @var integer
     */
    protected $minuteIntervalSunday; //validate NumberRange(startRange=1,endRange=1440)

    /**
     * Overrules the allowed cumulative amount of appointments over X consecutive days
     *
     * @var integer
     */
    protected $maxAmountPerVarDays; //validate NumberRange(startRange=0,endRange=10080)

    /**
     * X consecutive days
     *
     * @var integer
     */
    protected $perVarDays; //validate NumberRange(startRange=0,endRange=7)

    /**
     * X consecutive days test interval & buffer
     *
     * @var integer
     */
    protected $perVarDaysInterval; //validate NumberRange(startRange=0,endRange=168)

    /**
     * Number of minutes reserved between, before, and after appointments
     *
     * @var integer
     */
    protected $betweenMinutes; //validate Integer

    /**
     * Number of hours the appointment can be changed after being scheduled.
     *
     * @var integer
     */
    protected $hoursMutable; //validate Integer

    /**
     * Number of hours blocked until first appointment the user is able to schedule
     *
     * @var integer
     */
    protected $blockedHours; //validate Integer

    /**
     * Number of hours blocked from workdays, in case blocked hours fall in weekend
     *
     * @var integer
     */
    protected $blockedHoursWorkdays; //validate Integer

    /**
     * Maximum days forward available to schedule
     *
     * @var integer
     */
    protected $maxDaysForward; //validate Integer

    /**
     * If set, disables the entire address
     *
     * @var boolean
     */
    protected $addressDisable;

    /**
     * If set, enables Name fields
     *
     * @var boolean
     */
    protected $addressEnableName;

    /**
     * If set, enables Gender field
     *
     * @var boolean
     */
    protected $addressEnableGender;

    /**
     * If set, enables Birthday field
     *
     * @var boolean
     */
    protected $addressEnableBirthday;

    /**
     * If set, enables Address fields
     *
     * @var boolean
     */
    protected $addressEnableAddress;

    /**
     * If set, enables Social Security Number field
     *
     * @var boolean
     */
    protected $addressEnableSecurity;

    /**
     * If set, enables Email Address field
     *
     * @var boolean
     */
    protected $addressEnableEmail;

    /**
     * Form Fields for this Type
     *
     * Lazy although a clone is modified in new/edit cases, after which count() will prove useless.
     * To remedy that, we convert the modified clone toArray() once we need to count, so we can keep it lazy.
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Appointments\Domain\Model\FormField>
     */
    #[\TYPO3\CMS\Extbase\Annotation\ORM\Lazy]
    #[\TYPO3\CMS\Extbase\Annotation\ORM\Cascade(['value' => 'remove'])]
    protected $formFields;

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
        $this->formFields = new ObjectStorage();
    }

    /**
     * Returns the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name
     *
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * Returns superuserOnly
     *
     * @return boolean
     */
    public function getSuperuserOnly()
    {
        return $this->superuserOnly;
    }

    /**
     * Sets superuserOnly
     *
     * @param boolean $superuserOnly
     */
    public function setSuperuserOnly($superuserOnly): void
    {
        $this->superuserOnly = $superuserOnly;
    }

    /**
     * Returns exclusideAvailability
     *
     * @return boolean
     */
    public function getExclusiveAvailability()
    {
        return $this->exclusiveAvailability;
    }

    /**
     * Sets exclusiveAvailability
     *
     * @param boolean $exclusiveAvailability
     */
    public function setExclusiveAvailability($exclusiveAvailability): void
    {
        $this->exclusiveAvailability = $exclusiveAvailability;
    }

    /**
     * Returns dontblocktypes
     *
     * @return boolean
     */
    public function getDontBlockTypes()
    {
        return $this->dontBlockTypes;
    }

    /**
     * Sets dontblocktypes
     *
     * @param boolean $dontBlockTypes
     */
    public function setDontBlockTypes($dontBlockTypes): void
    {
        $this->dontBlockTypes = $dontBlockTypes;
    }

    /**
     * Returns dontrestrictypecounts
     *
     * @return boolean
     */
    public function getDontRestrictTypeCounts()
    {
        return $this->dontRestrictTypeCounts;
    }

    /**
     * Sets dontrestricttypecounts
     *
     * @param boolean $dontRestrictTypeCounts
     */
    public function setDontRestrictTypeCounts($dontRestrictTypeCounts): void
    {
        $this->dontRestrictTypeCounts = $dontRestrictTypeCounts;
    }

    /**
     * Returns the defaultDuration in minutes
     *
     * @return integer
     */
    public function getDefaultDuration()
    {
        return $this->defaultDuration;
    }

    /**
     * Sets the defaultDuration in minutes
     *
     * @param integer $defaultDuration
     */
    public function setDefaultDuration($defaultDuration): void
    {
        $this->defaultDuration = $defaultDuration;
    }

    /**
     * Returns the startTimeMonday
     *
     * @return string
     */
    public function getStartTimeMonday()
    {
        return $this->startTimeMonday;
    }

    /**
     * Sets the startTimeMonday
     *
     * @param string $startTimeMonday
     */
    public function setStartTimeMonday($startTimeMonday): void
    {
        $this->startTimeMonday = $startTimeMonday;
    }

    /**
     * Returns the startTimeTuesday
     *
     * @return string
     */
    public function getStartTimeTuesday()
    {
        return $this->startTimeTuesday;
    }

    /**
     * Sets the startTimeTuesday
     *
     * @param string $startTimeTuesday
     */
    public function setStartTimeTuesday($startTimeTuesday): void
    {
        $this->startTimeTuesday = $startTimeTuesday;
    }

    /**
     * Returns the startTimeWednesday
     *
     * @return string
     */
    public function getStartTimeWednesday()
    {
        return $this->startTimeWednesday;
    }

    /**
     * Sets the startTimeWednesday
     *
     * @param string $startTimeWednesday
     */
    public function setStartTimeWednesday($startTimeWednesday): void
    {
        $this->startTimeWednesday = $startTimeWednesday;
    }

    /**
     * Returns the startTimeThursday
     *
     * @return string
     */
    public function getStartTimeThursday()
    {
        return $this->startTimeThursday;
    }

    /**
     * Sets the startTimeThursday
     *
     * @param string $startTimeThursday
     */
    public function setStartTimeThursday($startTimeThursday): void
    {
        $this->startTimeThursday = $startTimeThursday;
    }

    /**
     * Returns the startTimeFriday
     *
     * @return string
     */
    public function getStartTimeFriday()
    {
        return $this->startTimeFriday;
    }

    /**
     * Sets the startTimeFriday
     *
     * @param string $startTimeFriday
     */
    public function setStartTimeFriday($startTimeFriday): void
    {
        $this->startTimeFriday = $startTimeFriday;
    }

    /**
     * Returns the startTimeSaturday
     *
     * @return string
     */
    public function getStartTimeSaturday()
    {
        return $this->startTimeSaturday;
    }

    /**
     * Sets the startTimeSaturday
     *
     * @param string $startTimeSaturday
     */
    public function setStartTimeSaturday($startTimeSaturday): void
    {
        $this->startTimeSaturday = $startTimeSaturday;
    }

    /**
     * Returns the startTimeSunday
     *
     * @return string
     */
    public function getStartTimeSunday()
    {
        return $this->startTimeSunday;
    }

    /**
     * Sets the startTimeSunday
     *
     * @param string $startTimeSunday
     */
    public function setStartTimeSunday($startTimeSunday): void
    {
        $this->startTimeSunday = $startTimeSunday;
    }

    /**
     * Returns the stopTimeMonday
     *
     * @return string
     */
    public function getStopTimeMonday()
    {
        return $this->stopTimeMonday;
    }

    /**
     * Sets the stopTimeMonday
     *
     * @param string $stopTimeMonday
     */
    public function setStopTimeMonday($stopTimeMonday): void
    {
        $this->stopTimeMonday = $stopTimeMonday;
    }

    /**
     * Returns the stopTimeTuesday
     *
     * @return string
     */
    public function getStopTimeTuesday()
    {
        return $this->stopTimeTuesday;
    }

    /**
     * Sets the stopTimeTuesday
     *
     * @param string $stopTimeTuesday
     */
    public function setStopTimeTuesday($stopTimeTuesday): void
    {
        $this->stopTimeTuesday = $stopTimeTuesday;
    }

    /**
     * Returns the stopTimeWednesday
     *
     * @return string
     */
    public function getStopTimeWednesday()
    {
        return $this->stopTimeWednesday;
    }

    /**
     * Sets the stopTimeWednesday
     *
     * @param string $stopTimeWednesday
     */
    public function setStopTimeWednesday($stopTimeWednesday): void
    {
        $this->stopTimeWednesday = $stopTimeWednesday;
    }

    /**
     * Returns the stopTimeThursday
     *
     * @return string
     */
    public function getStopTimeThursday()
    {
        return $this->stopTimeThursday;
    }

    /**
     * Sets the stopTimeThursday
     *
     * @param string $stopTimeThursday
     */
    public function setStopTimeThursday($stopTimeThursday): void
    {
        $this->stopTimeThursday = $stopTimeThursday;
    }

    /**
     * Returns the stopTimeFriday
     *
     * @return string
     */
    public function getStopTimeFriday()
    {
        return $this->stopTimeFriday;
    }

    /**
     * Sets the stopTimeFriday
     *
     * @param string $stopTimeFriday
     */
    public function setStopTimeFriday($stopTimeFriday): void
    {
        $this->stopTimeFriday = $stopTimeFriday;
    }

    /**
     * Returns the stopTimeSaturday
     *
     * @return string
     */
    public function getStopTimeSaturday()
    {
        return $this->stopTimeSaturday;
    }

    /**
     * Sets the stopTimeSaturday
     *
     * @param string $stopTimeSaturday
     */
    public function setStopTimeSaturday($stopTimeSaturday): void
    {
        $this->stopTimeSaturday = $stopTimeSaturday;
    }

    /**
     * Returns the stopTimeSunday
     *
     * @return string
     */
    public function getStopTimeSunday()
    {
        return $this->stopTimeSunday;
    }

    /**
     * Sets the stopTimeSunday
     *
     * @param string $stopTimeSunday
     */
    public function setStopTimeSunday($stopTimeSunday): void
    {
        $this->stopTimeSunday = $stopTimeSunday;
    }

    /**
     * Returns the excludeHolidays
     *
     * @return boolean
     */
    public function getExcludeHolidays()
    {
        return $this->excludeHolidays;
    }

    /**
     * Sets the excludeHolidays
     *
     * @param boolean $excludeHolidays
     */
    public function setExcludeHolidays($excludeHolidays): void
    {
        $this->excludeHolidays = $excludeHolidays;
    }

    /**
     * Returns the boolean state of excludeHolidays
     *
     * @return boolean
     */
    public function isExcludeHolidays()
    {
        return $this->getExcludeHolidays();
    }

    /**
     * Returns the maxAmountMonday
     *
     * @return integer
     */
    public function getMaxAmountMonday()
    {
        return $this->maxAmountMonday;
    }

    /**
     * Sets the maxAmountMonday
     *
     * @param integer $maxAmountMonday
     */
    public function setMaxAmountMonday($maxAmountMonday): void
    {
        $this->maxAmountMonday = $maxAmountMonday;
    }

    /**
     * Returns the maxAmountTuesday
     *
     * @return integer
     */
    public function getMaxAmountTuesday()
    {
        return $this->maxAmountTuesday;
    }

    /**
     * Sets the maxAmountTuesday
     *
     * @param integer $maxAmountTuesday
     */
    public function setMaxAmountTuesday($maxAmountTuesday): void
    {
        $this->maxAmountTuesday = $maxAmountTuesday;
    }

    /**
     * Returns the maxAmountWednesday
     *
     * @return integer
     */
    public function getMaxAmountWednesday()
    {
        return $this->maxAmountWednesday;
    }

    /**
     * Sets the maxAmountWednesday
     *
     * @param integer $maxAmountWednesday
     */
    public function setMaxAmountWednesday($maxAmountWednesday): void
    {
        $this->maxAmountWednesday = $maxAmountWednesday;
    }

    /**
     * Returns the maxAmountThursday
     *
     * @return integer
     */
    public function getMaxAmountThursday()
    {
        return $this->maxAmountThursday;
    }

    /**
     * Sets the maxAmountThursday
     *
     * @param integer $maxAmountThursday
     */
    public function setMaxAmountThursday($maxAmountThursday): void
    {
        $this->maxAmountThursday = $maxAmountThursday;
    }

    /**
     * Returns the maxAmountFriday
     *
     * @return integer
     */
    public function getMaxAmountFriday()
    {
        return $this->maxAmountFriday;
    }

    /**
     * Sets the maxAmountFriday
     *
     * @param integer $maxAmountFriday
     */
    public function setMaxAmountFriday($maxAmountFriday): void
    {
        $this->maxAmountFriday = $maxAmountFriday;
    }

    /**
     * Returns the maxAmountSaturday
     *
     * @return integer
     */
    public function getMaxAmountSaturday()
    {
        return $this->maxAmountSaturday;
    }

    /**
     * Sets the maxAmountSaturday
     *
     * @param integer $maxAmountSaturday
     */
    public function setMaxAmountSaturday($maxAmountSaturday): void
    {
        $this->maxAmountSaturday = $maxAmountSaturday;
    }

    /**
     * Returns the maxAmountSunday
     *
     * @return integer
     */
    public function getMaxAmountSunday()
    {
        return $this->maxAmountSunday;
    }

    /**
     * Sets the maxAmountSunday
     *
     * @param integer $maxAmountSunday
     */
    public function setMaxAmountSunday($maxAmountSunday): void
    {
        $this->maxAmountSunday = $maxAmountSunday;
    }

    /**
     * Returns the minuteIntervalMonday
     *
     * @return integer
     */
    public function getMinuteIntervalMonday()
    {
        return $this->minuteIntervalMonday;
    }

    /**
     * Sets the minuteIntervalMonday
     *
     * @param integer $minuteIntervalMonday
     */
    public function setMinuteIntervalMonday($minuteIntervalMonday): void
    {
        $this->minuteIntervalMonday = $minuteIntervalMonday;
    }

    /**
     * Returns the minuteIntervalTuesday
     *
     * @return integer
     */
    public function getMinuteIntervalTuesday()
    {
        return $this->minuteIntervalTuesday;
    }

    /**
     * Sets the minuteIntervalTuesday
     *
     * @param integer $minuteIntervalTuesday
     */
    public function setMinuteIntervalTuesday($minuteIntervalTuesday): void
    {
        $this->minuteIntervalTuesday = $minuteIntervalTuesday;
    }

    /**
     * Returns the minuteIntervalWednesday
     *
     * @return integer
     */
    public function getMinuteIntervalWednesday()
    {
        return $this->minuteIntervalWednesday;
    }

    /**
     * Sets the minuteIntervalWednesday
     *
     * @param integer $minuteIntervalWednesday
     */
    public function setMinuteIntervalWednesday($minuteIntervalWednesday): void
    {
        $this->minuteIntervalWednesday = $minuteIntervalWednesday;
    }

    /**
     * Returns the minuteIntervalThursday
     *
     * @return integer
     */
    public function getMinuteIntervalThursday()
    {
        return $this->minuteIntervalThursday;
    }

    /**
     * Sets the minuteIntervalThursday
     *
     * @param integer $minuteIntervalThursday
     */
    public function setMinuteIntervalThursday($minuteIntervalThursday): void
    {
        $this->minuteIntervalThursday = $minuteIntervalThursday;
    }

    /**
     * Returns the minuteIntervalFriday
     *
     * @return integer
     */
    public function getMinuteIntervalFriday()
    {
        return $this->minuteIntervalFriday;
    }

    /**
     * Sets the minuteIntervalFriday
     *
     * @param integer $minuteIntervalFriday
     */
    public function setMinuteIntervalFriday($minuteIntervalFriday): void
    {
        $this->minuteIntervalFriday = $minuteIntervalFriday;
    }

    /**
     * Returns the minuteIntervalSaturday
     *
     * @return integer
     */
    public function getMinuteIntervalSaturday()
    {
        return $this->minuteIntervalSaturday;
    }

    /**
     * Sets the minuteIntervalSaturday
     *
     * @param integer $minuteIntervalSaturday
     */
    public function setMinuteIntervalSaturday($minuteIntervalSaturday): void
    {
        $this->minuteIntervalSaturday = $minuteIntervalSaturday;
    }

    /**
     * Returns the minuteIntervalSunday
     *
     * @return integer
     */
    public function getMinuteIntervalSunday()
    {
        return $this->minuteIntervalSunday;
    }

    /**
     * Sets the minuteIntervalSunday
     *
     * @param integer $minuteIntervalSunday
     */
    public function setMinuteIntervalSunday($minuteIntervalSunday): void
    {
        $this->minuteIntervalSunday = $minuteIntervalSunday;
    }

    /**
     * Returns the maxAmountPerVarDays
     *
     * @return integer
     */
    public function getMaxAmountPerVarDays()
    {
        return $this->maxAmountPerVarDays;
    }

    /**
     * Sets the maxAmountPerVarDays
     *
     * @param integer $maxAmountPerVarDays
     */
    public function setMaxAmountPerVarDays($maxAmountPerVarDays): void
    {
        $this->maxAmountPerVarDays = $maxAmountPerVarDays;
    }

    /**
     * Returns perVarDays
     *
     * @return integer
     */
    public function getPerVarDays()
    {
        return $this->perVarDays;
    }

    /**
     * Sets perVarDays
     *
     * @param integer $perVarDays
     */
    public function setPerVarDays($perVarDays): void
    {
        $this->perVarDays = $perVarDays;
    }

    /**
     * Returns perVarDaysInterval
     *
     * @return integer
     */
    public function getPerVarDaysInterval()
    {
        return $this->perVarDaysInterval;
    }

    /**
     * Sets perVarDaysInterval
     *
     * @param integer $perVarDaysInterval
     */
    public function setPerVarDaysInterval($perVarDaysInterval): void
    {
        $this->perVarDaysInterval = $perVarDaysInterval;
    }

    /**
     * Returns the betweenMinutes
     *
     * @return integer
     */
    public function getBetweenMinutes()
    {
        return $this->betweenMinutes;
    }

    /**
     * Sets the betweenMinutes
     *
     * @param integer $betweenMinutes
     */
    public function setBetweenMinutes($betweenMinutes): void
    {
        $this->betweenMinutes = $betweenMinutes;
    }

    /**
     * Returns the hoursMutable
     *
     * @return integer
     */
    public function getHoursMutable()
    {
        return $this->hoursMutable;
    }

    /**
     * Sets the hoursMutable
     *
     * @param integer $hoursMutable
     */
    public function setHoursMutable($hoursMutable): void
    {
        $this->hoursMutable = $hoursMutable;
    }

    /**
     * Returns the blockedHours
     *
     * @return integer
     */
    public function getBlockedHours()
    {
        return $this->blockedHours;
    }

    /**
     * Sets the blockedHours
     *
     * @param integer $blockedHours
     */
    public function setBlockedHours($blockedHours): void
    {
        $this->blockedHours = $blockedHours;
    }

    /**
     * Returns the blockedHoursWorkdays
     *
     * @return integer
     */
    public function getBlockedHoursWorkdays()
    {
        return $this->blockedHoursWorkdays;
    }

    /**
     * Sets the blockedHoursWorkdays
     *
     * @param integer $blockedHoursWorkdays
     */
    public function setBlockedHoursWorkdays($blockedHoursWorkdays): void
    {
        $this->blockedHoursWorkdays = $blockedHoursWorkdays;
    }

    /**
     * Returns the maxDaysForward
     *
     * @return integer
     */
    public function getMaxDaysForward()
    {
        return $this->maxDaysForward;
    }

    /**
     * Sets the maxDaysForward
     *
     * @param integer $maxDaysForward
     */
    public function setMaxDaysForward($maxDaysForward): void
    {
        $this->maxDaysForward = $maxDaysForward;
    }

    /**
     * Returns addressDisable
     *
     * @return boolean
     */
    public function getAddressDisable()
    {
        return $this->addressDisable;
    }

    /**
     * Sets addressDisable
     *
     * @param boolean $addressDisable
     */
    public function setAddressDisable($addressDisable): void
    {
        $this->addressDisable = $addressDisable;
    }

    /**
     * Returns addressEnableName
     *
     * @return boolean
     */
    public function getAddressEnableName()
    {
        return $this->addressEnableName;
    }

    /**
     * Sets addressEnableName
     *
     * @param boolean $addressEnableName
     */
    public function setAddressEnableName($addressEnableName): void
    {
        $this->addressEnableName = $addressEnableName;
    }

    /**
     * Returns addressEnableGender
     *
     * @return boolean
     */
    public function getAddressEnableGender()
    {
        return $this->addressEnableGender;
    }

    /**
     * Sets addressEnableGender
     *
     * @param boolean $addressEnableGender
     */
    public function setAddressEnableGender($addressEnableGender): void
    {
        $this->addressEnableGender = $addressEnableGender;
    }

    /**
     * Returns addressEnableBirthday
     *
     * @return boolean
     */
    public function getAddressEnableBirthday()
    {
        return $this->addressEnableBirthday;
    }

    /**
     * Sets addressEnableBirthday
     *
     * @param boolean $addressEnableBirthday
     */
    public function setAddressEnableBirthday($addressEnableBirthday): void
    {
        $this->addressEnableBirthday = $addressEnableBirthday;
    }

    /**
     * Returns addressEnableAddress
     *
     * @return boolean
     */
    public function getAddressEnableAddress()
    {
        return $this->addressEnableAddress;
    }

    /**
     * Sets addressEnableAddress
     *
     * @param boolean $addressEnableAddress
     */
    public function setAddressEnableAddress($addressEnableAddress): void
    {
        $this->addressEnableAddress = $addressEnableAddress;
    }

    /**
     * Returns addressEnableSecurity
     *
     * @return boolean
     */
    public function getAddressEnableSecurity()
    {
        return $this->addressEnableSecurity;
    }

    /**
     * Sets addressEnableSecurity
     *
     * @param boolean $addressEnableSecurity
     */
    public function setAddressEnableSecurity($addressEnableSecurity): void
    {
        $this->addressEnableSecurity = $addressEnableSecurity;
    }

    /**
     * Returns addressEnableEmail
     *
     * @return boolean
     */
    public function getAddressEnableEmail()
    {
        return $this->addressEnableEmail;
    }

    /**
     * Sets addressEnableEmail
     *
     * @param boolean $addressEnableEmail
     */
    public function setAddressEnableEmail($addressEnableEmail): void
    {
        $this->addressEnableEmail = $addressEnableEmail;
    }

    /**
     * Adds a FormField
     */
    public function addFormField(FormField $formField): void
    {
        $this->formField->attach($formField);
    }

    /**
     * Removes a FormField
     *
     * @param \Innologi\Appointments\Domain\Model\FormField $formFieldToRemove The FormField to be removed
     */
    public function removeFormField(FormField $formFieldToRemove): void
    {
        $this->formField->detach($formFieldToRemove);
    }

    /**
     * Returns the formFields
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getFormFields()
    {
        return $this->formFields;
    }

    /**
     * Sets the formFields
     */
    public function setFormField(ObjectStorage $formFields): void
    {
        $this->formFields = $formFields;
    }
}

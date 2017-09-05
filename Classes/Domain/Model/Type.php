<?php
namespace Innologi\Appointments\Domain\Model;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
 *
 */
class Type extends AbstractEntity {

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
	protected $superuserOnly = FALSE;

	/**
	 * Exclusive availability?
	 *
	 * @var boolean
	 */
	protected $exclusiveAvailability = FALSE;
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
	protected $dontBlockTypes = FALSE;

	/**
	 * @var boolean
	 */
	protected $dontRestrictTypeCounts = FALSE;

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
	protected $excludeHolidays = FALSE;

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
	 * @lazy
	 * @cascade remove
	 */
	protected $formFields;

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
		$this->formFields = new ObjectStorage();
	}

	/**
	 * Returns the name
	 *
	 * @return string $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the name
	 *
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Returns superuserOnly
	 *
	 * @return boolean $superuserOnly
	 */
	public function getSuperuserOnly() {
		return $this->superuserOnly;
	}

	/**
	 * Sets superuserOnly
	 *
	 * @param boolean $superuserOnly
	 * @return void
	 */
	public function setSuperuserOnly($superuserOnly) {
		$this->superuserOnly = $superuserOnly;
	}

	/**
	 * Returns exclusideAvailability
	 *
	 * @return boolean $exclusiveAvailability
	 */
	public function getExclusiveAvailability() {
		return $this->exclusiveAvailability;
	}

	/**
	 * Sets exclusiveAvailability
	 *
	 * @param boolean $exclusiveAvailability
	 * @return void
	 */
	public function setExclusiveAvailability($exclusiveAvailability) {
		$this->exclusiveAvailability = $exclusiveAvailability;
	}

	/**
	 * Returns dontblocktypes
	 *
	 * @return boolean
	 */
	public function getDontBlockTypes() {
		return $this->dontBlockTypes;
	}

	/**
	 * Sets dontblocktypes
	 *
	 * @param boolean $dontBlockTypes
	 * @return void
	 */
	public function setDontBlockTypes($dontBlockTypes) {
		$this->dontBlockTypes = $dontBlockTypes;
	}

	/**
	 * Returns dontrestrictypecounts
	 *
	 * @return boolean
	 */
	public function getDontRestrictTypeCounts() {
		return $this->dontRestrictTypeCounts;
	}

	/**
	 * Sets dontrestricttypecounts
	 *
	 * @param boolean $dontRestrictTypeCounts
	 * @return void
	 */
	public function setDontRestrictTypeCounts($dontRestrictTypeCounts) {
		$this->dontRestrictTypeCounts = $dontRestrictTypeCounts;
	}

	/**
	 * Returns the defaultDuration in minutes
	 *
	 * @return integer $defaultDuration
	 */
	public function getDefaultDuration() {
		return $this->defaultDuration;
	}

	/**
	 * Sets the defaultDuration in minutes
	 *
	 * @param integer $defaultDuration
	 * @return void
	 */
	public function setDefaultDuration($defaultDuration) {
		$this->defaultDuration = $defaultDuration;
	}

	/**
	 * Returns the startTimeMonday
	 *
	 * @return string $startTimeMonday
	 */
	public function getStartTimeMonday() {
		return $this->startTimeMonday;
	}

	/**
	 * Sets the startTimeMonday
	 *
	 * @param string $startTimeMonday
	 * @return void
	 */
	public function setStartTimeMonday($startTimeMonday) {
		$this->startTimeMonday = $startTimeMonday;
	}

	/**
	 * Returns the startTimeTuesday
	 *
	 * @return string $startTimeTuesday
	 */
	public function getStartTimeTuesday() {
		return $this->startTimeTuesday;
	}

	/**
	 * Sets the startTimeTuesday
	 *
	 * @param string $startTimeTuesday
	 * @return void
	 */
	public function setStartTimeTuesday($startTimeTuesday) {
		$this->startTimeTuesday = $startTimeTuesday;
	}

	/**
	 * Returns the startTimeWednesday
	 *
	 * @return string $startTimeWednesday
	 */
	public function getStartTimeWednesday() {
		return $this->startTimeWednesday;
	}

	/**
	 * Sets the startTimeWednesday
	 *
	 * @param string $startTimeWednesday
	 * @return void
	 */
	public function setStartTimeWednesday($startTimeWednesday) {
		$this->startTimeWednesday = $startTimeWednesday;
	}

	/**
	 * Returns the startTimeThursday
	 *
	 * @return string $startTimeThursday
	 */
	public function getStartTimeThursday() {
		return $this->startTimeThursday;
	}

	/**
	 * Sets the startTimeThursday
	 *
	 * @param string $startTimeThursday
	 * @return void
	 */
	public function setStartTimeThursday($startTimeThursday) {
		$this->startTimeThursday = $startTimeThursday;
	}

	/**
	 * Returns the startTimeFriday
	 *
	 * @return string $startTimeFriday
	 */
	public function getStartTimeFriday() {
		return $this->startTimeFriday;
	}

	/**
	 * Sets the startTimeFriday
	 *
	 * @param string $startTimeFriday
	 * @return void
	 */
	public function setStartTimeFriday($startTimeFriday) {
		$this->startTimeFriday = $startTimeFriday;
	}

	/**
	 * Returns the startTimeSaturday
	 *
	 * @return string $startTimeSaturday
	 */
	public function getStartTimeSaturday() {
		return $this->startTimeSaturday;
	}

	/**
	 * Sets the startTimeSaturday
	 *
	 * @param string $startTimeSaturday
	 * @return void
	 */
	public function setStartTimeSaturday($startTimeSaturday) {
		$this->startTimeSaturday = $startTimeSaturday;
	}

	/**
	 * Returns the startTimeSunday
	 *
	 * @return string $startTimeSunday
	 */
	public function getStartTimeSunday() {
		return $this->startTimeSunday;
	}

	/**
	 * Sets the startTimeSunday
	 *
	 * @param string $startTimeSunday
	 * @return void
	 */
	public function setStartTimeSunday($startTimeSunday) {
		$this->startTimeSunday = $startTimeSunday;
	}

	/**
	 * Returns the stopTimeMonday
	 *
	 * @return string $stopTimeMonday
	 */
	public function getStopTimeMonday() {
		return $this->stopTimeMonday;
	}

	/**
	 * Sets the stopTimeMonday
	 *
	 * @param string $stopTimeMonday
	 * @return void
	 */
	public function setStopTimeMonday($stopTimeMonday) {
		$this->stopTimeMonday = $stopTimeMonday;
	}

	/**
	 * Returns the stopTimeTuesday
	 *
	 * @return string $stopTimeTuesday
	 */
	public function getStopTimeTuesday() {
		return $this->stopTimeTuesday;
	}

	/**
	 * Sets the stopTimeTuesday
	 *
	 * @param string $stopTimeTuesday
	 * @return void
	 */
	public function setStopTimeTuesday($stopTimeTuesday) {
		$this->stopTimeTuesday = $stopTimeTuesday;
	}

	/**
	 * Returns the stopTimeWednesday
	 *
	 * @return string $stopTimeWednesday
	 */
	public function getStopTimeWednesday() {
		return $this->stopTimeWednesday;
	}

	/**
	 * Sets the stopTimeWednesday
	 *
	 * @param string $stopTimeWednesday
	 * @return void
	 */
	public function setStopTimeWednesday($stopTimeWednesday) {
		$this->stopTimeWednesday = $stopTimeWednesday;
	}

	/**
	 * Returns the stopTimeThursday
	 *
	 * @return string $stopTimeThursday
	 */
	public function getStopTimeThursday() {
		return $this->stopTimeThursday;
	}

	/**
	 * Sets the stopTimeThursday
	 *
	 * @param string $stopTimeThursday
	 * @return void
	 */
	public function setStopTimeThursday($stopTimeThursday) {
		$this->stopTimeThursday = $stopTimeThursday;
	}

	/**
	 * Returns the stopTimeFriday
	 *
	 * @return string $stopTimeFriday
	 */
	public function getStopTimeFriday() {
		return $this->stopTimeFriday;
	}

	/**
	 * Sets the stopTimeFriday
	 *
	 * @param string $stopTimeFriday
	 * @return void
	 */
	public function setStopTimeFriday($stopTimeFriday) {
		$this->stopTimeFriday = $stopTimeFriday;
	}

	/**
	 * Returns the stopTimeSaturday
	 *
	 * @return string $stopTimeSaturday
	 */
	public function getStopTimeSaturday() {
		return $this->stopTimeSaturday;
	}

	/**
	 * Sets the stopTimeSaturday
	 *
	 * @param string $stopTimeSaturday
	 * @return void
	 */
	public function setStopTimeSaturday($stopTimeSaturday) {
		$this->stopTimeSaturday = $stopTimeSaturday;
	}

	/**
	 * Returns the stopTimeSunday
	 *
	 * @return string $stopTimeSunday
	 */
	public function getStopTimeSunday() {
		return $this->stopTimeSunday;
	}

	/**
	 * Sets the stopTimeSunday
	 *
	 * @param string $stopTimeSunday
	 * @return void
	 */
	public function setStopTimeSunday($stopTimeSunday) {
		$this->stopTimeSunday = $stopTimeSunday;
	}

	/**
	 * Returns the excludeHolidays
	 *
	 * @return boolean $excludeHolidays
	 */
	public function getExcludeHolidays() {
		return $this->excludeHolidays;
	}

	/**
	 * Sets the excludeHolidays
	 *
	 * @param boolean $excludeHolidays
	 * @return void
	 */
	public function setExcludeHolidays($excludeHolidays) {
		$this->excludeHolidays = $excludeHolidays;
	}

	/**
	 * Returns the boolean state of excludeHolidays
	 *
	 * @return boolean
	 */
	public function isExcludeHolidays() {
		return $this->getExcludeHolidays();
	}

	/**
	 * Returns the maxAmountMonday
	 *
	 * @return integer $maxAmountMonday
	 */
	public function getMaxAmountMonday() {
		return $this->maxAmountMonday;
	}

	/**
	 * Sets the maxAmountMonday
	 *
	 * @param integer $maxAmountMonday
	 * @return void
	 */
	public function setMaxAmountMonday($maxAmountMonday) {
		$this->maxAmountMonday = $maxAmountMonday;
	}

	/**
	 * Returns the maxAmountTuesday
	 *
	 * @return integer $maxAmountTuesday
	 */
	public function getMaxAmountTuesday() {
		return $this->maxAmountTuesday;
	}

	/**
	 * Sets the maxAmountTuesday
	 *
	 * @param integer $maxAmountTuesday
	 * @return void
	 */
	public function setMaxAmountTuesday($maxAmountTuesday) {
		$this->maxAmountTuesday = $maxAmountTuesday;
	}

	/**
	 * Returns the maxAmountWednesday
	 *
	 * @return integer $maxAmountWednesday
	 */
	public function getMaxAmountWednesday() {
		return $this->maxAmountWednesday;
	}

	/**
	 * Sets the maxAmountWednesday
	 *
	 * @param integer $maxAmountWednesday
	 * @return void
	 */
	public function setMaxAmountWednesday($maxAmountWednesday) {
		$this->maxAmountWednesday = $maxAmountWednesday;
	}

	/**
	 * Returns the maxAmountThursday
	 *
	 * @return integer $maxAmountThursday
	 */
	public function getMaxAmountThursday() {
		return $this->maxAmountThursday;
	}

	/**
	 * Sets the maxAmountThursday
	 *
	 * @param integer $maxAmountThursday
	 * @return void
	 */
	public function setMaxAmountThursday($maxAmountThursday) {
		$this->maxAmountThursday = $maxAmountThursday;
	}

	/**
	 * Returns the maxAmountFriday
	 *
	 * @return integer $maxAmountFriday
	 */
	public function getMaxAmountFriday() {
		return $this->maxAmountFriday;
	}

	/**
	 * Sets the maxAmountFriday
	 *
	 * @param integer $maxAmountFriday
	 * @return void
	 */
	public function setMaxAmountFriday($maxAmountFriday) {
		$this->maxAmountFriday = $maxAmountFriday;
	}

	/**
	 * Returns the maxAmountSaturday
	 *
	 * @return integer $maxAmountSaturday
	 */
	public function getMaxAmountSaturday() {
		return $this->maxAmountSaturday;
	}

	/**
	 * Sets the maxAmountSaturday
	 *
	 * @param integer $maxAmountSaturday
	 * @return void
	 */
	public function setMaxAmountSaturday($maxAmountSaturday) {
		$this->maxAmountSaturday = $maxAmountSaturday;
	}

	/**
	 * Returns the maxAmountSunday
	 *
	 * @return integer $maxAmountSunday
	 */
	public function getMaxAmountSunday() {
		return $this->maxAmountSunday;
	}

	/**
	 * Sets the maxAmountSunday
	 *
	 * @param integer $maxAmountSunday
	 * @return void
	 */
	public function setMaxAmountSunday($maxAmountSunday) {
		$this->maxAmountSunday = $maxAmountSunday;
	}

	/**
	 * Returns the minuteIntervalMonday
	 *
	 * @return integer $minuteIntervalMonday
	 */
	public function getMinuteIntervalMonday() {
		return $this->minuteIntervalMonday;
	}

	/**
	 * Sets the minuteIntervalMonday
	 *
	 * @param integer $minuteIntervalMonday
	 * @return void
	 */
	public function setMinuteIntervalMonday($minuteIntervalMonday) {
		$this->minuteIntervalMonday = $minuteIntervalMonday;
	}

	/**
	 * Returns the minuteIntervalTuesday
	 *
	 * @return integer $minuteIntervalTuesday
	 */
	public function getMinuteIntervalTuesday() {
		return $this->minuteIntervalTuesday;
	}

	/**
	 * Sets the minuteIntervalTuesday
	 *
	 * @param integer $minuteIntervalTuesday
	 * @return void
	 */
	public function setMinuteIntervalTuesday($minuteIntervalTuesday) {
		$this->minuteIntervalTuesday = $minuteIntervalTuesday;
	}

	/**
	 * Returns the minuteIntervalWednesday
	 *
	 * @return integer $minuteIntervalWednesday
	 */
	public function getMinuteIntervalWednesday() {
		return $this->minuteIntervalWednesday;
	}

	/**
	 * Sets the minuteIntervalWednesday
	 *
	 * @param integer $minuteIntervalWednesday
	 * @return void
	 */
	public function setMinuteIntervalWednesday($minuteIntervalWednesday) {
		$this->minuteIntervalWednesday = $minuteIntervalWednesday;
	}

	/**
	 * Returns the minuteIntervalThursday
	 *
	 * @return integer $minuteIntervalThursday
	 */
	public function getMinuteIntervalThursday() {
		return $this->minuteIntervalThursday;
	}

	/**
	 * Sets the minuteIntervalThursday
	 *
	 * @param integer $minuteIntervalThursday
	 * @return void
	 */
	public function setMinuteIntervalThursday($minuteIntervalThursday) {
		$this->minuteIntervalThursday = $minuteIntervalThursday;
	}

	/**
	 * Returns the minuteIntervalFriday
	 *
	 * @return integer $minuteIntervalFriday
	 */
	public function getMinuteIntervalFriday() {
		return $this->minuteIntervalFriday;
	}

	/**
	 * Sets the minuteIntervalFriday
	 *
	 * @param integer $minuteIntervalFriday
	 * @return void
	 */
	public function setMinuteIntervalFriday($minuteIntervalFriday) {
		$this->minuteIntervalFriday = $minuteIntervalFriday;
	}

	/**
	 * Returns the minuteIntervalSaturday
	 *
	 * @return integer $minuteIntervalSaturday
	 */
	public function getMinuteIntervalSaturday() {
		return $this->minuteIntervalSaturday;
	}

	/**
	 * Sets the minuteIntervalSaturday
	 *
	 * @param integer $minuteIntervalSaturday
	 * @return void
	 */
	public function setMinuteIntervalSaturday($minuteIntervalSaturday) {
		$this->minuteIntervalSaturday = $minuteIntervalSaturday;
	}

	/**
	 * Returns the minuteIntervalSunday
	 *
	 * @return integer $minuteIntervalSunday
	 */
	public function getMinuteIntervalSunday() {
		return $this->minuteIntervalSunday;
	}

	/**
	 * Sets the minuteIntervalSunday
	 *
	 * @param integer $minuteIntervalSunday
	 * @return void
	 */
	public function setMinuteIntervalSunday($minuteIntervalSunday) {
		$this->minuteIntervalSunday = $minuteIntervalSunday;
	}

	/**
	 * Returns the maxAmountPerVarDays
	 *
	 * @return integer $maxAmountPerVarDays
	 */
	public function getMaxAmountPerVarDays() {
		return $this->maxAmountPerVarDays;
	}

	/**
	 * Sets the maxAmountPerVarDays
	 *
	 * @param integer $maxAmountPerVarDays
	 * @return void
	 */
	public function setMaxAmountPerVarDays($maxAmountPerVarDays) {
		$this->maxAmountPerVarDays = $maxAmountPerVarDays;
	}

	/**
	 * Returns perVarDays
	 *
	 * @return integer $perVarDays
	 */
	public function getPerVarDays() {
		return $this->perVarDays;
	}

	/**
	 * Sets perVarDays
	 *
	 * @param integer $perVarDays
	 * @return void
	 */
	public function setPerVarDays($perVarDays) {
		$this->perVarDays = $perVarDays;
	}

	/**
	 * Returns perVarDaysInterval
	 *
	 * @return integer $perVarDaysInterval
	 */
	public function getPerVarDaysInterval() {
		return $this->perVarDaysInterval;
	}

	/**
	 * Sets perVarDaysInterval
	 *
	 * @param integer $perVarDaysInterval
	 * @return void
	 */
	public function setPerVarDaysInterval($perVarDaysInterval) {
		$this->perVarDaysInterval = $perVarDaysInterval;
	}

	/**
	 * Returns the betweenMinutes
	 *
	 * @return integer $betweenMinutes
	 */
	public function getBetweenMinutes() {
		return $this->betweenMinutes;
	}

	/**
	 * Sets the betweenMinutes
	 *
	 * @param integer $betweenMinutes
	 * @return void
	 */
	public function setBetweenMinutes($betweenMinutes) {
		$this->betweenMinutes = $betweenMinutes;
	}

	/**
	 * Returns the hoursMutable
	 *
	 * @return integer $hoursMutable
	 */
	public function getHoursMutable() {
		return $this->hoursMutable;
	}

	/**
	 * Sets the hoursMutable
	 *
	 * @param integer $hoursMutable
	 * @return void
	 */
	public function setHoursMutable($hoursMutable) {
		$this->hoursMutable = $hoursMutable;
	}

	/**
	 * Returns the blockedHours
	 *
	 * @return integer $blockedHours
	 */
	public function getBlockedHours() {
		return $this->blockedHours;
	}

	/**
	 * Sets the blockedHours
	 *
	 * @param integer $blockedHours
	 * @return void
	 */
	public function setBlockedHours($blockedHours) {
		$this->blockedHours = $blockedHours;
	}

	/**
	 * Returns the blockedHoursWorkdays
	 *
	 * @return integer $blockedHoursWorkdays
	 */
	public function getBlockedHoursWorkdays() {
		return $this->blockedHoursWorkdays;
	}

	/**
	 * Sets the blockedHoursWorkdays
	 *
	 * @param integer $blockedHoursWorkdays
	 * @return void
	 */
	public function setBlockedHoursWorkdays($blockedHoursWorkdays) {
		$this->blockedHoursWorkdays = $blockedHoursWorkdays;
	}

	/**
	 * Returns the maxDaysForward
	 *
	 * @return integer $maxDaysForward
	 */
	public function getMaxDaysForward() {
		return $this->maxDaysForward;
	}

	/**
	 * Sets the maxDaysForward
	 *
	 * @param integer $maxDaysForward
	 * @return void
	 */
	public function setMaxDaysForward($maxDaysForward) {
		$this->maxDaysForward = $maxDaysForward;
	}

	/**
	 * Returns addressDisable
	 *
	 * @return boolean $addressDisable
	 */
	public function getAddressDisable() {
		return $this->addressDisable;
	}

	/**
	 * Sets addressDisable
	 *
	 * @param boolean $addressDisable
	 * @return void
	 */
	public function setAddressDisable($addressDisable) {
		$this->addressDisable = $addressDisable;
	}

	/**
	 * Returns addressEnableName
	 *
	 * @return boolean $addressEnableName
	 */
	public function getAddressEnableName() {
		return $this->addressEnableName;
	}

	/**
	 * Sets addressEnableName
	 *
	 * @param boolean $addressEnableName
	 * @return void
	 */
	public function setAddressEnableName($addressEnableName) {
		$this->addressEnableName = $addressEnableName;
	}

	/**
	 * Returns addressEnableGender
	 *
	 * @return boolean $addressEnableGender
	 */
	public function getAddressEnableGender() {
		return $this->addressEnableGender;
	}

	/**
	 * Sets addressEnableGender
	 *
	 * @param boolean $addressEnableGender
	 * @return void
	 */
	public function setAddressEnableGender($addressEnableGender) {
		$this->addressEnableGender = $addressEnableGender;
	}

	/**
	 * Returns addressEnableBirthday
	 *
	 * @return boolean $addressEnableBirthday
	 */
	public function getAddressEnableBirthday() {
		return $this->addressEnableBirthday;
	}

	/**
	 * Sets addressEnableBirthday
	 *
	 * @param boolean $addressEnableBirthday
	 * @return void
	 */
	public function setAddressEnableBirthday($addressEnableBirthday) {
		$this->addressEnableBirthday = $addressEnableBirthday;
	}

	/**
	 * Returns addressEnableAddress
	 *
	 * @return boolean $addressEnableAddress
	 */
	public function getAddressEnableAddress() {
		return $this->addressEnableAddress;
	}

	/**
	 * Sets addressEnableAddress
	 *
	 * @param boolean $addressEnableAddress
	 * @return void
	 */
	public function setAddressEnableAddress($addressEnableAddress) {
		$this->addressEnableAddress = $addressEnableAddress;
	}

	/**
	 * Returns addressEnableSecurity
	 *
	 * @return boolean $addressEnableSecurity
	 */
	public function getAddressEnableSecurity() {
		return $this->addressEnableSecurity;
	}

	/**
	 * Sets addressEnableSecurity
	 *
	 * @param boolean $addressEnableSecurity
	 * @return void
	 */
	public function setAddressEnableSecurity($addressEnableSecurity) {
		$this->addressEnableSecurity = $addressEnableSecurity;
	}

	/**
	 * Returns addressEnableEmail
	 *
	 * @return boolean $addressEnableEmail
	 */
	public function getAddressEnableEmail() {
		return $this->addressEnableEmail;
	}

	/**
	 * Sets addressEnableEmail
	 *
	 * @param boolean $addressEnableEmail
	 * @return void
	 */
	public function setAddressEnableEmail($addressEnableEmail) {
		$this->addressEnableEmail = $addressEnableEmail;
	}

	/**
	 * Adds a FormField
	 *
	 * @param \Innologi\Appointments\Domain\Model\FormField $formField
	 * @return void
	 */
	public function addFormField(FormField $formField) {
		$this->formField->attach($formField);
	}

	/**
	 * Removes a FormField
	 *
	 * @param \Innologi\Appointments\Domain\Model\FormField $formFieldToRemove The FormField to be removed
	 * @return void
	 */
	public function removeFormField(FormField $formFieldToRemove) {
		$this->formField->detach($formFieldToRemove);
	}

	/**
	 * Returns the formFields
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getFormFields() {
		return $this->formFields;
	}

	/**
	 * Sets the formFields
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $formFields
	 * @return void
	 */
	public function setFormField(ObjectStorage $formFields) {
		$this->formFields = $formFields;
	}

}
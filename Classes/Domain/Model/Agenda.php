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
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * Agenda domain model
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Agenda extends AbstractEntity {

	/**
	 * Name of agenda
	 *
	 * @var string
	 */
	protected $name; //validate NotEmpty

	/**
	 * Holiday dates, one on each line
	 *
	 * @var string
	 */
	protected $holidays = '';

	/**
	 * Array of the holiday dates, with the holidays as key
	 *
	 * @var array
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
	 */
	protected $holidayArray;

	/**
	 * Types
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Appointments\Domain\Model\Type>
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
	 */
	protected $types;
	#@FIX _perhaps we do need different storages for the types, or subTypes .. storing their status in the type themselves and relying on TYPO3's clipboard to make copy's and create the same appointment types with a different name and status is way redundant and not flexible
	#@TODO _type needs a setting that says whether other appointments of which ones count towards their max
	/**
	 * Email types
	 *
	 * @var integer
	 */
	protected $emailTypes;

	/**
	 * Email owner types
	 *
	 * @var integer
	 */
	protected $emailOwnerTypes;

	/**
	 * Email field types
	 *
	 * @var integer
	 */
	protected $emailFieldTypes;

	/**
	 * Calendar Invite types
	 *
	 * @var integer
	 */
	protected $calendarInviteTypes;

	/**
	 * Emails a confirmation on every scheduled appointment to an address
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Appointments\Domain\Model\Address>
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
	 */
	protected $emailAddress;

	/**
	 * Email text
	 *
	 * @var string
	 */
	protected $emailText = '';

	/**
	 * Emails a calendar invitation on every scheduled appointment to an address
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Appointments\Domain\Model\Address>
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
	 */
	protected $calendarInviteAddress;

	/**
	 * Calendar invite text
	 *
	 * @var string
	 */
	protected $calendarInviteText = '';

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
		$this->types = new ObjectStorage();
		$this->emailAddress = new ObjectStorage();
		$this->calendarInviteAddress = new ObjectStorage();
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
	 * Returns the holidays
	 *
	 * @return array $holidays
	 */
	public function getHolidays() {
		return $this->holidays;
	}

	/**
	 * Sets the holidays
	 *
	 * @param string $holidays
	 * @return void
	 */
	public function setHolidays($holidays) {
		$this->holidays = $holidays;
	}

	/**
	 * Returns the holidayArray
	 *
	 * @return array $holidayArray
	 */
	public function getHolidayArray() {
		if ($this->holidayArray === NULL) {
			$this->setHolidayArray();
		}
		return $this->holidayArray;
	}

	/**
	 * Sets the holidayArray
	 *
	 * @return void
	 */
	public function setHolidayArray() {
		$holidays = str_replace("\r\n","\n",$this->holidays);
		$this->holidayArray = array_flip(GeneralUtility::trimExplode("\n", $holidays, 1));
	}

	/**
	 * Adds a Type
	 *
	 * @param \Innologi\Appointments\Domain\Model\Type $type
	 * @return void
	 */
	public function addTypes(Type $type) {
		$this->types->attach($type);
	}

	/**
	 * Removes a Type
	 *
	 * @param \Innologi\Appointments\Domain\Model\Type $typeToRemove The Type to be removed
	 * @return void
	 */
	public function removeTypes(Type $typeToRemove) {
		$this->types->detach($typeToRemove);
	}

	/**
	 * Returns types
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $types
	 */
	public function getTypes() {
		return $this->types;
	}

	/**
	 * Sets types
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $types
	 * @return void
	 */
	public function setTypes(ObjectStorage $types) {
		$this->types = $types;
	}

	/**
	 * Returns the email types
	 *
	 * @return integer $emailTypes
	 */
	public function getEmailTypes() {
		return $this->emailTypes;
	}

	/**
	 * Sets the email types
	 *
	 * @param integer $emailTypes
	 * @return void
	 */
	public function setEmailTypes($emailTypes) {
		$this->emailTypes = $emailTypes;
	}

	/**
	 * Returns the email owner types
	 *
	 * @return integer $emailOwnerTypes
	 */
	public function getEmailOwnerTypes() {
		return $this->emailOwnerTypes;
	}

	/**
	 * Sets the email owner types
	 *
	 * @param integer $emailOwnerTypes
	 * @return void
	 */
	public function setEmailOwnerTypes($emailOwnerTypes) {
		$this->emailOwnerTypes = $emailOwnerTypes;
	}

	/**
	 * Returns the email Field types
	 *
	 * @return integer $emailFieldTypes
	 */
	public function getEmailFieldTypes() {
		return $this->emailFieldTypes;
	}

	/**
	 * Sets the email Field types
	 *
	 * @param integer $emailFieldTypes
	 * @return void
	 */
	public function setEmailFieldTypes($emailFieldTypes) {
		$this->emailFieldTypes = $emailFieldTypes;
	}

	/**
	 * Returns the calendar invite types
	 *
	 * @return integer $calendarInviteTypes
	 */
	public function getCalendarInviteTypes() {
		return $this->calendarInviteTypes;
	}

	/**
	 * Sets the calendar invite types
	 *
	 * @param integer $calendarInviteTypes
	 * @return void
	 */
	public function setCalendarInviteTypes($calendarInviteTypes) {
		$this->calendarInviteTypes = $calendarInviteTypes;
	}

	/**
	 * Adds a Address
	 *
	 * @param \Innologi\Appointments\Domain\Model\Address $emailAddress
	 * @return void
	 */
	public function addEmailAddress(Address $emailAddress) {
		$this->emailAddress->attach($emailAddress);
	}

	/**
	 * Removes a Address
	 *
	 * @param \Innologi\Appointments\Domain\Model\Address $emailAddressToRemove The Address to be removed
	 * @return void
	 */
	public function removeEmailAddress(Address $emailAddressToRemove) {
		$this->emailAddress->detach($emailAddressToRemove);
	}

	/**
	 * Returns the emailAddress
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getEmailAddress() {
		return $this->emailAddress;
	}

	/**
	 * Sets the emailAddress
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $emailAddress
	 * @return void
	 */
	public function setEmailAddress(ObjectStorage $emailAddress) {
		$this->emailAddress = $emailAddress;
	}

	/**
	 * Returns the email text
	 *
	 * @return string $emailText
	 */
	public function getEmailText() {
		return $this->emailText;
	}

	/**
	 * Sets the emailText
	 *
	 * @param string $emailText
	 * @return void
	 */
	public function setEmailText($emailText) {
		$this->emailText = $emailText;
	}

	/**
	 * Adds a Address
	 *
	 * @param \Innologi\Appointments\Domain\Model\Address $calendarInviteAddress
	 * @return void
	 */
	public function addCalendarInviteAddress(Address $calendarInviteAddress) {
		$this->calendarInviteAddress->attach($calendarInviteAddress);
	}

	/**
	 * Removes a Address
	 *
	 * @param \Innologi\Appointments\Domain\Model\Address $calendarInviteAddressToRemove The Address to be removed
	 * @return void
	 */
	public function removeCalendarInviteAddress(Address $calendarInviteAddressToRemove) {
		$this->calendarInviteAddress->detach($calendarInviteAddressToRemove);
	}

	/**
	 * Returns the calendarInviteAddress
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getCalendarInviteAddress() {
		return $this->calendarInviteAddress;
	}

	/**
	 * Sets the calendarInviteAddress
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $calendarInviteAddress
	 * @return void
	 */
	public function setCalendarInviteAddress(ObjectStorage $calendarInviteAddress) {
		$this->calendarInviteAddress = $calendarInviteAddress;
	}

	/**
	 * Returns the calendarInviteText
	 *
	 * @return string $calendarInviteText
	 */
	public function getCalendarInviteText() {
		return $this->calendarInviteText;
	}

	/**
	 * Sets the calendarInviteText
	 *
	 * @param string $calendarInviteText
	 * @return void
	 */
	public function setCalendarInviteText($calendarInviteText) {
		$this->calendarInviteText = $calendarInviteText;
	}

}
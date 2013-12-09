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
 * Address domain model
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Domain_Model_Address extends Tx_Extbase_DomainObject_AbstractEntity {

	/**
	 * First Name
	 *
	 * @var string
	 */
	protected $firstName;

	/**
	 * Middle Name
	 *
	 * @var string
	 */
	protected $middleName;

	/**
	 * Last Name
	 *
	 * @var string
	 */
	protected $lastName;

	/**
	 * Name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Gender
	 *
	 * @var string
	 */
	protected $gender; #@LOW validate StringLength(minimum=1, maximum=1) but GEMHMK doesn't want to

	/**
	 * Birthday
	 *
	 * @var DateTime
	 */
	protected $birthday; #@LOW is property-value in formField template still necessary?

	/**
	 * Email
	 *
	 * Also used by emailService where applicable.
	 *
	 * @var string
	 */
	protected $email;

	/**
	 * Address
	 *
	 * @var string
	 */
	protected $address; #@LOW regex validator? but GEMHMK doesn't want to

	/**
	 * Zip code
	 *
	 * @var string
	 */
	protected $zip; #@LOW validate RegularExpression(regularExpression=/[0-9]{4}[A-Z]{2}/) but GEMHMK doesn't want to

	/**
	 * City
	 *
	 * @var string
	 */
	protected $city;

	/**
	 * Social Security Number
	 *
	 * @var string
	 */
	protected $socialSecurityNumber;

	/**
	 * State of creation
	 *
	 * @var integer
	 */
	protected $creationProgress = Tx_Appointments_Domain_Model_Appointment::UNFINISHED;

	/**
	 * Returns the firstName
	 *
	 * @return string $firstName
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/**
	 * Sets the firstName
	 *
	 * @param string $firstName
	 * @return void
	 */
	public function setFirstName($firstName) {
		$this->firstName = $firstName;
	}

	/**
	 * Returns the middleName
	 *
	 * @return string $middleName
	 */
	public function getMiddleName() {
		return $this->middleName;
	}

	/**
	 * Sets the middleName
	 *
	 * @param string $middleName
	 * @return void
	 */
	public function setMiddleName($middleName) {
		$this->middleName = $middleName;
	}

	/**
	 * Returns the lastName
	 *
	 * @return string $lastName
	 */
	public function getLastName() {
		return $this->lastName;
	}

	/**
	 * Sets the lastName
	 *
	 * @param string $lastName
	 * @return void
	 */
	public function setLastName($lastName) {
		$this->lastName = $lastName;
	}

	/**
	 * Returns the name
	 *
	 * @return string $name
	 */
	public function getName() {
		$this->setName();
		return $this->name;
	}

	/**
	 * Sets the name
	 *
	 * @param string $name
	 * @return void
	 */
	public function setName($name = NULL) {
		$name = $this->firstName.' '.$this->middleName.' '.$this->lastName;
		//clean up in case of any missing values
		$this->name = join(' ', t3lib_div::trimExplode(' ',$name,1));
	}

	/**
	 * Returns the gender
	 *
	 * @return string $gender
	 */
	public function getGender() {
		return $this->gender;
	}

	/**
	 * Sets the gender
	 *
	 * @param string $gender
	 * @return void
	 */
	public function setGender($gender) {
		$this->gender = $gender;
	}

	/**
	 * Returns the birthday
	 *
	 * @return DateTime $birthday
	 */
	public function getBirthday() {
		return $this->birthday;
	}

	/**
	 * Sets the birthday
	 *
	 * @param DateTime $birthday
	 * @return void
	 */
	public function setBirthday($birthday) {
		$this->birthday = $birthday;
	}

	/**
	 * Returns the email
	 *
	 * @return string $email
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Sets the email
	 *
	 * @param string $email
	 * @return void
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * Returns the address
	 *
	 * @return string $address
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * Sets the address
	 *
	 * @param string $address
	 * @return void
	 */
	public function setAddress($address) {
		$this->address = $address;
	}

	/**
	 * Returns the zip
	 *
	 * @return string $zip
	 */
	public function getZip() {
		return $this->zip;
	}

	/**
	 * Sets the zip
	 *
	 * @param string $zip
	 * @return void
	 */
	public function setZip($zip) {
		$this->zip = $zip;
	}

	/**
	 * Returns the city
	 *
	 * @return string $city
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * Sets the city
	 *
	 * @param string $city
	 * @return void
	 */
	public function setCity($city) {
		$this->city = $city;
	}

	/**
	 * Returns the social security number
	 *
	 * @return string $socialSecurityNumber
	 */
	public function getSocialSecurityNumber() {
		return $this->socialSecurityNumber;
	}

	/**
	 * Sets the social security number
	 *
	 * @param string $socialSecurityNumber
	 * @return void
	 */
	public function setSocialSecurityNumber($socialSecurityNumber) {
		$this->socialSecurityNumber = $socialSecurityNumber;
	}

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
	}

}
?>
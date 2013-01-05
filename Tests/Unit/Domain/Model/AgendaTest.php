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
 *  the Free Software Foundation; either version 2 of the License, or
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
 * Test case for class Tx_Appointments_Domain_Model_Agenda.
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @package TYPO3
 * @subpackage Appointment Scheduler
 *
 * @author Frenck Lutke <frenck@innologi.nl>
 */
class Tx_Appointments_Domain_Model_AgendaTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_Appointments_Domain_Model_Agenda
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new Tx_Appointments_Domain_Model_Agenda();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getNameReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setNameForStringSetsName() { 
		$this->fixture->setName('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getName()
		);
	}
	
	/**
	 * @test
	 */
	public function getHolidaysReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setHolidaysForStringSetsHolidays() { 
		$this->fixture->setHolidays('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getHolidays()
		);
	}
	
	/**
	 * @test
	 */
	public function getEmailAddressReturnsInitialValueForObjectStorageContainingTx_Appointments_Domain_Model_Address() { 
		$newObjectStorage = new Tx_Extbase_Persistence_ObjectStorage();
		$this->assertEquals(
			$newObjectStorage,
			$this->fixture->getEmailAddress()
		);
	}

	/**
	 * @test
	 */
	public function setEmailAddressForObjectStorageContainingTx_Appointments_Domain_Model_AddressSetsEmailAddress() { 
		$emailAddres = new Tx_Appointments_Domain_Model_Address();
		$objectStorageHoldingExactlyOneEmailAddress = new Tx_Extbase_Persistence_ObjectStorage();
		$objectStorageHoldingExactlyOneEmailAddress->attach($emailAddres);
		$this->fixture->setEmailAddress($objectStorageHoldingExactlyOneEmailAddress);

		$this->assertSame(
			$objectStorageHoldingExactlyOneEmailAddress,
			$this->fixture->getEmailAddress()
		);
	}
	
	/**
	 * @test
	 */
	public function addEmailAddresToObjectStorageHoldingEmailAddress() {
		$emailAddres = new Tx_Appointments_Domain_Model_Address();
		$objectStorageHoldingExactlyOneEmailAddres = new Tx_Extbase_Persistence_ObjectStorage();
		$objectStorageHoldingExactlyOneEmailAddres->attach($emailAddres);
		$this->fixture->addEmailAddres($emailAddres);

		$this->assertEquals(
			$objectStorageHoldingExactlyOneEmailAddres,
			$this->fixture->getEmailAddress()
		);
	}

	/**
	 * @test
	 */
	public function removeEmailAddresFromObjectStorageHoldingEmailAddress() {
		$emailAddres = new Tx_Appointments_Domain_Model_Address();
		$localObjectStorage = new Tx_Extbase_Persistence_ObjectStorage();
		$localObjectStorage->attach($emailAddres);
		$localObjectStorage->detach($emailAddres);
		$this->fixture->addEmailAddres($emailAddres);
		$this->fixture->removeEmailAddres($emailAddres);

		$this->assertEquals(
			$localObjectStorage,
			$this->fixture->getEmailAddress()
		);
	}
	
	/**
	 * @test
	 */
	public function getCalendarInviteAddressReturnsInitialValueForObjectStorageContainingTx_Appointments_Domain_Model_Address() { 
		$newObjectStorage = new Tx_Extbase_Persistence_ObjectStorage();
		$this->assertEquals(
			$newObjectStorage,
			$this->fixture->getCalendarInviteAddress()
		);
	}

	/**
	 * @test
	 */
	public function setCalendarInviteAddressForObjectStorageContainingTx_Appointments_Domain_Model_AddressSetsCalendarInviteAddress() { 
		$calendarInviteAddres = new Tx_Appointments_Domain_Model_Address();
		$objectStorageHoldingExactlyOneCalendarInviteAddress = new Tx_Extbase_Persistence_ObjectStorage();
		$objectStorageHoldingExactlyOneCalendarInviteAddress->attach($calendarInviteAddres);
		$this->fixture->setCalendarInviteAddress($objectStorageHoldingExactlyOneCalendarInviteAddress);

		$this->assertSame(
			$objectStorageHoldingExactlyOneCalendarInviteAddress,
			$this->fixture->getCalendarInviteAddress()
		);
	}
	
	/**
	 * @test
	 */
	public function addCalendarInviteAddresToObjectStorageHoldingCalendarInviteAddress() {
		$calendarInviteAddres = new Tx_Appointments_Domain_Model_Address();
		$objectStorageHoldingExactlyOneCalendarInviteAddres = new Tx_Extbase_Persistence_ObjectStorage();
		$objectStorageHoldingExactlyOneCalendarInviteAddres->attach($calendarInviteAddres);
		$this->fixture->addCalendarInviteAddres($calendarInviteAddres);

		$this->assertEquals(
			$objectStorageHoldingExactlyOneCalendarInviteAddres,
			$this->fixture->getCalendarInviteAddress()
		);
	}

	/**
	 * @test
	 */
	public function removeCalendarInviteAddresFromObjectStorageHoldingCalendarInviteAddress() {
		$calendarInviteAddres = new Tx_Appointments_Domain_Model_Address();
		$localObjectStorage = new Tx_Extbase_Persistence_ObjectStorage();
		$localObjectStorage->attach($calendarInviteAddres);
		$localObjectStorage->detach($calendarInviteAddres);
		$this->fixture->addCalendarInviteAddres($calendarInviteAddres);
		$this->fixture->removeCalendarInviteAddres($calendarInviteAddres);

		$this->assertEquals(
			$localObjectStorage,
			$this->fixture->getCalendarInviteAddress()
		);
	}
	
}
?>
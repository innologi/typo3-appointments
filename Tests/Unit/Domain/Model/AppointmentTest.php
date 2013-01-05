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
 * Test case for class Tx_Appointments_Domain_Model_Appointment.
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
class Tx_Appointments_Domain_Model_AppointmentTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_Appointments_Domain_Model_Appointment
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new Tx_Appointments_Domain_Model_Appointment();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getBeginTimeReturnsInitialValueForDateTime() { }

	/**
	 * @test
	 */
	public function setBeginTimeForDateTimeSetsBeginTime() { }
	
	/**
	 * @test
	 */
	public function getEndTimeReturnsInitialValueForDateTime() { }

	/**
	 * @test
	 */
	public function setEndTimeForDateTimeSetsEndTime() { }
	
	/**
	 * @test
	 */
	public function getTypeReturnsInitialValueForTx_Appointments_Domain_Model_Type() { 
		$this->assertEquals(
			NULL,
			$this->fixture->getType()
		);
	}

	/**
	 * @test
	 */
	public function setTypeForTx_Appointments_Domain_Model_TypeSetsType() { 
		$dummyObject = new Tx_Appointments_Domain_Model_Type();
		$this->fixture->setType($dummyObject);

		$this->assertSame(
			$dummyObject,
			$this->fixture->getType()
		);
	}
	
	/**
	 * @test
	 */
	public function getFormFieldValuesReturnsInitialValueForObjectStorageContainingTx_Appointments_Domain_Model_FormFieldValue() { 
		$newObjectStorage = new Tx_Extbase_Persistence_ObjectStorage();
		$this->assertEquals(
			$newObjectStorage,
			$this->fixture->getFormFieldValues()
		);
	}

	/**
	 * @test
	 */
	public function setFormFieldValuesForObjectStorageContainingTx_Appointments_Domain_Model_FormFieldValueSetsFormFieldValues() { 
		$formFieldValue = new Tx_Appointments_Domain_Model_FormFieldValue();
		$objectStorageHoldingExactlyOneFormFieldValues = new Tx_Extbase_Persistence_ObjectStorage();
		$objectStorageHoldingExactlyOneFormFieldValues->attach($formFieldValue);
		$this->fixture->setFormFieldValues($objectStorageHoldingExactlyOneFormFieldValues);

		$this->assertSame(
			$objectStorageHoldingExactlyOneFormFieldValues,
			$this->fixture->getFormFieldValues()
		);
	}
	
	/**
	 * @test
	 */
	public function addFormFieldValueToObjectStorageHoldingFormFieldValues() {
		$formFieldValue = new Tx_Appointments_Domain_Model_FormFieldValue();
		$objectStorageHoldingExactlyOneFormFieldValue = new Tx_Extbase_Persistence_ObjectStorage();
		$objectStorageHoldingExactlyOneFormFieldValue->attach($formFieldValue);
		$this->fixture->addFormFieldValue($formFieldValue);

		$this->assertEquals(
			$objectStorageHoldingExactlyOneFormFieldValue,
			$this->fixture->getFormFieldValues()
		);
	}

	/**
	 * @test
	 */
	public function removeFormFieldValueFromObjectStorageHoldingFormFieldValues() {
		$formFieldValue = new Tx_Appointments_Domain_Model_FormFieldValue();
		$localObjectStorage = new Tx_Extbase_Persistence_ObjectStorage();
		$localObjectStorage->attach($formFieldValue);
		$localObjectStorage->detach($formFieldValue);
		$this->fixture->addFormFieldValue($formFieldValue);
		$this->fixture->removeFormFieldValue($formFieldValue);

		$this->assertEquals(
			$localObjectStorage,
			$this->fixture->getFormFieldValues()
		);
	}
	
	/**
	 * @test
	 */
	public function getAddressReturnsInitialValueForTx_Appointments_Domain_Model_Address() { 
		$this->assertEquals(
			NULL,
			$this->fixture->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function setAddressForTx_Appointments_Domain_Model_AddressSetsAddress() { 
		$dummyObject = new Tx_Appointments_Domain_Model_Address();
		$this->fixture->setAddress($dummyObject);

		$this->assertSame(
			$dummyObject,
			$this->fixture->getAddress()
		);
	}
	
	/**
	 * @test
	 */
	public function getFeUserReturnsInitialValueForTx_Extbase_Domain_Model_FrontendUser() { }

	/**
	 * @test
	 */
	public function setFeUserForTx_Extbase_Domain_Model_FrontendUserSetsFeUser() { }
	
	/**
	 * @test
	 */
	public function getAgendaReturnsInitialValueForTx_Appointments_Domain_Model_Agenda() { 
		$this->assertEquals(
			NULL,
			$this->fixture->getAgenda()
		);
	}

	/**
	 * @test
	 */
	public function setAgendaForTx_Appointments_Domain_Model_AgendaSetsAgenda() { 
		$dummyObject = new Tx_Appointments_Domain_Model_Agenda();
		$this->fixture->setAgenda($dummyObject);

		$this->assertSame(
			$dummyObject,
			$this->fixture->getAgenda()
		);
	}
	
}
?>
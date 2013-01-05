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
 * Test case for class Tx_Appointments_Domain_Model_FormFieldValue.
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
class Tx_Appointments_Domain_Model_FormFieldValueTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_Appointments_Domain_Model_FormFieldValue
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new Tx_Appointments_Domain_Model_FormFieldValue();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getValueReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setValueForStringSetsValue() { 
		$this->fixture->setValue('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getValue()
		);
	}
	
	/**
	 * @test
	 */
	public function getFormFieldReturnsInitialValueForTx_Appointments_Domain_Model_FormField() { 
		$this->assertEquals(
			NULL,
			$this->fixture->getFormField()
		);
	}

	/**
	 * @test
	 */
	public function setFormFieldForTx_Appointments_Domain_Model_FormFieldSetsFormField() { 
		$dummyObject = new Tx_Appointments_Domain_Model_FormField();
		$this->fixture->setFormField($dummyObject);

		$this->assertSame(
			$dummyObject,
			$this->fixture->getFormField()
		);
	}
	
}
?>
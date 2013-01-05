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
 * Test case for class Tx_Appointments_Domain_Model_FormField.
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
class Tx_Appointments_Domain_Model_FormFieldTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_Appointments_Domain_Model_FormField
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new Tx_Appointments_Domain_Model_FormField();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getTitleReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setTitleForStringSetsTitle() { 
		$this->fixture->setTitle('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getTitle()
		);
	}
	
	/**
	 * @test
	 */
	public function getDescriptionReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setDescriptionForStringSetsDescription() { 
		$this->fixture->setDescription('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getDescription()
		);
	}
	
	/**
	 * @test
	 */
	public function getTypeReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getType()
		);
	}

	/**
	 * @test
	 */
	public function setTypeForIntegerSetsType() { 
		$this->fixture->setType(12);

		$this->assertSame(
			12,
			$this->fixture->getType()
		);
	}
	
	/**
	 * @test
	 */
	public function getChoicesReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setChoicesForStringSetsChoices() { 
		$this->fixture->setChoices('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getChoices()
		);
	}
	
	/**
	 * @test
	 */
	public function getFunctionReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getFunction()
		);
	}

	/**
	 * @test
	 */
	public function setFunctionForIntegerSetsFunction() { 
		$this->fixture->setFunction(12);

		$this->assertSame(
			12,
			$this->fixture->getFunction()
		);
	}
	
	/**
	 * @test
	 */
	public function getTimeAddReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setTimeAddForStringSetsTimeAdd() { 
		$this->fixture->setTimeAdd('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getTimeAdd()
		);
	}
	
	/**
	 * @test
	 */
	public function getEnableFieldReturnsInitialValueForTx_Appointments_Domain_Model_FormField() { 
		$this->assertEquals(
			NULL,
			$this->fixture->getEnableField()
		);
	}

	/**
	 * @test
	 */
	public function setEnableFieldForTx_Appointments_Domain_Model_FormFieldSetsEnableField() { 
		$dummyObject = new Tx_Appointments_Domain_Model_FormField();
		$this->fixture->setEnableField($dummyObject);

		$this->assertSame(
			$dummyObject,
			$this->fixture->getEnableField()
		);
	}
	
}
?>
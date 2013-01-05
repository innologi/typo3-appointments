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
 * Test case for class Tx_Appointments_Domain_Model_Type.
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
class Tx_Appointments_Domain_Model_TypeTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_Appointments_Domain_Model_Type
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new Tx_Appointments_Domain_Model_Type();
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
	public function getStartTimeMondayReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setStartTimeMondayForStringSetsStartTimeMonday() { 
		$this->fixture->setStartTimeMonday('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getStartTimeMonday()
		);
	}
	
	/**
	 * @test
	 */
	public function getStartTimeTuesdayReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setStartTimeTuesdayForStringSetsStartTimeTuesday() { 
		$this->fixture->setStartTimeTuesday('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getStartTimeTuesday()
		);
	}
	
	/**
	 * @test
	 */
	public function getStartTimeWednesdayReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setStartTimeWednesdayForStringSetsStartTimeWednesday() { 
		$this->fixture->setStartTimeWednesday('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getStartTimeWednesday()
		);
	}
	
	/**
	 * @test
	 */
	public function getStartTimeThursdayReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setStartTimeThursdayForStringSetsStartTimeThursday() { 
		$this->fixture->setStartTimeThursday('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getStartTimeThursday()
		);
	}
	
	/**
	 * @test
	 */
	public function getStartTimeFridayReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setStartTimeFridayForStringSetsStartTimeFriday() { 
		$this->fixture->setStartTimeFriday('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getStartTimeFriday()
		);
	}
	
	/**
	 * @test
	 */
	public function getStartTimeSaturdayReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setStartTimeSaturdayForStringSetsStartTimeSaturday() { 
		$this->fixture->setStartTimeSaturday('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getStartTimeSaturday()
		);
	}
	
	/**
	 * @test
	 */
	public function getStartTimeSundayReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setStartTimeSundayForStringSetsStartTimeSunday() { 
		$this->fixture->setStartTimeSunday('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getStartTimeSunday()
		);
	}
	
	/**
	 * @test
	 */
	public function getStopTimeMondayReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setStopTimeMondayForStringSetsStopTimeMonday() { 
		$this->fixture->setStopTimeMonday('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getStopTimeMonday()
		);
	}
	
	/**
	 * @test
	 */
	public function getStopTimeTuesdayReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setStopTimeTuesdayForStringSetsStopTimeTuesday() { 
		$this->fixture->setStopTimeTuesday('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getStopTimeTuesday()
		);
	}
	
	/**
	 * @test
	 */
	public function getStopTimeWednesdayReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setStopTimeWednesdayForStringSetsStopTimeWednesday() { 
		$this->fixture->setStopTimeWednesday('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getStopTimeWednesday()
		);
	}
	
	/**
	 * @test
	 */
	public function getStopTimeThursdayReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setStopTimeThursdayForStringSetsStopTimeThursday() { 
		$this->fixture->setStopTimeThursday('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getStopTimeThursday()
		);
	}
	
	/**
	 * @test
	 */
	public function getStopTimeFridayReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setStopTimeFridayForStringSetsStopTimeFriday() { 
		$this->fixture->setStopTimeFriday('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getStopTimeFriday()
		);
	}
	
	/**
	 * @test
	 */
	public function getStopTimeSaturdayReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setStopTimeSaturdayForStringSetsStopTimeSaturday() { 
		$this->fixture->setStopTimeSaturday('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getStopTimeSaturday()
		);
	}
	
	/**
	 * @test
	 */
	public function getStopTimeSundayReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setStopTimeSundayForStringSetsStopTimeSunday() { 
		$this->fixture->setStopTimeSunday('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getStopTimeSunday()
		);
	}
	
	/**
	 * @test
	 */
	public function getExcludeHolidaysReturnsInitialValueForBoolean() { 
		$this->assertSame(
			TRUE,
			$this->fixture->getExcludeHolidays()
		);
	}

	/**
	 * @test
	 */
	public function setExcludeHolidaysForBooleanSetsExcludeHolidays() { 
		$this->fixture->setExcludeHolidays(TRUE);

		$this->assertSame(
			TRUE,
			$this->fixture->getExcludeHolidays()
		);
	}
	
	/**
	 * @test
	 */
	public function getMaxAmountMondayReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMaxAmountMonday()
		);
	}

	/**
	 * @test
	 */
	public function setMaxAmountMondayForIntegerSetsMaxAmountMonday() { 
		$this->fixture->setMaxAmountMonday(12);

		$this->assertSame(
			12,
			$this->fixture->getMaxAmountMonday()
		);
	}
	
	/**
	 * @test
	 */
	public function getMaxAmountTuesdayReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMaxAmountTuesday()
		);
	}

	/**
	 * @test
	 */
	public function setMaxAmountTuesdayForIntegerSetsMaxAmountTuesday() { 
		$this->fixture->setMaxAmountTuesday(12);

		$this->assertSame(
			12,
			$this->fixture->getMaxAmountTuesday()
		);
	}
	
	/**
	 * @test
	 */
	public function getMaxAmountWednesdayReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMaxAmountWednesday()
		);
	}

	/**
	 * @test
	 */
	public function setMaxAmountWednesdayForIntegerSetsMaxAmountWednesday() { 
		$this->fixture->setMaxAmountWednesday(12);

		$this->assertSame(
			12,
			$this->fixture->getMaxAmountWednesday()
		);
	}
	
	/**
	 * @test
	 */
	public function getMaxAmountThursdayReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMaxAmountThursday()
		);
	}

	/**
	 * @test
	 */
	public function setMaxAmountThursdayForIntegerSetsMaxAmountThursday() { 
		$this->fixture->setMaxAmountThursday(12);

		$this->assertSame(
			12,
			$this->fixture->getMaxAmountThursday()
		);
	}
	
	/**
	 * @test
	 */
	public function getMaxAmountFridayReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMaxAmountFriday()
		);
	}

	/**
	 * @test
	 */
	public function setMaxAmountFridayForIntegerSetsMaxAmountFriday() { 
		$this->fixture->setMaxAmountFriday(12);

		$this->assertSame(
			12,
			$this->fixture->getMaxAmountFriday()
		);
	}
	
	/**
	 * @test
	 */
	public function getMaxAmountSaturdayReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMaxAmountSaturday()
		);
	}

	/**
	 * @test
	 */
	public function setMaxAmountSaturdayForIntegerSetsMaxAmountSaturday() { 
		$this->fixture->setMaxAmountSaturday(12);

		$this->assertSame(
			12,
			$this->fixture->getMaxAmountSaturday()
		);
	}
	
	/**
	 * @test
	 */
	public function getMaxAmountSundayReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMaxAmountSunday()
		);
	}

	/**
	 * @test
	 */
	public function setMaxAmountSundayForIntegerSetsMaxAmountSunday() { 
		$this->fixture->setMaxAmountSunday(12);

		$this->assertSame(
			12,
			$this->fixture->getMaxAmountSunday()
		);
	}
	
	/**
	 * @test
	 */
	public function getMinuteIntervalMondayReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMinuteIntervalMonday()
		);
	}

	/**
	 * @test
	 */
	public function setMinuteIntervalMondayForIntegerSetsMinuteIntervalMonday() { 
		$this->fixture->setMinuteIntervalMonday(12);

		$this->assertSame(
			12,
			$this->fixture->getMinuteIntervalMonday()
		);
	}
	
	/**
	 * @test
	 */
	public function getMinuteIntervalTuesdayReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMinuteIntervalTuesday()
		);
	}

	/**
	 * @test
	 */
	public function setMinuteIntervalTuesdayForIntegerSetsMinuteIntervalTuesday() { 
		$this->fixture->setMinuteIntervalTuesday(12);

		$this->assertSame(
			12,
			$this->fixture->getMinuteIntervalTuesday()
		);
	}
	
	/**
	 * @test
	 */
	public function getMinuteIntervalWednesdayReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMinuteIntervalWednesday()
		);
	}

	/**
	 * @test
	 */
	public function setMinuteIntervalWednesdayForIntegerSetsMinuteIntervalWednesday() { 
		$this->fixture->setMinuteIntervalWednesday(12);

		$this->assertSame(
			12,
			$this->fixture->getMinuteIntervalWednesday()
		);
	}
	
	/**
	 * @test
	 */
	public function getMinuteIntervalThursdayReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMinuteIntervalThursday()
		);
	}

	/**
	 * @test
	 */
	public function setMinuteIntervalThursdayForIntegerSetsMinuteIntervalThursday() { 
		$this->fixture->setMinuteIntervalThursday(12);

		$this->assertSame(
			12,
			$this->fixture->getMinuteIntervalThursday()
		);
	}
	
	/**
	 * @test
	 */
	public function getMinuteIntervalFridayReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMinuteIntervalFriday()
		);
	}

	/**
	 * @test
	 */
	public function setMinuteIntervalFridayForIntegerSetsMinuteIntervalFriday() { 
		$this->fixture->setMinuteIntervalFriday(12);

		$this->assertSame(
			12,
			$this->fixture->getMinuteIntervalFriday()
		);
	}
	
	/**
	 * @test
	 */
	public function getMinuteIntervalSaturdayReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMinuteIntervalSaturday()
		);
	}

	/**
	 * @test
	 */
	public function setMinuteIntervalSaturdayForIntegerSetsMinuteIntervalSaturday() { 
		$this->fixture->setMinuteIntervalSaturday(12);

		$this->assertSame(
			12,
			$this->fixture->getMinuteIntervalSaturday()
		);
	}
	
	/**
	 * @test
	 */
	public function getMinuteIntervalSundayReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMinuteIntervalSunday()
		);
	}

	/**
	 * @test
	 */
	public function setMinuteIntervalSundayForIntegerSetsMinuteIntervalSunday() { 
		$this->fixture->setMinuteIntervalSunday(12);

		$this->assertSame(
			12,
			$this->fixture->getMinuteIntervalSunday()
		);
	}
	
	/**
	 * @test
	 */
	public function getMaxAmountPer2daysReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMaxAmountPer2days()
		);
	}

	/**
	 * @test
	 */
	public function setMaxAmountPer2daysForIntegerSetsMaxAmountPer2days() { 
		$this->fixture->setMaxAmountPer2days(12);

		$this->assertSame(
			12,
			$this->fixture->getMaxAmountPer2days()
		);
	}
	
	/**
	 * @test
	 */
	public function getBetweenMinutesReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getBetweenMinutes()
		);
	}

	/**
	 * @test
	 */
	public function setBetweenMinutesForIntegerSetsBetweenMinutes() { 
		$this->fixture->setBetweenMinutes(12);

		$this->assertSame(
			12,
			$this->fixture->getBetweenMinutes()
		);
	}
	
	/**
	 * @test
	 */
	public function getHoursMutableReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getHoursMutable()
		);
	}

	/**
	 * @test
	 */
	public function setHoursMutableForIntegerSetsHoursMutable() { 
		$this->fixture->setHoursMutable(12);

		$this->assertSame(
			12,
			$this->fixture->getHoursMutable()
		);
	}
	
	/**
	 * @test
	 */
	public function getBlockedHoursReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getBlockedHours()
		);
	}

	/**
	 * @test
	 */
	public function setBlockedHoursForIntegerSetsBlockedHours() { 
		$this->fixture->setBlockedHours(12);

		$this->assertSame(
			12,
			$this->fixture->getBlockedHours()
		);
	}
	
	/**
	 * @test
	 */
	public function getBlockedHoursWorkdaysReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getBlockedHoursWorkdays()
		);
	}

	/**
	 * @test
	 */
	public function setBlockedHoursWorkdaysForIntegerSetsBlockedHoursWorkdays() { 
		$this->fixture->setBlockedHoursWorkdays(12);

		$this->assertSame(
			12,
			$this->fixture->getBlockedHoursWorkdays()
		);
	}
	
	/**
	 * @test
	 */
	public function getFormFieldReturnsInitialValueForObjectStorageContainingTx_Appointments_Domain_Model_FormField() { 
		$newObjectStorage = new Tx_Extbase_Persistence_ObjectStorage();
		$this->assertEquals(
			$newObjectStorage,
			$this->fixture->getFormField()
		);
	}

	/**
	 * @test
	 */
	public function setFormFieldForObjectStorageContainingTx_Appointments_Domain_Model_FormFieldSetsFormField() { 
		$formField = new Tx_Appointments_Domain_Model_FormField();
		$objectStorageHoldingExactlyOneFormField = new Tx_Extbase_Persistence_ObjectStorage();
		$objectStorageHoldingExactlyOneFormField->attach($formField);
		$this->fixture->setFormField($objectStorageHoldingExactlyOneFormField);

		$this->assertSame(
			$objectStorageHoldingExactlyOneFormField,
			$this->fixture->getFormField()
		);
	}
	
	/**
	 * @test
	 */
	public function addFormFieldToObjectStorageHoldingFormField() {
		$formField = new Tx_Appointments_Domain_Model_FormField();
		$objectStorageHoldingExactlyOneFormField = new Tx_Extbase_Persistence_ObjectStorage();
		$objectStorageHoldingExactlyOneFormField->attach($formField);
		$this->fixture->addFormField($formField);

		$this->assertEquals(
			$objectStorageHoldingExactlyOneFormField,
			$this->fixture->getFormField()
		);
	}

	/**
	 * @test
	 */
	public function removeFormFieldFromObjectStorageHoldingFormField() {
		$formField = new Tx_Appointments_Domain_Model_FormField();
		$localObjectStorage = new Tx_Extbase_Persistence_ObjectStorage();
		$localObjectStorage->attach($formField);
		$localObjectStorage->detach($formField);
		$this->fixture->addFormField($formField);
		$this->fixture->removeFormField($formField);

		$this->assertEquals(
			$localObjectStorage,
			$this->fixture->getFormField()
		);
	}
	
}
?>
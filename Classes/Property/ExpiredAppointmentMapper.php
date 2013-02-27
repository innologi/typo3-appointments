<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Jochen Rau <jochen.rau@typoplanet.de>
*  All rights reserved
*
*  This class is a backport of the corresponding class of FLOW3.
*  All credits go to the v5 team.
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
 * The Property Mapper maps properties from a source onto a given target object, often a
 * (domain-) model. Which properties are required and how they should be filtered can
 * be customized.
 *
 * During the mapping process, the property values are validated and the result of this
 * validation can be queried.
 *
 * The following code would map the property of the source array to the target:
 *
 * $target = new ArrayObject();
 * $source = new ArrayObject(
 *    array(
 *       'someProperty' => 'SomeValue'
 *    )
 * );
 * $mapper->mapAndValidate(array('someProperty'), $source, $target);
 *
 * Now the target object equals the source object.
 *
 * @package Appointments
 * @subpackage Property
 * @version $Id$
 */
class Tx_Appointments_Property_ExpiredAppointmentMapper extends Tx_Extbase_Property_Mapper {
	#@TODO doc all

	/**
	 * Finds an object from the repository by searching for its technical UID.
	 *
	 * @param string $dataType the data type to fetch
	 * @param int $uid The object's uid
	 * @return object Either the object matching the uid or, if none or more than one object was found, NULL
	 */
	// TODO This is duplicated code; see Argument class
	protected function findObjectByUid($dataType, $uid) {
		$query = $this->queryFactory->create($dataType);
		$query->getQuerySettings()->setRespectSysLanguage(FALSE);
		$query->getQuerySettings()->setRespectStoragePage(FALSE);

		$constraints = array(
			0 => $query->equals('uid', intval($uid))
		);

		$tempDataTypes = array(
				'Tx_Appointments_Domain_Model_Appointment',
				'Tx_Appointments_Domain_Model_Address',
				'Tx_Appointments_Domain_Model_FormFieldValue'
		);
		if (in_array($dataType,$tempDataTypes)) { #@FIXME when an address and/or formfieldvalues also were stored, they have also been deleted (@cascade remove), thus causing problems when the previously deleted appointment is built
			return $this->findDeletedTempByUid($query,$constraints);
		} else {
			return $query->matching(
					$constraints[0]
			)->execute()->getFirst();
		}
	}

	#@TODO doc
	protected function findDeletedTempByUid($query, $constraints) {
		$query->getQuerySettings()->setRespectEnableFields(FALSE);
		$constraints[] = $query->logicalOr(array(
				$query->equals('deleted', 0),
				$query->logicalAnd(array(
						$query->equals('deleted', 1),
						$query->equals('temporary', 1)
				))
		));

		return $appointment = $query->matching(
				$query->logicalAnd(
						$constraints
				)
		)->execute()->getFirst();
	}
}
?>
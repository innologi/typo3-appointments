<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
 * Type Repository
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Domain_Repository_TypeRepository extends Tx_Appointments_Persistence_NoPersistRepository {
	#@TODO _the use of this function has changed, so you might want to look if the function itself might benefit from a change
	/**
	 * Returns all objects of this repository belonging to the provided category
	 *
	 * @param array $typeArray Contains type uids
	 * @param boolean $showSuperUserTypes Shows all types on TRUE or only the normal types on FALSE
	 * @return Tx_Extbase_Persistence_QueryResultInterface|array The query result object or an array if $this->getQuerySettings()->getReturnRawQueryResult() is TRUE
	 */
	public function findIn($typeArray, $showSuperUserTypes = FALSE) {
		$query = $this->createQuery();
		$constraints = array(
				$query->in('uid', $typeArray)
		);
		if ($showSuperUserTypes === FALSE) {
			$constraints[] = $query->equals('superuser_only', 0);
		}

		$result = $query->matching(
				$query->logicalAnd(
						$constraints
				)
		)->execute();
		return $result;
	}

	/**
	 * Returns all objects of this repository.
	 *
	 * @param boolean $showSuperUserTypes Shows all types on TRUE or only the normal types on FALSE
	 * @return Tx_Extbase_Persistence_QueryResultInterface|array
	 *         all objects, will be empty if no objects are found, will be an array if raw query results are enabled
	 */
	public function findAll($showSuperUserTypes = FALSE) {
		$query = $this->createQuery();
		if ($showSuperUserTypes === FALSE) {
			$query->matching(
					$query->equals('superuser_only', 0)
			);
		}

		$result = $query->execute();
		return $result;
	}

	/**
	 * Returns the type with the smallest 'blocked_hours' value.
	 *
	 * @param array $types Contains type uid's to filter by
	 * @return Tx_Appointments_Domain_Model_Type The type
	 */
	public function findBySmallestBlockedHours(array $types) { #@LOW no longer used, clean up?
		$query = $this->createQuery();
		$result = $query->matching(
				$query->in('uid', $types)
		)->setOrderings(
				array(
						'blocked_hours' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING
				)
		)->setLimit(1)->execute();

		return $result->getFirst();
	}

}
?>
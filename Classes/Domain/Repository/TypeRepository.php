<?php
namespace Innologi\Appointments\Domain\Repository;
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
use TYPO3\CMS\Extbase\Persistence\Repository;
/**
 * Type Repository
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class TypeRepository extends Repository {
	#@TODO _the use of this function has changed, so you might want to look if the function itself might benefit from a change
	/**
	 * Returns all objects of this repository belonging to the provided category
	 *
	 * @param array $typeArray Contains type uids
	 * @param boolean $showSuperUserTypes Shows all types on TRUE or only the normal types on FALSE
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array The query result object or an array if $this->getQuerySettings()->getReturnRawQueryResult() is TRUE
	 */
	public function findIn($typeArray, $showSuperUserTypes = FALSE) {
		$query = $this->createQuery();
		$constraints = [
			$query->in('uid', $typeArray)
		];
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
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
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

}
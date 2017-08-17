<?php
namespace Innologi\Appointments\Domain\Repository;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
 * FormFieldValue Repository
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FormFieldValueRepository extends Repository {

	/**
	 * Finds all orphaned formfieldvalues (where appointment is NULL)
	 *
	 * It doesn't get formfieldvalues of appointments with the deleted=1 mark, because when undeleting the appointment
	 * the relation of the formfieldvalues is not taken into account. [4.5] Leaving them be, except when they have no more
	 * valid relation, at least keeps them intact when undeleting their appointment. They're hidden in TCA anyway.
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array The query result object or an array if $this->getQuerySettings()->getReturnRawQueryResult() is TRUE
	 */
	public function findOrphaned() {
		$query = $this->createQuery();
		#$query->getQuerySettings()->setRespectStoragePage(FALSE);
		#$result = $query->matching( //doesn't work, probably because deleted is not in TCA. either way, wouldn't know how to get any with NULL either
		#		$query->equals('appointment.deleted', 1)
		#)->execute()->toArray();
		$result = $query->statement(
			'SELECT `ffv`.*
			FROM `tx_appointments_domain_model_formfieldvalue` `ffv` LEFT JOIN (`tx_appointments_domain_model_appointment` `a`) ON (`ffv`.`appointment`=`a`.`uid`)
			WHERE `ffv`.`deleted`=0 AND `a`.`uid` IS NULL'
		)->execute();

		return $result;
	}

}
?>
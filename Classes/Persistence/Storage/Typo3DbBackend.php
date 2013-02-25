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
 * A Storage backend
 *
 * @package Appointments
 * @subpackage Persistence\Storage
 * @version $Id$
 */
class Tx_Appointments_Persistence_Storage_Typo3DbBackend extends Tx_Extbase_Persistence_Storage_Typo3DbBackend {
	#@TODO create a TYPO3 patch to support extbase propertypaths
	#@TODO doc all
	public function addRow($tableName, array $row, $isRelation = FALSE) {
		if ($tableName === 'tx_appointments_domain_model_formfieldvalue' && isset($row['formField.sorting'])) {
			unset($row['formField.sorting']);
		}
		return parent::addRow($tableName, $row, $isRelation);
	}

	/**
	 * Updates a row in the storage
	 *
	 * @param string $tableName The database table name
	 * @param array $row The row to be updated
	 * @param boolean $isRelation TRUE if we are currently inserting into a relation table, FALSE by default
	 * @return bool
	 */
	public function updateRow($tableName, array $row, $isRelation = FALSE) {
		if ($tableName === 'tx_appointments_domain_model_formfieldvalue' && isset($row['formField.sorting'])) {
			unset($row['formField.sorting']);
		}
		return parent::updateRow($tableName, $row, $isRelation);
	}

}

?>
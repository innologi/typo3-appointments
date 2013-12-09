<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
 * FrontendUser, allows us to include fe_users of any recordType.
 *
 * If I ever find a way, it should be decided in a site's TS with:
 * - config.tx_extbase.persistence.classes.Tx_Extbase_Domain_Model_FrontendUser.mapping.recordType
 * - config.tx_extbase.persistence.classes.Tx_Extbase_Domain_Model_FrontendUserGroup.mapping.recordType
 *
 * But whatever I do, it keeps expecting the extbase record types [4.7],
 * and I currently can't be arsed to debug extbase for it.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Domain_Model_FrontendUser extends Tx_Extbase_Domain_Model_FrontendUser implements Tx_Appointments_Domain_Model_EmailContainerInterface{

	/**
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Appointments_Domain_Model_FrontendUserGroup>
	 */
	protected $usergroup;

	/**
	 * Sets the usergroups. Keep in mind that the property is called "usergroup"
	 * although it can hold several usergroups.
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_Appointments_Domain_Model_FrontendUserGroup> $usergroup An object storage containing the usergroups to add
	 * @return void
	 */
	public function setUsergroup(Tx_Extbase_Persistence_ObjectStorage $usergroup) {
		$this->usergroup = $usergroup;
	}

	/**
	 * Adds a usergroup to the frontend user
	 *
	 * @param Tx_Appointments_Domain_Model_FrontendUserGroup $usergroup
	 * @return void
	 */
	public function addUsergroup(Tx_Appointments_Domain_Model_FrontendUserGroup $usergroup) {
		$this->usergroup->attach($usergroup);
	}

	/**
	 * Removes a usergroup from the frontend user
	 *
	 * @param Tx_Appointments_Domain_Model_FrontendUserGroup $usergroup
	 * @return void
	 */
	public function removeUsergroup(Tx_Appointments_Domain_Model_FrontendUserGroup $usergroup) {
		$this->usergroup->detach($usergroup);
	}


	/**
	 * Returns the usergroups. Keep in mind that the property is called "usergroup"
	 * although it can hold several usergroups.
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_Appointments_Domain_Model_FrontendUserGroup> An object storage containing the usergroup
	 */
	public function getUsergroup() {
		return $this->usergroup;
	}
}
?>
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
 * The Extbase Persistence Manager
 *
 * @package Appointments
 * @subpackage Persistence
 * @version $Id$
 */
class Tx_Appointments_Persistence_Manager extends Tx_Extbase_Persistence_Manager {
	#@TODO doc all
	/**
	 * Commits new objects and changes to objects in the current persistence
	 * session into the backend
	 *
	 * @return void
	 */
	public function persistRepository($repository) {
		$aggregateRootObjects = new Tx_Extbase_Persistence_ObjectStorage();
		$removedObjects = new Tx_Extbase_Persistence_ObjectStorage();

		// fetch and inspect objects from all known repositories
		$aggregateRootObjects->addAll($repository->getAddedObjects());
		$removedObjects->addAll($repository->getRemovedObjects());

		foreach ($this->session->getReconstitutedObjects() as $reconstitutedObject) {
			$reconstitutedClass = str_replace('_Model_','_Repository_',get_class($reconstitutedObject)) . 'Repository';
			if ($reconstitutedClass === get_class($repository)) {
				$aggregateRootObjects->attach($reconstitutedObject);
			}
		}

			// hand in only aggregate roots, leaving handling of subobjects to
			// the underlying storage layer
		$this->backend->setAggregateRootObjects($aggregateRootObjects);
		$this->backend->setDeletedObjects($removedObjects);
		$this->backend->commit();

			// this needs to unregister more than just those, as at least some of
			// the subobjects are supposed to go away as well...
			// OTOH those do no harm, changes to the unused ones should not happen,
			// so all they do is eat some memory.
		foreach($removedObjects as $removedObject) {
			$this->session->unregisterReconstitutedObject($removedObject);
		}
	}

}
?>
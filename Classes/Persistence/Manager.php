<?php
namespace Innologi\Appointments\Persistence;
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
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\Repository;
/**
 * The Appointments Persistence Manager
 *
 * Adds a method to persist a single repository, as well as session.
 *
 * CURRENTLY UNUSED: using this one in a repository means every add() and remove(), you'll
 * need to persist manually, in addition to the automatic persistAll() that happens in the
 * normal repository singleton. WHY? Because a repository stores itself in the
 * persistencemanager it refers to, the pesistencemanager is a singleton which stores
 * those references globally in its single instance, and thus the normal persistencemanager
 * instantiated by Bootstrap never registers our repository. update() calls DO get persisted
 * by it, because the replace() method stores these changes also in the session singleton,
 * which is checked as well @ persistAll().
 *
 * Add to this the compatibility issues that might arise in major version changes when
 * changing core classes, as well as the use of this class' method having been DRASTICALLY
 * reduced: the tiny performance-gain that might still be there is hardly worth the
 * sacrifice of software quality I made here.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Manager extends PersistenceManager {

	/**
	 * Commits new objects and changes to objects of a single repository, as well as
	 * its child-objects, in the current persistence session into the backend.
	 *
	 * Is mostly an adjusted copy of persistAll(), used as a lighter alternative
	 * when appointment-persistence has to happen mid-process.
	 *
	 * @param Repository $repository The repository to persist
	 * @return void
	 */
	public function persistRepository(Repository $repository) {
		$aggregateRootObjects = new ObjectStorage();
		$removedObjects = new ObjectStorage();

		//fetch and inspect objects from the repository
		$aggregateRootObjects->addAll($repository->getAddedObjects());
		$removedObjects->addAll($repository->getRemovedObjects());

		//note that we can't remove this part because some (child) objects are reconstituted through session
		foreach ($this->session->getReconstitutedObjects() as $reconstitutedObject) {
			$reconstitutedClass = str_replace('\\Model\\','\\Repository\\',get_class($reconstitutedObject)) . 'Repository';
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
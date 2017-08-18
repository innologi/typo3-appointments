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
use TYPO3\CMS\Extbase\Persistence\{Repository, PersistenceManagerInterface};
use Innologi\Appointments\Mvc\Exception;
/**
 * This repository prevents registration @ persistence manager.
 *
 * This is an answer to the custom manager having been made useless.
 * Any repository from which the add/remove/update/replace methods are
 * NEVER called, should inherit from this repository, so they don't
 * unnecessarily pollute the persistence-process.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class NoPersistRepository extends Repository {

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager; //for completeness' sake

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface $persistenceManager
	 * @return void
	 */
	public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
		//don't register this repository
		#$this->persistenceManager->registerRepositoryClassName($this->getRepositoryClassName());
	}

	/**
	 * Not allowed
	 *
	 * @param object $object
	 * @return void
	 * @throws Exception\NoPersistRepository
	 */
	public function add($object) {
		throw new Exception\NoPersistRepository();
	}

	/**
	 * Not allowed
	 *
	 * @param object $object
	 * @return void
	 * @throws Exception\NoPersistRepository
	 */
	public function remove($object) {
		throw new Exception\NoPersistRepository();
	}

	/**
	 * Not allowed
	 *
	 * @param object $modifiedObject
	 * @return void
	 * @throws Exception\NoPersistRepository
	 */
	public function update($modifiedObject) {
		throw new Exception\NoPersistRepository();
	}

	/**
	 * Not allowed
	 *
	 * @param object $existingObject
	 * @param object $newObject
	 * @return void
	 * @throws Exception\NoPersistRepository
	 */
	public function replace($existingObject, $newObject) {
		throw new Exception\NoPersistRepository();
	}
}
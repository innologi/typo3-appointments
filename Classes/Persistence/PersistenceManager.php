<?php
namespace Innologi\Appointments\Persistence;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * Extbase 6.1+ bugfix with use of the deprecatedPropertyMapper.
 *
 * In 6.1, the update-mechanism is more aligned to that of FLOW,
 * which works great for the rewrittenPropertyMapper, which is the
 * default in this version. However, it no longer works for the
 * deprecatedPropertyMapper. Since the deprecatedPropertyMapper is
 * still functional until 6.3 and I have to maintain compatibility
 * with 4.5-6.2, I use it in several extensions until I get to
 * rewriting my form-handling for 6.x exclusively.
 *
 * The bug is that the update() method checks if the object exists
 * based on the spl_object_hash. However, spl_object_hash() returns
 * only the same hash if the object-instance is one and the same.
 * With the deprecatedPropertyMapper, this is never the case: the
 * one set in the objectMap is instantiated seperately from the one
 * that is mapped to the controller and eventually passed by
 * update()'s argument.
 *
 * My "fix" is just an implementation similar to pre-6.1, but only
 * if the condition-check of the rewrittenPropertyMapper fails.
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PersistenceManager extends \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager {

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager;

	/**
	 * Update an object in the persistence.
	 *
	 * @param object $object The modified object
	 * @return void
	 * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
	 * @see \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::update()
	 */
	public function update($object) {
		if (version_compare(TYPO3_branch, '6.1', '<')
			|| $this->configurationManager->isFeatureEnabled('rewrittenPropertyMapper')
		) {
			// fall back to default
			return parent::update($object);
		}

		$uid = $object->getUid();
		if ($uid === NULL || $this->getObjectByIdentifier($uid, get_class($object)) === NULL) {
			throw new \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException('The object of type "' . get_class($object) . '" given to update must be persisted already, but is new.', 1249479819);
		}
		$this->changedObjects->attach($object);
	}

}
?>
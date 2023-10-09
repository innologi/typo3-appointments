<?php
namespace Innologi\Appointments\Persistence;
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
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
/**
 * Modification of ObjectStorage. Instead of using hashes of objects as keys,
 * it takes the $key property of the objects as keys. This makes it possible
 * to request an object when only the key is known to you.
 *
 * Assumes the object to have a $key property with associated getter.
 * $object->getKey()
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class KeyObjectStorage extends ObjectStorage {

	/**
	 * Associates data to an object in the storage. offsetSet() is an alias of attach().
	 *
	 * @param object $object The object to add.
	 * @param mixed $information The data to associate with the object.
	 * @return void
	 */
	public function offsetSet($object, $information) {
		$this->isModified = TRUE;
		$this->storage[$object->getKey()] = ['obj' => $object, 'inf' => $information];
	}

	/**
	 * Checks whether an object exists in the storage.
	 *
	 * @param string $objectKey The object to look for.
	 * @return boolean
	 */
	public function offsetExists($objectKey) {
		//this way, an isset can be performed on the objectStorage while only the key is known
		return isset($this->storage[$objectKey]);
	}

	/**
	 * Removes an object from the storage. offsetUnset() is an alias of detach().
	 *
	 * @param object $object The object to remove.
	 * @return void
	 */
	public function offsetUnset($object) {
		$this->isModified = TRUE;
		unset($this->storage[$object->getKey()]);
	}

	/**
	 * Returns the data associated with an object.
	 *
	 * @param string $objectKey The object to look for.
	 * @return object The object in the storage.
	 */
	public function offsetGet($objectKey) {
		return $this->storage[$objectKey]['obj'];
	}

	/**
	 * Returns this object storage as an array
	 *
	 * @return array The object storage
	 */
	public function toArray() {
		$array = [];
		$storage = array_values($this->storage);
		foreach ($storage as $key=>$item) {
			$array[$key] = $item['obj'];
		}
		return $array;
	}

	/**
	 * Gets first object in storage.
	 *
	 * @return object The first object
	 */
	public function getFirst() {
		$this->rewind();
		return $this->current();
	}

	/**
	 * The last object in storage
	 *
	 * @return object The last object
	 */
	public function getLast() {
		end($this->storage);
		return $this->current();
	}

	/**
	 * Adds all objects-data pairs from a different storage in the current storage,
	 * and then sorts all objects by key.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage
	 * @return void
	 */
	public function addAll(ObjectStorage $objectStorage) {
		parent::addAll($objectStorage);

		ksort($this->storage);
	}

}
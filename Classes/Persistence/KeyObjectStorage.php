<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
class Tx_Appointments_Persistence_KeyObjectStorage extends Tx_Extbase_Persistence_ObjectStorage {

	/**
	 * Associates data to an object in the storage. offsetSet() is an alias of attach().
	 *
	 * @param object $object The object to add.
	 * @param mixed $information The data to associate with the object.
	 * @return void
	 */
	public function offsetSet($object, $information) {
		$this->isModified = TRUE;
		$this->storage[$object->getKey()] = array('obj' => $object, 'inf' => $information);
	}

	/**
	 * Checks whether an object exists in the storage.
	 *
	 * @param object $object The object to look for.
	 * @return boolean
	 */
	public function offsetExists($object) {
		return isset($this->storage[$object->getKey()]);
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
	 * @param object $object The object to look for.
	 * @return mixed The data associated with an object in the storage.
	 */
	public function offsetGet($object) {
		return $this->storage[$object->getKey()]['inf'];
	}

	/**
	 * Returns this object storage as an array
	 *
	 * @return array The object storage
	 */
	public function toArray() {
		$array = array();
		$storage = array_values($this->storage);
		foreach ($storage as $key=>$item) {
			$array[$key] = $item['obj'];
		}
		return $array;
	}

	/**
	 * Returns an object by its key.
	 *
	 * @param string $key Object key
	 * @return mixed The object or boolean false
	 */
	public function getObjectByKey($key) { #@SHOULD maakt het feit dat de class ArrayAccess is dit niet onnodig? gewoon $objectStorage[$key]?
		if (isset($this->storage[$key])) {
			return $this->storage[$key]['obj'];
		}
		return FALSE;
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

}
?>
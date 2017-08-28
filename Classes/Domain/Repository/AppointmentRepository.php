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
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Innologi\Appointments\Domain\Model\{Agenda, Appointment};
/**
 * Appointment Repository
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class AppointmentRepository extends Repository {

	/**
	 * @var \Innologi\Appointments\Domain\Service\SlotService
	 * @inject
	 */
	protected $slotService;



	//************************
	// Query Result functions
	//************************

	/**
	 * Returns all objects of this repository belonging to Agenda, Types and FrontendUser, and optionally
	 * from, between or up to a start and/or end time. Only finished appointments.
	 *
	 * @param \Innologi\Appointments\Domain\Model\Agenda $agenda The agenda which the appointments belong to
	 * @param array $types The types the appointments belong to
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $feUser The user which the appointments belong to
	 * @param boolean $unfinished If TRUE: get unfinished appointments instead
	 * @param \DateTime $start Optional start time
	 * @param \DateTime $end Optional end time
	 * @param boolean $descending If TRUE: sorts by begintime descending, if FALSE: ascending
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array The query result object or an array if $this->getQuerySettings()->getReturnRawQueryResult() is TRUE
	 */
	public function findPersonalList(Agenda $agenda, array $types, \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $feUser, $unfinished = FALSE, \DateTime $start = NULL, \DateTime $end = NULL, $descending = FALSE) {
		$query = $this->createQuery();
		$constraints = array(
			$query->equals('agenda', $agenda),
			$query->in('type',$types),
			$query->equals('feUser', $feUser),
			$unfinished
				? $query->logicalNot(
					$query->equals('creation_progress', Appointment::FINISHED)
				) : $query->equals('creation_progress', Appointment::FINISHED)
		);
		if ($start !== NULL) {
			$constraints[] = $query->greaterThanOrEqual('beginTime', $start->getTimestamp());
		}
		if ($end !== NULL) {
			$constraints[] = $query->lessThan('beginTime', $end->getTimestamp());
		}

		$result = $query->matching(
				$query->logicalAnd(
						$constraints
				)
		)->setOrderings(
				array(
					'beginTime' => $descending ? QueryInterface::ORDER_DESCENDING : QueryInterface::ORDER_ASCENDING
				)
		)->execute();

		return $result;
	}

	/**
	 * Returns all objects of this repository of a agenda between two times.
	 *
	 * @param \Innologi\Appointments\Domain\Model\Agenda $agenda The agenda which the appointments belong to
	 * @param \DateTime $start The starting time
	 * @param \DateTime $end The ending time
	 * @param array $types Types to limit appointments by, if not NULL
	 * @param boolean $includeExclusive Include exclusives, but only if $types === NULL
	 * @param \Innologi\Appointments\Domain\Model\Appointment $excludeAppointment Appointment that is ignored in retrieving appointments
	 * @param boolean $includeUnfinished On true, includes unfinished appointments
	 * @param boolean $dontRestrictTypeCounts If set, sets the relevant type parameter as condition
	 * @return array An array of objects, empty if no objects found
	 */
	public function findBetween(Agenda $agenda, \DateTime $start, \DateTime $end, array $types = NULL, $includeExclusive = FALSE, Appointment $excludeAppointment = NULL, $includeUnfinished = FALSE, $dontRestrictTypeCounts = NULL) {
		$query = $this->createQuery();

		$constraint = array(
				$query->equals('agenda', $agenda),
				$query->greaterThanOrEqual('beginTime', $start->getTimestamp()),
				$query->lessThan('beginTime', $end->getTimestamp())
		);

		if ($types !== NULL) {
				$constraint[] = $query->in('type', $types);
		} elseif (!$includeExclusive)  {
				$constraint[] = $query->equals('type.dontBlockTypes', 0);
		}

		if ($dontRestrictTypeCounts !== NULL) {
			$constraint[] = $query->equals('type.dontRestrictTypeCounts', (int) $dontRestrictTypeCounts);
		}

		if ($includeUnfinished) { //aka no expired appointments
			$constraint[] = $query->logicalNot(
					$query->equals('creation_progress', Appointment::EXPIRED)
			);
		} else { //aka only finished appointments
			$constraint[] = $query->equals('creation_progress', Appointment::FINISHED);
		}

		if ($excludeAppointment !== NULL && !$excludeAppointment->_isNew()) {
			$constraint[] = $query->logicalNot(
					$query->equals('uid', $excludeAppointment->getUid())
			);
		}

		$result = $query->matching(
				$query->logicalAnd(
						$constraint
				)
		)->setOrderings(
				array(
					'beginTime' => QueryInterface::ORDER_ASCENDING
				)
		)->execute()->toArray();

		return $result;
	}

	/**
	 * Returns all objects of this repository that take place at or somewhere during the same time
	 * as the given appointment. Note that appointments are allowed to overlap in their reserveblocks.
	 * (aka "BETWEEN Minutes") The query adheres to that rule. No expired appointments.
	 *
	 * @param \Innologi\Appointments\Domain\Model\Appointment $appointment The appointment
	 * @param boolean $includeExclusive Include exclusives, but only if the current one isn't exclusive
	 * @return array An array of objects, empty if no objects found
	 */
	public function findCrossAppointments(Appointment $appointment, $includeExclusive = FALSE) {
		$query = $this->createQuery();

		$beginReserved = $appointment->getBeginReserved()->getTimestamp();
		$endReserved = $appointment->getEndReserved()->getTimestamp();
		$beginTime = $appointment->getBeginTime()->getTimestamp();
		$endTime = $appointment->getEndTime()->getTimestamp();
		$isExclusive = $appointment->getType()->getExclusiveAvailability();

		$constraint = array(
			//apparently, if $agenda isn't validated separately, its lazy storages aren't resolved, which generates an exception, hence we'll stick with its uid
			$query->equals('agenda', $appointment->getAgenda()->getUid()),
			$query->logicalNot(
					$query->equals('uid', $appointment->getUid())
			),
			$query->logicalNot(
					$query->equals('creation_progress', Appointment::EXPIRED)
			),
			$query->logicalOr( array(
					$query->logicalAnd( array( //looks for an overlap @ beginTime
							$query->lessThanOrEqual('beginTime', $beginTime),
							$query->logicalOr( array( //ignores overlapping reserved blocks
									$query->greaterThan('endTime', $beginReserved),
									$query->greaterThan('endReserved', $beginTime)
								)
							)
						)
					),
					$query->logicalAnd( array( //looks for an overlap @ endTime
							$query->greaterThanOrEqual('endTime', $endTime),
							$query->logicalOr( array( //ignores overlapping reserved blocks
									$query->lessThan('beginTime', $endReserved),
									$query->lessThan('beginReserved', $endTime)
								)
							)
						)
					),
					$query->logicalAnd( array( //looks for an overlap between beginTime & endTime
							$query->greaterThan('beginTime', $beginTime),
							$query->lessThan('endTime', $endTime)
						)
					)
				)
			)
		);

		//if exclusive availability, will only be influenced by appointments of the same type
		if ($isExclusive) {
			$constraint[] = $query->equals('type', $appointment->getType());
		} elseif (!$includeExclusive) {
			$constraint[] = $query->equals('type.dontBlockTypes', 0);
		}

		$result = $query->matching(
				$query->logicalAnd($constraint)
		)->execute()->toArray();

		return $result;
	}

	/**
	 * Returns all unfinished objects of this repository that are expired according to the argument.
	 *
	 * An unfinished appointment expires counting from its crdate, NOT its tstamp!
	 *
	 * @param \Innologi\Appointments\Domain\Model\Agenda $agenda The agenda to check
	 * @param integer $expireMinutes The number of minutes since creation date
	 * @return array An array of objects, empty if no objects found
	 */
	public function findExpiredUnfinished(Agenda $agenda, $expireMinutes = 15) {
		if ($expireMinutes < 1) {
			$expireMinutes = 15;
		}

		$query = $this->createQuery();
		$result = $query->matching(
				$query->logicalAnd( array(
						$query->equals('agenda', $agenda),
						$query->lessThanOrEqual('crdate', time() - ($expireMinutes * 60)),
						$query->equals('creation_progress', Appointment::UNFINISHED)
					)
				)
		)->execute()->toArray();
		return $result;
	}

	/**
	 * Finds all appointments that have been expired for at least $age amount of seconds, anywhere.
	 *
	 * Because of enableFields, doesn't match any appointments that are hidden AND expired..
	 *
	 * @param integer $age Number of seconds the appointment needs to have been expired at least
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array The query result object or an array if $this->getQuerySettings()->getReturnRawQueryResult() is TRUE
	 */
	public function findExpiredByAge($age = 3600) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$result = $query->matching(
				$query->logicalAnd(array(
					$query->equals('creation_progress', Appointment::EXPIRED),
					$query->lessThanOrEqual('tstamp', time() - $age),
				))
		)->execute();

		return $result;
	}


	//***************************
	// Query Result Manipulation
	//***************************

	/**
	 * Rearranges an appointment array in a multidimensional array per timeblock.
	 *
	 * - Key format for timeblock elements is 'dd-mm-yyyy hh:mm:ss'
	 * - Order of timeblock elements is ascending
	 *
	 * @param array $array Contains appointments
	 * @param integer $perHours Length of each timeblock in hours
	 * @return array Resulting multidimensional array
	 */
	public function rearrangeAppointmentArray($array, $perHours = 24) {
		//timeblock hours, includes 24 for structural purposes because of the $hour < $h check
		$hours = array();
		for ($i = 0; $i <= 24; $i += $perHours) {
			$hours[] = $i;
		}

		$resultArray = array();
		foreach ($array as $appointment) {
			$beginTime = $appointment->getBeginTime();
			$hour = intval($beginTime->format('G')); // 1 - 24
			foreach ($hours as $h) {
				if ($hour < $h) {
					//if the appointment hour is earlier, the appointment belongs to the previous timeblock-hour
					$h = $prevH < 10 ? '0' . $prevH : $prevH;
					$resultArrayKey = $beginTime->format('d-m-Y ' . $h . ':00:00');
					break;
				}
				$prevH = $h;
			}
			$resultArray[$resultArrayKey][$beginTime->getTimestamp()] = $appointment;
		}
		return $resultArray;
	}


	//*********************************
	// Override functions / variations
	//*********************************

	/**
	 * Persists all changes manually.
	 *
	 * @return void
	 */
	public function persistChanges() {
		$this->persistenceManager->persistAll();
	}

	/**
	 * Adds an object to this repository
	 *
	 * @param object $object The object to add
	 * @return void
	 */
	public function add($object) {
		parent::add($object);
		$this->persistChanges(); //because the only use add() sees requires persisting
		$this->slotService->resetStorageObject($object->getType(), $object->getAgenda());
	}

	/**
	 * Replaces an existing object with the same identifier by the given object
	 *
	 * @param object $modifiedObject The modified object
	 * @param boolean $resetStorageObject If set to FALSE, won't reset storage object automatically
	 * @return void
	 */
	public function update($modifiedObject, $resetStorageObject = TRUE) {
		parent::update($modifiedObject);
		if ($resetStorageObject) {
			$this->slotService->resetStorageObject($modifiedObject->getType(), $modifiedObject->getAgenda());
		}
	}

	/**
	 * Removes an object from this repository.
	 *
	 * @param object $object The object to remove
	 * @return void
	 */
	public function remove($object) {
		parent::remove($object);
		$this->slotService->resetStorageObject($object->getType(), $object->getAgenda());
	}
}
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
 * Appointment Repository
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Domain_Repository_AppointmentRepository extends Tx_Extbase_Persistence_Repository {

	/**
	 * @var Tx_Appointments_Persistence_Manager
	 */
	protected $persistenceManager;

	/**
	 * @param Tx_Appointments_Persistence_Manager $persistenceManager
	 * @return void
	 */
	public function injectPersistenceManager(Tx_Appointments_Persistence_Manager $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
		$this->persistenceManager->registerRepositoryClassName($this->getRepositoryClassName());
	}

	/**
	 * Returns all objects of this repository belonging to Agenda and FrontendUser, and optionally
	 * from, between or up to a start and/or end time. Only finished appointments.
	 *
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda which the appointments belong to
	 * @param Tx_Appointments_Domain_Model_FrontendUser $feUser The user which the appointments belong to
	 * @param DateTime $start Optional start time
	 * @param DateTime $end Optional end time
	 * @param boolean $descending If TRUE: sorts by begintime descending, if FALSE: ascending
	 * @return Tx_Extbase_Persistence_QueryResultInterface|array The query result object or an array if $this->getQuerySettings()->getReturnRawQueryResult() is TRUE
	 */
	public function findByAgendaAndFeUser(Tx_Appointments_Domain_Model_Agenda $agenda, Tx_Appointments_Domain_Model_FrontendUser $feUser, DateTime $start = NULL, DateTime $end = NULL, $descending = FALSE) {
		$query = $this->createQuery();
		$constraints = array(
				$query->equals('agenda', $agenda),
				$query->equals('feUser', $feUser),
				$query->equals('creation_progress', Tx_Appointments_Domain_Model_Appointment::FINISHED)
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
						'beginTime' => $descending ? Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING : Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING
				)
		)->execute();

		return $result;
	}

	#@SHOULD no longer used, clean up? maybe check EVERYTHING again, because there have been a lot of efficiency-changes
	/**
	 * Returns all objects of this repository belonging to the specified day. No expired appointments.
	 *
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda which the appointments belong to
	 * @param DateTime $day Day to which appointments belong to
	 * @return Tx_Extbase_Persistence_QueryResultInterface|array The query result object or an array if $this->getQuerySettings()->getReturnRawQueryResult() is TRUE
	 */
	public function findByAgendaAndDay(Tx_Appointments_Domain_Model_Agenda $agenda, DateTime $day) {
		$query = $this->createQuery();
		$result = $query->matching(
				$query->logicalAnd( array(
						$query->logicalNot(
								$query->equals('creation_progress', Tx_Appointments_Domain_Model_Appointment::EXPIRED)
						),
						$query->equals('agenda', $agenda),
						$query->greaterThanOrEqual('beginTime', $day->setTime(0,0)->getTimestamp()),
						$query->lessThanOrEqual('beginTime', $day->setTime(23,59)->getTimestamp())
					)
				)
		)->setOrderings(
				array(
						'beginTime' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING
				)
		)->execute();
		return $result;
	}

	/**
	 * Returns all objects of this repository of a agenda between two times.
	 *
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda which the appointments belong to
	 * @param DateTime $start The starting time
	 * @param DateTime $end The ending time
	 * @param boolean $includeUnfinished On true, includes unfinished appointments
	 * @param integer $rearrangePerHours On true, rebuilds the array through rearrangeAppointmentArray()
	 * @param Tx_Appointments_Domain_Model_Appointment $excludeAppointment Appointment that is ignored in retrieving appointments
	 * @param array $types Types to limit appointments by, if not NULL
	 * @return array An array of objects, empty if no objects found
	 */
	public function findBetween(Tx_Appointments_Domain_Model_Agenda $agenda, DateTime $start, DateTime $end, $includeUnfinished = FALSE, $rearrangePerHours = 0, Tx_Appointments_Domain_Model_Appointment $excludeAppointment = NULL, array $types = NULL) {
		$query = $this->createQuery();

		$constraint = array(
				$query->equals('agenda', $agenda),
				$query->greaterThanOrEqual('beginTime', $start->getTimestamp()),
				$query->lessThan('beginTime', $end->getTimestamp())
		);

		if ($types !== NULL) {
				$constraint[] = $query->in('type', $types);
		}

		if ($includeUnfinished) { //aka no expired appointments
			$constraint[] = $query->logicalNot(
					$query->equals('creation_progress', Tx_Appointments_Domain_Model_Appointment::EXPIRED)
			);
		} else { //aka only finished appointments
			$constraint[] = $query->equals('creation_progress', Tx_Appointments_Domain_Model_Appointment::FINISHED);
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
						'beginTime' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING
				)
		)->execute()->toArray();

		if ($rearrangePerHours > 0) {
			return $this->rearrangeAppointmentArray($result, $rearrangePerHours); #@SHOULD call this method separately, but where to define it?
		}

		return $result;
	}

	/**
	 * Returns all objects of this repository that take place at or somewhere during the same time
	 * as the given appointment. Note that appointments are allowed to overlap in their reserveblocks.
	 * (aka "BETWEEN Minutes") The query adheres to that rule. No expired appointments.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment
	 * @return array An array of objects, empty if no objects found
	 */
	public function findCrossAppointments(Tx_Appointments_Domain_Model_Appointment $appointment) {
		$query = $this->createQuery();

		$beginReserved = $appointment->getBeginReserved()->getTimestamp();
		$endReserved = $appointment->getEndReserved()->getTimestamp();
		$beginTime = $appointment->getBeginTime()->getTimestamp();
		$endTime = $appointment->getEndTime()->getTimestamp();
		$exclusive = $appointment->getType()->getExclusiveAvailability();

		$constraint = array(
			//apparently, if $agenda isn't validated separately, its lazy storages aren't resolved, which generates an exception, hence we'll stick with its uid
			$query->equals('agenda', $appointment->getAgenda()->getUid()),
			$query->logicalNot(
					$query->equals('uid', $appointment->getUid())
			),
			$query->logicalNot(
					$query->equals('creation_progress', Tx_Appointments_Domain_Model_Appointment::EXPIRED)
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
		if ($exclusive) {
			$constraint[] = $query->equals('type', $appointment->getType());
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
	 * @param integer $expireMinutes The number of minutes since creation date
	 * @return array An array of objects, empty if no objects found
	 */
	public function findExpiredUnfinished($expireMinutes = 15) {
		if ($expireMinutes < 1) {
			$expireMinutes = 15;
		}

		$query = $this->createQuery();
		$result = $query->matching(
				$query->logicalAnd( array(
						$query->lessThanOrEqual('crdate', time() - ($expireMinutes * 60)),
						$query->equals('creation_progress', Tx_Appointments_Domain_Model_Appointment::UNFINISHED)
					)
				)
		)->execute()->toArray();
		return $result;
	}

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
	protected function rearrangeAppointmentArray($array, $perHours) {
		//timeblock hours, includes 24 for structural purposes
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
			$resultArray[$resultArrayKey][] = $appointment;
		}
		return $resultArray;
	}

	/**
	 * Persists all changes manually.
	 *
	 * @return void
	 */
	public function persistChanges() {
		$this->persistenceManager->persistRepository($this);
	}

}
?>
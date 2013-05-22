<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
 * Agenda Controller
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Controller_AgendaController extends Tx_Appointments_MVC_Controller_AppointmentsActionController {
	#@TODO transform core functionality to a widget, then change the plugin(s?) so that the widget and the appointment-listAction are easily interchangable
	/**
	 * Indicates if user needs to be logged in to access action methods
	 *
	 * @var boolean
	 */
	protected $requireLogin = FALSE; #@SHOULD be configurable?

	/**
	 * action show month
	 *
	 * @param integer $monthModifier Modifies the displayed month
	 * @return void
	 */
	public function showMonthAction($monthModifier = 0) {
		$this->showGeneral('createAgendaMonth','month',$monthModifier);
	}

	/**
	 * action show weeks
	 *
	 * @param integer $weeksModifier Modifies the displayed weeks
	 * @return void
	 */
	public function showWeeksAction($weeksModifier = 0) { #@TODO wait, why does he get an integerValidation in actionController, but do values get set to string? What's happening here?
		$this->showGeneral('createAgendaWeeks','weeks',$weeksModifier);
	}

	/**
	 * Logic for all 'show' actions
	 *
	 * @param string $creationFunction The function which creates the container
	 * @param string $containerName The name of the container in the template
	 * @param integer $modifier Modifies the displayed units of time
	 * @return void
	 */
	protected function showGeneral($creationFunction, $containerName, $modifier = 0) { #@TODO can this be done in initialize instead?
		$allowTypes = $this->getTypes();
		#@TODO enable/disable whether superuser type APPOINTMENTS should be SHOWN to non-superusers? or even better: what about picking showTypes separately?
		$superUser = $this->userService->isInGroup($this->settings['suGroup']);
		$showTypes = $superUser ? $allowTypes : (
				empty($this->typeUidArray) ? $this->typeRepository->findAll(TRUE) : $this->typeRepository->findIn($this->typeUidArray,TRUE)
		);

		$allowTypes = $allowTypes->toArray(); //we need them to be array because their args in repository function isn't a queryResult @ all uses
		$showTypes = $showTypes->toArray(); #@FIXME is this even alright when showTypes == allowTypes? and why would we even? I can do it once in those cases

		$modifier = intval($modifier);
		$container = $this->$creationFunction($modifier,$this->agenda,$showTypes,$allowTypes);
		$this->view->assign('modifier', $modifier);
		$this->view->assign($containerName, $container);

		$currentDate = strftime('%d-%m-%Y');
		$this->view->assign('currentDate', $currentDate);
		$this->view->assign('agenda', $this->agenda);
	}

	/**
	 * Create and return a Month object for display as calendar/agenda.
	 *
	 * @param integer $monthModifier Relative modifier of month to get
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda to display appointments from
	 * @param array $showTypes Types to show on the agenda
	 * @param array $allowTypes Types to allow on the agenda
	 * @return Tx_Appointments_Domain_Model_Agenda_Month
	 */
	protected function createAgendaMonth($monthModifier, Tx_Appointments_Domain_Model_Agenda $agenda, array $showTypes, array $allowTypes) {
		$month = new Tx_Appointments_Domain_Model_Agenda_Month();

		$start = new DateTime(); //will represent the first minute of the month
		$start->setDate($start->format('Y'),$start->format('m'), 1)->setTime(0,0);
		//adjust $start per $monthModifier
		if ($monthModifier !== 0) {
			$operator = ($monthModifier > 0) ? '+' : '';
			$start->modify("$operator$monthModifier months");
		}

		//set standard month properties
		#$monthName = strftime('%B',$start->getTimestamp()); //DateTime::format doesn't heed locale, hence strftime()
		$monthName = Tx_Extbase_Utility_Localization::translate('tx_appointments_agenda.month_'.$start->format('n'), $this->extensionName);
		$month->setName($monthName);
		$month->setYear($start->format('Y'));

		//Number of days counting backwards until monday
		$month->setWeekdaysBeforeFirst(intval($start->format('N')) - 1); // [1 (Monday) - 7 (Sunday)]

		$this->setGeneralContainerProperties($month,$monthModifier,1,'month',$agenda,$showTypes,$allowTypes,$start);

		//Number of days counting forward until sunday
		$month->setWeekdaysAfterLast(7 - intval($start->modify('-1 day')->format('N')));

		return $month;
	}

	/**
	 * Create and return a Weeks object for display as calendar/agenda.
	 *
	 * @param integer $weeksModifier Relative modifier of weeks to get
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda to display appointments from
	 * @param array $showTypes Types to show on the agenda
	 * @param array $allowTypes Types to allow on the agenda
	 * @return Tx_Appointments_Domain_Model_Agenda_Weeks
	 */
	protected function createAgendaWeeks($weeksModifier, Tx_Appointments_Domain_Model_Agenda $agenda, array $showTypes, array $allowTypes) {
		$weeks = new Tx_Appointments_Domain_Model_Agenda_Weeks();
		$weeksBefore = intval($this->settings['agendaWeeksBeforeCurrent']);
		$weeksAfter = intval($this->settings['agendaWeeksAfterCurrent']);

		$start = new DateTime(); //will represent the first minute of the month
		$daysBack = $start->setTime(0,0)->format('N')-1;
		if ($daysBack) {
			$start->modify("-$daysBack days");
		}
		$start->modify("-$weeksBefore weeks")->setTime(0,0);
		$modWeeks = 1 + $weeksBefore + $weeksAfter;
		//adjust $start per $monthModifier
		if ($weeksModifier !== 0) {
			$totalWeeks = $modWeeks * $weeksModifier;
			$operator = ($weeksModifier > 0) ? '+' : '';
			$start->modify("$operator$totalWeeks weeks");
		}

		$this->setGeneralContainerProperties($weeks,$weeksModifier,$modWeeks,'weeks',$agenda,$showTypes,$allowTypes,$start);

		return $weeks;
	}

	/**
	 * Sets general agenda container properties.
	 *
	 * @param Tx_Appointments_Domain_Model_Agenda_AbstractContainer $container The container object to set properties in
	 * @param integer $modifier Agenda navigation modifier
	 * @param integer $modEndModifier Modifier value for container endtime
	 * @param string  $modEndUnit Modifier unit for container endtime
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda to display appointments from
	 * @param array $showTypes Types to show on the agenda
	 * @param array $allowTypes Types to allow on the agenda
	 * @param DateTime $start container starttime
	 * @return void
	 */
	protected function setGeneralContainerProperties(Tx_Appointments_Domain_Model_Agenda_AbstractContainer $container, $modifier, $modEndModifier, $modEndUnit, Tx_Appointments_Domain_Model_Agenda $agenda, array $showTypes, array $allowTypes, DateTime $start) {
		//set standard container properties
		$container->setMaxBack(-intval($this->settings['agendaBack']));
		$container->setBackModifier($modifier-1);
		$container->setMaxForward(intval($this->settings['agendaForward']));
		$container->setForwardModifier($modifier+1);

		//will represent the first minute of the next modifier unit
		$end = new DateTime($start->format('Y-m-d\TH:i:s'),$start->getTimezone());
		$end->modify("+$modEndModifier $modEndUnit");

		$freeSlotInMinutes = intval($this->settings['freeSlotInMinutes']);
		$allowCreateTypes = array();
		foreach ($allowTypes as $type) { #@TODO make this configurable
			$dateSlotStorage = $this->slotService->getDateSlots($type, $this->agenda, $freeSlotInMinutes);
			foreach ($dateSlotStorage as $dateSlot) {
				$dateSlot instanceof Tx_Appointments_Domain_Model_DateSlot;
				$allowCreateTypes[] = strftime('%d-%m-%Y',$dateSlot->getTimestamp());
			}
		}

		#@TODO can we do some caching here?
		//creates date objects in week storages for the container, because each day and week contain different properties
		$endTime = $end->getTimestamp();
		$holidays = $agenda->getHolidays();
		$appointments = $this->appointmentRepository->rearrangeAppointmentArray(
				$this->appointmentRepository->findBetween($agenda, $start, $end, $showTypes), 24
		);
		while ($start->getTimestamp() < $endTime) {
			$week = new Tx_Extbase_Persistence_ObjectStorage();
			for ($i = intval($start->format('N')); $i <= 7 && $start->getTimestamp() < $endTime; $i++) {
				$date = new Tx_Appointments_Domain_Model_Agenda_Date();
				$date->setDayNumber($start->format('j'));
				$monthShort = Tx_Extbase_Utility_Localization::translate('tx_appointments_agenda.month_s'.$start->format('n'), $this->extensionName); #@TODO this can be stored in an array or smth .. or not necessary if we use locales
				$date->setMonthShort($monthShort);
				$fulldate = $start->format('d-m-Y');
				$date->setDateString($fulldate);
				$date->setTimestamp($start->getTimestamp());
				$date->setIsHoliday(in_array($fulldate,$holidays));
				$date->setAllowCreate(in_array($fulldate,$allowCreateTypes));
				$fulldate .= ' 00:00:00';
				if (isset($appointments[$fulldate])) {
					foreach ($appointments[$fulldate] as $a) {
						$date->addAppointment($a);
					}
				}
				$week->attach($date);
				$start->modify('+1 day');
			}
			$container->addWeek($week);
		}
	}

}
?>
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
 * Agenda Controller
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Controller_AgendaController extends Tx_Appointments_MVC_Controller_SettingsOverrideController {

	/**
	 * agendaRepository
	 *
	 * @var Tx_Appointments_Domain_Repository_AgendaRepository
	 */
	protected $agendaRepository;

	/**
	 * appointmentRepository
	 *
	 * @var Tx_Appointments_Domain_Repository_AppointmentRepository
	 */
	protected $appointmentRepository;

	/**
	 * typeRepository
	 *
	 * @var Tx_Appointments_Domain_Repository_TypeRepository
	 */
	protected $typeRepository;

	/**
	 * frontendUserRepository
	 *
	 * @var Tx_Extbase_Domain_Repository_FrontendUserRepository
	 */
	protected $frontendUserRepository;

	/**
	 * frontendUserGroupRepository
	 *
	 * @var Tx_Extbase_Domain_Repository_FrontendUserGroupRepository
	 */
	protected $frontendUserGroupRepository;

	/**
	 * injectAgendaRepository
	 *
	 * @param Tx_Appointments_Domain_Repository_AgendaRepository $agendaRepository
	 * @return void
	 */
	public function injectAgendaRepository(Tx_Appointments_Domain_Repository_AgendaRepository $agendaRepository) {
		$this->agendaRepository = $agendaRepository;
	}

	/**
	 * injectAppointmentRepository
	 *
	 * @param Tx_Appointments_Domain_Repository_AppointmentRepository $appointmentRepository
	 * @return void
	 */
	public function injectAppointmentRepository(Tx_Appointments_Domain_Repository_AppointmentRepository $appointmentRepository) {
		$this->appointmentRepository = $appointmentRepository;
	}

	/**
	 * injectTypeRepository
	 *
	 * @param Tx_Appointments_Domain_Repository_TypeRepository $typeRepository
	 * @return void
	 */
	public function injectTypeRepository(Tx_Appointments_Domain_Repository_TypeRepository $typeRepository) {
		$this->typeRepository = $typeRepository;
	}

	/**
	 * injectFrontendUserRepository
	 *
	 * @param Tx_Extbase_Domain_Repository_FrontendUserRepository $frontendUserRepository
	 * @return void
	 */
	public function injectFrontendUserRepository(Tx_Extbase_Domain_Repository_FrontendUserRepository $frontendUserRepository) {
		$this->frontendUserRepository = $frontendUserRepository;
	}

	/**
	 * injectFrontendUserGroupRepository
	 *
	 * @param Tx_Extbase_Domain_Repository_FrontendUserGroupRepository $frontendUserGroupRepository
	 * @return void
	 */
	public function injectFrontendUserGroupRepository(Tx_Extbase_Domain_Repository_FrontendUserGroupRepository $frontendUserGroupRepository) {
		$this->frontendUserGroupRepository = $frontendUserGroupRepository;
	}

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
	public function showWeeksAction($weeksModifier = 0) {
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
	protected function showGeneral($creationFunction, $containerName, $modifier = 0) {
		global $TSFE; #@TODO move to a service/helper class?
		$superUser = FALSE;
		if ($TSFE->fe_user) {
			$feUser = $this->frontendUserRepository->findByUid($TSFE->fe_user->user['uid']);
			if ($feUser !== NULL) { #@TODO agenda uses this, list uses fed, let's unify all this crap AND make it possible for non-users to see things without error messages
				$suGroup = $this->frontendUserGroupRepository->findByUid($this->settings['suGroup']);
				if ($feUser->getUsergroup()->contains($suGroup)) {
					$superUser = TRUE;
				}
			}
		}

		$agendaUid = $this->settings['agendaUid'];
		$agenda = $this->agendaRepository->findByUid($agendaUid);
		$this->view->assign('agenda', $agenda);

		$typeArray = t3lib_div::trimExplode(',', $this->settings['appointmentTypeList'], 1);
		$allowTypes = empty($typeArray) ? $this->typeRepository->findAll($superUser) : $this->typeRepository->findIn($typeArray,$superUser);
		$showTypes = $superUser ? $allowTypes : ( #@TODO enable/disable whether superuser type APPOINTMENTS should be SHOWN to non-superusers?
				empty($typeArray) ? $this->typeRepository->findAll(TRUE) : $this->typeRepository->findIn($typeArray,TRUE)
		);

		if ($agenda !== NULL && !empty($allowTypes)) { //because there could be no agenda or no agenda selected, or even no types created
			$modifier = intval($modifier);
			$container = $this->$creationFunction($modifier,$agenda,$showTypes);
			$this->view->assign('modifier', $modifier);
			$this->view->assign($containerName, $container);

			$currentDate = strftime('%d-%m-%Y');
			$this->view->assign('currentDate', $currentDate);

			$blockedHours = $this->typeRepository->findBySmallestBlockedHours($allowTypes)->getBlockedHours();
			$blockedSeconds = $blockedHours * 60 * 60;
			$startCreateTime = strtotime($currentDate) + $blockedSeconds;
			$this->view->assign('startCreateTime', $startCreateTime); #@TODO replace this mechanism with one that is actually aware of available timeslots
		} else {
			#@TODO error?
		}
	}

	/**
	 * Create and return a Month object for display as calendar/agenda.
	 *
	 * @param integer $monthModifier Relative modifier of month to get
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda to display appointments from
	 * @param Tx_Extbase_Persistence_QueryResultInterface $types Types to show on the agenda
	 * @return Tx_Appointments_Domain_Model_Agenda_Month
	 */
	protected function createAgendaMonth($monthModifier, Tx_Appointments_Domain_Model_Agenda $agenda, Tx_Extbase_Persistence_QueryResultInterface $types) {
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

		$this->setGeneralContainerProperties($month,$monthModifier,1,'month',$agenda,$types,$start);

		//Number of days counting forward until sunday
		$month->setWeekdaysAfterLast(7 - intval($start->modify('-1 day')->format('N')));

		return $month;
	}

	/**
	 * Create and return a Weeks object for display as calendar/agenda.
	 *
	 * @param integer $weeksModifier Relative modifier of weeks to get
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda to display appointments from
	 * @param Tx_Extbase_Persistence_QueryResultInterface $types Types to show on the agenda
	 * @return Tx_Appointments_Domain_Model_Agenda_Weeks
	 */
	protected function createAgendaWeeks($weeksModifier, Tx_Appointments_Domain_Model_Agenda $agenda, Tx_Extbase_Persistence_QueryResultInterface $types) {
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

		$this->setGeneralContainerProperties($weeks,$weeksModifier,$modWeeks,'weeks',$agenda,$types,$start);

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
	 * @param Tx_Extbase_Persistence_QueryResultInterface $types Types to show on the agenda
	 * @param DateTime $start container starttime
	 * @return void
	 */
	protected function setGeneralContainerProperties(Tx_Appointments_Domain_Model_Agenda_AbstractContainer $container, $modifier, $modEndModifier, $modEndUnit, Tx_Appointments_Domain_Model_Agenda $agenda, Tx_Extbase_Persistence_QueryResultInterface $types, DateTime $start) {
		//set standard container properties
		$container->setMaxBack(-intval($this->settings['agendaBack']));
		$container->setBackModifier($modifier-1);
		$container->setMaxForward(intval($this->settings['agendaForward']));
		$container->setForwardModifier($modifier+1);

		//will represent the first minute of the next modifier unit
		$end = new DateTime($start->format('Y-m-d\TH:i:s'),$start->getTimezone());
		$end->modify("+$modEndModifier $modEndUnit");

		//creates date objects in week storages for the container, because each day and week contain different properties
		$endTime = $end->getTimestamp();
		$holidays = $agenda->getHolidays();
		$appointments = $this->appointmentRepository->findBetween($agenda, $start, $end, 0, 24, 0, $types);
		while ($start->getTimestamp() < $endTime) {
			$week = new Tx_Extbase_Persistence_ObjectStorage();
			for ($i = intval($start->format('N')); $i <= 7 && $start->getTimestamp() < $endTime; $i++) {
				$date = new Tx_Appointments_Domain_Model_Agenda_Date();
				$date->setDayNumber($start->format('j'));
				$monthShort = Tx_Extbase_Utility_Localization::translate('tx_appointments_agenda.month_s'.$start->format('n'), $this->extensionName); #@FIXME this can be stored in an array or smth .. or not necessary if we use locales
				$date->setMonthShort($monthShort);
				$fulldate = $start->format('d-m-Y');
				$date->setDateString($fulldate);
				$date->setTimestamp($start->getTimestamp());
				$date->setIsHoliday(in_array($fulldate,$holidays));
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
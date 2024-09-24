<?php

namespace Innologi\Appointments\Controller;

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
use Innologi\Appointments\Domain\Model\Agenda;
use Innologi\Appointments\Domain\Model\Agenda\{AbstractContainer, Date, Month, Weeks};
use Innologi\Appointments\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Agenda Controller
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AgendaController extends ActionController
{
    # @TODO transform core functionality to a widget, then change the plugin(s?) so that the widget and the appointment-listAction are easily interchangable
    /**
     * Indicates if user needs to be logged in to access action methods
     *
     * @var boolean
     */
    protected $requireLogin = false; # @LOW be configurable?

    /**
     * action show month
     *
     * @param integer $monthModifier Modifies the displayed month
     */
    public function showMonthAction($monthModifier = 0): ResponseInterface
    {
        return $this->showGeneral('createAgendaMonth', 'month', $monthModifier);
    }

    /**
     * action show weeks
     *
     * @param integer $weeksModifier Modifies the displayed weeks
     */
    public function showWeeksAction($weeksModifier = 0): ResponseInterface
    {
        return $this->showGeneral('createAgendaWeeks', 'weeks', $weeksModifier);
    }

    /**
     * Logic for all 'show' actions
     *
     * @param string $creationFunction The function which creates the container
     * @param string $containerName The name of the container in the template
     * @param integer $modifier Modifies the displayed units of time
     */
    protected function showGeneral($creationFunction, $containerName, $modifier = 0): ResponseInterface
    {
        $allowTypes = $this->getTypes();

        # @TODO enable/disable whether superuser type APPOINTMENTS should be SHOWN to non-superusers? or even better: what about picking showTypes separately?
        # $allowTypes = $allowTypes->toArray(); //we need them to be array because their args in repository function isn't a queryResult @ all uses
        $superUser = $this->userService->isInGroup($this->settings['suGroup']);
        # if ($superUser) {
        # $showTypes = $allowTypes;
        # } else {
        # $showTypes = empty($this->typeUidArray) ? $this->typeRepository->findAll(TRUE) : $this->typeRepository->findIn($this->typeUidArray,TRUE);
        # $showTypes = $showTypes->toArray();
        # }
        $showTypes = $superUser ? $allowTypes : $this->agenda->getTypes()->toArray();

        $modifier = intval($modifier);
        $container = $this->{$creationFunction}($modifier, $this->agenda, $showTypes, $allowTypes);
        $this->view->assign($containerName, $container);
        $this->view->assign('agenda', $this->agenda);

        return $this->htmlResponse();
    }

    /**
     * Create and return a Month object for display as calendar/agenda.
     *
     * @param integer $monthModifier Relative modifier of month to get
     * @param \Innologi\Appointments\Domain\Model\Agenda $agenda The agenda to display appointments from
     * @param array $showTypes Types to show on the agenda
     * @param array $allowTypes Types to allow on the agenda
     * @return \Innologi\Appointments\Domain\Model\Agenda\Month
     */
    protected function createAgendaMonth($monthModifier, Agenda $agenda, array $showTypes, array $allowTypes)
    {
        $month = new Month();

        $start = new \DateTime(); // will represent the first minute of the month
        $start->setDate($start->format('Y'), $start->format('m'), 1)->setTime(0, 0);
        // adjust $start per $monthModifier
        if ($monthModifier !== 0) {
            $operator = ($monthModifier > 0) ? '+' : '';
            $start->modify("{$operator}{$monthModifier} months");
        }

        // set standard month properties
        # $monthName = strftime('%B',$start->getTimestamp()); //DateTime::format doesn't heed locale, hence strftime()
        $monthName = LocalizationUtility::translate('tx_appointments_agenda.month_' . $start->format('n'), $this->extensionName);
        $month->setName($monthName);
        $month->setYear($start->format('Y'));

        // Number of days counting backwards until monday
        $month->setWeekdaysBeforeFirst(intval($start->format('N')) - 1); // [1 (Monday) - 7 (Sunday)]

        $this->setGeneralContainerProperties($month, $monthModifier, 1, 'month', $agenda, $showTypes, $allowTypes, $start);

        // Number of days counting forward until sunday
        $month->setWeekdaysAfterLast(7 - intval($start->modify('-1 day')->format('N')));

        return $month;
    }

    /**
     * Create and return a Weeks object for display as calendar/agenda.
     *
     * @param integer $weeksModifier Relative modifier of weeks to get
     * @param \Innologi\Appointments\Domain\Model\Agenda $agenda The agenda to display appointments from
     * @param array $showTypes Types to show on the agenda
     * @param array $allowTypes Types to allow on the agenda
     * @return \Innologi\Appointments\Domain\Model\Agenda\Weeks
     */
    protected function createAgendaWeeks($weeksModifier, Agenda $agenda, array $showTypes, array $allowTypes)
    {
        $weeks = new Weeks();
        $weeksBefore = intval($this->settings['agendaWeeksBeforeCurrent']);
        $weeksAfter = intval($this->settings['agendaWeeksAfterCurrent']);

        $start = new \DateTime(); // will represent the first minute of the month
        $daysBack = $start->setTime(0, 0)->format('N') - 1;
        if ($daysBack) {
            $start->modify("-{$daysBack} days");
        }
        $start->modify("-{$weeksBefore} weeks")->setTime(0, 0);
        $modWeeks = 1 + $weeksBefore + $weeksAfter;
        // adjust $start per $monthModifier
        if ($weeksModifier !== 0) {
            $totalWeeks = $modWeeks * $weeksModifier;
            $operator = ($weeksModifier > 0) ? '+' : '';
            $start->modify("{$operator}{$totalWeeks} weeks");
        }

        $this->setGeneralContainerProperties($weeks, $weeksModifier, $modWeeks, 'weeks', $agenda, $showTypes, $allowTypes, $start);

        return $weeks;
    }

    /**
     * Sets general agenda container properties.
     *
     * @param \Innologi\Appointments\Domain\Model\Agenda\AbstractContainer $container The container object to set properties in
     * @param integer $modifier Agenda navigation modifier
     * @param integer $modEndModifier Modifier value for container endtime
     * @param string $modEndUnit Modifier unit for container endtime
     * @param \Innologi\Appointments\Domain\Model\Agenda $agenda The agenda to display appointments from
     * @param array $showTypes Types to show on the agenda
     * @param array $allowTypes Types to allow on the agenda
     * @param \DateTime $start container starttime
     */
    protected function setGeneralContainerProperties(AbstractContainer $container, $modifier, $modEndModifier, $modEndUnit, Agenda $agenda, array $showTypes, array $allowTypes, \DateTime $start)
    {
        // set standard container properties
        $container->setMaxBack(-intval($this->settings['agendaBack']));
        $container->setBackModifier($modifier - 1);
        $container->setMaxForward(intval($this->settings['agendaForward']));
        $container->setForwardModifier($modifier + 1);

        // will represent the first minute of the next modifier unit
        $end = new \DateTime($start->format('Y-m-d\TH:i:s'), $start->getTimezone());
        $end->modify("+{$modEndModifier} {$modEndUnit}");

        $allowCreateTypes = [];
        if ($this->settings['allowCreate']) { // is this peformance hog enabled?
            foreach ($allowTypes as $type) {
                $dateSlotStorage = $this->slotService->getDateSlots($type, $this->agenda);
                /** @var \Innologi\Appointments\Domain\Model\DateSlot $dateSlot */
                foreach ($dateSlotStorage as $dateSlot) {
                    $allowCreateTypes[(new \DateTime())->setTimestamp($dateSlot->getTimestamp())->format('d-m-Y')] = 1;
                }
            }
        }

        # @TODO can we do some caching here? the container is created from scratch every single time
        // creates date objects in week storages for the container, because each day and week contain different properties
        $endTime = $end->getTimestamp();
        $currentDate = (new \DateTime())->format('d-m-Y');
        $holidayArray = $agenda->getHolidayArray();
        $appointments = $this->appointmentRepository->rearrangeAppointmentArray($this->appointmentRepository->findBetween($agenda, $start, $end, $showTypes, 1));
        while ($start->getTimestamp() < $endTime) {
            $week = new ObjectStorage();
            for ($i = intval($start->format('N')); $i <= 7 && $start->getTimestamp() < $endTime; $i++) {
                $date = new Date();
                $date->setDayNumber($start->format('j'));
                $monthShort = LocalizationUtility::translate('tx_appointments_agenda.month_s' . $start->format('n'), $this->extensionName); # @TODO this can be stored in an array or smth .. or not necessary if we use locales
                $date->setMonthShort($monthShort);
                $fullDate = $start->format('d-m-Y');
                $date->setTimestamp($start->getTimestamp());
                $date->setIsToday($fullDate === $currentDate);
                $date->setIsHoliday(isset($holidayArray[$fullDate]));
                $date->setAllowCreate(isset($allowCreateTypes[$fullDate]));
                $fullDate .= ' 00:00:00';
                if (isset($appointments[$fullDate])) {
                    foreach ($appointments[$fullDate] as $a) {
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

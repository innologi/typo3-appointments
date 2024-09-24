<?php

namespace Innologi\Appointments\Task;

/***************************************************************
 *  Copyright notice
*
*  (c) 2013-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use Innologi\Appointments\Domain\Repository\AppointmentRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Core\Bootstrap;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Clean Up Scheduler Task
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CleanUpTask extends AbstractTask
{
    /**
     * Age
     *
     * @var integer
     */
    protected $age;

    /**
     * appointmentRepository
     *
     * @var AppointmentRepository
     */
    protected $appointmentRepository;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * Initialize repositories (DI doesn't work here)
     */
    protected function initRepositories()
    {
        $this->appointmentRepository = GeneralUtility::makeInstance(AppointmentRepository::class);
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
    }

    /**
     * Returns age
     *
     * @return integer
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Sets age
     *
     * @param integer $age
     */
    public function setAge($age)
    {
        $this->age = $age;
    }

    /**
     * Executes task business logic
     *
     * @return	boolean		True on success, false on failure
     */
    public function execute()
    {
        $bootstrap = GeneralUtility::makeInstance(Bootstrap::class);
        $bootstrap->initialize([
            'pluginName' => 'CleanupTask',
            'extensionName' => 'Appointments',
            'vendorName' => 'Innologi',
        ]);

        $this->initRepositories();

        $expiredAppointments = $this->appointmentRepository->findExpiredByAge($this->age);
        if ($expiredAppointments->count() > 0) {
            foreach ($expiredAppointments as $appointment) {
                $this->appointmentRepository->remove($appointment);
            }
            $this->persistenceManager->persistAll();
        }

        return true;
    }
}

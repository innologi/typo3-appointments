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
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Task\Enumeration\Action;

/**
 * Additional Field Provider for CleanUp Task. Adds 'Age' field.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CleanUpTaskAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    /**
     * Additional field
     *
     * @var string
     */
    protected $field = 'age';

    /**
     * Add field to scheduler form.
     *
     * @param array $taskInfo Values of the fields from the add/edit task form
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task The task object being edited. Null when adding a task!
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the scheduler backend module
     * @return array A two dimensional array, array('Identifier' => array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
     */
    public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule)
    {
        $field = $this->field;
        //set field value
        if (empty($taskInfo[$field])) {
            if ($schedulerModule->getCurrentAction()->equals(Action::EDIT)) { //existing task, meaning there is a value
                $taskInfo[$field] = $task->getAge();
            } else {
                $taskInfo[$field] = '';
            }
        }

        //return field config
        $fieldID = 'task_' . $field;
        $additionalFields = [
            $fieldID => [
                'code' => '<input type="text" name="tx_scheduler[' . $field . ']" id="' . $fieldID . '" value="' . htmlspecialchars((string) $taskInfo[$field]) . '" size="8" />',
                'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_be.xml:tx_appointments_task_label.' . $field,
                'cshKey' => 'tx_appointments_csh_task_clean_up',
                'cshLabel' => $fieldID,
            ],
        ];
        return $additionalFields;
    }

    /**
     * Validates the fields' values
     *
     * @param array $submittedData An array containing the data submitted by the add/edit task form
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the scheduler backend module
     * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule)
    {
        //validate age
        if (!is_numeric($submittedData[$this->field]) || intval($submittedData[$this->field]) < 1) {
            // @extensionScannerIgnoreLine
            $this->addMessage(
                $GLOBALS['LANG']->sL('LLL:EXT:appointments/Resources/Private/Language/locallang_be.xml:tx_appointments_task_noAge'),
                FlashMessage::ERROR,
            );
            $result = false;
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * Save field value in task object
     *
     * @param array $submittedData An array containing the data submitted by the add/edit task form
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task Reference to the task object
     */
    public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task): void
    {
        $task->setAge(intval($submittedData[$this->field]));
    }
}

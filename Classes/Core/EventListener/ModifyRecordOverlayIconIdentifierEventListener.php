<?php

namespace Innologi\Appointments\Core\EventListener;

/***************************************************************
 *  Copyright notice
*
*  (c) 2025 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Imaging\Event\ModifyRecordOverlayIconIdentifierEvent;

/**
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ModifyRecordOverlayIconIdentifierEventListener
{
    #[AsEventListener('appointments/core/modify-record-overlay-icon-identifier')]
    public function __invoke(ModifyRecordOverlayIconIdentifierEvent $event): void
    {
        if ($event->getTable() === 'tx_appointments_domain_model_appointment' && isset($event->getRow()['creation_progress'])) {
            $creationProgressState = \intval($event->getRow()['creation_progress']);
            switch ($creationProgressState) {
                case 1:
                    $event->setOverlayIconIdentifier('overlay-missing');
                    break;
                case 2:
                    $event->setOverlayIconIdentifier('overlay-deleted');
                    break;
            }
        }
    }
}

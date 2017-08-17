<?php
namespace Innologi\Appointments\Hooks;
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

/**
 * Hook for t3lib_iconworks.
 *
 * Provides additional statuses to allow icon overlays based on an appointments' creation progress.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IconFactoryHook {
	// @FIX test if this works
	/**
	 * @param string $table
	 * @param array $row
	 * @param array $status
	 * @param string $iconName
	 * @return string the new (or given) $iconName
	 */
	function postOverlayPriorityLookup($table, array $row, array &$status, $iconName) {
		if ($table === 'tx_appointments_domain_model_appointment' && isset($row['creation_progress'])) { #@TODO address too!
			switch (intval($row['creation_progress'])) {
				case 1:
					$status['tx_appointments_unfinished'] = TRUE; #@TODO __can we add a pencil overlay?
					break;
				case 2:
					$status['tx_appointments_expired'] = TRUE;
			}
		}
		return $iconName;
	}
}
?>
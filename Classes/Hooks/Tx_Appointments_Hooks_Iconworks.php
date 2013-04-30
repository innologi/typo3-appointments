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
 * Hook for t3lib_iconworks.
 *
 * Provides additional statuses to allow icon overlays based on an appointments' creation progress.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Appointments_Hooks_Iconworks {

	/**
	 * Visualizes the creation progress of an appointment.
	 *
	 * @param string $table Name of the table
	 * @param array $row Record row containing the field values
	 * @param array $status Status to be used for rendering the icon
	 * @return void
	 */
	public function overrideIconOverlay($table, array $row, array &$status) {
		if ($table === 'tx_appointments_domain_model_appointment' && isset($row['creation_progress'])) { #@TODO address too!
			switch (intval($row['creation_progress'])) {
				case 1:
					$status['tx_appointments_unfinished'] = TRUE; #@SHOULD can we add a pencil overlay?
					break;
				case 2:
					$status['tx_appointments_expired'] = TRUE;
			}
		}
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/appointments/Classes/Hooks/Tx_Appointments_Hooks_Iconworks.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/appointments/Classes/Hooks/Tx_Appointments_Hooks_Iconworks.php']);
}
?>
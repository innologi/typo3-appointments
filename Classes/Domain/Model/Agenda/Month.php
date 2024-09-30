<?php

namespace Innologi\Appointments\Domain\Model\Agenda;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
 * Agenda Month
 *
 * A non-persisted entity for easy use in views.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Month extends AbstractContainer
{
    /**
     * Days before the first month day in the first week
     *
     * @var integer
     */
    protected $weekdaysBeforeFirst;

    /**
     * Days after the last month day in the final week
     *
     * @var integer
     */
    protected $weekdaysAfterLast;

    /**
     * Returns the Weekdays before first
     *
     * @return integer
     */
    public function getWeekdaysBeforeFirst()
    {
        return $this->weekdaysBeforeFirst;
    }

    /**
     * Sets the Weekdays before first
     *
     * @param integer $weekdaysBeforeFirst
     */
    public function setWeekdaysBeforeFirst($weekdaysBeforeFirst): void
    {
        $this->weekdaysBeforeFirst = $weekdaysBeforeFirst;
    }

    /**
     * Returns the Weekdays after last
     *
     * @return integer
     */
    public function getWeekdaysAfterLast()
    {
        return $this->weekdaysAfterLast;
    }

    /**
     * Sets the Weekdays after last
     *
     * @param integer $weekdaysAfterLast
     */
    public function setWeekdaysAfterLast($weekdaysAfterLast): void
    {
        $this->weekdaysAfterLast = $weekdaysAfterLast;
    }
}

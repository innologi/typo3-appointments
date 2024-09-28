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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Abstract Agenda Data Container
 *
 * A non-persisted entity for easy use in views.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractContainer extends AbstractEntity
{
    /**
     * name
     *
     * @var string
     */
    protected $name;

    /**
     * Year
     *
     * @var string
     */
    protected $year;

    /**
     * back modifier
     *
     * @var integer
     */
    protected $backModifier;

    /**
     * forward modifier
     *
     * @var integer
     */
    protected $forwardModifier;

    /**
     * Maximum back modifier
     *
     * @var integer
     */
    protected $maxBack;

    /**
     * Maximum forward modifier
     *
     * @var integer
     */
    protected $maxForward;

    /**
     * Array of week storages
     *
     * @var array<\TYPO3\CMS\Extbase\Persistence\ObjectStorage>
     */
    protected $weeks; #@LOW why not array<array> again?

    /**
     * __construct
     */
    public function __construct()
    {
        $this->weeks = [];
    }

    /**
     * Returns the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name
     *
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * Returns the year
     *
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Sets the year
     *
     * @param string $year
     */
    public function setYear($year): void
    {
        $this->year = $year;
    }

    /**
     * Returns the back modifier
     *
     * @return integer
     */
    public function getBackModifier()
    {
        return $this->backModifier;
    }

    /**
     * Sets the back modifier
     *
     * @param integer $backModifier
     */
    public function setBackModifier($backModifier): void
    {
        $this->backModifier = $backModifier;
    }

    /**
     * Returns the forward modifier
     *
     * @return integer
     */
    public function getForwardModifier()
    {
        return $this->forwardModifier;
    }

    /**
     * Sets the forward modifier
     *
     * @param integer $forwardModifier
     */
    public function setForwardModifier($forwardModifier): void
    {
        $this->forwardModifier = $forwardModifier;
    }

    /**
     * Returns maximum back modifier
     *
     * @return integer
     */
    public function getMaxBack()
    {
        return $this->maxBack;
    }

    /**
     * Sets the maximum back modifier
     *
     * @param integer $maxBack
     */
    public function setMaxBack($maxBack): void
    {
        $this->maxBack = $maxBack;
    }

    /**
     * Returns the maximum forward modifier
     *
     * @return integer
     */
    public function getMaxForward()
    {
        return $this->maxForward;
    }

    /**
     * Sets the maximum forward modifier
     *
     * @param integer $maxForward
     */
    public function setMaxForward($maxForward): void
    {
        $this->maxForward = $maxForward;
    }

    /**
     * Returns whether back is allowed
     *
     * @return boolean
     */
    public function getCanBack()
    {
        return $this->maxBack <= $this->backModifier;
    }

    /**
     * Returns whether forward is allowed
     *
     * @return boolean
     */
    public function getCanForward()
    {
        return $this->maxForward >= $this->forwardModifier;
    }

    /**
     * Adds a week storage
     */
    public function addWeek(ObjectStorage $week): void
    {
        $this->weeks[] = $week;
    }

    /**
     * Returns the weeks
     *
     * @return array
     */
    public function getWeeks()
    {
        return $this->weeks;
    }

    /**
     * Sets the weeks
     *
     * @param array $weeks
     */
    public function setWeeks($weeks): void
    {
        $this->weeks = $weeks;
    }
}

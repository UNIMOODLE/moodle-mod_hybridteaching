<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Behat hybridteaching-related steps definitions.
 *
 * @package    hybridteaching
 * @category   hybridteaching
 * @copyright  2023 ISYC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * hybridteaching-related steps definitions.
 *
 * @package    hybridteaching
 * @category   hybridteaching
 * @copyright  2023 ISYC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_hybridteaching extends behat_base {

    /**
     * @Given /^the session date is set to the current date and time plus 1 hour$/
     */
    public function theSessionDateIsSetToTheCurrentDateAndTimePlusOneHour() {
        $currentdatetime = new \DateTime();

        $currentdatetime->modify('+1 hour');

        $year = $currentdatetime->format('Y');
        $month = $currentdatetime->format('n');
        $day = $currentdatetime->format('j');
        $hour = $currentdatetime->format('G');
        $minute = $currentdatetime->format('i');

        $this->getSession()->getPage()->selectFieldOption('starttime[day]', $day);
        $this->getSession()->getPage()->selectFieldOption('starttime[month]', $month);
        $this->getSession()->getPage()->selectFieldOption('starttime[year]', $year);
        $this->getSession()->getPage()->selectFieldOption('starttime[hour]', $hour);
        $this->getSession()->getPage()->selectFieldOption('starttime[minute]', $minute);
    }

    /**
     * @Given /^I confirm the dialog$/
     * 
     */
    public function iConfirmTheDialog() {
        $this->getSession()->getDriver()->executeScript('window.confirm = function () { return true; };');
    }

    /**
     * Click on the 'More' link if it exists, otherwise click on 'Sessions'.
     *
     * @Given /^I click on "More" if it exists otherwise "Sessions"$/
     */
    public function iClickOnMoreIfExistsOtherwiseSessions() {
        $morebutton = $this->getSession()->getPage()->find('css', '.secondary-navigation .moremenu .more-nav .dropdownmoremenu');

        if ($morebutton !== null && $morebutton->isVisible()) {
            $morebutton->click();
        }
    
        $this->getSession()->getPage()->findLink('Sessions')->click();
    }
}

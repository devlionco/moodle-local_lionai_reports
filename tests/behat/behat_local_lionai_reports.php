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
 * Behat local_lionai_reports related steps definitions.
 *
 * @package    local_lionai_reports
 * @category   test
 * @copyright  2023 Devlion <info@devlion.co>
 * @author     Alex P
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL used, this file may be required by behat before including /config.php.

require_once(__DIR__ . '../../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\FatalThrowableError;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;

/**
 * Contains functions used by local_lionai_reports to test functionality.
 *
 * @package    local_lionai_reports
 * @category   test
 * @copyright  2023 Devlion <info@devlion.co>
 * @author     Alex P
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_lionai_reports extends behat_base {
    /**
     * I type the text :arg1 into the input with class :arg2
     *
     * @param string $arg1
     * @param string $arg2
     */
    public function i_type_the_text_into_the_input_with_class(string $arg1, string $arg2) {
        $input = $this->getSession()->getPage()->find('css', $arg2);

        if (!$input instanceof NodeElement) {
            throw new Exception('Could not find a textarea element with class "' . $arg2 . '".');
        }

        $input->setValue($arg1);
    }
}

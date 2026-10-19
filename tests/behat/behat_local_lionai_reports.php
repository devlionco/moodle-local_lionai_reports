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
     * @Given /^I type the text "(?P<text_string>(?:[^"]|\\")*)" into the input with class "(?P<class_string>(?:[^"]|\\")*)"$/
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

    /**
     * Creates a LionAI report whose history already holds the given SQL, without calling the AI service.
     *
     * @Given /^a LionAI report named "([^"]*)" exists with the query "([^"]*)"$/
     * @param string $name
     * @param string $sql
     */
    public function a_lionai_report_exists_with_the_query(string $name, string $sql) {
        global $DB;

        $now = time();
        $DB->insert_record('local_lionai_reports', (object) [
            'name' => $name,
            'userid' => $DB->get_field('user', 'id', ['username' => 'admin'], MUST_EXIST),
            'options' => json_encode(['history' => [
                ['role' => 'user', 'content' => 'Prepared prompt'],
                ['role' => 'assistant', 'content' => $sql],
            ]]),
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
    }

    /**
     * Opens the LionAI report with the given name.
     *
     * @Given /^I open the LionAI report named "([^"]*)"$/
     * @param string $name
     */
    public function i_open_the_lionai_report_named(string $name) {
        global $DB;

        $id = $DB->get_field_select(
            'local_lionai_reports',
            'id',
            $DB->sql_compare_text('name') . ' = ' . $DB->sql_compare_text(':name'),
            ['name' => $name],
            MUST_EXIST
        );
        $this->execute('behat_general::i_visit', ['/local/lionai_reports/index.php?id=' . $id]);
    }
}

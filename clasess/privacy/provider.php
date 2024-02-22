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
 * Privacy provider for plugin.
 *
 * @package    local_lionai_reports
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lionai_reports\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;

/**
 * Privacy provider for plugin
 */
class provider implements
    // This portfolio plugin does not store any data itself.
    \core_privacy\local\metadata\provider {
    /**
     * Returns meta data about this plugin.
     *
     * @param   collection $collection The initialised collection to add items to.
     * @return  collection A listing of user data stored through this plugin.
     */
    public static function get_metadata(collection $collection) :collection {

        $collection->add_database_table('local_lionai_reports', [
                'userid' => 'privacy:metadata:local_lionai_reports:userid',
                'options' => 'privacy:metadata:local_lionai_reports:options',
        ], 'privacy:metadata:local_lionai_reports');

        return $collection;
    }
}

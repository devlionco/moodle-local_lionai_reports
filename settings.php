<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     local_lionai_reports
 * @category    admin
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_admin\local\externalpage\accesscallback;
use core_reportbuilder\permission;

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins',
            new admin_category('local_lionai_reports_settings', new lang_string('pluginname', 'local_lionai_reports')));
    $settingspage = new admin_settingpage('managelocal_lionai_reports', new lang_string('pluginname', 'local_lionai_reports'));

    if ($ADMIN->fulltree) {
        $settingspage->add(new admin_setting_configpasswordunmask('local_lionai_reports/lionai_reports_apikey',
                get_string('lionai_reports_apikey', 'local_lionai_reports'),
            get_string('lionai_reports_apikeyinfo', 'local_lionai_reports'), '9vDfmIZIDC9JIv1XUCRKwM0pYXKIp76x', PARAM_RAW, 128));

        $settingspage->add(new admin_setting_configtext('local_lionai_reports/lionai_reports_apiurl',
                get_string('lionai_reports_apiurl', 'local_lionai_reports'), '',
                'https://apireprot.learnapp.io/LionAI/index.php', PARAM_URL, 35));

        $settingspage->add(new admin_setting_configcheckbox('local_lionai_reports/lionai_reports_allsee',
                get_string('lionai_reports_allsee', 'local_lionai_reports'),
                get_string('lionai_reports_allsee_info', 'local_lionai_reports'), 1));

        $settingspage->add(new admin_setting_configtext('local_lionai_reports/lionai_reports_limitrecords',
                get_string('lionai_reports_limitrecords', 'local_lionai_reports'),
            get_string('lionai_reports_limitrecordsinfo', 'local_lionai_reports'), '10', PARAM_INT, 3));

    }

    $ADMIN->add('localplugins', $settingspage);

    $ADMIN->add(
        'reports', new admin_category(
            'lionai_reports',
            new lang_string('allreports', 'local_lionai_reports')
        )
    );

    $ADMIN->add(
        'lionai_reports', new accesscallback(
            'allreports',
            get_string('allreports', 'local_lionai_reports'),
            (new moodle_url('/local/lionai_reports'))->out(),
            static function (accesscallback $accesscallback): bool {
                return true;
            }
        )
    );

}



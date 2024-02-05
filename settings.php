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
 * Settings plugin.
 *
 * @package     local_greetings
 * @copyright   2024 Nursandi <echo.nursandi@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Moodle IntelliSense for global variables.
 *
 * @var \admin_root $ADMIN
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/theme/eduhub/lib/helpers.php');

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_greetings', get_string('pluginname', 'local_greetings'));

    $ADMIN->add('localplugins', $settings);

    if ($ADMIN->fulltree) {
        require_once($CFG->dirroot . '/local/greetings/lib.php');

        $settings->add(new admin_setting_configtext(
            'local_greetings/messagecardbgcolor',
            get_string('messagecardbgcolor', 'local_greetings'),
            get_string('messagecardbgcolordesc', 'local_greetings'),
            '#FFFFFF'
        ));

        $settings->add(new admin_setting_configtext(
            'local_greetings/messagecardtextcolor',
            get_string('messagecardtextcolor', 'local_greetings'),
            get_string('messagecardtextcolordesc', 'local_greetings'),
            '#000000'
        ));
    }

}
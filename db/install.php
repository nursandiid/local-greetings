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
 * Install plugin
 *
 * @package    local_greetings
 * @copyright  2024 Nursandi <echo.nursandi@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define install steps.
 */
function xmldb_local_greetings_install() {
    global $DB;

    $dbman = $DB->get_manager();
    $table = new xmldb_table('local_greetings_messages');

    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
        $fields = [
            new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, true, null),
            new xmldb_field('message', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null),
            new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null),
            new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null),
        ];

        array_map(fn ($field) => $dbman->add_field($table, $field), $fields);
    }

    $key = new xmldb_key('local_greetings_messages_fk', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
    $dbman->add_key($table, $key);

    return true;
}
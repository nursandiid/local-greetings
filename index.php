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
 * Greetings
 *
 * @package    local_greetings
 * @copyright  2024 Nursandi <echo.nursandi@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @var \stdClass $CFG
 * @var \core_renderer $OUTPUT
 * @var \moodle_page $PAGE
 * @var \stdClass $SITE
 * @var \moodle_database $DB
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/greetings/lib.php');

global $DB;

$context = \context_system::instance();

// Require login if the plugin or Moodle is configured to force login.
if ($CFG->forcelogin) {
    require_login();
}

if (isguestuser()) {
    throw new moodle_exception('noguest');
}

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/greetings'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('greetings', 'local_greetings'));
$PAGE->set_heading(get_string('greetings', 'local_greetings'));

$allowview = has_capability('local/greetings:viewmessages', $context);
$allowpost = has_capability('local/greetings:postmessages', $context);
$editselectedpost = has_capability('local/greetings:editselectedmessage', $context);
$deleteanypost = has_capability('local/greetings:deleteanymessage', $context);

require_capability('local/greetings:viewmessages', $context);
require_capability('local/greetings:postmessages', $context);

$templatecontext = [
    'title' => local_greetings_get_greeting($USER),
    'greetingsurl' => $PAGE->url,
    'sesskey' => sesskey(),
    'allowpost' => $allowpost,
    'allowview' => $allowview,
    'editselectedpost' => $editselectedpost,
    'deleteanypost' => $deleteanypost,
];

if ($allowview) {
    $userfields = \core_user\fields::for_name()->with_identity($context);
    $userfieldssql = $userfields->get_sql('u');

    $sql = "SELECT m.id, m.message, m.timecreated {$userfieldssql->selects} 
              FROM {local_greetings_messages} m 
        INNER JOIN {user} u ON u.id = m.userid
             WHERE u.id = {$USER->id}
          ORDER BY m.timecreated";
    $records = $DB->get_records_sql($sql);

    $templatecontext = [
        ...$templatecontext,
        'messages' => array_values($records),
        'cardbgcolor' => get_config('local_greetings', 'messagecardbgcolor'),
        'cardtextcolor' => get_config('local_greetings', 'messagecardtextcolor')
    ];
}

if ($allowpost && $editselectedpost) {
    $messageform = new \local_greetings\form\message_form();
    $templatecontext['messageform'] = $messageform->render();

    if ($messageform->get_data()) {
        $id = required_param('id', PARAM_TEXT);
        $message = required_param('message', PARAM_TEXT);

        if (empty($message)) {
            throw new \moodle_exception('missingparam', '', '', 'message');
        }

        if (empty($id)) {
            $record = new stdClass;
            $record->message = $message;
            $record->timecreated = time();
            $record->userid = $USER->id;

            $DB->insert_record('local_greetings_messages', $record);
            redirect(
                $PAGE->url,
                get_string('successfullycreated', 'local_greetings'), 
                3, 
                \core\output\notification::NOTIFY_SUCCESS
            );
        } else {
            $DB->update_record('local_greetings_messages', compact('id', 'message'));
            redirect(
                $PAGE->url,
                get_string('successfullyupdated', 'local_greetings'), 
                3, 
                \core\output\notification::NOTIFY_SUCCESS
            );
        }
    }
}

$action = optional_param('action', '', PARAM_TEXT);
if ($deleteanypost && $action == 'del') {
    require_sesskey();
    $id = required_param('id', PARAM_TEXT);

    $DB->delete_records('local_greetings_messages', ['id' => $id]);
    redirect(
        $PAGE->url, 
        get_string('successfullydeleted', 'local_greetings'), 
        3, 
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// $view = new \local_greetings\output\view('local_greetings/view', $templatecontext);
// echo $OUTPUT->render($view); 

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_greetings/view', $templatecontext);
echo $OUTPUT->footer();
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
 * Moodle IntelliSense for global variables.
 *
 * Configuration settings for Moodle.
 * @var \stdClass $CFG
 *
 * The renderer responsible for generating HTML output in the Eduhub theme.
 * @var \theme_eduhub\output\core_renderer $OUTPUT
 *
 * Represents the current page being displayed in Moodle.
 * @var \moodle_page $PAGE
 *
 * Information about the site, including its name and other relevant details.
 * @var \stdClass $SITE
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/greetings/lib.php');
require_once($CFG->dirroot . '/theme/eduhub/lib/helpers.php');

$context = \context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/greetings/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading(get_string('greetings', 'local_greetings'));

require_login();

if (isguestuser()) {
    throw new moodle_exception('noguest');
}

$PAGE->set_secondary_navigation(true);

$baseurl = $PAGE->url;
$nextitemurl = new moodle_url($PAGE->url, ['item' => 1]);

// $date = new DateTime('tomorrow');

// $grade = 20.00 / 3;
// echo format_float($grade, 2);

// dd(get_string('greetings', 'local_greetings'));

echo $OUTPUT->header();
if (isloggedin()) {
    echo html_writer::tag('h5', local_greetings_get_greeting($USER));
} else {
    echo html_writer::tag(
        'h5',
        get_string('greetinguser', 'local_greetings')
    );
}

// echo html_writer::empty_tag('br');
// echo html_writer::tag('label', 'Your message');
// echo html_writer::tag('textarea', '', [
//     'type' => 'text',
//     'name' => 'username',
//     'placeholder' => get_string('username', 'local_greetings'),
//     'class' => 'col-lg-8 form-control'
// ]);
// echo html_writer::tag('button', 'Submit', [
//     'class' => 'btn btn-primary mt-3'
// ]);

global $DB;

// $DB->insert_record('user', [
// 'username' => 'test',
// 'password' => password_hash('123456', PASSWORD_BCRYPT),
// 'email' => 'test@gmail.com',
// 'firstname' => 'Test'
// ]);

// $userTest = $DB->get_record('user', ['username' => 'test']);
// dd($userTest);

$messageform = new \local_greetings\form\message_form();
$messageform->display();

if ($data = $messageform->get_data()) {
    $message = required_param('message', PARAM_TEXT);

    // if (!isloggedin()) {
    //     echo html_writer::start_div('alert alert-danger');
    //     echo html_writer::tag('p', 'You have to log in first', ['class' => 'mb-0']);
    //     echo html_writer::end_div();
    // }

    if (!empty($message)) {
        $record = new stdClass;
        $record->message = $message;
        $record->timecreated = time();
        $record->userid = $USER->id;

        $DB->insert_record('local_greetings_messages', $record);
    }
}

$userfields = \core_user\fields::for_name()->with_identity($context);
$userfieldssql = $userfields->get_sql('u');

$sql = "SELECT m.id, m.message, m.timecreated {$userfieldssql->selects} 
          FROM {local_greetings_messages} m 
    INNER JOIN {user} u ON u.id = m.userid
      ORDER BY m.timecreated";
$messages = $DB->get_records_sql($sql);

// dd($messages);

// if (isloggedin()) {
    echo $OUTPUT->heading('Output messages:', 6);
    echo html_writer::start_div('row');
    foreach ($messages as $message) {
        echo html_writer::start_div('col col-lg-3');
        echo html_writer::start_div('card');
        echo html_writer::start_div('card-body');
        echo html_writer::tag('p', get_string('postedby', 'local_greetings', fullname($message)), ['class' => 'mb-2 font-weight-bold']);
        echo html_writer::tag('p', format_text($message->message), ['class' => 'my-0']);
        echo html_writer::tag('small', userdate($message->timecreated));
        echo html_writer::end_div();
        echo html_writer::end_div();
        echo html_writer::end_div();
    }
    echo html_writer::end_div();
// }

echo $OUTPUT->footer();

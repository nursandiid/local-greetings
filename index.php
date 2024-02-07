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
 * @var \theme_eduhub\output\core_renderer $OUTPUT
 * @var \moodle_page $PAGE
 * @var \stdClass $SITE
 * @var \moodle_database $DB
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/greetings/lib.php');
require_once($CFG->dirroot . '/theme/eduhub/lib/helpers.php');

class greetings_controller {
    
    private $context;
    private $PAGE;
    private $USER;
    private $DB;
    private $OUTPUT;
    private $SITE;

    public function __construct() {
        /**
         * @var \moodle_page $PAGE
         * @var \stdClass $USER
         * @var \moodle_database $DB
         * @var \theme_eduhub\output\core_renderer $OUTPUT
         * @var \stdClass $SITE
         */
        global $PAGE, $USER, $DB, $OUTPUT, $SITE;

        $this->PAGE = $PAGE;
        $this->USER = $USER;
        $this->DB = $DB;
        $this->OUTPUT = $OUTPUT;
        $this->SITE = $SITE;
        $this->context = \context_system::instance();
    }

    /**
     * Setup page
     *
     * @return void
     */
    private function setup() {
        require_login();

        $this->PAGE->set_context($this->context);
        $this->PAGE->set_url(new moodle_url('/local/greetings/index.php'));
        $this->PAGE->set_pagelayout('standard');
        $this->PAGE->set_title($this->SITE->fullname);
        $this->PAGE->set_heading(get_string('greetings', 'local_greetings'));

        if (isguestuser()) {
            throw new moodle_exception('noguest');
        }

        $this->PAGE->set_secondary_navigation(true);
    }

    /**
     * Display messages
     *
     * @return void
     */
    private function display_messages() {
        $userfields = \core_user\fields::for_name()->with_identity($this->context);
        $userfieldssql = $userfields->get_sql('u');

        $sql = "SELECT m.id, m.message, m.timecreated {$userfieldssql->selects} 
                  FROM {local_greetings_messages} m 
            INNER JOIN {user} u ON u.id = m.userid
                 WHERE u.id = {$this->USER->id}
              ORDER BY m.timecreated";
        $messages = $this->DB->get_records_sql($sql);

        $editselectedpost = has_capability('local/greetings:editselectedmessage', $this->context);
        $deleteanypost = has_capability('local/greetings:deleteanymessage', $this->context);

        $allowview = has_capability('local/greetings:viewmessages', $this->context);
        if ($allowview) {
            $cardbgcolor = get_config('local_greetings', 'messagecardbgcolor');
            $cardtextcolor = get_config('local_greetings', 'messagecardtextcolor');

            echo $this->OUTPUT->heading('Output messages:', 5);
            echo html_writer::start_div('row', ['style' => 'row-gap: 30px;']);
            foreach ($messages as $message) {
                echo html_writer::start_div('col-6 col-lg-3');
                echo html_writer::start_div('card', ['style' => 'min-height: 220px; max-height: 220px; background: '. $cardbgcolor .'; color: ' . $cardtextcolor . ';']);
                echo html_writer::start_div('card-body d-flex flex-column');
                echo html_writer::tag('p', get_string('postedby', 'local_greetings', fullname($message)), ['class' => 'mb-2 font-weight-bold']);
                echo html_writer::tag('p', format_text($message->message), ['class' => 'my-0']);
                echo html_writer::tag('small', userdate($message->timecreated), ['class' => 'text-truncate']);
                if ($deleteanypost) {
                    echo html_writer::start_tag('div', array('class' => 'text-center mt-auto mb-2'));
                    echo html_writer::link(
                        new moodle_url(
                            '/local/greetings/index.php',
                            [
                                'action' => 'del', 
                                'sesskey' => sesskey(),
                                'id' => $message->id
                            ]
                        ),
                        $this->OUTPUT->pix_icon('t/delete', '') . get_string('delete'),
                        ['class' => 'btn btn-danger btn-block']
                    );
                    echo html_writer::end_tag('div');
                }
                if ($editselectedpost) {
                    echo html_writer::start_tag('div', array('class' => 'text-center'));
                    echo html_writer::link(
                        new moodle_url(
                            '/local/greetings/index.php',
                            [
                                'id' => $message->id,
                                'message' => $message->message,
                            ]
                        ),
                        $this->OUTPUT->pix_icon('i/edit', '') . get_string('edit'),
                        ['class' => 'btn btn-warning btn-block']
                    );
                    echo html_writer::end_tag('div');
                }
                echo html_writer::end_div();
                echo html_writer::end_div();
                echo html_writer::end_div();
            }
            echo html_writer::end_div();
        }
    }

    /**
     * Handle post message
     *
     * @param \local_greetings\form\message_form $messageform
     * @return bool
     */
    private function handle_post_message($messageform) {
        $allowpost = has_capability('local/greetings:postmessages', $this->context);

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
                $record->userid = $this->USER->id;

                $this->DB->insert_record('local_greetings_messages', $record);
                redirect(
                    new moodle_url('/local/greetings/index.php'), 
                    get_string('successfullycreated', 'local_greetings'), 
                    3, 
                    \core\output\notification::NOTIFY_SUCCESS
                );
            } else {
                $this->DB->update_record('local_greetings_messages', [
                    'id' => $id,
                    'message' => $message
                ]);
                redirect(
                    new moodle_url('/local/greetings/index.php'), 
                    get_string('successfullyupdated', 'local_greetings'), 
                    3, 
                    \core\output\notification::NOTIFY_SUCCESS
                );
            }
        }

        return $allowpost;
    }

    /**
     * Handle delete message
     *
     * @return never
     */
    private function handle_delete_message() {
        $deleteanypost = has_capability('local/greetings:deleteanymessage', $this->context);

        $action = optional_param('action', '', PARAM_TEXT);
        if ($action == 'del') {
            $id = required_param('id', PARAM_TEXT);
            require_sesskey();

            if ($deleteanypost) {
                $this->DB->delete_records('local_greetings_messages', ['id' => $id]);
                redirect($this->PAGE->url, get_string('successfullydeleted', 'local_greetings'), 3, \core\output\notification::NOTIFY_SUCCESS);
            } else {
                redirect($this->PAGE->url, 'You do not have a permission to delete a post', 3, \core\output\notification::NOTIFY_ERROR);
            }
        }
    }

    /**
     * Display header whether user is logged in or not
     *
     * @return void
     */
    private function display_header() {
        echo $this->OUTPUT->header();
        if (isloggedin()) {
            echo html_writer::tag('h5', local_greetings_get_greeting($this->USER));
        } else {
            echo html_writer::tag(
                'h5',
                get_string('greetinguser', 'local_greetings')
            );
        }
    }

    /**
     * Display footer
     *
     * @return void
     */
    private function display_footer() {
        echo $this->OUTPUT->footer();
    }

    /**
     * Render page
     *
     * @return void
     */
    public function render() {
        $this->setup();

        $messageform = new \local_greetings\form\message_form();
        $allowpost = $this->handle_post_message($messageform);

        $this->handle_delete_message();

        $this->display_header();

        if ($allowpost) {
            $messageform->display();
        }

        require_capability('local/greetings:postmessages', $this->context);

        $this->display_messages();

        require_capability('local/greetings:viewmessages', $this->context);

        $this->display_footer();
    }
}

global $DB;

$greetings = new greetings_controller;
$greetings->render();
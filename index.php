<?php

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

/**
 * Greetings
 *
 * @package    local_greetings
 * @copyright  2024 Nursandi
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
require_once($CFG->dirroot. '/local/greetings/lib.php');

$context = \context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/greetings/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading(get_string('greetings', 'local_greetings'));

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
    echo html_writer::tag('h5', get_string('greetinguser', 'local_greetings'));
}
echo html_writer::tag('input', '', [
    'type' => 'text',
    'name' => 'username',
    'placeholder' => get_string('username', 'local_greetings')
]);

echo $OUTPUT->footer();

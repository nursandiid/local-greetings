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
 * Message form
 *
 * @package    local_greetings
 * @copyright  2024 Nursandi <echo.nursandi@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_greetings\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class message_form extends \moodleform {
    public function definition() {
        $id = optional_param('id', '', PARAM_TEXT);
        $message = optional_param('message', '', PARAM_TEXT);

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_TEXT);
        $mform->setDefault('id', $id);

        $mform->addElement('textarea', 'message', get_string('yourmessage', 'local_greetings'));
        $mform->setType('message', PARAM_TEXT);
        $mform->setDefault('message', $message);
        $mform->addRule('message', get_string('err_required', 'form'), 'required', null, 'client');
        $mform->addRule('message', get_string('err_minlength', 'form', ['format' => 5]), 'minlength', 5, 'client');

        $submitlabel = get_string('submit');
        $mform->addElement('submit', 'submitmessage', $submitlabel);
    }
}

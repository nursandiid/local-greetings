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
 * Prints a particular instance
 *
 * @package    local_greetings
 * @copyright  2024 Nursandi <echo.nursandi@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_greetings\output;

use renderable;
use templatable;
use core\output\named_templatable;

defined('MOODLE_INTERNAL') || die();

class view implements renderable, templatable, named_templatable {
    /**
     * View template
     * 
     * @var string $view
     */
    protected $view;

    /**
     * Context
     * 
     * @var array|object $data
     */
    protected $data;

    /**
     * 
     * @param string $view
     * @param array|object $data
     */
    public function __construct($view, $data) {
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(\renderer_base $output) {
        return $this->data;
    }

    /**
     * Get the name of the template to use for this templatable.
     *
     * @param \renderer_base $renderer The renderer requesting the template name
     * @return string
     */
    public function get_template_name(\renderer_base $renderer): string {

        return $this->view;
    }
}
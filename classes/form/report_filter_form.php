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
 * Filter form for the imported certificates report.
 *
 * @package   local_certificateimport
 * @copyright 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_certificateimport\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Provides filtering controls for the report.
 */
class report_filter_form extends \moodleform {
    /**
     * Define fields.
     */
    public function definition(): void {
        $mform = $this->_form;
        $mform->updateAttributes(['method' => 'get']);

        $templateoptions = $this->_customdata['templateoptions'] ?? [];
        $filters = $this->_customdata['filters'] ?? [];

        $templateoptions = [0 => get_string('filter:template:any', 'local_certificateimport')] + $templateoptions;
        $statusoptions = [
            'all' => get_string('filter:status:any', 'local_certificateimport'),
            'queued' => get_string('issue:status:queued', 'local_certificateimport'),
            'active' => get_string('issue:status:active', 'local_certificateimport'),
            'revoked' => get_string('issue:status:revoked', 'local_certificateimport'),
            'missing' => get_string('issue:status:missing', 'local_certificateimport'),
        ];
        $perpageoptions = [
            10 => 10,
            25 => 25,
            50 => 50,
            100 => 100,
        ];

        $mform->addElement('select', 'templateid', get_string('filter:template', 'local_certificateimport'), $templateoptions);
        $mform->addElement('select', 'status', get_string('filter:status', 'local_certificateimport'), $statusoptions);
        $mform->addElement('text', 'user', get_string('filter:user', 'local_certificateimport'));
        $mform->setType('user', PARAM_RAW_TRIMMED);
        $mform->addElement(
            'date_selector',
            'datefrom',
            get_string('filter:datefrom', 'local_certificateimport'),
            ['optional' => true]
        );
        $mform->addElement(
            'date_selector',
            'dateto',
            get_string('filter:dateto', 'local_certificateimport'),
            ['optional' => true]
        );
        $mform->addElement('select', 'perpage', get_string('filter:perpage', 'local_certificateimport'), $perpageoptions);

        foreach (['templateid', 'status', 'user', 'datefrom', 'dateto', 'perpage'] as $field) {
            if (array_key_exists($field, $filters)) {
                $mform->setDefault($field, $filters[$field]);
            }
        }

        $buttons = [];
        $buttons[] = $mform->createElement(
            'submit',
            'submitbutton',
            get_string('filter:apply', 'local_certificateimport')
        );
        $buttons[] = $mform->createElement(
            'cancel',
            'resetbutton',
            get_string('filter:reset', 'local_certificateimport')
        );
        $mform->addGroup($buttons, 'actions', '', [' '], false);
    }
}

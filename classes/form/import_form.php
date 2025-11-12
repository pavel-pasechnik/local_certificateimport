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
 * Form that captures CSV + ZIP payload for certificate imports.
 *
 * @package   local_certificateimport
 * @copyright 2025 Pavel Pasechnik
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_certificateimport\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Upload form for the certificate import helper.
 */
class import_form extends \moodleform {
    /**
     * Defines form elements.
     */
    public function definition(): void {
        $mform = $this->_form;

        $templateoptions = $this->_customdata['templateoptions'] ?? [];
        $selectoptions = [0 => get_string('form:template:choose', 'local_certificateimport')] + $templateoptions;
        $mform->addElement('select', 'templateid', get_string('form:template', 'local_certificateimport'), $selectoptions);
        $mform->addHelpButton('templateid', 'form:template', 'local_certificateimport');
        $mform->addRule('templateid', get_string('form:error:required', 'local_certificateimport'), 'required', null, 'client');

        $csvoptions = [
            'accepted_types' => ['.csv'],
            'maxbytes' => 0,
        ];
        $mform->addElement('filepicker', 'csvfile', get_string('form:csvfile', 'local_certificateimport'), null, $csvoptions);
        $mform->addHelpButton('csvfile', 'form:csvfile', 'local_certificateimport');
        $mform->addRule('csvfile', get_string('form:error:required', 'local_certificateimport'), 'required', null, 'client');

        $zipoptions = [
            'accepted_types' => ['.zip'],
            'maxbytes' => 0,
        ];
        $mform->addElement('filepicker', 'zipfile', get_string('form:zipfile', 'local_certificateimport'), null, $zipoptions);
        $mform->addHelpButton('zipfile', 'form:zipfile', 'local_certificateimport');
        $mform->addRule('zipfile', get_string('form:error:required', 'local_certificateimport'), 'required', null, 'client');

        $this->add_action_buttons(false, get_string('form:submit', 'local_certificateimport'));
    }

    /**
     * Server-side validation for uploaded fields.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if (empty($data['templateid'])) {
            $errors['templateid'] = get_string('form:error:required', 'local_certificateimport');
        }
        if (!$this->get_new_filename('csvfile')) {
            $errors['csvfile'] = get_string('form:error:required', 'local_certificateimport');
        }
        if (!$this->get_new_filename('zipfile')) {
            $errors['zipfile'] = get_string('form:error:required', 'local_certificateimport');
        }

        return $errors;
    }
}

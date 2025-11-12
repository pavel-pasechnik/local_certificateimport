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
 * Lists imported certificates and allows CSV export.
 *
 * @package   local_certificateimport
 * @copyright 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/csvlib.class.php');

require_login();
$context = context_system::instance();
require_capability('local/certificateimport:import', $context);

$templateid = optional_param('templateid', 0, PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);

$PAGE->set_url(new moodle_url('/local/certificateimport/report.php', ['templateid' => $templateid]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('report:heading', 'local_certificateimport'));
$PAGE->set_heading(get_string('pluginname', 'local_certificateimport'));

$templateoptions = local_certificateimport_get_template_options();
$hasoptions = !empty($templateoptions);

if ($download === 'csv' && $templateid && $hasoptions && array_key_exists($templateid, $templateoptions)) {
    require_sesskey();
    $records = local_certificateimport_get_import_log($templateid);
    $csv = new csv_export_writer();
    $csv->set_filename('certificateimport-' . $templateid);
    $csv->add_data([
        get_string('report:col:userid', 'local_certificateimport'),
        get_string('report:col:username', 'local_certificateimport'),
        get_string('report:col:filename', 'local_certificateimport'),
        get_string('report:col:code', 'local_certificateimport'),
        get_string('report:col:imported', 'local_certificateimport'),
    ]);
    foreach ($records as $record) {
        $user = (object)[
            'firstname' => $record->firstname,
            'lastname' => $record->lastname,
            'firstnamephonetic' => $record->firstnamephonetic,
            'lastnamephonetic' => $record->lastnamephonetic,
            'middlename' => $record->middlename,
            'alternatename' => $record->alternatename,
        ];
        $csv->add_data([
            $record->userid,
            fullname($user),
            $record->filename ?: $record->storedfilename,
            $record->code,
            userdate($record->timeimported),
        ]);
    }
    $csv->download_file();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report:heading', 'local_certificateimport'));
echo html_writer::div(get_string('report:instructions', 'local_certificateimport'), 'alert alert-info');

if (!$hasoptions) {
    echo $OUTPUT->notification(get_string('error:notemplates', 'local_certificateimport'), \core\output\notification::NOTIFY_WARNING);
    echo $OUTPUT->footer();
    exit;
}

$formurl = new moodle_url('/local/certificateimport/report.php');
echo html_writer::start_tag('form', ['method' => 'get', 'action' => $formurl->out(false), 'class' => 'mb-4']);
echo html_writer::label(get_string('form:template', 'local_certificateimport'), 'id_templateid', ['class' => 'mr-2']);
echo html_writer::select(
    [0 => get_string('form:template:choose', 'local_certificateimport')] + $templateoptions,
    'templateid',
    $templateid,
    null,
    ['id' => 'id_templateid', 'class' => 'custom-select d-inline-block w-auto mr-2']
);
echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'value' => get_string('report:filter', 'local_certificateimport'),
    'class' => 'btn btn-primary',
]);
echo html_writer::end_tag('form');

if ($templateid && array_key_exists($templateid, $templateoptions)) {
    $records = local_certificateimport_get_import_log($templateid);

    if (!$records) {
        echo html_writer::div(get_string('report:none', 'local_certificateimport'), 'alert alert-secondary');
    } else {
        $exporturl = new moodle_url('/local/certificateimport/report.php', [
            'templateid' => $templateid,
            'download' => 'csv',
            'sesskey' => sesskey(),
        ]);
        $exportlink = html_writer::link($exporturl, get_string('report:export', 'local_certificateimport'), [
            'class' => 'btn btn-secondary mb-3',
        ]);
        echo html_writer::div($exportlink);

        $table = new html_table();
        $table->attributes['class'] = 'generaltable';
        $table->head = [
            get_string('report:col:userid', 'local_certificateimport'),
            get_string('report:col:username', 'local_certificateimport'),
            get_string('report:col:filename', 'local_certificateimport'),
            get_string('report:col:code', 'local_certificateimport'),
            get_string('report:col:imported', 'local_certificateimport'),
        ];

        foreach ($records as $record) {
            $user = (object)[
                'firstname' => $record->firstname,
                'lastname' => $record->lastname,
                'firstnamephonetic' => $record->firstnamephonetic,
                'lastnamephonetic' => $record->lastnamephonetic,
                'middlename' => $record->middlename,
                'alternatename' => $record->alternatename,
            ];
            $table->data[] = [
                $record->userid,
                fullname($user),
                s($record->filename ?: $record->storedfilename),
                s($record->code),
                userdate($record->timeimported),
            ];
        }

        echo html_writer::table($table);
    }
} else {
    echo html_writer::div(get_string('report:picktemplate', 'local_certificateimport'), 'alert alert-secondary');
}

echo $OUTPUT->footer();

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
 * Report that lists imported certificates with filtering/export.
 *
 * @package   local_certificateimport
 * @copyright 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_certificateimport_report');
$context = context_system::instance();

$filters = [
    'templateid' => optional_param('templateid', 0, PARAM_INT),
    'status' => optional_param('status', 'all', PARAM_ALPHA),
    'user' => optional_param('user', '', PARAM_RAW_TRIMMED),
    'datefrom' => optional_param('datefrom', 0, PARAM_INT),
    'dateto' => optional_param('dateto', 0, PARAM_INT),
    'perpage' => optional_param('perpage', 25, PARAM_INT),
];
$filters['perpage'] = min(100, max(10, $filters['perpage']));
$download = optional_param('download', '', PARAM_ALPHA);
$action = optional_param('action', '', PARAM_ALPHA);
$selected = optional_param_array('selected', [], PARAM_INT);

$baseparams = $filters;
if (!empty($download)) {
    $baseparams['download'] = $download;
}
$PAGE->set_url(new moodle_url('/local/certificateimport/report.php', $baseparams));
$PAGE->set_context($context);
$PAGE->set_title(get_string('report:issues:title', 'local_certificateimport'));
$PAGE->set_heading(get_string('report:issues:title', 'local_certificateimport'));

if ($action === 'reissue' && confirm_sesskey()) {
    if (empty($selected)) {
        \core\notification::warning(get_string('report:reissue:none', 'local_certificateimport'));
    } else {
        try {
            $summary = local_certificateimport_reissue_items($selected);
            if ($summary['success'] > 0) {
                \core\notification::success(get_string('report:reissue:success', 'local_certificateimport', $summary));
            }
            if ($summary['errors'] > 0) {
                \core\notification::error(get_string('report:reissue:errors', 'local_certificateimport', $summary));
            }
        } catch (moodle_exception $exception) {
            \core\notification::error($exception->getMessage());
        }
    }
    redirect(new moodle_url('/local/certificateimport/report.php', $filters));
}

$templateoptions = local_certificateimport_get_template_options();
$filterform = new \local_certificateimport\form\report_filter_form(null, [
    'templateoptions' => $templateoptions,
    'filters' => $filters,
]);
$filterform->set_data((object)$filters);

if ($filterform->is_cancelled()) {
    redirect(new moodle_url('/local/certificateimport/report.php'));
}
if ($data = $filterform->get_data()) {
    $params = [
        'templateid' => (int)$data->templateid,
        'status' => clean_param($data->status, PARAM_ALPHA),
        'user' => clean_param($data->user, PARAM_RAW_TRIMMED),
        'datefrom' => (int)$data->datefrom,
        'dateto' => (int)$data->dateto,
        'perpage' => min(100, max(10, (int)$data->perpage)),
    ];
    redirect(new moodle_url('/local/certificateimport/report.php', $params));
}

$table = new \local_certificateimport\table\issues_table('local_certificateimport_issues', $filters, $PAGE->url);
$table->is_downloading($download, 'certificateimport-issues', 'certificateimport-issues');
$table->show_download_buttons_at([TABLE_P_BOTTOM]);
$perpage = $filters['perpage'];

if ($table->is_downloading()) {
    $table->out(0, false);
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report:issues:title', 'local_certificateimport'));
echo html_writer::div(get_string('report:issues:description', 'local_certificateimport'), 'alert alert-info');

$filterform->display();

echo html_writer::start_tag('form', [
    'method' => 'post',
    'action' => $PAGE->url,
    'class' => 'local-certimport-bulkform',
]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'reissue']);
$table->out($perpage, true);

$buttonattributes = [
    'type' => 'submit',
    'class' => 'btn btn-primary mt-2',
];
echo html_writer::tag('button', get_string('report:reissue:selected', 'local_certificateimport'), $buttonattributes);
echo html_writer::end_tag('form');

echo $OUTPUT->footer();

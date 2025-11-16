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
 * Local fullname format overrides.
 *
 * @package   local_certificateimport
 * @copyright 2025 Pavel Pasechnik
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

$context = context_system::instance();
require_capability('local/certificateimport:import', $context);

$PAGE->set_url(new moodle_url('/local/certificateimport/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pagetitle', 'local_certificateimport'));
$PAGE->set_heading(get_string('pluginname', 'local_certificateimport'));

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/csvlib.class.php');

$action = optional_param('action', '', PARAM_ALPHA);
$batchid = optional_param('batchid', 0, PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);

$templateoptions = local_certificateimport_get_template_options();

$sessionresults = $SESSION->local_certificateimport_lastresults ?? [];
if ($action === 'register' && $batchid) {
    require_sesskey();
    if (!local_certificateimport_is_available()) {
        redirect(
            $PAGE->url,
            get_string('status:unavailable:details', 'local_certificateimport'),
            0,
            \core\output\notification::NOTIFY_WARNING
        );
    }
    try {
        $summary = local_certificateimport_register_batch($batchid);
        $summarydata = (object)[
            'success' => $summary['success'],
            'errors' => $summary['errors'],
        ];
        \core\notification::success(get_string('batch:register:success', 'local_certificateimport', $summarydata));
    } catch (moodle_exception $exception) {
        \core\notification::error($exception->getMessage());
    } catch (Throwable $throwable) {
        \core\notification::error(get_string('error:unexpected', 'local_certificateimport', $throwable->getMessage()));
    }
    redirect($PAGE->url);
}

$sessionresults = $SESSION->local_certificateimport_lastresults ?? [];
if ($download === 'csv') {
    require_sesskey();
    if (empty($sessionresults)) {
        redirect(
            $PAGE->url,
            get_string('result:export:empty', 'local_certificateimport'),
            0,
            \core\output\notification::NOTIFY_WARNING
        );
    }

    $csv = new csv_export_writer();
    $csv->set_filename('certificateimport-latest');
    $csv->add_data([
        get_string('result:table:user', 'local_certificateimport'),
        get_string('result:table:code', 'local_certificateimport'),
        get_string('result:table:status', 'local_certificateimport'),
    ]);

    foreach ($sessionresults as $row) {
        $statuslabel = get_string('result:status:' . $row['status'], 'local_certificateimport');
        $statusdetails = $statuslabel;
        if (!empty($row['message'])) {
            $statusdetails .= ' - ' . $row['message'];
        }
        $csv->add_data([
            $row['userdisplay'] ?: get_string('user') . ' #' . $row['userid'],
            $row['code'],
            $statusdetails,
        ]);
    }

    $csv->download_file();
    exit;
}

$mform = new \local_certificateimport\form\import_form(null, [
    'templateoptions' => $templateoptions,
]);
$results = [];

if ($mform->is_cancelled()) {
    redirect($PAGE->url);
} else if ($data = $mform->get_data()) {
    $tempdirname = 'local_certificateimport/' . time() . '_' . random_string(6);
    $tempdir = make_temp_directory($tempdirname);
    $pdfdir = $tempdir . '/pdf';
    check_dir_exists($pdfdir, true, true);

    try {
        $csvfilename = $mform->get_new_filename('csvfile');
        $zipfilename = $mform->get_new_filename('zipfile');
        if (!$csvfilename || !$zipfilename) {
            throw new moodle_exception('error:missingfiles', 'local_certificateimport');
        }

        $template = local_certificateimport_get_template((int)$data->templateid);

        $mform->save_files($tempdir);
        $csvpath = $tempdir . '/' . $csvfilename;
        $zipfilepath = $tempdir . '/' . $zipfilename;

        $maxarchivesize = local_certificateimport_get_max_archive_size_mb();
        if ($maxarchivesize > 0) {
            $limitbytes = $maxarchivesize * 1024 * 1024;
            if (filesize($zipfilepath) > $limitbytes) {
                throw new moodle_exception(
                    'error:maxarchivesize',
                    'local_certificateimport',
                    '',
                    display_size($limitbytes)
                );
            }
        }

        $zip = new ZipArchive();
        $zipopen = $zip->open($zipfilepath);
        if ($zipopen !== true) {
            throw new moodle_exception('error:zipopen', 'local_certificateimport', '', $zipopen);
        }
        if (!$zip->extractTo($pdfdir)) {
            throw new moodle_exception('error:zipextract', 'local_certificateimport');
        }
        $zip->close();

        $results = local_certificateimport_run_import($csvpath, $pdfdir, $template);

        $summary = (object)[
            'total' => count($results),
        ];
        \core\notification::success(get_string('result:summary:converting', 'local_certificateimport', $summary));
    } catch (moodle_exception $exception) {
        \core\notification::error($exception->getMessage());
    } catch (Throwable $throwable) {
        \core\notification::error(get_string('error:unexpected', 'local_certificateimport', $throwable->getMessage()));
    } finally {
        fulldelete($tempdir);
    }
}

if (!empty($results)) {
    $SESSION->local_certificateimport_lastresults = $results;
} else {
    unset($SESSION->local_certificateimport_lastresults);
}

$batches = local_certificateimport_get_recent_batches();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pagetitle', 'local_certificateimport'));
echo html_writer::div(get_string('page:instructions', 'local_certificateimport'), 'alert alert-info');

if (!local_certificateimport_is_available()) {
    echo $OUTPUT->notification(
        get_string('status:unavailable:details', 'local_certificateimport'),
        \core\output\notification::NOTIFY_WARNING
    );
}

if (empty($templateoptions)) {
    echo $OUTPUT->notification(
        get_string('error:notemplates', 'local_certificateimport'),
        \core\output\notification::NOTIFY_WARNING
    );
}

$templateurl = new moodle_url('/local/certificateimport/template.php');
$templatelink = html_writer::link($templateurl, get_string('page:csvtemplate', 'local_certificateimport'), [
    'class' => 'btn btn-secondary',
    'role' => 'button',
]);
echo html_writer::div($templatelink, 'mb-2');

$reporturl = new moodle_url('/local/certificateimport/report.php');
$reportlink = html_writer::link($reporturl, get_string('report:menu', 'local_certificateimport'), [
    'class' => 'btn btn-outline-secondary',
    'role' => 'button',
]);
echo html_writer::div($reportlink, 'mb-4');
$mform->display();

echo $OUTPUT->heading(get_string('report:title', 'local_certificateimport'), 3);

if (!empty($results)) {
    $exporturl = new moodle_url('/local/certificateimport/index.php', [
        'download' => 'csv',
        'sesskey' => sesskey(),
    ]);
    $exportlink = html_writer::link($exporturl, get_string('result:export', 'local_certificateimport'), [
        'class' => 'btn btn-secondary mb-3',
        'role' => 'button',
    ]);
    echo $exportlink;

    $table = new html_table();
    $table->attributes['class'] = 'generaltable certimport-report';
    $table->head = [
        get_string('result:table:preview', 'local_certificateimport'),
        get_string('result:table:user', 'local_certificateimport'),
        get_string('result:table:code', 'local_certificateimport'),
        get_string('result:table:status', 'local_certificateimport'),
    ];

    foreach ($results as $row) {
        $statuslabel = get_string('result:status:' . $row['status'], 'local_certificateimport');
        $statuscell = html_writer::span($statuslabel, 'status-label status-' . $row['status']);
        if (!empty($row['message'])) {
            $statuscell .= html_writer::tag('div', s($row['message']), ['class' => 'status-message']);
        }

        $previewcell = '-';
        if (!empty($row['previewurl'])) {
            $filename = $row['filename'] ?: get_string('preview:alt:generic', 'local_certificateimport');
            $alt = get_string('result:preview:alt', 'local_certificateimport', $filename);
            $previewcell = local_certificateimport_render_thumbnail($row['previewurl'], $alt) ?: '-';
        }

        $table->data[] = [
            $previewcell,
            $row['userdisplay'] ?: get_string('user') . ' #' . $row['userid'],
            s($row['code']),
            $statuscell,
        ];
    }

    echo html_writer::table($table);
} else {
    echo html_writer::div(get_string('result:none', 'local_certificateimport'), 'alert alert-secondary');
}

echo $OUTPUT->heading(get_string('batch:list:title', 'local_certificateimport'), 3);
if (empty($batches)) {
    echo html_writer::div(get_string('batch:none', 'local_certificateimport'), 'alert alert-secondary');
} else {
    $batchtable = new html_table();
    $batchtable->attributes['class'] = 'generaltable certimport-batches';
    $batchtable->head = [
        get_string('batch:table:template', 'local_certificateimport'),
        get_string('batch:table:created', 'local_certificateimport'),
        get_string('batch:table:status', 'local_certificateimport'),
        get_string('batch:table:queued', 'local_certificateimport'),
        get_string('batch:table:registered', 'local_certificateimport'),
        get_string('batch:table:errors', 'local_certificateimport'),
        get_string('batch:table:actions', 'local_certificateimport'),
    ];

    foreach ($batches as $batch) {
        $templatename = format_string($batch->templatename, true, ['contextid' => $batch->templatecontextid]);
        $statuslabel = get_string('batch:status:' . $batch->status, 'local_certificateimport');
        $queued = $batch->items_ready . ' / ' . $batch->items_total;
        $registered = $batch->items_registered;
        $errors = $batch->items_errors;

        $actioncell = '';
        if ((int)$batch->items_ready > 0 && $batch->status !== LOCAL_CERTIFICATEIMPORT_BATCH_STATUS_PROCESSING) {
            $registerurl = new moodle_url('/local/certificateimport/index.php', [
                'action' => 'register',
                'batchid' => $batch->id,
                'sesskey' => sesskey(),
            ]);
            $button = new \core\output\single_button($registerurl, get_string('batch:register', 'local_certificateimport'));
            $button->method = 'post';
            $button->add_confirm_action(new \core\output\confirm_action(
                get_string('batch:register:confirm', 'local_certificateimport')
            ));
            $actioncell = $OUTPUT->render($button);
        }

        $batchtable->data[] = [
            $templatename,
            userdate($batch->timecreated),
            html_writer::span($statuslabel, 'status-label status-' . $batch->status),
            $queued,
            $registered,
            $errors,
            $actioncell,
        ];
    }

    echo html_writer::table($batchtable);
}

echo $OUTPUT->footer();

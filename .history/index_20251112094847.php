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
require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url('/local/certificateimport/index.php');
$PAGE->set_context($context);
$PAGE->set_title('Імпорт PDF-сертифікатів');
echo $OUTPUT->header();

$mform = new \local_certificateimport\form\import_form();

if ($data = $mform->get_data()) {
    $path = make_temp_directory('certimport');
    $zipfile = $mform->get_new_filename('zipfile');
    $csvfile = $mform->get_new_filename('csvfile');
    $mform->save_files($path);

    $zip = new ZipArchive();
    $zip->open("$path/$zipfile");
    $zip->extractTo("$path/pdf/");
    $zip->close();

    $rows = array_map('str_getcsv', file("$path/$csvfile"));
    $fs = get_file_storage();

    foreach ($rows as $r) {
        list($userid, $templateid, $code, $filename) = $r;
        $issue = $DB->get_record('tool_certificate_issues', ['userid'=>$userid,'templateid'=>$templateid]);
        if (!$issue) {
            $issue = (object)[
                'userid'=>$userid,'templateid'=>$templateid,'code'=>$code,
                'timecreated'=>time(),'timemodified'=>time()
            ];
            $issue->id = $DB->insert_record('tool_certificate_issues', $issue);
        }
        $contextid = $DB->get_field('context','id',['instanceid'=>$templateid,'contextlevel'=>80]);
        $full = "$path/pdf/$filename";
        if (file_exists($full)) {
            $fs->create_file_from_pathname([
                'contextid'=>$contextid,'component'=>'tool_certificate','filearea'=>'issues',
                'itemid'=>$issue->id,'filepath'=>'/','filename'=>$filename
            ], $full);
            echo "✅ $filename (UserID $userid)<br>";
        } else {
            echo "⚠️ Не найден: $filename<br>";
        }
    }
}
$mform->display();
echo $OUTPUT->footer();
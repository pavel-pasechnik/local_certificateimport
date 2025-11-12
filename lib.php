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
 * Helper functions for the certificate import workflow.
 *
 * @package   local_certificateimport
 * @copyright 2025 Pavel Pasechnik
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

/**
 * Runs the import pipeline for a CSV file and a directory with extracted PDFs.
 *
 * @param string $csvpath Absolute path to the CSV file.
 * @param string $pdfdir  Directory that contains extracted PDF files.
 * @return array<int, array<string, mixed>> Report rows.
 */
function local_certificateimport_run_import(string $csvpath, string $pdfdir): array {
    $records = local_certificateimport_parse_csv($csvpath);
    $fileindex = local_certificateimport_build_file_index($pdfdir);
    $fs = get_file_storage();

    $results = [];
    foreach ($records as $record) {
        $results[] = local_certificateimport_process_record($record, $fileindex, $fs);
    }

    return $results;
}

/**
 * Parses the CSV file into an array of normalized rows.
 *
 * @param string $csvpath Absolute path to CSV file.
 * @return array<int, array<string, mixed>>
 * @throws moodle_exception When the file cannot be read or is empty.
 */
function local_certificateimport_parse_csv(string $csvpath): array {
    if (!is_readable($csvpath)) {
        throw new moodle_exception('error:csvread', 'local_certificateimport');
    }

    $handle = fopen($csvpath, 'r');
    if (!$handle) {
        throw new moodle_exception('error:csvread', 'local_certificateimport');
    }

    $records = [];
    $line = 0;
    $delimiter = ',';
    $enclosure = '"';
    $escape = '\\';
    while (($row = fgetcsv($handle, 0, $delimiter, $enclosure, $escape)) !== false) {
        $line++;
        if ($line === 1 && isset($row[0])) {
            $row[0] = local_certificateimport_strip_bom($row[0]);
        }
        if (local_certificateimport_row_is_empty($row) || local_certificateimport_is_header_row($row)) {
            continue;
        }
        if (count($row) < 4) {
            throw new moodle_exception('error:csvcolumns', 'local_certificateimport', '', $line);
        }

        $records[] = [
            'line' => $line,
            'userid' => (int)trim((string)$row[0]),
            'templateid' => (int)trim((string)$row[1]),
            'code' => trim((string)$row[2]),
            'filename' => trim((string)$row[3]),
            'timecreated' => local_certificateimport_normalize_time($row[4] ?? null),
        ];
    }
    fclose($handle);

    if (!$records) {
        throw new moodle_exception('error:csvempty', 'local_certificateimport');
    }

    return $records;
}

/**
 * Processes a single CSV record.
 *
 * @param array $record Normalized CSV record.
 * @param array $fileindex Map of filenames (lowercase) to absolute paths.
 * @param file_storage $fs Moodle file storage API instance.
 * @return array<string, mixed>
 */
function local_certificateimport_process_record(array $record, array $fileindex, file_storage $fs): array {
    global $DB;

    $result = [
        'line' => $record['line'],
        'userid' => $record['userid'],
        'code' => $record['code'],
        'filename' => $record['filename'],
        'userdisplay' => '',
        'status' => 'error',
        'message' => '',
    ];

    try {
        if (empty($record['userid']) || empty($record['templateid'])) {
            throw new moodle_exception('error:csvcolumns', 'local_certificateimport', '', $record['line']);
        }

        $user = $DB->get_record('user', ['id' => $record['userid']]);
        if (!$user) {
            throw new moodle_exception('error:usernotfound', 'local_certificateimport', '', $record['userid']);
        }
        $result['userdisplay'] = fullname($user) . " (ID {$user->id})";

        $template = $DB->get_record('tool_certificate_templates', ['id' => $record['templateid']], 'id, contextid');
        if (!$template) {
            throw new moodle_exception('error:templatenotfound', 'local_certificateimport', '', $record['templateid']);
        }

        $code = local_certificateimport_normalize_code($record['code']);
        if ($code === '') {
            throw new moodle_exception('error:codeempty', 'local_certificateimport');
        }

        $storedfilename = clean_filename($record['filename']);
        if ($storedfilename === '') {
            throw new moodle_exception('error:filename', 'local_certificateimport', '', $record['filename']);
        }
        if (core_text::strtolower(pathinfo($storedfilename, PATHINFO_EXTENSION)) !== 'pdf') {
            throw new moodle_exception('error:pdfextension', 'local_certificateimport', '', $record['filename']);
        }

        $filepath = local_certificateimport_locate_pdf($fileindex, $record['filename']);
        if (!$filepath) {
            $result['status'] = 'filemissing';
            $result['message'] = get_string('result:message:filemissing', 'local_certificateimport', $record['filename']);
            return $result;
        }

        $timecreated = $record['timecreated'] ?? time();

        $issue = $DB->get_record('tool_certificate_issues', [
            'userid' => $record['userid'],
            'templateid' => $record['templateid'],
        ]);

        $newissue = false;
        if (!$issue) {
            $issue = (object)[
                'userid' => $record['userid'],
                'templateid' => $record['templateid'],
                'code' => $code,
                'emailed' => 0,
                'timecreated' => $timecreated,
                'expires' => 0,
                'data' => null,
                'component' => 'tool_certificate',
                'courseid' => null,
                'archived' => 0,
            ];
            $issue->id = $DB->insert_record('tool_certificate_issues', $issue);
            $newissue = true;
        } else {
            $needupdate = false;
            if ($issue->code !== $code) {
                $issue->code = $code;
                $needupdate = true;
            }
            if (!empty($record['timecreated']) && (int)$issue->timecreated !== (int)$record['timecreated']) {
                $issue->timecreated = $record['timecreated'];
                $needupdate = true;
            }
            if ($needupdate) {
                $DB->update_record('tool_certificate_issues', $issue);
            }
        }

        // Replace existing stored files with the uploaded PDF.
        $fs->delete_area_files($template->contextid, 'tool_certificate', 'issues', $issue->id);
        $fs->create_file_from_pathname([
            'contextid' => $template->contextid,
            'component' => 'tool_certificate',
            'filearea' => 'issues',
            'itemid' => $issue->id,
            'filepath' => '/',
            'filename' => $storedfilename,
        ], $filepath);

        $result['status'] = 'imported';
        $result['message'] = $newissue
            ? get_string('result:message:newissue', 'local_certificateimport')
            : get_string('result:message:updatedissue', 'local_certificateimport');
    } catch (moodle_exception $exception) {
        $result['status'] = 'error';
        $result['message'] = $exception->getMessage();
    } catch (Throwable $throwable) {
        $result['status'] = 'error';
        $result['message'] = get_string('error:unexpected', 'local_certificateimport', $throwable->getMessage());
    }

    return $result;
}

/**
 * Normalizes certificate code values to match DB expectations.
 *
 * @param string $code
 * @return string
 */
function local_certificateimport_normalize_code(string $code): string {
    $code = trim($code);
    if ($code === '') {
        return '';
    }

    return (string)core_text::substr($code, 0, 40);
}

/**
 * Builds an index of PDF files that were extracted from the ZIP archive.
 *
 * @param string $directory
 * @return array<string, string>
 */
function local_certificateimport_build_file_index(string $directory): array {
    $index = [];
    if (!is_dir($directory)) {
        return $index;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $fileinfo) {
        if ($fileinfo->isDir()) {
            continue;
        }
        if (core_text::strtolower($fileinfo->getExtension()) !== 'pdf') {
            continue;
        }
        $fullpath = $fileinfo->getPathname();
        $relative = ltrim(str_replace($directory, '', $fullpath), DIRECTORY_SEPARATOR);
        $relative = str_replace(DIRECTORY_SEPARATOR, '/', $relative);

        $index[core_text::strtolower($relative)] = $fullpath;
        $basename = core_text::strtolower($fileinfo->getFilename());
        if (!array_key_exists($basename, $index)) {
            $index[$basename] = $fullpath;
        }
    }

    return $index;
}

/**
 * Locates a PDF file path inside the extracted directory.
 *
 * @param array $index File index produced by local_certificateimport_build_file_index().
 * @param string $filename Requested filename from the CSV file.
 * @return string|null
 */
function local_certificateimport_locate_pdf(array $index, string $filename): ?string {
    $filename = trim($filename);
    if ($filename === '') {
        return null;
    }

    $normalized = core_text::strtolower(str_replace('\\', '/', ltrim($filename, './')));
    $candidates = array_filter(array_unique([
        $normalized,
        core_text::strtolower(basename($normalized)),
        core_text::strtolower(basename($filename)),
    ]));

    foreach ($candidates as $candidate) {
        if (array_key_exists($candidate, $index)) {
            return $index[$candidate];
        }
    }

    return null;
}

/**
 * Determines whether the given CSV row looks like a header.
 *
 * @param array $row
 * @return bool
 */
function local_certificateimport_is_header_row(array $row): bool {
    if (count($row) < 2) {
        return false;
    }

    $first = core_text::strtolower(trim((string)$row[0]));
    $second = core_text::strtolower(trim((string)$row[1]));

    return $first === 'userid' && $second === 'templateid';
}

/**
 * Checks if the row is empty (all values blank).
 *
 * @param array $row
 * @return bool
 */
function local_certificateimport_row_is_empty(array $row): bool {
    foreach ($row as $value) {
        if (trim((string)$value) !== '') {
            return false;
        }
    }

    return true;
}

/**
 * Removes UTF-8 BOM from a string.
 *
 * @param string $value
 * @return string
 */
function local_certificateimport_strip_bom(string $value): string {
    if (strncmp($value, "\xEF\xBB\xBF", 3) === 0) {
        return substr($value, 3);
    }

    return $value;
}

/**
 * Converts raw CSV value to a UNIX timestamp.
 *
 * @param mixed $value
 * @return int|null
 */
function local_certificateimport_normalize_time($value): ?int {
    if ($value === null) {
        return null;
    }

    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }

    if (is_numeric($value)) {
        $timestamp = (int)$value;
        return $timestamp > 0 ? $timestamp : null;
    }

    $timestamp = strtotime($value);
    if ($timestamp && $timestamp > 0) {
        return $timestamp;
    }

    $formats = [
        'd.m.Y H:i:s',
        'd.m.Y H:i',
        'd.m.Y',
        'd/m/Y H:i:s',
        'd/m/Y H:i',
        'd/m/Y',
        'Y-m-d H:i:s',
        'Y-m-d H:i',
        'Y-m-d',
        'Y/m/d H:i:s',
        'Y/m/d H:i',
        'Y/m/d',
    ];

    $timezone = new DateTimeZone(date_default_timezone_get());
    foreach ($formats as $format) {
        $datetime = DateTime::createFromFormat($format, $value, $timezone);
        if ($datetime instanceof DateTime) {
            $errors = DateTime::getLastErrors();
            if (empty($errors['warning_count']) && empty($errors['error_count'])) {
                return $datetime->getTimestamp();
            }
        }
    }

    return null;
}

/**
 * Checks whether the plugin can operate (dependencies present, tables exist).
 *
 * @return bool
 */
function local_certificateimport_is_available(): bool {
    global $DB;

    $plugindir = core_component::get_plugin_directory('tool', 'certificate');
    if (!$plugindir || !file_exists($plugindir . '/version.php')) {
        return false;
    }

    $dbman = $DB->get_manager();
    return $dbman->table_exists('tool_certificate_templates')
        && $dbman->table_exists('tool_certificate_issues');
}

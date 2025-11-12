<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Language strings for the local_certificateimport plugin.
 *
 * @package   local_certificateimport
 * @copyright 2024 Pavel
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Certificate PDF import';
$string['pagetitle'] = 'CSV/ZIP certificate import';
$string['page:instructions'] = 'Upload a CSV file in the format <code>userid,templateid,code,filename,timecreated</code> and a ZIP file that contains all PDF certificates. After the import you can verify the entries via <code>tool/certificate/index.php</code>. Use the download link below if you need a ready-made CSV template.';
$string['page:csvtemplate'] = 'Download CSV template';

$string['form:csvfile'] = 'CSV file';
$string['form:csvfile_help'] = 'The CSV must contain the columns: userid, templateid, code, filename, timecreated. The timecreated column may contain a UNIX timestamp or a readable date (YYYY-MM-DD, DD.MM.YYYY, etc.).';
$string['form:zipfile'] = 'ZIP archive with PDFs';
$string['form:zipfile_help'] = 'PDF filenames have to match the <code>filename</code> column in the CSV.';
$string['form:submit'] = 'Import certificates';
$string['form:error:required'] = 'This file is required.';

$string['report:title'] = 'Import report';
$string['result:none'] = 'Upload files and run the import to see the report.';
$string['result:summary'] = '{$a->imported} of {$a->total} rows imported successfully.';
$string['result:table:user'] = 'User';
$string['result:table:code'] = 'Code';
$string['result:table:status'] = 'Status';
$string['result:status:imported'] = 'Imported';
$string['result:status:filemissing'] = 'File not found';
$string['result:status:error'] = 'Error';
$string['result:message:newissue'] = 'Issue created and PDF stored.';
$string['result:message:updatedissue'] = 'Existing issue updated with the uploaded PDF.';
$string['result:message:filemissing'] = 'PDF file "{$a}" was not found in the uploaded ZIP archive.';

$string['error:missingfiles'] = 'Both CSV and ZIP files must be provided.';
$string['error:csvread'] = 'Unable to read the CSV file.';
$string['error:csvcolumns'] = 'Line {$a}: the row does not match the expected format (userid,templateid,code,filename,timecreated).';
$string['error:csvempty'] = 'The CSV file does not contain any data rows.';
$string['error:usernotfound'] = 'User with ID {$a} was not found.';
$string['error:templatenotfound'] = 'Certificate template with ID {$a} was not found.';
$string['error:codeempty'] = 'Certificate code cannot be empty.';
$string['error:filename'] = 'Filename cannot be determined for value "{$a}".';
$string['error:pdfextension'] = 'Only PDF files can be imported (value "{$a}").';
$string['error:zipopen'] = 'Unable to open the ZIP archive (error code {$a}).';
$string['error:zipextract'] = 'Unable to extract files from the ZIP archive.';
$string['error:unexpected'] = 'Unexpected error: {$a}';

$string['status:available'] = 'Available';
$string['status:unavailable'] = 'Unavailable';
$string['status:unavailable:details'] = 'The importer is disabled until the official Certificate tool (tool_certificate) is installed and its database tables are present.';

$string['privacy:metadata'] = 'The plugin does not store personal data beyond the standard tool_certificate tables.';
$string['certificateimport:import'] = 'Import certificate PDF files';

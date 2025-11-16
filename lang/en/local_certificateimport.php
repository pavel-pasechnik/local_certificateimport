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
$string['page:instructions'] = 'Select the certificate template below, then upload a CSV file in the format <code>userid,filename,timecreated</code> (the <code>timecreated</code> column is optional) and a ZIP file that contains all PDF certificates. After the import you can verify the entries via <code>tool/certificate/index.php</code>. Use the download link below if you need a ready-made CSV template.';
$string['page:csvtemplate'] = 'Download CSV template';

$string['form:template'] = 'Certificate template';
$string['form:template_help'] = 'Choose which certificate template the uploaded issues should belong to. This selection overrides any template IDs in the CSV.';
$string['form:template:choose'] = 'Choose a template';
$string['form:csvfile'] = 'CSV file';
$string['form:csvfile_help'] = 'The CSV must contain the columns: userid, filename, timecreated. The timecreated column may contain a UNIX timestamp or a readable date (YYYY-MM-DD, DD.MM.YYYY, etc.) or be left blank.';
$string['form:zipfile'] = 'ZIP archive with PDFs';
$string['form:zipfile_help'] = 'PDF filenames have to match the <code>filename</code> column in the CSV.';
$string['form:submit'] = 'Import certificates';
$string['form:error:required'] = 'This file is required.';

$string['report:title'] = 'Import report';
$string['result:export'] = 'Export report to CSV';
$string['result:export:empty'] = 'No recent import results are available for export.';
$string['result:none'] = 'Upload files and run the import to see the report.';
$string['result:summary:converting'] = '{$a->total} row(s) accepted. Background conversion will continue via cron.';
$string['result:table:preview'] = 'Preview';
$string['result:table:user'] = 'User';
$string['result:table:code'] = 'Code';
$string['result:table:status'] = 'Status';
$string['result:status:imported'] = 'Imported';
$string['result:status:queued'] = 'Queued';
$string['result:status:registered'] = 'Registered';
$string['result:status:filemissing'] = 'File not found';
$string['result:status:error'] = 'Error';
$string['result:status:converting'] = 'Converting';
$string['result:message:newissue'] = 'Issue created and PDF stored.';
$string['result:message:updatedissue'] = 'Existing issue updated with the uploaded PDF.';
$string['result:message:filemissing'] = 'PDF file "{$a}" was not found in the uploaded ZIP archive.';
$string['result:message:queued'] = 'PDF converted to JPEG and added to the registration queue.';
$string['result:message:converting'] = 'PDF stored and scheduled for conversion via cron.';
$string['result:preview:alt'] = 'Preview of {$a}';

$string['issue:status:queued'] = 'Queued';
$string['issue:status:active'] = 'Active';
$string['issue:status:revoked'] = 'Revoked';
$string['issue:status:missing'] = 'Missing';

$string['error:missingfiles'] = 'Both CSV and ZIP files must be provided.';
$string['error:csvread'] = 'Unable to read the CSV file.';
$string['error:csvcolumns'] = 'Line {$a}: the row does not match the expected format (userid,filename,timecreated). The first two columns are required.';
$string['error:csvempty'] = 'The CSV file does not contain any data rows.';
$string['error:usernotfound'] = 'User with ID {$a} was not found.';
$string['error:templatenotfound'] = 'Certificate template with ID {$a} was not found.';
$string['error:filename'] = 'Filename cannot be determined for value "{$a}".';
$string['error:pdfextension'] = 'Only PDF files can be imported (value "{$a}").';
$string['error:zipopen'] = 'Unable to open the ZIP archive (error code {$a}).';
$string['error:zipextract'] = 'Unable to extract files from the ZIP archive.';
$string['error:batchinprogress'] = 'This batch is already processing or completed.';
$string['error:batchreadyempty'] = 'There are no queued certificates waiting for registration in this batch.';
$string['error:backgroundmissing'] = 'Converted background image for "{$a}" is missing.';
$string['error:sourcefilemissing'] = 'Source PDF for item {$a} is missing. Delete the record and re-import.';
$string['error:maxrecords'] = 'You can import at most {$a} certificates at once. Split the CSV/ZIP and try again.';
$string['error:maxarchivesize'] = 'The ZIP archive exceeds the allowed size ({$a}). Split the file before uploading.';
$string['error:convertermissing'] = 'PDF conversion requires Imagick or one of the CLI tools (convert, pdftoppm, gs). Install one of them on the server.';
$string['error:converterimagick'] = 'Imagick was unable to convert the PDF: {$a}';
$string['error:convertercli'] = 'ImageMagick CLI could not convert the PDF (exit code {$a}).';
$string['error:converterpdftoppm'] = 'pdftoppm could not convert the PDF (exit code {$a}).';
$string['error:convertergs'] = 'Ghostscript could not convert the PDF (exit code {$a}).';
$string['error:unexpected'] = 'Unexpected error: {$a}';

$string['status:available'] = 'Available';
$string['status:unavailable'] = 'Unavailable';
$string['status:unavailable:details'] = 'The importer is disabled until the official Certificate tool (tool_certificate) is installed and its database tables are present.';
$string['error:notemplates'] = 'No certificate templates were found. Create one via tool_certificate before running the import.';

$string['batch:list:title'] = 'Import batches';
$string['batch:none'] = 'No staged imports yet.';
$string['batch:table:template'] = 'Template';
$string['batch:table:created'] = 'Created';
$string['batch:table:status'] = 'Status';
$string['batch:table:queued'] = 'Queued / Total';
$string['batch:table:registered'] = 'Registered';
$string['batch:table:errors'] = 'Errors';
$string['batch:table:actions'] = 'Actions';
$string['batch:register'] = 'Register certificates';
$string['batch:register:confirm'] = 'Issue certificates for this batch using tool_certificate?';
$string['batch:register:success'] = '{$a->success} certificate(s) issued. Errors: {$a->errors}.';
$string['batch:status:pending'] = 'Queued';
$string['batch:status:processing'] = 'Processing';
$string['batch:status:completed'] = 'Completed';
$string['batch:status:completed_errors'] = 'Completed with errors';
$string['batch:status:failed'] = 'Failed';

$string['filter:template'] = 'Certificate template';
$string['filter:template:any'] = 'All templates';
$string['filter:status'] = 'Status';
$string['filter:status:any'] = 'All statuses';
$string['filter:user'] = 'User (name or email)';
$string['filter:datefrom'] = 'Imported after';
$string['filter:dateto'] = 'Imported before';
$string['filter:perpage'] = 'Results per page';
$string['filter:apply'] = 'Apply filters';
$string['filter:reset'] = 'Reset';

$string['settings:heading'] = 'Certificate import limits';
$string['settings:maxrecords'] = 'Maximum certificates per import';
$string['settings:maxrecords_desc'] = 'Restrict how many CSV rows can be processed in one run. Set to 0 to disable the limit.';
$string['settings:maxarchivesize'] = 'Maximum ZIP size (MB)';
$string['settings:maxarchivesize_desc'] = 'Reject uploads whose ZIP archive exceeds this size (in megabytes). Set to 0 to disable the limit.';
$string['settings:pdftoppmpath'] = 'pdftoppm path';
$string['settings:pdftoppmpath_desc'] = 'Absolute path to the pdftoppm binary. Leave empty to auto-detect.';
$string['settings:ghostscriptpath'] = 'Ghostscript path';
$string['settings:ghostscriptpath_desc'] = 'Absolute path to the Ghostscript (gs) binary. Leave empty to auto-detect.';

$string['report:issues:title'] = 'Imported certificates';
$string['report:issues:description'] = 'Browse every certificate queued or issued through the importer, filter by template/status/date, export to CSV, reissue revoked records, delete revoked/not-issued entries, and open background previews.';
$string['report:menu'] = 'Imported certificates report';
$string['report:col:number'] = '#';
$string['report:col:preview'] = 'Preview';
$string['report:col:certificate'] = 'Template';
$string['report:col:user'] = 'User';
$string['report:col:imported'] = 'Imported on';
$string['report:col:issued'] = 'Issued on';
$string['report:col:status'] = 'Status';
$string['report:col:code'] = 'Certificate code';
$string['report:reissue:selected'] = 'Reissue selected certificates';
$string['report:reissue:none'] = 'Select at least one revoked certificate to reissue.';
$string['report:reissue:success'] = '{$a->success} certificate(s) reissued.';
$string['report:reissue:errors'] = 'Failed to reissue {$a->errors} certificate(s).';
$string['report:delete:selected'] = 'Delete selected records';
$string['report:delete:none'] = 'Select at least one revoked or not-issued certificate to delete.';
$string['report:delete:success'] = '{$a->deleted} record(s) deleted.';
$string['report:delete:skipped'] = '{$a->skipped} record(s) skipped because they are still active.';
$string['report:delete:noneeligible'] = 'The selected certificates cannot be deleted.';
$string['report:delete:confirm'] = 'Delete the selected records? This action cannot be undone.';
$string['report:preview:alt'] = 'Preview for {$a}';

$string['task:convertbackground'] = 'Convert certificate backgrounds';
$string['privacy:metadata'] = 'The plugin does not store personal data beyond the standard tool_certificate tables.';
$string['certificateimport:import'] = 'Import certificate PDF files';
$string['preview:alt:generic'] = 'Certificate background preview';

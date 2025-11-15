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
 * Handles deferred registration of staged certificates.
 *
 * @package   local_certificateimport
 * @copyright 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_certificateimport\local;

use coding_exception;
use context_system;
use core_php_time_limit;
use moodle_exception;
use stdClass;
use stored_file;
use tool_certificate\template;

/**
 * Issues certificates via tool_certificate using staged import data.
 */
class registrar {
    /** @var template */
    protected $template;

    /**
     * Registrar constructor.
     *
     * @param template $template
     */
    public function __construct(template $template) {
        $this->template = $template;
    }

    /**
     * Issues certificates for the provided batch.
     *
     * @param stdClass $batch
     * @param array $items
     * @return array Array with counters (success, errors)
     * @throws moodle_exception
     * @throws coding_exception
     */
    public function register(stdClass $batch, array $items): array {
        global $DB;

        core_php_time_limit::raise(0);
        raise_memory_limit(MEMORY_EXTRA);

        $success = 0;
        $errors = 0;
        $now = time();

        foreach ($items as $item) {
            if ($item->status !== LOCAL_CERTIFICATEIMPORT_ITEM_STATUS_QUEUED) {
                continue;
            }
            try {
                $issueid = $this->issue_certificate($item);
                $item->status = LOCAL_CERTIFICATEIMPORT_ITEM_STATUS_REGISTERED;
                $item->issueid = $issueid;
                $item->timeprocessed = $now;
                $success++;
            } catch (moodle_exception $exception) {
                $item->status = LOCAL_CERTIFICATEIMPORT_ITEM_STATUS_ERROR;
                $item->timeprocessed = $now;
                $errors++;
            } catch (\Throwable $throwable) {
                $item->status = LOCAL_CERTIFICATEIMPORT_ITEM_STATUS_ERROR;
                $item->timeprocessed = $now;
                $errors++;
            }
            $DB->update_record('local_certimp_items', $item);
        }

        $batch->processeditems = $this->count_registered($batch->id);
        $batch->timeupdated = $now;
        $batch->timeregistered = $now;
        if ($errors && !$success) {
            $batch->status = LOCAL_CERTIFICATEIMPORT_BATCH_STATUS_FAILED;
        } else if ($errors && $success) {
            $batch->status = LOCAL_CERTIFICATEIMPORT_BATCH_STATUS_COMPLETED_WITH_ERRORS;
        } else {
            $batch->status = LOCAL_CERTIFICATEIMPORT_BATCH_STATUS_COMPLETED;
        }
        $DB->update_record('local_certimp_batches', $batch);

        return ['success' => $success, 'errors' => $errors];
    }

    /**
     * Issues a single certificate via tool_certificate.
     *
     * @param stdClass $item
     * @return int
     * @throws moodle_exception
     */
    public function issue_certificate(stdClass $item): int {
        global $DB;

        $user = $DB->get_record('user', ['id' => $item->userid], '*', MUST_EXIST);
        $data = $this->build_issue_data($item);

        $issueid = $this->template->issue_certificate($user->id, null, $data);
        $this->override_issue_time($issueid, (int)($item->issuetime ?? 0));

        return $issueid;
    }

    /**
     * Prepares the payload that will be stored in tool_certificate_issues.data.
     *
     * @param stdClass $item
     * @return array
     * @throws moodle_exception
     */
    protected function build_issue_data(stdClass $item): array {
        if (empty($item->backgroundfileid)) {
            throw new moodle_exception('error:backgroundmissing', 'local_certificateimport', '', $item->filename);
        }

        $fs = get_file_storage();
        $file = $fs->get_file_by_id($item->backgroundfileid);
        if (!$file instanceof stored_file) {
            throw new moodle_exception('error:backgroundmissing', 'local_certificateimport', '', $item->filename);
        }

        return [
            'local_certificateimport' => [
                'backgroundfileid' => $file->get_id(),
                'filename' => $file->get_filename(),
                'contextid' => $file->get_contextid(),
            ],
        ];
    }

    /**
     * Overrides the issue time when requested by the CSV.
     *
     * @param int $issueid
     * @param int $timestamp
     * @return void
     */
    protected function override_issue_time(int $issueid, int $timestamp): void {
        global $DB;

        if ($timestamp <= 0) {
            return;
        }

        if ($issue = $DB->get_record('tool_certificate_issues', ['id' => $issueid])) {
            $issue->timecreated = $timestamp;
            $DB->update_record('tool_certificate_issues', $issue);
        }
    }

    /**
     * Counts registered items within the batch.
     *
     * @param int $batchid
     * @return int
     */
    protected function count_registered(int $batchid): int {
        global $DB;

        return $DB->count_records('local_certimp_items', [
            'batchid' => $batchid,
            'status' => LOCAL_CERTIFICATEIMPORT_ITEM_STATUS_REGISTERED,
        ]);
    }
}

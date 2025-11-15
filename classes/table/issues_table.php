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
 * Reporting table for imported certificates.
 *
 * @package   local_certificateimport
 * @copyright 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_certificateimport\table;

use html_writer;
use local_certificateimport_item_status;
use local_certificateimport_item_status_label;
use moodle_url;
use stdClass;
use table_sql;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');

/**
 * Displays filtered certificate import rows with pagination and downloads.
 */
class issues_table extends table_sql {
    /** @var array<string, mixed> */
    protected $filters;
    /** @var int */
    protected $rownumber = 0;

    /**
     * issues_table constructor.
     *
     * @param string $uniqueid
     * @param array $filters
     * @param moodle_url $baseurl
     */
    public function __construct(string $uniqueid, array $filters, moodle_url $baseurl) {
        parent::__construct($uniqueid);
        $this->filters = $filters;
        $this->define_columns(['select', 'rownum', 'certname', 'user', 'imported', 'issued', 'status', 'code']);
        $this->define_headers([
            '',
            get_string('report:col:number', 'local_certificateimport'),
            get_string('report:col:certificate', 'local_certificateimport'),
            get_string('report:col:user', 'local_certificateimport'),
            get_string('report:col:imported', 'local_certificateimport'),
            get_string('report:col:issued', 'local_certificateimport'),
            get_string('report:col:status', 'local_certificateimport'),
            get_string('report:col:code', 'local_certificateimport'),
        ]);

        $this->define_baseurl($baseurl);
        $this->sortable(false);
        $this->collapsible(false);
        $this->pageable(true);

        $userfields = \core_user\fields::for_name()->get_sql('u', false, '', '', false);
        $namesselect = $userfields->selects ? ', ' . $userfields->selects : '';

        $fields = "i.id AS itemid,
                   i.timecreated AS importtime,
                   i.issuetime AS csvissuetime,
                   b.templateid,
                   b.id AS batchid,
                   t.name AS templatename,
                   t.contextid AS templatecontextid,
                   u.id AS userid,
                   ti.id AS issueid,
                   ti.timecreated AS issuetime,
                   ti.code AS issuecode,
                   ti.archived AS issuearchived
                   {$namesselect}";
        $from = "{local_certimp_items} i
                 JOIN {local_certimp_batches} b ON b.id = i.batchid
                 JOIN {tool_certificate_templates} t ON t.id = b.templateid
                 JOIN {user} u ON u.id = i.userid
            LEFT JOIN {tool_certificate_issues} ti ON ti.id = i.issueid";

        [$where, $params] = $this->build_filters();

        $this->set_sql($fields, $from, $where, $params);
        $this->set_count_sql("SELECT COUNT(1) FROM {local_certimp_items} i
                 JOIN {local_certimp_batches} b ON b.id = i.batchid
                 JOIN {tool_certificate_templates} t ON t.id = b.templateid
                 JOIN {user} u ON u.id = i.userid
            LEFT JOIN {tool_certificate_issues} ti ON ti.id = i.issueid
                WHERE {$where}", $params);
    }

    /**
     * Builds SQL fragments for the active filters.
     *
     * @return array{0:string,1:array}
     */
    protected function build_filters(): array {
        $conditions = ['1=1'];
        $params = [];

        if (!empty($this->filters['templateid'])) {
            $conditions[] = 'b.templateid = :templateid';
            $params['templateid'] = $this->filters['templateid'];
        }

        if (!empty($this->filters['user'])) {
            $conditions[] = '(u.firstname LIKE :usersearch OR u.lastname LIKE :usersearch OR u.email LIKE :usersearch)';
            $params['usersearch'] = '%' . $this->filters['user'] . '%';
        }

        if (!empty($this->filters['datefrom'])) {
            $conditions[] = 'i.timecreated >= :datefrom';
            $params['datefrom'] = $this->filters['datefrom'];
        }
        if (!empty($this->filters['dateto'])) {
            $conditions[] = 'i.timecreated <= :dateto';
            $params['dateto'] = $this->filters['dateto'];
        }

        if (!empty($this->filters['status']) && $this->filters['status'] !== 'all') {
            switch ($this->filters['status']) {
                case 'queued':
                    $conditions[] = 'i.issueid IS NULL';
                    break;
                case 'active':
                    $conditions[] = 'i.issueid IS NOT NULL AND ti.id IS NOT NULL AND ti.archived = 0';
                    break;
                case 'revoked':
                    $conditions[] = 'i.issueid IS NOT NULL AND ti.id IS NOT NULL AND ti.archived = 1';
                    break;
                case 'missing':
                    $conditions[] = 'i.issueid IS NOT NULL AND ti.id IS NULL';
                    break;
            }
        }

        return [implode(' AND ', $conditions), $params];
    }

    /**
     * Adds row numbers.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_rownum(stdClass $row): string {
        return (string)(++$this->rownumber + $this->currentpage * $this->pagesize);
    }

    /**
     * Checkbox column (only when not downloading).
     *
     * @param stdClass $row
     * @return string
     */
    public function col_select(stdClass $row): string {
        if ($this->is_downloading() || $this->get_status_code($row) !== 'revoked') {
            return '';
        }

        return html_writer::checkbox('selected[]', $row->itemid, false);
    }

    /**
     * Template column.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_certname(stdClass $row): string {
        return format_string($row->templatename, true, ['contextid' => $row->templatecontextid]);
    }

    /**
     * Display the user name.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_user(stdClass $row): string {
        return fullname($row);
    }

    /**
     * Import time column.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_imported(stdClass $row): string {
        return userdate($row->importtime);
    }

    /**
     * Issue time column.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_issued(stdClass $row): string {
        if (!empty($row->issuetime)) {
            return userdate($row->issuetime);
        }
        if (!empty($row->csvissuetime)) {
            return userdate($row->csvissuetime);
        }
        return '-';
    }

    /**
     * Status column.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_status(stdClass $row): string {
        $status = $this->get_status_code($row);
        $label = local_certificateimport_item_status_label($status);

        return html_writer::span($label, 'status-label status-' . $status);
    }

    /**
     * Issue code column.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_code(stdClass $row): string {
        return s($row->issuecode ?? '');
    }

    /**
     * Determines the status code for a row.
     *
     * @param stdClass $row
     * @return string
     */
    protected function get_status_code(stdClass $row): string {
        $archived = isset($row->issuearchived) ? (int)$row->issuearchived : null;
        return local_certificateimport_item_status($row->issueid ?? null, $archived);
    }
}

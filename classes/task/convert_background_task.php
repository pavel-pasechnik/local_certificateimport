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

namespace local_certificateimport\task;

use core\task\adhoc_task;
use core\task\manager;

/**
 * Adhoc task that converts staged PDFs into JPEG backgrounds.
 *
 * @package   local_certificateimport
 * @copyright 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class convert_background_task extends adhoc_task {
    /**
     * Task name for admin UI.
     *
     * @return string
     */
    public function get_name(): string {
        return \get_string('task:convertbackground', 'local_certificateimport');
    }

    /**
     * Executes the conversion.
     *
     * @return void
     */
    public function execute(): void {
        global $CFG;

        require_once($CFG->dirroot . '/local/certificateimport/lib.php');

        $data = (object)($this->get_custom_data() ?? []);
        if (empty($data->itemid)) {
            return;
        }

        try {
            \local_certificateimport_process_conversion((int)$data->itemid);
        } catch (\moodle_exception $exception) {
            $message = 'local_certificateimport: conversion failed for item ' . $data->itemid .
                ' - ' . $exception->getMessage();
            debugging($message, DEBUG_DEVELOPER);
            \local_certificateimport_flag_conversion_error((int)$data->itemid);
        } catch (\Throwable $throwable) {
            $message = 'local_certificateimport: conversion failed for item ' . $data->itemid .
                ' - ' . $throwable->getMessage();
            debugging($message, DEBUG_DEVELOPER);
            \local_certificateimport_flag_conversion_error((int)$data->itemid);
        }
    }

    /**
     * Convenience method for re-queuing conversions.
     *
     * @param int $itemid
     * @return void
     */
    public static function requeue(int $itemid): void {
        $task = new self();
        $task->set_component('local_certificateimport');
        $task->set_custom_data(['itemid' => $itemid]);
        $task->set_next_run_time(time());
        manager::queue_adhoc_task($task, true);
    }
}

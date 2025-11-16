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
 * Admin settings hook for local_certificateimport.
 *
 * @package   local_certificateimport
 * @copyright 2025 Pavel Pasechnik
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/lib.php');

if ($hassiteconfig) {
    $category = 'certificates';

    if (!$ADMIN->locate($category)) {
        $ADMIN->add('root', new admin_category($category, get_string('pluginname', 'tool_certificate')));
    }

    $ADMIN->add($category, new admin_externalpage(
        'local_certificateimport',
        get_string('pluginname', 'local_certificateimport'),
        new moodle_url('/local/certificateimport/index.php'),
        'local/certificateimport:import'
    ));

    $ADMIN->add($category, new admin_externalpage(
        'local_certificateimport_report',
        get_string('report:menu', 'local_certificateimport'),
        new moodle_url('/local/certificateimport/report.php'),
        'local/certificateimport:import'
    ));

    $settingspage = new admin_settingpage(
        'local_certificateimport_settings',
        get_string('settings:heading', 'local_certificateimport')
    );

    $settingspage->add(new admin_setting_configtext(
        'local_certificateimport/maxrecords',
        get_string('settings:maxrecords', 'local_certificateimport'),
        get_string('settings:maxrecords_desc', 'local_certificateimport'),
        LOCAL_CERTIFICATEIMPORT_DEFAULT_MAXRECORDS,
        PARAM_INT
    ));

    $settingspage->add(new admin_setting_configtext(
        'local_certificateimport/maxarchivesize',
        get_string('settings:maxarchivesize', 'local_certificateimport'),
        get_string('settings:maxarchivesize_desc', 'local_certificateimport'),
        LOCAL_CERTIFICATEIMPORT_DEFAULT_MAXARCHIVESIZE_MB,
        PARAM_INT
    ));

    $pdftoppmpath = local_certificateimport_find_pdftoppm_path();
    $pdftoppmstatus = $pdftoppmpath
        ? \html_writer::span('&#10004; ' . get_string('settings:binary:available', 'local_certificateimport', $pdftoppmpath), 'text-success fw-bold')
        : \html_writer::span('&#10060; ' . get_string('settings:binary:missing', 'local_certificateimport'), 'text-danger fw-bold');
    $pdftoppmdesc = \html_writer::div(
        get_string('settings:pdftoppm_desc', 'local_certificateimport') .
        \html_writer::tag('div', $pdftoppmstatus, ['class' => 'my-2']),
        'local-certimport-binary-status'
    );
    $settingspage->add(new admin_setting_heading(
        'local_certificateimport_pdftoppm',
        get_string('settings:pdftoppm', 'local_certificateimport'),
        $pdftoppmdesc
    ));

    $ghostscriptpath = local_certificateimport_find_ghostscript_path();
    $ghostscriptstatus = $ghostscriptpath
        ? \html_writer::span('&#10004; ' . get_string('settings:binary:available', 'local_certificateimport', $ghostscriptpath), 'text-success fw-bold')
        : \html_writer::span('&#10060; ' . get_string('settings:binary:missing', 'local_certificateimport'), 'text-danger fw-bold');
    $ghostscriptdesc = \html_writer::div(
        get_string('settings:ghostscript_desc', 'local_certificateimport') .
        \html_writer::tag('div', $ghostscriptstatus, ['class' => 'my-2']),
        'local-certimport-binary-status'
    );
    $settingspage->add(new admin_setting_heading(
        'local_certificateimport_ghostscript',
        get_string('settings:ghostscript', 'local_certificateimport'),
        $ghostscriptdesc
    ));

    $ADMIN->add($category, $settingspage);
}

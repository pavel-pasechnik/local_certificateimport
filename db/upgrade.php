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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Upgrade steps for the local_certificateimport plugin.
 *
 * @package    local_certificateimport
 * @copyright  2025 Pavel Pasechnik
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Executes upgrade steps for local_certificateimport.
 *
 * @param int $oldversion the version number we are upgrading from.
 * @return bool always true when the upgrade completes without error.
 */
function xmldb_local_certificateimport_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2025111600) {
        $table = new xmldb_table('local_certificateimport_log');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('issueid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('templateid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('filename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        $table->add_field('storedfilename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        $table->add_field('timeimported', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('issueid_fk', XMLDB_KEY_UNIQUE, ['issueid']);

        $table->add_index('templateid_idx', XMLDB_INDEX_NOTUNIQUE, ['templateid']);
        $table->add_index('userid_idx', XMLDB_INDEX_NOTUNIQUE, ['userid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2025111600, 'local', 'certificateimport');
    }

    if ($oldversion < 2025111600) {
        // Reserved for observer/original-file storage upgrade.
        upgrade_plugin_savepoint(true, 2025111600, 'local', 'certificateimport');
    }

    if ($oldversion < 2025111600) {
        $table = new xmldb_table('local_certificateimport_log');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        $fs = get_file_storage();
        $systemcontext = context_system::instance();
        $fs->delete_area_files($systemcontext->id, 'local_certificateimport', 'originals');

        upgrade_plugin_savepoint(true, 2025111600, 'local', 'certificateimport');
    }

    if ($oldversion < 2025111600) {
        $batches = new xmldb_table('local_certimp_batches');
        if (!$dbman->table_exists($batches)) {
            $batches->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $batches->add_field('templateid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $batches->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $batches->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'pending');
            $batches->add_field('totalitems', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
            $batches->add_field('processeditems', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
            $batches->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
            $batches->add_field('timeupdated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
            $batches->add_field('timeregistered', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

            $batches->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $batches->add_index('template_idx', XMLDB_INDEX_NOTUNIQUE, ['templateid']);
            $batches->add_index('status_idx', XMLDB_INDEX_NOTUNIQUE, ['status']);

            $dbman->create_table($batches);
        }

        $items = new xmldb_table('local_certimp_items');
        if (!$dbman->table_exists($items)) {
            $items->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $items->add_field('batchid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $items->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $items->add_field('filename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
            $items->add_field('csvline', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
            $items->add_field('backgroundfileid', XMLDB_TYPE_INTEGER, '10', null);
            $items->add_field('issueid', XMLDB_TYPE_INTEGER, '10', null);
            $items->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'queued');
            $items->add_field('issuetime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
            $items->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
            $items->add_field('timeprocessed', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

            $items->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $items->add_index('batch_idx', XMLDB_INDEX_NOTUNIQUE, ['batchid']);
            $items->add_index('user_idx', XMLDB_INDEX_NOTUNIQUE, ['userid']);
            $items->add_index('status_idx', XMLDB_INDEX_NOTUNIQUE, ['status']);

            $dbman->create_table($items);
        }

        upgrade_plugin_savepoint(true, 2025111600, 'local', 'certificateimport');
    }

    if ($oldversion < 2025111600) {
        $items = new xmldb_table('local_certimp_items');
        $field = new xmldb_field('errormessage');
        if ($dbman->field_exists($items, $field)) {
            $dbman->drop_field($items, $field);
        }

        upgrade_plugin_savepoint(true, 2025111600, 'local', 'certificateimport');
    }

    if ($oldversion < 2025111600) {
        $items = new xmldb_table('local_certimp_items');
        $field = new xmldb_field('sourcefileid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'backgroundfileid');
        if (!$dbman->field_exists($items, $field)) {
            $dbman->add_field($items, $field);
        }

        upgrade_plugin_savepoint(true, 2025111600, 'local', 'certificateimport');
    }

    return true;
}

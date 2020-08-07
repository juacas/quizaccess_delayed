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
 * This file keeps track of upgrades to the delayed module
 *
 * @package    quizaccess_delayed
 * @copyright  2020 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute delayed upgrade from the given old version
 * @global moodle_database $DB
 * @param int $oldversion
 * @return bool
 */
function xmldb_quizaccess_delayed_upgrade($oldversion) {
    /** @global moodle_database $DB */
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2020051500) {

        // Create new table: quizaccess_delayed.
        $table = new xmldb_table('quizaccess_delayed');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('quizid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('delayedattempt', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, null, null, '0');

            // Add keys to table quizaccess_delayed.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('quizid', XMLDB_KEY_FOREIGN, array('quizid'), 'quiz', array('id'));

            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2020051500, 'quizaccess', 'delayed');
    }

    return true;
}

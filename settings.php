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
 * Implementaton of the quizaccess_activatedelayedattempt plugin.
 * Based on quizaccess_activateattempt https://github.com/IITBombayWeb/moodle-quizaccess_activatedelayedattempt/tree/v1.0.3
 *
 * @package   quizaccess_activatedelayedattempt
 * @author    Juan Pablo de Castro
 * @copyright 2020 University of Valladolid, Spain
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

if ($hassiteconfig) {

    $settings->add(new admin_setting_configcheckbox(
        'quizaccess_activatedelayedattempt/enabled',
        new lang_string('quizaccess_activatedelayedattempt_enabled', 'quizaccess_activatedelayedattempt'),
        '',
        1
    ));
    $vals = [];
    foreach( range(1,100) as $val) {
        $vals[$val] =$val;
    }
    $settings->add(new admin_setting_configselect(
        'quizaccess_activatedelayedattempt/startrate',
        new lang_string('quizaccess_activatedelayedattempt_startrate', 'quizaccess_activatedelayedattempt'),
        '',
        '25',
        $vals
    ));
    $settings->add(new admin_setting_configselect(
        'quizaccess_activatedelayedattempt/maxdelay',
        new lang_string('quizaccess_activatedelayedattempt_maxdelay', 'quizaccess_activatedelayedattempt'),
        '',
        '10',
        $vals
    ));
    $settings->add(new admin_setting_confightmleditor(
        'quizaccess_activatedelayedattempt/notice',
        new lang_string('quizaccess_activatedelayedattempt_notice', 'quizaccess_activatedelayedattempt'),
        '',
        '',
        PARAM_RAW,
        '60',
        '8'
    ));
}

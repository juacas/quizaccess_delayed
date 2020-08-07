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
 * Implementaton of the quizaccess_delayed plugin.
 * Based on quizaccess_activateattempt https://github.com/IITBombayWeb/moodle-quizaccess_delayed/tree/v1.0.3
 *
 * @package   quizaccess_delayed
 * @author    Juan Pablo de Castro
 * @copyright 2020 University of Valladolid, Spain
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

if ($hassiteconfig) {

    $settings->add(new admin_setting_configcheckbox(
        'quizaccess_delayed/enabled',
        new lang_string('quizaccess_delayed_enabled', 'quizaccess_delayed'),
        '',
        1
    ));

    // Allow disable the rule per instance.
    $settings->add(new admin_setting_configcheckbox(
        'quizaccess_delayed/allowdisable',
        new lang_string('quizaccess_delayed_allowdisable', 'quizaccess_delayed'),
        '',
        1
    ));
    // Default enabled state in new instances.
    $settings->add(new admin_setting_configcheckbox(
        'quizaccess_delayed/enabledbydefault',
        new lang_string('quizaccess_delayed_enabledbydefault', 'quizaccess_delayed'),
        '',
        1
    ));

    $vals = [];
    foreach( range(1,100) as $val) {
        $vals[$val] =$val;
    }
    $settings->add(new admin_setting_configselect(
        'quizaccess_delayed/startrate',
        new lang_string('quizaccess_delayed_startrate', 'quizaccess_delayed'),
        '',
        '25',
        $vals
    ));
    $settings->add(new admin_setting_configselect(
        'quizaccess_delayed/maxdelay',
        new lang_string('quizaccess_delayed_maxdelay', 'quizaccess_delayed'),
        '',
        '10',
        $vals
    ));
    $vals = [0 => get_string('none')];
    foreach (range(10, 100, 10) as $val) {
        $vals[$val] = "$val %";
    }
    $settings->add(new admin_setting_configselect(
        'quizaccess_delayed/timelimitpercent',
        new lang_string('quizaccess_delayed_timelimitpercent', 'quizaccess_delayed'),
        '',
        '10',
        $vals
    ));
    $settings->add(new admin_setting_configselect(
        'quizaccess_delayed/countertype',
        new lang_string('quizaccess_delayed_countertype', 'quizaccess_delayed'),
        '',
        'flipdown',
        [
            'flipdown' => new lang_string('flipdowncounter', 'quizaccess_delayed'),
            'text' => new lang_string('plaintextcounter', 'quizaccess_delayed')
            ]
    ));
    // Show the teacher a warning in some circunstances.
    $settings->add(new admin_setting_configcheckbox(
        'quizaccess_delayed/showdangerousquiznotice',
        new lang_string('quizaccess_delayed_showdangerousquiznotice', 'quizaccess_delayed'),
        '',
        1
    ));
    $settings->add(new admin_setting_confightmleditor(
        'quizaccess_delayed/dangerousquiznotice',
        new lang_string('quizaccess_delayed_dangerousquiznotice', 'quizaccess_delayed'),
        new lang_string('quizaccess_delayed_dangerousquiznotice_desc', 'quizaccess_delayed'),
        '',
        PARAM_RAW,
        '60',
        '8'
    ));
    $settings->add(new admin_setting_confightmleditor(
        'quizaccess_delayed/notice',
        new lang_string('quizaccess_delayed_notice', 'quizaccess_delayed'),
        new lang_string('quizaccess_delayed_notice_desc', 'quizaccess_delayed'),
        '',
        PARAM_RAW,
        '60',
        '8'
    ));

}

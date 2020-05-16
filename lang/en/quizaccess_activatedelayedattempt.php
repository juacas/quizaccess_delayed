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
 * Strings for the quizaccess_activatedelayedattempt plugin.
 * Based on quizaccess_activateattempt https://github.com/IITBombayWeb/moodle-quizaccess_activatedelayedattempt/tree/v1.0.3
 *
 * @package   quizaccess_activatedelayedattempt
 * @author    Juan Pablo de Castro <juan.pablo.de.castro@gmail.com>
 * @copyright 2020 Juan Pablo de Castro @University of Valladolid, Spain
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

$string['attemptquiz'] = 'Attempt quiz now';
$string['quizwillstartinabout'] = 'Your turn for this quiz will start in about';
$string['quizwillstartinless'] = 'Your turn for this quiz will start in less than a minute';
$string['quizaccess_activatedelayedattempt_enabled'] = 'Delayed attempt enabled';
$string['quizaccess_activatedelayedattempt_allowdisable'] = 'The teachers are allowed to disable the rule';
$string['quizaccess_activatedelayedattempt_enabledbydefault'] = 'New quizzes will use this rule by default';
$string['quizaccess_activatedelayedattempt_startrate'] = 'Entry rate (students per minute)';
$string['quizaccess_activatedelayedattempt_maxdelay'] = 'Maximum delay (minutes)';
$string['quizaccess_activatedelayedattempt_notice'] = 'Notice to students';
$string['quizaccess_activatedelayedattempt_teachernotice'] = 'This quiz will use a phased entry control, which will cause students to enter randomly with up to {$a} minutes of delay.';
$string['quizaccess_activatedelayedattempt_countertype'] ='Type of coundown to use.';
$string['pleasewait'] = 'Please wait here';
$string['noscriptwarning'] = 'This quiz requires a browser that supports JavaScript. If you have a Javascript blocker you will need to disable it.';
$string['pluginname_desc'] = 'Auto activate quiz attempt button with random delay access rule';
$string['pluginname'] = 'Entry to quiz attempt with random delay';
$string['delayedattemptlock'] = 'Gradual entry to the quiz';
$string['delayedattemptlock_help'] = 'When enabled, on loading the quiz page before the quiz start date the start attempt button is disabled transiently. 
A countdown period is started (random up to a time set up by your institution). When the countdown ends the start attempt button is re-enabled and the students can initiate the quiz attempt. ';
$string['explaindelayedattempt'] = 'Sets a random access delay';

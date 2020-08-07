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
 * Strings for the quizaccess_delayed plugin.
 * Based on quizaccess_activateattempt https://github.com/IITBombayWeb/moodle-quizaccess_delayed/tree/v1.0.3
 *
 * @package   quizaccess_delayed
 * @author    Juan Pablo de Castro <juan.pablo.de.castro@gmail.com>
 * @copyright 2020 Juan Pablo de Castro @University of Valladolid, Spain
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

$string['attemptquiz'] = 'Attempt quiz now';
$string['quizwillstartinabout'] = 'Your turn for this quiz will start in about';
$string['quizwillstartinless'] = 'Your turn for this quiz will start in less than a minute';
$string['quizaccess_delayed_enabled'] = 'Delayed attempt enabled';
$string['quizaccess_delayed_allowdisable'] = 'The teachers are allowed to disable the rule';
$string['quizaccess_delayed_enabledbydefault'] = 'New quizzes will use this rule by default';
$string['quizaccess_delayed_showdangerousquiznotice'] = 'Shows the teacher a warning if their quiz is resource intensive';
$string['quizaccess_delayed_dangerousquiznotice'] = 'Institutional message displayed if the quiz could be problematic';
$string['quizaccess_delayed_dangerousquiznotice_desc'] = 'This message will be displayed to teachers while setting up the quiz if the plugin determines that the parameters may be problematic. This is a good place to link manuals or instructions for designing less problematic quizzes.';
$string['quizaccess_delayed_startrate'] = 'Entry rate (students per minute)';
$string['quizaccess_delayed_maxdelay'] = 'Maximum delay (minutes)';
$string['quizaccess_delayed_timelimitpercent'] = 'Maximum delay as a percentage of completion time';
$string['quizaccess_delayed_notice'] = 'Notice to students';
$string['quizaccess_delayed_notice_desc'] = 'This text is shown to all students in addition to the text the teacher uses as a description of the activity. It is designed to display an institutional message, usually related to instructions on how to use the quizzes.';
$string['quizaccess_delayed_teachernotice'] = 'This quiz will use a phased entry control, which will cause students to enter randomly with up to {$a} minutes of delay.';
$string['quizaccess_delayed_teachernotice2'] = 'Message for the Teacher: Your students will see the following message while waiting:';
$string['quizaccess_delayed_countertype'] ='Type of coundown to use.';
$string['pleasewait'] = 'Please wait here';
$string['noscriptwarning'] = 'This quiz requires a browser that supports JavaScript. If you have a Javascript blocker you will need to disable it.';
$string['pluginname_desc'] = 'Auto activate quiz attempt button with random delay access rule';
$string['pluginname'] = 'Entry to quiz attempt with random delay';
$string['delayedattemptlock'] = 'Gradual entry to the quiz';
$string['delayedattemptlock_help'] = 'When enabled, on loading the quiz page before the quiz start date the start attempt button is disabled transiently.
A countdown period is started (random up to a time set up by your institution). When the countdown ends the start attempt button is re-enabled and the students can initiate the quiz attempt. ';
$string['explaindelayedattempt'] = 'Sets a random access delay';
$string['tooshortpagesadvice'] = 'The questionnaire has {$a->pages} pages that are too short. This increases the load on the server severely. Consider alloting more time for each page (i.e. putting more questions)';
$string['tooshorttimeguardadvice'] = 'An availability time of {$a->timespanstr} is too tight. Please note that some students will be delayed by up to {$a->maxdelaystr}, they have {$a->timelimitstr} to take the test and it is advisable to leave a safety margin for other delays at the start of the quiz.';
$string['flipdowncounter'] = 'Animated card counter';
$string['plaintextcounter'] = 'Plain text counter';
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
 * @author	  Enrique Castro	
 * @copyright 2020 University of Valladolid, Spain
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\notification;
use core\output\notification as OutputNotification;
use core\plugininfo\format;

defined ( 'MOODLE_INTERNAL' ) || die ();

require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');

/**
 * A rule implementing auto-appearance of “Attempt quiz now” button at quiz open timing
 * without requiring to refresh the page and with a randomized delay to spread user's starts.
 *
 * @copyright  2020 University of Valladolid, Spain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_activatedelayedattempt extends quiz_access_rule_base {
    // Cache maxdela.
    var $maxdelay = null;
    // Cache students count.
    var $students = null;
    /** Return an appropriately configured instance of this rule
     * @param quiz $quizobj information about the quiz in question.
     * @param int $timenow the time that should be considered as 'now'.
     * @param bool $canignoretimelimits whether the current user is exempt from
     *  time limits by the mod/quiz:ignoretimelimits capability.
     */
    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {
        
        if (quizaccess_activatedelayedattempt::is_enabled_in_instance($quizobj) === false ) {
            return null;
        } 
        
        return new self ( $quizobj, $timenow );
    }
  
    /**
     * Whether or not a user should be allowed to start a new attempt at this quiz now.
     * @param int $numattempts the number of previous attempts this user has made.
     * @param object $lastattempt information about the user's last completed attempt.
     */
    public function prevent_access() {
       
        $enabled = quizaccess_activatedelayedattempt::is_enabled_in_instance($this->quizobj);
        $result = "";
        if ($enabled && $this->is_pending()) { 
            $this->configure_timerscript('.quizattempt');
            $result .= "<noscript>" . get_string('noscriptwarning', 'quizaccess_activatedelayedattempt') . "</noscript>";
        }
        return $result; // Used as a prevent message.
    }

    public function description()
    {
        $message = '';
        if ($this->is_pending() && quizaccess_activatedelayedattempt::is_enabled_in_instance($this->quizobj)) {
            if (has_capability('mod/quiz:manage', $this->quizobj->get_context())) {
                // Show a warning if the quiz is resource intensive.
                $intensivequizdetection = get_config('quizaccess_activatedelayedattempt', 'showdangerousquiznotice');
                if ($intensivequizdetection) {
                    global $OUTPUT;
                    list($isintensive, $notices) = $this->get_warning_messages($this->quizobj);
                    foreach ($notices as $notice) {
                        $message .= $OUTPUT->notification( $notice, notification::WARNING); // TODO: DANGER message also!
                    }
                    // Show institutional message if the quiz is marked as intensive.
                    if ($isintensive) {
                        $message .= format_text(get_config('quizaccess_activatedelayedattempt', 'dangerousquiznotice'));
                    }
                }
                $message .= get_string('quizaccess_activatedelayedattempt_teachernotice',
                'quizaccess_activatedelayedattempt',
                ceil($this->calculate_max_delay()/60));
                $message .= "<noscript>" . get_string('noscriptwarning', 'quizaccess_activatedelayedattempt') . "</noscript>";
        // Show also the counter to the teacher.
                $this->configure_timerscript('.quizattempt');
            }
        // Show the notice to the students.
           if (has_capability('mod/quiz:attempt', $this->quizobj->get_context())) {
               $message .=  format_text(get_config('quizaccess_activatedelayedattempt', 'notice'));
           }
        }
        return $message;
    }
    /**
     * @param quiz $quizobj
     * @return array(string) Messages to show.
     */
    protected function get_warning_messages(quiz $quizobj) {
        $notices = [];
        $sections = $quizobj->get_sections();
        $hasquestions = $quizobj->has_questions();
        // Get preloaded qustions just for counting them.
        $reflection = new ReflectionClass($quizobj);
        $property = $reflection->getProperty('questions');
        $property->setAccessible(true);
        $questions = $property->getValue($quizobj); // Sections are not pages?????????
        // Count questions.
        $questioncount = count($questions);
        // count pages.
        $pages = 1;
        $hasrandom = false;
        foreach ($questions as $question) {
            if ($pages < $question->page) {
                $pages = $question->page;
            }
            if ($question->qtype == 'random') {
                $hasrandom = true;
            }
        }
        $timelimit = $quizobj->get_quiz()->timelimit;
        $timespan = $this->get_time_span($quizobj);
        $maxdelay = $this->calculate_max_delay();
        $students = $this->get_student_count($quizobj);
        $randomdelay = $this->get_user_delay();
        $rate = get_config('quizaccess_activatedelayedattempt', 'startrate');

        $timeperpage = $timespan / $pages; // if timeperpage < 10 minutes then warning!
        // Data for messages.
        $a = new stdClass();
        $a->maxdelay = format_time($maxdelay);
        $a->randomdelay = format_time($randomdelay);
        $a->timespan = format_time($timespan);
        $a->timelimit = format_time($timelimit);
        $a->pages = $pages;
        $a->students = $students;
        $tooshortpages = $timeperpage < 600 ;
        if ($tooshortpages) {
            $notices[] = get_string('tooshortpagesadvice', 'quizaccess_activatedelayedattempt', $a);
        }
        if (($timespan-$timelimit) < ($maxdelay + $timelimit * 0.2) ) {
            $notices[] = get_string('tooshorttimeguardadvice', 'quizaccess_activatedelayedattempt', $a);
        }
        
        $isintensive = (($maxdelay/$students) > $rate) || $tooshortpages; // TODO define better the formula.
        
        return array($isintensive, $notices);
    }

    /**
     * @return time span of the quiz in seconds.
     */
    protected function get_time_span($quizobj) {
        $timeclose = $quizobj->get_quiz()->timeclose;
        $timeopen = $quizobj->get_quiz()->timeopen;
        if ($timeopen == 0) {
            $timeopen = time();
        }
        if ($timeclose == 0) {
            return PHP_INT_MAX;
        }
        return $timeclose - $timeopen;
    }
    /**
     * Add any fields that this rule requires to the quiz settings form. This
     * method is called from {@link mod_quiz_mod_form::definition()}, while the
     * security seciton is being built.
     * @param mod_quiz_mod_form $quizform the quiz settings form that is being built.
     * @param MoodleQuickForm $mform the wrapped MoodleQuickForm.
     */
    public static function add_settings_form_fields(
            mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
        
        $enabled = get_config('quizaccess_activatedelayedattempt', 'enabled');
        $allowdisable = get_config('quizaccess_activatedelayedattempt', 'allowdisable');
        if($enabled && $allowdisable) {
            $enabledbydefault = get_config('quizaccess_activatedelayedattempt', 'enabledbydefault');
            $mform->addElement('advcheckbox', 'activatedelayedattempt',
                    get_string('delayedattemptlock', 'quizaccess_activatedelayedattempt'),
                    get_string('explaindelayedattempt', 'quizaccess_activatedelayedattempt'));
            $mform->setDefault('activatedelayedattempt', $enabledbydefault);
            $mform->addHelpButton('activatedelayedattempt',
                    'delayedattemptlock', 'quizaccess_activatedelayedattempt');
        }
    }
    
    /**
     * Save any submitted settings when the quiz settings form is submitted. This
     * is called from {@link quiz_after_add_or_update()} in lib.php.
     * @param object $quiz the data from the quiz form, including $quiz->id
     *      which is the id of the quiz being saved.
     * @global moodle_database $DB
     */
    public static function save_settings($quiz) {
        global $DB;
        $record = new stdClass();
        $record->quizid = $quiz->id;
        $record->activatedelayedattempt = $quiz->activatedelayedattempt;
        $DB->delete_records('quizaccess_delayedattempt', ['quizid' => $record->quizid]);
        $DB->insert_record('quizaccess_delayedattempt', $record);
    }

    /**
     * Delete any rule-specific settings when the quiz is deleted. This is called
     * from {@link quiz_delete_instance()} in lib.php.
     * @param object $quiz the data from the database, including $quiz->id
     *      which is the id of the quiz being deleted.
     * @since Moodle 2.7.1, 2.6.4, 2.5.7
     */
    public static function delete_settings($quiz) {
        global $DB;
        $DB->delete_records('quizaccess_delayedattempt', array('quizid' => $quiz->id));
    }

    /**
     * Return the bits of SQL needed to load all the settings from all the access
     * plugins in one DB query. The easiest way to understand what you need to do
     * here is probalby to read the code of {@link quiz_access_manager::load_settings()}.
     *
     * If you have some settings that cannot be loaded in this way, then you can
     * use the {@link get_extra_settings()} method instead, but that has
     * performance implications.
     *
     * @param int $quizid the id of the quiz we are loading settings for. This
     *     can also be accessed as quiz.id in the SQL. (quiz is a table alisas for {quiz}.)
     * @return array with three elements:
     *     1. fields: any fields to add to the select list. These should be aliased
     *        if neccessary so that the field name starts the name of the plugin.
     *     2. joins: any joins (should probably be LEFT JOINS) with other tables that
     *        are needed.
     *     3. params: array of placeholder values that are needed by the SQL. You must
     *        used named placeholders, and the placeholder names should start with the
     *        plugin name, to avoid collisions.
     */
    public static function get_settings_sql($quizid) {
        return array(
            'activatedelayedattempt',
            'LEFT JOIN {quizaccess_delayedattempt} delayedattempt ON delayedattempt.quizid = quiz.id',
            array());
    }    
    /**
     * Gets the delay associated to current user.
     * @return int delay in seconds.
     */
    protected function get_user_delay() {
        // Calculate a random delay to improve scalation of requests.
        $maxdelay = $this->calculate_max_delay();
        // Calculate a pseudorandom delay for the user (seconds).
        return $this->calculate_random_delay($maxdelay);
    }
    /**
     * Generates a pseudorandom delay for each user.
     * The delay should be the same every call for the same user and quiz instance.
     * @param int $maxdelay
     * @global stdClass $USER
     */
    protected function calculate_random_delay($maxdelay) {
        global $USER;
        $pseudoidx = ($USER->id * $this->quizobj->get_cmid()) % 100;
        $random = $pseudoidx * $maxdelay / 100;
        return $random;
    }
    /**
     * Gets an upper limit for the delay (in seconds).
     */
    protected function calculate_max_delay(){
        if ($this->maxdelay == null) {
            // Entries per minute.
            $rate = get_config('quizaccess_activatedelayedattempt', 'startrate');
            /** @var real $maxalloweddelay in minutes.*/
            $maxalloweddelay = get_config('quizaccess_activatedelayedattempt', 'maxdelay');
            $numalumns = $this->get_student_count($this->quizobj);
            if ($this->quiz->timelimit > 0 ) {
                // Timelimit comes in seconds.
                $maxalloweddelay = max(1, min($maxalloweddelay, $this->quiz->timelimit/60 * 0.1));
            } 
            // The maximum delay if 10% of quiz time.
            // The delay is calculated as "startrate" students per minute in average with maxalloweddelay minutes maximum.
            // The spread of delays is set from 1 to $maxalloweddelay minutes depending on number of students in the quiz.
            $this->maxdelay = min($maxalloweddelay, max(1, $numalumns / $rate )) * 60;
        }
        return $this->maxdelay;
    }
    protected function get_student_count($quizobj) {
        if ($this->students == null) {
            $this->students = count_enrolled_users($quizobj->get_context(), 'mod/quiz:attempt', 0, true);
        }
        return $this->students;
    }
    /**
     * Wheter the quiz is waiting to start for the current user.
     */
    protected function is_pending() {
        $randomdelay = $this->get_user_delay();
        return ($this->timenow < ($this->quiz->timeopen + $randomdelay));   
    }
    /**
     *  @global moodle_page $PAGE
     */
    protected function configure_timerscript($selector) {
        global $PAGE, $CFG;
        $countertype = get_config('quizaccess_activatedelayedattempt', 'countertype');

        $actionlink = "$CFG->wwwroot/mod/quiz/startattempt.php";
        $sessionkey = sesskey();
        $attemptquiz = get_string('attemptquiz', 'quizaccess_activatedelayedattempt');
        // Pass strigns to JScript.
        $langstrings = [
            'months' => get_string('months'),
            'month' => get_string('month'),
            'days' => get_string('days'),
            'day' => get_string('day'),
            'hours' => get_string('hours'),
            'hour' => get_string('hour'),
            'minutes' => get_string('minutes'),
            'minute' => get_string('minute'),
            'seconds' => get_string('seconds'),
            'pleasewait' => get_string('pleasewait', 'quizaccess_activatedelayedattempt'),
            'quizwillstartinabout' => get_string('quizwillstartinabout', 'quizaccess_activatedelayedattempt')
        ];
        // Gets the delay associated to current user.
        $randomdelay = $this->get_user_delay();
        $diff = ($this->quiz->timeopen) - ($this->timenow) + $randomdelay;
        $diffmillisecs = $diff * 1000;
        // Inject some info for debugging and testing.
        $langstrings['debug_maxdelay'] = $this->calculate_max_delay();
        $langstrings['debug_randomdebug'] = 'Random delay is ' . $randomdelay . ' seconds.';
        $PAGE->requires->js_call_amd(
            "quizaccess_activatedelayedattempt/timer_$countertype",
            'init',
            [
                $selector,
                $actionlink, $this->quizobj->get_cmid(), $sessionkey, $attemptquiz, $diffmillisecs,
                $langstrings
            ]
        );
        $PAGE->requires->css('/mod/quiz/accessrule/activatedelayedattempt/styles.css'); // Discouraged.
    }
    /**
     * Determines if this instance should apply the rule.
     */
    protected static function is_enabled_in_instance(quiz $quizobj)
    {
        $enabled = get_config('quizaccess_activatedelayedattempt', 'enabled');
        $allowdisable = get_config('quizaccess_activatedelayedattempt', 'allowdisable');
        $locallyenabled = $quizobj->get_quiz()->activatedelayedattempt;

        if ($enabled == true && ($allowdisable == false || $locallyenabled == true)) {
            return true;
        }
        return false;
    }
}


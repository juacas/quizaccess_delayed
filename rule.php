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

require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');

/**
 * A rule implementing auto-appearance of “Attempt quiz now” button at quiz open timing
 * without requiring to refresh the page and with a randomized delay to spread user's starts.
 *
 * @copyright  2020 University of Valladolid, Spain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_activatedelayedattempt extends quiz_access_rule_base {
    /** Return an appropriately configured instance of this rule
     * @param quiz $quizobj information about the quiz in question.
     * @param int $timenow the time that should be considered as 'now'.
     * @param bool $canignoretimelimits whether the current user is exempt from
     *  time limits by the mod/quiz:ignoretimelimits capability.
     */
    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {
        // This rule is always used, even if the quiz has no open or close date.
        return new self ( $quizobj, $timenow );
    }
    /**
     * Whether or not a user should be allowed to start a new attempt at this quiz now.
     * @param int $numattempts the number of previous attempts this user has made.
     * @param object $lastattempt information about the user's last completed attempt.
     */
    public function prevent_access() {
       
        $enabled = get_config('quizaccess_activatedelayedattempt', 'enabled');

        $result = "";
        if ($enabled && $this->timenow < $this->quiz->timeopen) {
            $this->configure_timerscript('.quizattempt');
            $result .= "<noscript>" . get_string('noscriptwarning', 'quizaccess_activatedelayedattempt') . "</noscript>";
        }
        return $result; // Used as a prevent message.
    }

    public function description()
    {
        $message = '';
        if ($this->is_pending() && get_config('quizaccess_activatedelayedattempt', 'enabled')) {
            if (has_capability('mod/quiz:manage', $this->quizobj->get_context())) {
                $message .= get_string('quizaccess_activatedelayedattempt_teachernotice',
                                        'quizaccess_activatedelayedattempt',
                                        ceil($this->calculate_max_delay()/60));
                $message .= "<noscript>" . get_string('noscriptwarning', 'quizaccess_activatedelayedattempt') . "</noscript>";

        //         // Show the counter to the teacher.
                $this->configure_timerscript('.quizattempt');
            }
           if (has_capability('mod/quiz:attempt', $this->quizobj->get_context())) {
               $message .=  format_text(get_config('quizaccess_activatedelayedattempt', 'notice'));
           }
        }
        return $message;
    }
    /**
     * Generates a pseudorandom delay for each user.
     * The delay should be the same every call for the same user and quiz instance.
     * @param int $maxdelay
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
        $rate = get_config('quizaccess_activatedelayedattempt', 'startrate');
        $maxalloweddelay = get_config('quizaccess_activatedelayedattempt', 'maxdelay');
        $numalumns = count_enrolled_users($this->quizobj->get_context(), 'mod/quiz:attempt', 0, true);
        // The delay is calculated as "startrate" students per minute in average with 10 minutes maximum.
        // The spread of delays is set from 1 to 10 minutes depending on number of students in the quiz.
        $maxdelay = min($maxalloweddelay * 60, max(60, $numalumns * $rate / 60));
        return $maxdelay;
    }
    /**
     * Wheter the quiz is waiting to start.
     */
    protected function is_pending() {
         return ($this->timenow < $this->quiz->timeopen);   
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
        // Calculate a random delay to improve scalation of requests.
        $maxdelay = $this->calculate_max_delay();
        // Calculate a pseudorandom delay for the user (seconds).
        $randomdelay = $this->calculate_random_delay($maxdelay);
        $diff = ($this->quiz->timeopen) - ($this->timenow) + $randomdelay;
        $diffmillisecs = $diff * 1000;
        // Inject some info for debugging and testing.
        $langstrings['debug_maxdelay'] = $maxdelay;
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
}


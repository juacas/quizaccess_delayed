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

// phpcs:disable moodle.Commenting.ValidTags.Invalid

/**
 * Implementaton of the quizaccess_delayed plugin.
 * Based on quizaccess_activateattempt https://github.com/IITBombayWeb/moodle-quizaccess_delayed/tree/v1.0.3
 *
 * @package   quizaccess_delayed
 * @author    Juan Pablo de Castro
 * @author    Enrique Castro
 * @copyright 2020 University of Valladolid, Spain
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\notification;

defined('MOODLE_INTERNAL') || die();

global $CFG;

// Check moodle version and make it compatible with 4.1 and prior.
// TODO: deprecate this patch and refactor class names.
if ($CFG->version < 2023042400) {
    // Moodle 4.1 and previous.
    require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');
} else {
    // Use aliases at class_loader level to maintain compatibility.
    \class_alias(mod_quiz\local\access_rule_base::class, quiz_access_rule_base::class);
    \class_alias(mod_quiz\quiz_settings::class, quiz::class);
}

/**
 * A rule implementing auto-appearance of “Attempt quiz now” button at quiz open timing
 * without requiring to refresh the page and with a randomized delay to spread user's starts.
 *
 * @copyright  2020 University of Valladolid, Spain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_delayed extends quiz_access_rule_base {
    /**
     * @var int Cache maxdelay in seconds.
     */
    protected $maxdelay = null;
    /**
     * @var int Cache students count.
     */
    protected $students = null;
    /**
     * Return an appropriately configured instance of this rule.
     * @param quiz $quizobj information about the quiz in question.
     * @param int $timenow the time that should be considered as 'now'.
     * @param bool $canignoretimelimits whether the current user is exempt from
     *  time limits by the mod/quiz:ignoretimelimits capability.
     */
    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {

        if (self::is_enabled_in_instance($quizobj) === false) {
            return null;
        }

        return new self($quizobj, $timenow);
    }

    /**
     * Whether or not a user should be allowed to start a new attempt at this quiz now.
     */
    public function prevent_access() {
        // Check if the rule is enabled for this instance and if we are pending.
        $enabled = self::is_enabled_in_instance($this->quizobj);
        $result = "";
        if ($enabled && $this->is_pending()) {
            $this->configure_timerscript('.quizattempt');
            $result .= get_string(
                'quizaccess_delayed_teachernotice',
                'quizaccess_delayed',
                ceil($this->calculate_max_delay() / 60)
            );
        }
        return $result; // Used as a prevent message.
    }
    /**
     * Generates HTML to be shown and javascript for the counter.
     * @global moodle_page $PAGE
     */
    public function description() {
        $message = '';
        global $PAGE;
        /** @var core_renderer $output */
        $output = $PAGE->get_renderer('core');
        if ($this->is_pending() && self::is_enabled_in_instance($this->quizobj)) {
            if (has_capability('mod/quiz:manage', $this->quizobj->get_context())) { // phpcs:ignore PHP0406
                // Show a warning if the quiz is resource intensive.
                $intensivequizdetection = get_config('quizaccess_delayed', 'showdangerousquiznotice');
                if ($intensivequizdetection) {
                    $diags = $this->get_quiz_diagnosis($this->quizobj);
                    foreach ($diags->notices as $notice) {
                        $message .= $output->notification($notice, notification::WARNING); // phpcs:ignore PHP0406
                    }
                    // Show institutional message if the quiz is marked as intensive.
                    if ($diags->isintensive) {
                        $message .= format_text(
                            get_config('quizaccess_delayed', 'dangerousquiznotice'),
                            FORMAT_MOODLE,
                            ['newlines' => false]
                        );
                    }
                }
                $message .= $output->notification(
                    get_string('quizaccess_delayed_teachernotice', 'quizaccess_delayed', ceil($this->calculate_max_delay() / 60)),
                    notification::INFO
                );
                $message .= "<noscript>" . get_string('noscriptwarning', 'quizaccess_delayed') . "</noscript>";
                // Show also the counter to the teacher.
                $this->configure_timerscript('.quizattempt');
            }
            // Show the notice to the students.
            $studentmsg = format_text(
                get_config('quizaccess_delayed', 'notice'),
                FORMAT_MOODLE,
                ['newlines' => false]
            );
            if (has_capability('mod/quiz:attempt', $this->quizobj->get_context())) { // phpcs:ignore PHP0406
                $message .= $output->box($studentmsg);
            } else if (
                has_capability('mod/quiz:manage', $this->quizobj->get_context()) // phpcs:ignore PHP0406
                && $studentmsg != ''
            ) {
                // Show the teachers what the students will see.
                $message .= $output->box(
                    get_string('quizaccess_delayed_teachernotice2', 'quizaccess_delayed')
                    . $studentmsg
                );
            }
        }
        // If called by a web service, return plain text only.
        if (defined('WS_SERVER') && WS_SERVER) {
            return trim(strip_tags($message));
        }
        return $message;
    }
    /**
     * Return diagnostics about the timing of the quiz and compose a message to give advice to teachers.
     * A quiz is resource-intensive if:
     * its short-timed AND
     * entry rate is greater than startrate students per minute,
     * has too short pages (multiplies requests).
     * @param quiz $quizobj
     * @return stdClass diagnostics and messages to show.
     */
    protected function get_quiz_diagnosis(quiz $quizobj) {
        $quizobj->has_questions();
        $questions = $quizobj->get_questions(null, false);
        // Data for messages.
        $a = new stdClass();
        $a->notices = [];
        // Count pages.
        $a->pages = 1;
        foreach ($questions as $question) {
            if ($a->pages < $question->page) {
                $a->pages = $question->page;
            }
        }
        $a->timelimit = $quizobj->get_quiz()->timelimit;
        $a->students = $this->get_student_count($quizobj);
        $a->timespan = $this->get_time_span($quizobj);

        $a->maxdelay = $this->calculate_max_delay();
        $a->rate = max(1, (int)get_config('quizaccess_delayed', 'startrate'));
        $a->timeperpage = $a->timespan / $a->pages; // Remember that if timeperpage < 10 minutes then warning!

        $a->maxdelaystr = format_time($a->maxdelay);
        $a->timespanstr = format_time($a->timespan);
        $a->timelimitstr = format_time($a->timelimit);

        // Heuristics for diagnosis.
        $a->tooshortpages = $a->timeperpage < 600;
        $a->iscriticaltimelimit = ($a->timespan - $a->timelimit) < ($a->maxdelay + $a->timelimit * 0.1);
        $a->isshorttimed = $a->timelimit > 0 && ($a->iscriticaltimelimit || ($a->timespan / $a->timelimit < 1.2));

        $a->isintensive = $a->isshorttimed
                            && (($a->students * 60 / max(1, $a->maxdelay)) > $a->rate || $a->tooshortpages);
        // Advice messages.
        if ($a->tooshortpages) {
            $a->notices[] = get_string('tooshortpagesadvice', 'quizaccess_delayed', $a);
        }
        if ($a->iscriticaltimelimit) {
            $a->notices[] = get_string('tooshorttimeguardadvice', 'quizaccess_delayed', $a);
        }
        return $a;
    }

    /**
     * Get the time span of the quiz in seconds.
     * @return int time span of the quiz in seconds.
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
        mod_quiz_mod_form $quizform,
        MoodleQuickForm $mform
    ) {

        $enabled = get_config('quizaccess_delayed', 'enabled');
        $allowdisable = get_config('quizaccess_delayed', 'allowdisable');
        if ($enabled && $allowdisable) {
            $enabledbydefault = get_config('quizaccess_delayed', 'enabledbydefault');
            $mform->addElement(
                'advcheckbox',
                'delayedattempt',
                get_string('delayedattemptlock', 'quizaccess_delayed'),
                get_string('explaindelayedattempt', 'quizaccess_delayed')
            );
            $mform->setDefault('delayedattempt', $enabledbydefault);
            $mform->addHelpButton(
                'delayedattempt',
                'delayedattemptlock',
                'quizaccess_delayed'
            );
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
        $record->delayedattempt = self::is_enabled_in_quiz($quiz);
        $DB->delete_records('quizaccess_delayed', ['quizid' => $record->quizid]);
        $DB->insert_record('quizaccess_delayed', $record);
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
        $DB->delete_records('quizaccess_delayed', ['quizid' => $quiz->id]);
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
        return [
            'quizaccess_delayed_rule.delayedattempt AS delayedattempt',
            'LEFT JOIN {quizaccess_delayed} quizaccess_delayed_rule ON quizaccess_delayed_rule.quizid = quiz.id',
            []];
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
        $pseudoidx = ($USER->id + $this->quizobj->get_cmid()) % 100;
        $random = $pseudoidx * $maxdelay / 100;
        return (int)floor($random);
    }
    /**
     * Gets an upper limit for the delay (in seconds).
     * The limit is calculated from the number of students in the quiz,
     * the configured entry rate and the quiz timelimit.
     * @return int max delay in seconds.
     */
    protected function calculate_max_delay() {
        if ($this->maxdelay === null) {
            // Entries per minute.
            $rate = max(1, (int)get_config('quizaccess_delayed', 'startrate'));
            /** @var int $maxalloweddelay in minutes.*/ // phpcs:ignore
            $maxalloweddelay = max(1, (int)get_config('quizaccess_delayed', 'maxdelay'));
            $numalumns = $this->get_student_count($this->quizobj);
            if ($this->quiz->timelimit > 0) {
                $percent = get_config('quizaccess_delayed', 'timelimitpercent');
                if ($percent > 0) {
                    // Timelimit comes in seconds.
                    // The maximum delay if percent% of quiz time.
                    $maxalloweddelay = max(1, min($maxalloweddelay, $this->quiz->timelimit / 60 * $percent / 100));
                } else {
                    $maxalloweddelay = max(1, $maxalloweddelay);
                }
            }
            // The delay is calculated as "startrate" students per minute in average with maxalloweddelay minutes maximum.
            // The spread of delays is set from 1 to $maxalloweddelay minutes depending on number of students in the quiz.
            $this->maxdelay = (int)round(min($maxalloweddelay, max(1, $numalumns / $rate)) * 60);
        }
        return $this->maxdelay;
    }
    /**
     * Get the number of students enrolled in the quiz.
     * @param quiz $quizobj
     * @return int number of students.
     */
    protected function get_student_count($quizobj) {
        if ($this->students === null) {
            $quizzes = [];
            if (get_config('quizaccess_delayed', 'sitewidecount') && $quizobj->get_quiz()->timeopen > 0) {
                // Get quizzes that are about to start. Current quiz should be included.
                $timeopen = $this->quiz->timeopen;
                $maxalloweddelay = get_config('quizaccess_delayed', 'maxdelay') * 60;
                global $DB;
                $quizzes = $DB->get_records_select(
                    'quiz',
                    'timeopen >= ?
                    AND timeopen <= ?',
                    [$timeopen - $maxalloweddelay, $timeopen + $maxalloweddelay]
                );
            } else {
                // Just this quiz.
                $quizzes = [$quizobj];
            }
            $count = 0; // Accumulate potential students.
            $countedcourses = []; // Avoid counting courses twice.
            foreach ($quizzes as $quiz) {
                if ($quiz instanceof quiz) {
                    $quizobjtemp = $quiz;
                } else {
                    // Load quiz object if needed.
                    [$course, $cm] = get_course_and_cm_from_instance($quiz, 'quiz');
                    $quizobjtemp = new quiz($quiz, $cm, $course);
                }
                if ($countedcourses[$quizobjtemp->get_courseid()] ?? false) {
                    // Course already counted.
                    continue;
                }
                $count += count_enrolled_users(
                    $quizobjtemp->get_context(), // phpcs:ignore PHP0406
                    'mod/quiz:attempt',
                    0,
                    true
                );
                // Mark course as counted.
                $countedcourses[$quizobjtemp->get_courseid()] = true;
            }
            $this->students = $count;
        }
        return $this->students;
    }
    /**
     * Wheter the quiz is waiting to start for the current user.
     */
    protected function is_pending() {
        // Check if this user has done the quiz already.
        $randomdelay = $this->get_user_delay();
        return $this->timenow < ($this->quiz->timeopen + $randomdelay);
    }
    /**
     * Generates HTML to be shown and javascript for the counter.
     * @param string $selector CSS selector to find the container where to show the counter.
     * @global moodle_page $PAGE
     */
    protected function configure_timerscript($selector) {
        global $PAGE;
        $countertype = get_config('quizaccess_delayed', 'countertype');
        if (!in_array($countertype, ['flipdown', 'text'], true)) {
            $countertype = 'text';
        }

        // Pass strings to JavaScript.
        $langstrings = [
            'days' => get_string('days'),
            'day' => get_string('day'),
            'hours' => get_string('hours'),
            'hour' => get_string('hour'),
            'minutes' => get_string('minutes'),
            'minute' => get_string('minute'),
            'seconds' => get_string('seconds'),
            'pleasewait' => get_string('pleasewait', 'quizaccess_delayed'),
            'quizwillstartinabout' => get_string('quizwillstartinabout', 'quizaccess_delayed'),
        ];
        // Gets the delay associated to current user.
        $diff = ($this->quiz->timeopen) - ($this->timenow) + $this->get_user_delay();
        $diffmillisecs = $diff * 1000;
        $args = [$selector, $diffmillisecs, $langstrings];
        if ($countertype === 'flipdown') {
            $PAGE->requires->css(new moodle_url('/mod/quiz/accessrule/delayed/flipdown/flipdown.css'));
            $args[] = (new moodle_url('/mod/quiz/accessrule/delayed/flipdown/flipdown.min.js'))->out(false);
        }
        $PAGE->requires->js_call_amd(
            "quizaccess_delayed/timer_$countertype",
            'init',
            $args
        );
        $PAGE->requires->css(new moodle_url('/mod/quiz/accessrule/delayed/styles.css'));
    }
    /**
     * Determines if this instance should apply the rule.
     * @param quiz $quizobj
     * @return bool true if the rule is enabled for this instance.
     */
    protected static function is_enabled_in_instance(quiz $quizobj) {
        if ($quizobj->get_quiz()->timeopen == 0) {
            // No delay if the quiz is always open.
            return false;
        }
        $quiz = $quizobj->get_quiz();
        return self::is_enabled_in_quiz($quiz);
    }
    /**
     * Apply enablement admin settings over plugin configuration.
     * @param stdClass $quiz structure with quiz form data.
     */
    protected static function is_enabled_in_quiz($quiz) {
        $enabled = get_config('quizaccess_delayed', 'enabled');
        $allowdisable = get_config('quizaccess_delayed', 'allowdisable');
        // Issue #21: If the quiz was created before the plugin was installed, we need to apply the defaults.
        $locallyenabled = isset($quiz->delayedattempt) ?
            $quiz->delayedattempt :
            get_config('quizaccess_delayed', 'enabledbydefault');

        if ($enabled == true && ($allowdisable == false || $locallyenabled == true)) {
            return true;
        }
        return false;
    }
}

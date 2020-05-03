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
 * Implementaton of the quizaccess_activatedelayedattempt timer JScript.
 * Based on quizaccess_activateattempt https://github.com/IITBombayWeb/moodle-quizaccess_activatedelayedattempt/tree/v1.0.3
 *
 * @package   quizaccess_activatedelayedattempt
 * @author    Juan Pablo de Castro
 * @copyright 2020 University of Valladolid, Spain
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define( ['jquery'], function ($) {
    var strings;
    var quizOpenTime;
    var interval;
    return {
        get_string: function (key, component, param = null) {
            return strings[key];
        },
        set_strings: function (strs) {
            strings = strs;
        },
        /**
         * Init function.
         */
        init: function (selector = '.continuebutton', actionlink, cmid, sessionkey, attemptquiz, diffmillisecs, langstrings) {
            // Initialize strings to avoid json requests.
            this.set_strings(langstrings);
           
            var form = $('<form/>', {
                'method': 'post',
                'action': actionlink
            }).append(
                $('<input>', {
                    'type': 'hidden',
                    'name': 'cmid',
                    'value': cmid
                }),
                $('<input>', {
                    'type': 'hidden',
                    'name': 'sesskey',
                    'value': sessionkey
                }),
                $('<input>', {
                    'type': 'submit',
                    'class': 'btn btn-secondary',
                    'id': 'startAttemptButton',
                    'value': attemptquiz
                }));
            var divsection = $('<div id="activatedelayedattemptnotification" />')
                .append(
                    $('<p>', {
                        'id': 'activatedelayedtimer'
                    }),
                );
            $(selector).html(divsection).append(form); // Clean previous message.
            $('#startAttemptButton').prop('disabled', true);

            quizOpenTime = new Date().getTime() + diffmillisecs;
            interval = setInterval(this.update_time.bind(this), 1000);
        },
        update_time: function () {
            var currentTime = new Date().getTime();
            var countDownTime = quizOpenTime - currentTime;

            var datetxt = this.get_nice_duration(countDownTime / 1000, true, false, 2);
            document.getElementById('activatedelayedtimer').innerHTML =
                this.get_string('quizwillstartinabout') + ' ' +
                datetxt + ' ' +
                this.get_string('pleasewait');

            if (countDownTime < 0) {
                $('#activatedelayedtimer').hide();
                $('#startAttemptButton').show().prop('disabled', false);
                clearInterval(interval);
            }
        },
        /**
         * Format a human-readable format for a duration in months or days and below.
         * calculates from seconds to months.
         * trim the details to the two more significant units
         * @param int durationinseconds
         * @param boolean usemonths if false render in days.
         * @param boolean shortprecission if true only the most significative unit.
         * @return string
         */
        get_nice_duration: function (durationinseconds, usemonths = true, shortprecission = false, depth = 2) {
            var durationstring = '';
            var durationproms = [];
            var stop = false;
            var durationinseconds;
            var months;
            if (usemonths) {
                months = Math.floor(durationinseconds / (3600 * 24 * 30));
                durationinseconds -= months * (3600 * 24 * 30);
            }
            var days = Math.floor(durationinseconds / (3600 * 24));
            durationinseconds -= days * 3600 * 24;
            var hours = Math.floor(durationinseconds / 3600);
            durationinseconds -= hours * 3600;
            var minutes = Math.floor(durationinseconds / 60);
            var seconds = Math.round(durationinseconds - minutes * 60);

            if (usemonths && months > 0) {
                durationproms.push(months + this.get_string('month' + (months > 1 ? 's' : '')));
                hours = 0;
                minutes = 0;
                seconds = 0;
                if (shortprecission) {
                    stop = true;
                }
            }
            if (days > 0 && stop === false) {
                durationproms.push(' ' + days + ' ' + this.get_string('day' + (days > 1 ? 's' : '')));
                // Trim details less significant.
                if (depth < 2) {
                    minutes = 0;
                    seconds = 0;
                }
                if (shortprecission) {
                    stop = true;
                }
            }
            if (hours > 0 && stop === false) {
                durationproms.push(' ' + hours + ' ' + this.get_string('hour' + (hours > 1 ? 's' : '')));
                if (depth < 2) {
                    seconds = false;
                }
                if (shortprecission) {
                    stop = true;
                }
            }
            if (minutes > 0 && stop === false) {
                durationproms.push(' ' + minutes + ' ' + this.get_string('minute' + (minutes > 1 ? 's' : '')));
                if (shortprecission) {
                    stop = true;
                }
            }
            if (seconds > 0 && stop === false) {
                durationproms.push(' ' + seconds + ' s.');
            }
            if (durationproms.length === 0) {
                durationproms.push('-');
            }
            return durationproms.join('');
        }
    }
}
);
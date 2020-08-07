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
 * Implementaton of the quizaccess_delayed timer JScript.
 * Based on quizaccess_activateattempt https://github.com/IITBombayWeb/moodle-quizaccess_delayed/tree/v1.0.3
 *
 * @package   quizaccess_delayed
 * @author    Juan Pablo de Castro
 * @copyright 2020 University of Valladolid, Spain
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define( ['jquery'], function ($) {
    var strings;
    var quizOpenTime;
    return {
        get_string: function (key, component, param = null) {
            return strings[key];
        },
        set_strings: function (strs) {
            strings = strs;
        },
        /**
         * Init function.
         * @param selector for inserting the counter. Defaults '.continuebutton'
         */
        init: function (selector = '.continuebutton', actionlink, cmid, sessionkey, attemptquiz, diffmillisecs, langstrings) {
            if ($('.quizattempt #delayednotification').length > 0) {
                return false;
            }
            // Initialize strings to avoid json requests.
            this.set_strings(langstrings);
            quizOpenTime = new Date().getTime() + diffmillisecs;

            // Load flipboard.
            $('<link>')
                .appendTo('head')
                .attr({
                    type: 'text/css',
                    rel: 'stylesheet',
                    href: 'accessrule/delayed/flipdown/flipdown.css'
                });
            jQuery.getScript('accessrule/delayed/flipdown/flipdown.js', this.startCounter.bind(this));
            // Containes for counter.
            var divcounter = $('<center>'
                + langstrings.quizwillstartinabout
                + '<div id="flipdown" class="flipdown"></div>'
                + '<p>' + langstrings.pleasewait + '</p>'
                + '</center>');
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
                    $('<p>', {
                        'id': 'activatedelayedtimer'
                    }),
                    $('<input>', {
                        'type': 'submit',
                        'class': 'btn btn-secondary',
                        'id': 'startAttemptButton',
                        'disabled': true,
                        'value': attemptquiz
                    }));
            var divsection = $('<div id="delayednotification"/>').append(divcounter);
            $(selector).prepend(form, $('</br>'));
            // Insert above other buttons and messages.
            $(selector).prepend(divsection);
            $('[id=startAttemptButton]').prop('disabled', true);
        },
        startCounter: function () {
            new FlipDown(quizOpenTime / 1000, {
                theme: 'dark',
                headings: ['', '', '', '']
            })
                .start()
                .ifEnded(this.activateAttempt);
            // Adjust div width.
            $("#flipdown").width($("#flipdown").find(".rotor").length * 32);
        },
        activateAttempt: function () {
            var currentTime = new Date().getTime();
            var countDownTime = quizOpenTime - currentTime;
            if (countDownTime < 0) {
                $('#flipdown').hide();
                $('#delayednotification').hide();
                $('#startAttemptButton').show().prop('disabled', false);
            } else {
                // Retry in case of a small clock drift.
                setTimeout(this.activateAttempt, 1000);
            }
        }
    }
}
);
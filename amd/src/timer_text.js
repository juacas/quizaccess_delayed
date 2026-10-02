// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Text countdown for delayed quiz access.
 *
 * @module quizaccess_delayed/timer_text
 * @copyright 2020 University of Valladolid, Spain
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {
    /**
     * Display a duration using the two largest nonzero units.
     *
     * @param {Number} seconds Remaining seconds.
     * @param {Object} strings Translated labels.
     * @returns {String} Human readable duration.
     */
    function formatDuration(seconds, strings) {
        var units = [
            [86400, 'day', 'days'],
            [3600, 'hour', 'hours'],
            [60, 'minute', 'minutes'],
            [1, 'second', 'seconds']
        ];
        var parts = [];
        units.forEach(function(unit) {
            var count = Math.floor(seconds / unit[0]);
            if (count > 0 && parts.length < 2) {
                parts.push(count + ' ' + strings[count === 1 ? unit[1] : unit[2]]);
                seconds -= count * unit[0];
            }
        });
        return parts.join(' ');
    }

    return {
        /**
         * Start the countdown and reload when the server should allow entry.
         *
         * @param {String} selector Container selector.
         * @param {Number} delayms Time remaining in milliseconds.
         * @param {Object} strings Translated labels.
         */
        init: function(selector, delayms, strings) {
            var container = $(selector);
            if (!container.length || $('#delayednotification').length) {
                return;
            }
            var message = $('<p>', {id: 'delayednotification', 'class': 'delayednotification'});
            container.prepend(message);
            var deadline = Date.now() + Math.max(0, delayms);
            var interval;
            var update = function() {
                var remaining = Math.ceil((deadline - Date.now()) / 1000);
                if (remaining <= 0) {
                    clearInterval(interval);
                    window.location.reload();
                    return;
                }
                message.text(strings.quizwillstartinabout + ' ' +
                    formatDuration(remaining, strings) + ' ' + strings.pleasewait);
            };
            interval = setInterval(update, 1000);
            update();
        }
    };
});

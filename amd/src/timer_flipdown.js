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
 * Animated countdown for delayed quiz access.
 *
 * @module quizaccess_delayed/timer_flipdown
 * @copyright 2020 University of Valladolid, Spain
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {
    return {
        /**
         * Start the countdown and reload when the server should allow entry.
         *
         * @param {String} selector Container selector.
         * @param {Number} delayms Time remaining in milliseconds.
         * @param {Object} strings Translated labels.
         * @param {String} scripturl Absolute URL of the FlipDown library.
         */
        init: function(selector, delayms, strings, scripturl) {
            var container = $(selector);
            if (!container.length || $('#delayednotification').length) {
                return;
            }
            var deadline = Date.now() + Math.max(0, delayms);
            var notification = $('<div>', {id: 'delayednotification', 'class': 'delayednotification'});
            notification.append($('<p>').text(strings.quizwillstartinabout));
            notification.append($('<div>', {id: 'flipdown', 'class': 'flipdown'}));
            notification.append($('<p>').text(strings.pleasewait));
            container.prepend(notification);

            // Reload independently of the animation, including when its script cannot load.
            var reload = function() {
                if (Date.now() >= deadline) {
                    window.location.reload();
                } else {
                    setTimeout(reload, Math.min(60000, deadline - Date.now()));
                }
            };
            setTimeout(reload, Math.min(60000, Math.max(0, delayms)));
            $.getScript(scripturl).done(function() {
                new window.FlipDown(Math.ceil(deadline / 1000), {
                    theme: 'dark',
                    headings: ['', '', '', '']
                }).start();
            });
        }
    };
});

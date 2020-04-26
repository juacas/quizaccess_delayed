define('quizaccess_activatedelayedattempt/timer', ['jquery'], function ($, dyndate) {
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
         *
         */
        init: function (actionlink, cmid, sessionkey, attemptquiz, diffmillisecs, langstrings,
            quizwillstartinless, quizwillstartinabout) {
            // Initialize strings to avoid json requests.
            this.set_strings(langstrings);

            $('.continuebutton').prepend(
                $('</br>'),
                $('<form/>', {
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
                    }),
                    $('<p>', {
                        'id': 'timer'
                    })
                ),
                $('</br>')
            );

            $('#startAttemptButton').hide();
            quizOpenTime = new Date().getTime() + diffmillisecs;
            interval = setInterval(this.update_time.bind(this), 1000);
        },
        update_time: function () {
            var currentTime = new Date().getTime();
            var countDownTime = quizOpenTime - currentTime;

            var datetxt = this.get_nice_duration(countDownTime / 1000, false, false, 2);
            document.getElementById('timer').innerHTML = this.get_string('quizwillstartinabout') + datetxt;

            if (countDownTime < 0) {
                $('#timer').hide();
                $('#startAttemptButton').show();
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
# Quiz Access Rule quizaccess_activatedelayedattempt
Auto Activate the Quiz Attempt Button with a randomized delay to reduce the load at the start of the quizes.

## Background and Need

1) In a quiz with strict time constraints students tend to refresh the page too often, just to make sure if the attempt is available yet.
2) This unnecessarily increases the server load, and is a problem with large number of students.
3) Synchronized start of many attempts places a heavy short-time load in the Quiz core engine. By phasing in access, the impact is minimized.

## Solution

The “Activate Delayed Attempt” plugin makes the “Attempt quiz now” button auto-enable at quiz open timing plus a randomized delay, without requiring to refresh the page.
This is done by a client side countdown timer (javascript) which is initiated when the page is rendered in the browser. 
The plugin is implemented as an access-rule plugin overriding the default activity page render.  
The page, displays the time remaining to start the quiz using an animated countdown. 
A pseudo-random delay is assigned to each student depending on the number of students and a fixed rate of starts.
An optional message can be defined for all quizzes in the platform.

## Releases

- v1.1.1b Management controls in system settings.
- v1.1.1 Animated countdown.
- v1.1.0 Textual countdowmn.

## Installation

1) Unzip it into /mod/quiz/accessrule/activatedelayedattempt
2) Log in into Moodle
3) A notification will appear stating “Plugins requiring attention”.
4) Complete the installation by clicking on “Upgrade Moodle database now”,click on continue after the success 
notification appears on the page.

## Usage

If you enable quiz open time in quiz settings, quiz students will no longer have to manually refresh the 
page in order to get “Attempt Quiz Now” button at the quiz open timing.
A random delay up to 10 minutes is assigned to each student for spreading the entry times.

## Acknowledgements

This plugin is based on the previous work in the quizaccess_activateattempt plugin.
Customization by quiz instance code was contributed by Enrique Castro @ULPGC.
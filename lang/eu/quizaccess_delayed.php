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

$string['attemptquiz'] = 'Saiatu orain galdetegia egiten';
$string['delayedattemptlock'] = 'Galdetegirako sarbide mailakatua';
$string['delayedattemptlock_help'] = 'Markatuz gero, galdetegiaren saiakera hasteko botoia ausazko atzerapenarekin erakusten da.
Atzerako kontaketa bat hasten da (ausazkoa erakundeak zehaztutako denbora-muga batera arte). Atzerako kontaketa amaitzean saiakera hasteko botoia berriz erakusten da eta ikasleak saiakera hasi ahalko du.';
$string['explaindelayedattempt'] = 'Ezarri sarbiderako ausazko atzerapena';
$string['flipdowncounter'] = 'Fitxen bidezko atzerako kontaketa animatua';
$string['noscriptwarning'] = 'Galdetegi honek JavaScript onartzen duen nabigatzaile bat behar du. JavaScript-eko blokeatzailea bat erabiltzen baduzu desgaitu beharko duzu.';
$string['plaintextcounter'] = 'Testuzko atzerako kontaketa';
$string['pleasewait'] = 'Itxaron ezazu hemen, mesedez';
$string['pluginname'] = 'Galdetegiaren saiakerei ausazko atzerapena ezartzeko sarbide-araua';
$string['pluginname_desc'] = 'Aktibatu automatikoki galdetegiaren saiakera hasteko botoia ausazko atzerapenarekin erakusteko sarbide-araua';
$string['quizaccess_delayed_allowdisable'] = 'Irakasleek araua desgaitu dezakete';
$string['quizaccess_delayed_countertype'] = 'Erabili beharreko atzerako kontaketa mota';
$string['quizaccess_delayed_dangerousquiznotice'] = 'Galdetegia problematikoa izan badaiteke irakasleari erakusten zaion mezu instituzionala';
$string['quizaccess_delayed_enabled'] = 'Atzeratutako saiakerak gaituta';
$string['quizaccess_delayed_enabledbydefault'] = 'Erabili arau hau modu lehenetsian galdetegi berrietan';
$string['quizaccess_delayed_maxdelay'] = 'Gehienezko atzerapena (minututan)';
$string['quizaccess_delayed_notice'] = 'Ikasleentzako oharra';
$string['quizaccess_delayed_showdangerousquiznotice'] = 'Irakasleei mezu bat erakutsiko die galdetegiak baliabideen erabilera intentsiboa egingo badu';
$string['quizaccess_delayed_startrate'] = 'Sarrera-ratioa (ikasleak minutuko)';
$string['quizaccess_delayed_teachernotice'] = 'Galdetegi honek sarbide mailakaturako kontrola erabiltzen du, eta honek ikasleak modu mailakatuan gehienezko {$a} minutuko atzerapenarekin ausaz sartzea eragingo du.';
$string['quizaccess_delayed_timelimitpercent'] = 'Gehienezko atzerapena osaketa-denboraren ehuneko gisa';
$string['quizwillstartinabout'] = 'Galdetegi honetako zure txanda atzerako kontaketa amaitzean hasiko da:';
$string['quizwillstartinless'] = 'Galdetegi honetako zure txanda minutu bat baino gutxiagoan hasiko da';
$string['tooshortpagesadvice'] = 'Galdetegi honek laburrak diren {$a->pages} orri dauka. Honek zerbitzariari gainkarga ezartzen dionez, posiblea balitz orri bakoitzean galdera gehiago jartzea gomendagarria litzateke.';
$string['tooshorttimeguardadvice'] = 'Galdetegia egiteko {$a->timespanstr}ko denbora-tartea estuegia da. Kontuan izan ezazu ikasleetako batzuk {$a->maxdelaystr}ko atzerapenarekin hasiko dutela galdetegia eta {$a->timelimitstr}ko denbora-muga izango dutela galdetegia egiteko. Hori dela eta, gomendagarria da galdetegia egiteko denbora-tarteari gutxienez {$a->maxdelaystr}ko segurtasun-tarte bat gehitzea.';

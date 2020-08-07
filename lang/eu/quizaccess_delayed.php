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
$string['quizwillstartinabout'] = 'Galdetegi honetako zure txanda epe honetan hasiko da:';
$string['quizwillstartinless'] = 'Galdetegi honetako zure txanda minutu bat baino gutxiagoan hasiko da';
$string['quizaccess_delayed_enabled'] = 'Atzeratutako saiakerak gaituta';
$string['quizaccess_delayed_allowdisable'] = 'Irakasleek araua desgaitu dezakete';
$string['quizaccess_delayed_enabledbydefault'] = 'Galdetegi berriek arau hau erabiliko dute modu lehenetsian';
$string['quizaccess_delayed_showdangerousquiznotice'] = 'Irakasleei mezu bat erakutsiko die galdetegiak baliabideen erabilera intentsiboa egingo badu';
$string['quizaccess_delayed_dangerousquiznotice'] = 'Galdetegia problematikoa izan badaiteke erakusten den mezu instituzionala';
$string['quizaccess_delayed_startrate'] = 'Sarrera-ratioa (ikasleak minutuko)';
$string['quizaccess_delayed_maxdelay'] = 'Gehienezko atzerapena (minututan)';
$string['quizaccess_delayed_timelimitpercent'] = 'Gehienezko atzerapena osaketa-denboraren ehuneko gisa';
$string['quizaccess_delayed_notice'] = 'Ikasleentzako oharra';
$string['quizaccess_delayed_teachernotice'] = 'Galdetegi honek sarbide mailakaturako kontrola erabiltzen du, eta honek ikasleak modu mailakatuan gehienezko {$a} minutuko atzerapenarekin ausaz sartzea eragingo du.';
$string['quizaccess_delayed_countertype'] ='Erabili beharreko atzerako kontaketa mota.';
$string['pleasewait'] = 'Itxaron ezazu hemen, mesedez';
$string['noscriptwarning'] = 'Galdetegi honek JavaScript onartzen duen nabigatzaile bat behar du. JavaScript-eko blokeatzailea bat erabiltzen baduzu desgaitu beharko duzu.';
$string['pluginname_desc'] = 'Aktibatu automatikoki Auto activate quiz attempt button with random delay access rule';
$string['pluginname'] = 'Galdetegira ausazko atzerapena duten saiakeren sarbidea';
$string['delayedattemptlock'] = 'Galdetegirako sarbide mailakatua';
$string['delayedattemptlock_help'] = 'Gaituz gero, galdetegiaren hasiera-dataren aurretik saiakera hasteko botoia aldi baterako desgaitzen du. 
Atzerako kontaketa bat hasten da (ausazkoa erakundeak zehaztutako denbora-muga batera arte). Atzerako kontaketa amaitzean saiakera hasteko botoia berriz erakusten da eta ikasleak saiakera hasi ahalko du.';
$string['explaindelayedattempt'] = 'Sarbiderako ausazko atzerapena ezartzen du';
$string['tooshortpagesadvice'] = 'Galdetegiak motzak diren {$a->pages} orri dauzka. Honek zerbitzariari gainkarga ezartzen dio. Hausnartu ezazu orri bakoitzean galdera gehiago jartzeko aukeraren inguruan.';
$string['tooshorttimeguardadvice'] = '{$a->timespanstr}-(e)ko eskuragarritasun-denbora estuegia da. Kontuan izan mesedez ikasleetako batzuk {$a->maxdelaystr}-(e)ko atzerapenarekin hasiko dutela galdetegia eta {$a->timelimitstr} izango dute galdetegia egiteko, eta gomendagarria da galdetegia hasteko bestelako atzerapenentzako segurtasun-tarte bat izatea.';
$string['flipdowncounter'] = 'Fitxen bidezko atzerako kontaketa animatua';
$string['plaintextcounter'] = 'Testuzko atzerako kontaketa';

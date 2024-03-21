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

// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Display information about all the mod_hybridteaching modules in the requested course. *
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


$string['pluginname'] = 'Irakaskuntza hibridoa';
$string['modulename'] = 'Irakaskuntza hibridoa';
$string['modulenameu'] = 'Irakaskuntza_hibridoa';
$string['modulenameplural'] = 'Irakaskuntza hibridoa';
$string['hybridteachingname'] = 'Izena';
$string['pluginadministration'] = 'Irakaskuntza-administrazio hibridoa';

$string['sectionsessions '] = 'Saioaren denbora';
$string['sectionaudience'] = 'Partaideen sarbidea eta rola';
$string['sectionsessionaccess'] = 'Saiorako sarbidea';
$string['sectioninitialstates'] = 'Bideokonferentziaren hasierako egoerak';
$string['sectionrecording'] = 'Grabatzeko aukerak';
$string['sectionattendance '] = 'Asistentzia erregistroa';
$string['sessionscheduling'] = 'Erabili saioaren programazioa';
$string['undatedsession'] = 'Barne baliabidea berrerabili';
$string['starttime'] = 'Hasi saioa';
$string['duration'] = 'Iraupena';
$string['useattendance'] = 'Erabili ikasleen asistentzia-erregistroa';
$string['useattendance_help'] = 'Gaitu ikasleen asistentziaren grabazioa, eta, ondorioz, asistentziaren araberako kalifikazioak';
$string['usevideoconference'] = 'Erabili bideokonferentziarako sarbidea';
$string['usevideoconference_help'] = 'Gaitu bideokonferentzia';
$string['typevc'] = 'Bideokonferentzia mota';
$string['userecordvc'] = 'Baimendu bideokonferentziaren grabaketak';
$string['userecordvc_help'] = 'Baimendu bideo-konferentzian grabaketak';
$string['waitmoderator'] = 'Itxaron moderatzaileari';
$string['advanceentry'] = 'Sarrera aurreratua';
$string['advanceentry_help'] = 'Bilkura hasi baino zenbat denbora lehenago agertuko den Bat egin botoia.';
$string['closedoors'] = 'Ateak ixten';
$string['closedoors_help'] = 'Ordu honetatik aurrera ikasleak ezin dira sartu.';
$string['userslimit'] = 'Erabiltzaileen muga';
$string['userslimit_help'] = 'Behatzaileei bakarrik dagokie, ez moderatzaileei';
$string['wellcomemessage'] = 'Ongi etorri mezua';
$string['wellcomemessage_help'] = 'Bideokonferentzian sartzean bistaratzeko ongietorri mezua';

$string['disablewebcam'] = 'Desgaitu webcam-ak';
$string['disablemicro'] = 'Desgaitu mikrofonoak';
$string['disableprivatechat'] = 'Desgaitu txat pribatua';
$string['disablepublicchat'] = 'Desgaitu txat publikoa';
$string['disablesharednotes'] = 'Desgaitu partekatutako oharrak';
$string['hideuserlist'] = 'Ezkutatu erabiltzaileen zerrenda';
$string['blockroomdesign'] = 'Blokeatu gelaren diseinua';
$string['ignorelocksettings'] = 'Ez ikusi blokeo ezarpenak';

$string['initialrecord'] = 'Grabatu dena hasieratik';
$string['hiderecordbutton'] = 'Ezkutatu erregistro-botoia';
$string['showpreviewrecord'] = 'Erakutsi grabazioaren aurrebista';
$string['downloadrecords'] = 'Ikasleek grabazioak deskargatu ditzakete';
$string['validateattendance'] = 'Asistentzia baliozkotzeko iraupena';
$string['totalduration'] = 'Iraupen osoa';
$string['attendance '] = 'Asistentzia';
$string['attendance_help'] = 'Ikasleak bideokonferentzian eman behar duen denbora kopurua bere asistentzia baliozkoa izan dadin. <br>Denboran edo %an sar daiteke saioaren iraupen osoaren aldean';
$string['completionattendance'] = 'Erabiltzaileak saioetara joan behar du';
$string['completionattendancegroup'] = 'Laguntza behar du';
$string['completiondetail:attendance'] = 'Saioko asistentzia: {$a}';
$string['subplugintype_hybridteachvc'] = 'Bideokonferentzia mota';
$string['subplugintype_hybridteachvc_plural'] = 'Bideokonferentzia motak';
$string['hybridteachvc'] = 'Bideokonferentziaren plugina';
$string['hybridteachvcpluginname'] = 'Bideokonferentziaren plugina';
$string['headerconfig'] = 'Bideokonferentziaren luzapenak kudeatu';
$string['videoconferenceplugins'] = 'Bideokonferentziaren pluginak';
$string['subplugintype_hybridteachstore'] = 'Biltegiratze mota';
$string['subplugintype_hybridteachstore_plural'] = 'Biltegiratze motak';
$string['view_error_url_missing_parameters'] = 'Parametroak falta dira honetan URLa';
$string['programschedule '] = 'Ordutegia';
$string['sessions'] = 'Saioak';
$string['import'] = 'Inportatu';
$string['export'] = 'Esportatu';

$string['hybridteaching:addinstance'] = 'Gehitu Irakaskuntza Hibrido berri bat';
$string['hybridteaching:manageactivity'] = 'Kudeatu irakaskuntza hibridoaren konfigurazioa';
$string['hybridteaching:view'] = 'Ikusi irakaskuntza hibridoa';
$string['hybridteaching:viewjoinurl'] = 'Ikusi hasierako URLa';
$string['hybridteaching:programschedule'] = 'Irakaskuntzaren programazio hibridoa';

$string['hybridteaching:sessions'] = 'Ikusi saioak';
$string['hybridteaching:attendance'] = 'Ikusi asistentzia';
$string['hybridteaching:import'] = 'Inportatu';
$string['hybridteaching:export'] = 'Esportatu';
$string['hybridteaching:bulksessions'] = 'Erakutsi hainbat saio-ekintzen hautatzailea';
$string['hybridteaching:sessionsactions'] = 'Ikusi ekintzak saioen zerrendan';
$string['hybridteaching:sessionsfulltable'] = 'Erakutsi saio zerrendako eremu guztiak';
$string['hybridteaching:attendancesactions'] = 'Ekintzetarako sarbidea asistentzia-ikuspegian';
$string['hybridteaching:attendanceregister'] = 'Saioko asistentzia erregistratzeko baimena';
$string['hybridteaching:record'] = 'Baimendu grabaketak';
$string['hybridteaching:viewrecordings'] = 'Ikusi grabazioak';
$string['hybridteaching:viewchat'] = 'Ikusi txatak';
$string['hybridteaching:downloadrecordings'] = 'Deskargatu grabazioak';
$string['hybridteaching:viewhiddenitems'] = 'Ikusi ezkutuko elementuak';
$string['hybridteaching:viewallsessions'] = 'Baimendu saio guztiak talde-iragazkirik gabe ikusteko';

$string['type'] = 'Mota';
$string['order '] = 'Eskaera';
$string['hideshow'] = 'Ezkutatu/Erakutsi';
$string['addsetting'] = 'Gehitu ezarpenak';
$string['editconfig'] = 'Editatu konfigurazioa';
$string['saveconfig'] = 'Gorde konfigurazioa';
$string['configgeneralsettings'] = 'Irakaskuntzaren ezarpen orokorrak hibridoak';
$string['configname'] = 'Konfigurazioaren izena';
$string['configselect'] = 'Hautatu konfigurazio bat';
$string['generalconfig'] = 'Konfigurazio orokorra';
$string['configsconfig'] = 'Kudeatu konfigurazioak';
$string['configsvcconfig'] = 'Bideokonferentziaren ezarpenak kudeatu';
$string['configsstoreconfig'] = 'Kudeatu biltegiratze konfigurazioak';

$string['errorcreateconfig'] = 'Errorea konfigurazioa sortzean';
$string['errorupdateconfig'] = 'Errorea konfigurazioa eguneratzean';
$string['errordeleteconfig'] = 'Errorea konfigurazioa ezabatzean';
$string['createdconfig'] = 'Konfigurazioa behar bezala sortu da';
$string['updatedconfig'] = 'Konfigurazioa behar bezala eguneratu da';
$string['deletedconfig'] = 'Konfigurazioa behar bezala ezabatu da';
$string['deleteconfirm'] = 'Ziur konfigurazioa ezabatu nahi duzula: {$a}?';

$string['view_error_url_missing_parameters'] = 'Parametroak falta dira honetan URLa';

$string['recording'] = 'Grabaketa';
$string['materials'] = 'Materialak';
$string['actions'] = 'Ekintzak';
$string['start'] = 'Hasi';
$string['sessionfor'] = 'Talderako saioa';
$string['sessiondate'] = 'Saio-data';
$string['addsession'] = 'Gehitu saioa';
$string['allgroups'] = 'Talde guztiak';
$string['sessiontypehelp'] = 'Ikasle guztientzat edo ikasle talde baterako saioak gehi ditzakezu.
Mota desberdinak gehitzeko aukera jardueraren talde-moduaren araberakoa da.
  * "Talderik gabe" talde moduan soilik gehi ditzakezu saioak ikasle guztiak.
  * Talde moduan "Separate groups" saioak bakarrik gehi ditzakezu ikasle talde batentzat.
  * Talde moduan "Talde ikusgaiak" bi saio motak gehi ditzakezu.
';

$string['nogroups'] = 'Jarduera hau taldeak erabiltzeko konfiguratuta dago, baina ez dago talderik ikastaroan.';
$string['addsession'] = 'Gehitu saioa';
$string['presentationfile'] = 'Aurkezpen fitxategia';
$string['replicatedoc'] = 'Fitxategia saio guztietan errepikatu';
$string['caleneventpersession'] = 'Sortu egutegiko gertaera bat saio bakoitzeko';
$string['addmultiplesessions'] = 'Anitz saio';
$string['repeatasfollows'] = 'Errepikatu aurreko saioa honela';
$string['createmultiplesessions'] = 'Sortu hainbat saio';
$string['createmultiplesessions_help'] = 'Funtzio honek hainbat saio sortzeko aukera ematen dizu urrats sinple batean.
Saioak oinarrizko saioaren datan hasten dira eta "errepikapen" datara arte jarraitzen dute.

  * <strong>Errepikatu</strong>: hautatu zein egunetan asteko zure klasea noiz elkartuko den (adibidez, astelehena/asteazkena/ostirala).
  * <strong>Errepikatu behin</strong>: Honi esker, amaiztasuna. Zure klasea astero bilduko bada, hautatu 1;bi astetik behin elkartuko bazara, hautatu 2;hiru astean behin, hautatu 3, etab.
  * <strong>Errepikatu arte</strong>: hautatu klasearen azken eguna (Deia hartu nahi duzun azken egunean).
';

$string['repeaton'] = 'Errepikatu';
$string['repeatevery'] = 'Errepikatu';
$string['repeatuntil'] = 'Errepikatu arte';
$string['otheroptions'] = 'Beste aukera batzuk';
$string['sessionname'] = 'Saioaren izena';

$string['nosessions'] = 'Ez dago saiorik erabilgarri';
$string['nogroup'] = 'Hurrengo saioa ez da zure taldearentzat egiten';
$string['nosubplugin'] = 'Bideokonferentzia mota okerra da. Jarri harremanetan zure administratzailearekin';
$string['noconfig'] = 'Hautatutako bideokonferentziaren konfigurazioa ez dago. Jarri harremanetan zure administratzailearekin';
$string['noconfig_viewer'] = 'Bideokonferentziaren konfigurazioa ez dago. Jarri harremanetan zure irakaslearekin.';

$string['status_progress'] = 'Saioa abian da';
$string['status_finished'] = 'Saio hau amaitu da';
$string['status_start'] = 'Saioa laster hasiko da';
$string['status_ready'] = 'Saioa prest dago. Sar zaitezke orain.';
$string['status_undated'] = 'Saio errepikakor bat sor dezakezu';
$string['status_undated_wait'] = 'Saio berria hasi arte itxaron behar duzu';
$string['closedoors_hours'] = 'Hasi eta {$a} ordura';
$string['closedoors_minutes'] = 'Hasi eta {$a} minutura';
$string['closedoors_seconds'] = 'Hasi eta {$a} segundora';

$string['sessionstart'] = 'Hurrengo saioa egunean hasiko da';
$string['estimatedduration'] = 'Gutxi gorabeherako iraupena:';
$string['advanceentry'] = 'Sarrera aurreratua:';
$string['closedoors'] = 'Sarbide-ateak ixten:';
$string['status'] = 'Egoera';
$string['started'] = 'On hasi zen';
$string['inprogress '] = 'Abian';
$string['closedoorsnext'] = 'Ateak ondoren itxiko dira';
$string['closedoorsnext2'] = 'etxetik';

$string['closedoorsprev'] = 'Saio honek bere ateak ordu honetan itxi ditu';
$string['closedoorsafter'] = 'hasi';
$string['finished'] = 'Saio hau egunean amaitu zen';
$string['mod_form_field_participant_list_action_add'] = 'Gehitu';
$string['mod_form_field_participant_list'] = 'Parte-hartzaileen zerrenda';
$string['mod_form_field_participant_list_type_all'] = 'Erregistratutako erabiltzaile guztiak';
$string['mod_form_field_participant_list_type_role'] = 'Rola';
$string['mod_form_field_participant_list_type_user'] = 'Erabiltzailea';
$string['mod_form_field_participant_list_type_owner'] = 'Jabea';
$string['mod_form_field_participant_list_text_as'] = 'hasi saioa honela';
$string['mod_form_field_participant_list_action_add'] = 'Gehitu';
$string['mod_form_field_participant_list_action_remove'] = 'Kendu';
$string['mod_form_field_participant_role_moderator'] = 'Moderatzailea';
$string['mod_form_field_participant_role_viewer'] = 'Behatzailea';

$string['equalto'] = 'Berdin';
$string['morethan'] = 'Baino handiagoa';
$string['lessthan'] = 'Baino gutxiago';
$string['options'] = 'Aukerak';
$string['sesperpage'] = 'Saioak orrialde bakoitzeko';
$string['updatesessions'] = 'Eguneratu saioak';
$string['deletesessions'] = 'Ezabatu saioak';
$string['withselectedsessions'] = 'Hautatutako saioekin';
$string['go'] = 'Joan';
$string['options'] = 'Aukerak';
$string['sessionsuc'] = 'Saioak';
$string['programscheduleuc'] = 'Saioen programazioa';
$string['nosessionselected'] = 'Ez da saiorik hautatu';
$string['deletecheckfull'] = 'Ziur hurrengo saio hauek guztiz ezabatu nahi dituzula, erabiltzaileen datu guztiak barne?';
$string['sessiondeleted'] = 'Saioa ondo ezabatu da';
$string['strftimedmyhm'] = '%d %b %Y %I. %M%p';
$string['extend'] = 'Hedatu';
$string['reduce'] = 'Murriztu';
$string['seton'] = 'Ezarri hemen';
$string['updatesesduration'] = 'Aldatu saioaren iraupena';
$string['updatesesstarttime'] = 'Aldatu saioaren hasiera';
$string['updateduration'] = 'Aldatu iraupena';
$string['updatestarttime'] = 'Aldatu hasiera';
$string['advance'] = 'Aurrera';
$string['delayin'] = 'Atzerapena';
$string['editsession'] = 'Editatu saioa';

$string['headerconfigstore'] = 'Kudeatu biltegiratze hedadura';
$string['storageplugins'] = 'Biltegiratzeko Pluginak';
$string['importsessions'] = 'Inportatu saioak';
$string['invalidimportfile'] = 'Fitxategiaren formatua ez da zuzena.';
$string['processingfile'] = 'Fitxategia prozesatzen...';
$string['sessionsgenerated'] = '{$a} saio behar bezala sortu dira';
$string['error:importsessionname'] = 'Saio-izen baliogabea! {$a} lerroa saltatzen.';
$string['error:importsessionstarttime'] = 'Saio-hasteko ordua baliogabea! {$a} lerroa saltatzen.';
$string['error:importsessionduration'] = 'Saioen iraupena baliogabea! {$a} lerroa saltatzen.';
$string['formaterror:importsessionstarttime'] = 'Saio-ordurako formatu baliogabea! {$a} lerroa saltatzen.';
$string['formaterror:importsessionduration'] = 'Saioen iraupenerako formatu baliogabea! {$a} lerroa saltatzen.';
$string['error:sessionunknowngroup'] = 'Taldearen izen ezezaguna: {$a}.';
$string['examplecsv'] = 'Adibidezko testu-fitxategi';
$string['examplecsv_help'] = 'Saioak CSV, Excel edo ODP erabiliz inporta daitezke. Fitxategiaren formatuak honako hau izan behar du:
  * Fitxategiko lerro bakoitzak erregistro bat dauka
  * Erregistro bakoitza bereizleak bereizten dituen datu-serie bat da hautatua.
  * Lehenengo erregistroak definitzen duten eremu-izenen zerrenda dauka gainerako fitxategiaren formatua.
  * Beharrezko eremuen izenak izena, hasiera-ordua eta iraupena.
  * Aukerako eremuen izenak taldeak eta deskribapena dira';
$string['nostarttime'] = 'Hasteko datarik ez';
$string['noduration'] = 'Iraupenik ez';
$string['notypevc'] = 'Ez dago bideokonferentzia motarik';
$string['labeljoinvc'] = 'Sartu bideo-konferentzia';
$string['joinvc'] = 'Sartu bilerara';
$string['createsession'] = 'Sortu saioa';
$string['showqr'] = 'Erakutsi QR kodea';
$string['canjoin'] = 'Irakasleak hasitakoan bileran sar zaitezke';


$string['canattendance'] = 'Irakasleak saioa hasi duenean zure asistentzia erregistratu ahal izango duzu';
$string['recurringses'] = 'Saio errepikakorra';
$string['finishsession'] = 'Bukatu saioa';
$string['sessionnoaccess'] = 'Ez duzu saio honetarako sarbiderik';
$string['lessamin'] = 'Min 1 baino gutxiago';
$string['qrcode'] = 'QR Kodea';
$string['useqr'] = 'Sartu QR erabilera';
$string['rotateqr'] = 'Biratu QR kodea';
$string['studentpassword'] = 'Ikaslearen pasahitza';
$string['passwordheader'] = 'Idatzi pasahitza behean zure asistentzia erregistratzeko';
$string['qrcodeheader'] = 'Eskaneatu QR-a zure asistentzia erregistratzeko';
$string['qrcodeandpasswordheader'] = 'Eskaneatu QR edo sartu beheko pasahitza zure asistentzia erregistratzeko';
$string['noqrpassworduse'] = 'QR edo pasahitzaren erabilera desgaituta dago';

$string['labelshowqrpassword'] = 'Erakutsi pasahitza/QR ikasgelara joateko';
$string['showqrpassword'] = 'Erakutsi Pasahitza / QR';
$string['qrcodevalidbefore'] = 'QR kodea baliozkoa:';
$string['qrcodevalidafter'] = 'segundoak.';
$string['labelattendwithpassword'] = 'Erregistratu asistentzia Classroom-en';
$string['attendwithpassword'] = 'Sarbide pasahitza:';
$string['markattendance'] = 'Erregistratu asistentzia';
$string['incorrect_password'] = 'Pasahitz okerra sartu da.';
$string['attendance_registered'] = 'Asistentzia behar bezala erregistratu da';
$string['qr_expired'] = 'QR kodea iraungi da, ziurtatu kode zuzena irakurri duzula';
$string['grade '] = 'Kalifikazioak';
$string['commonattendance'] = 'Talde guztiak';
$string['videoconference'] = 'Vconf';
$string['classroom'] = 'Ikasgela';
$string['resultsperpage'] = 'Orri bakoitzeko emaitzak';
$string['sessresultsperpage_desc'] = 'Orri bakoitzeko saio kopurua';
$string['donotusepaging'] = 'Ez erabili orririk';
$string['reusesession'] = 'Kanpoko saioko baliabideak berrerabili';
$string['reusesession_desc'] = 'Markatuta badago, errepikatzen diren saioko baliabideak berrerabiliko dira';

$string['allsessions'] = 'Globala - saio guztiak';
$string['entrytime'] = 'Sarrera';
$string['leavetime'] = 'Utzi';
$string['permanence'] = 'Iraunkortasuna';

$string['passwordgrp'] = 'Ikaslearen pasahitza';
$string['passwordgrp_help'] = 'Ezartzen bada, ikasleek pasahitz hau sartu beharko dute saiorako bertaratzea finkatzeko.
  * Hutsik badago, ez da pasahitzik behar.
  * Biratu QR aukera markatuta badago, pasahitza aldakorra izango da eta QRren ondoan biratuko da.';
$string['maxgradeattendance'] = 'Gehieneko kalifikazioaren asistentzia';
$string['maxgradeattendance_help'] = 'Kalkulatzeko modua
  * Parte hartutzat jotzen den saio kopurua
  * Asistentzia kopurua % saio eskuragarrien guztizkoetatik
  * % parte hartu den denbora saio eskuragarrien kopuru nominalaren guztizkotik
';
$string['numsess'] = 'Saio kopurua';
$string['percennumatt'] = '% asistentzia kopurua';
$string['percentotaltime'] = '% guztirako denbora';
$string['percentage'] = 'Ehunekoa';
$string['eventsessionadded'] = 'Saioa gehitu da';
$string['eventsessionviewed'] = 'Saioa ikusi da';
$string['eventsessionupdated'] = 'Saioa eguneratu da';
$string['eventsessionrecordviewed'] = 'Saio-erregistroa ikusi da';
$string['eventsessionrecorddownloaded'] = 'Saio-erregistroa deskargatu da';
$string['eventsessionmngviewed'] = 'Saioen kudeaketaren ikuspegia';
$string['eventsessionjoined'] = 'Saio batu da';
$string['eventsessioninfoviewed'] = 'Saioko informazioa ikusi da';
$string['eventsessionfinished'] = 'Saioa amaitu da';
$string['eventsessiondeleted'] = 'Saioa ezabatu da';
$string['eventattviewed'] = 'Asistentzia ikusi da';
$string['eventattupdated'] = 'Eguneratu da euskarria';
$string['eventattmngviewed'] = 'Asistentzia kudeatzeko ikuspegia';
$string['gradenoun'] = 'Kalifikazioa';
$string['gradenoun_help'] = 'Saioko kalifikazioa/Jardueraren kalifikazio osoa/Jardueraren gehienezko nota';
$string['finishattend'] = 'Amaitu laguntza';
$string['bad_neededtime'] = 'Laguntza saioa baino gutxiago emateko denbora';
$string['attnotfound'] = 'Errorea aurkitu da administratzaile batekin harremanetan jartzeko laguntzarako IDa aurkitzeko';
$string['entryregistered'] = 'Zure sarrera behar bezala erregistratu da';
$string['exitregistered'] = 'Zure irteera behar bezala erregistratu da';
$string['alreadyregistered'] = 'Dagoeneko erregistratu zara, ezin baduzu saioa hasi, mesedez saiatu zure asistentzia amaitzen eta saiatu berriro sartzen';
$string['exitingleavedsession'] = 'Dagoeneko saioa amaitu duzu';
$string['entryneededtoexit'] = 'Saioko asistentzia sarrerarik gabe amaitzen saiatzen ari zarenean, irten baino lehen saioan erregistratu behar duzu';
$string['marks'] = 'Markatu';
$string['hour'] = 'Ordua';
$string['firstentry'] = 'Markatu sarrera saioan';
$string['sessionentry'] = 'Sartu saioa';
$string['sessionexit'] = 'Irten saio';
$string['lastexit'] = 'Saioa markatzen du';
$string['sessionstarttime'] = 'Hasi eraginkorra';
$string['sessionendtime'] = 'Benetako amaiera';
$string['participant'] = 'Parte-hartzailea';
$string['userfor'] = 'Ikasleentzako laguntza:';
$string['combinedat '] = 'Grabatutako guztira';
$string['withselectedattends'] = 'Hautatutako parte-hartzeekin';
$string['prevattend'] = 'Laguntza';
$string[''] = 'Aldatu asistentzia';
$string['setexempt'] = 'Aldaketa salbuetsita';
$string['setsessionexempt'] = 'Aldatu saioaren erabilera ohar informatikoan';
$string['activeattendance'] = 'Onartu lagunduta';
$string['inactiveattendance'] = 'Suposatu arretarik gabe';
$string['updateattendance'] = 'Eguneratu Asistentzia';
$string['attnotforgrade'] = '(Saioa ez da erabiltzen informatika-kalifikazioetan)';
$string['exempt '] = 'Salbuetsita';
$string['exemptattendance'] = 'Oharraren laguntzaren erabilera salbuetsita';
$string['notexemptattendance'] = 'Erabili oharren laguntza';
$string['exemptsessionattendance'] = 'Saioko parte hartzea salbuetsita';
$string['notexemptsessionattendance'] = 'Erabili saioa asistentzian';
$string['exemptuser'] = 'Erabiltzaile salbuetsia saioan';
$string['sessionsattendance'] = 'Saioen asistentzia';
$string['studentsattendance'] = 'Ikasleen asistentzia';

$string['graceperiod '] = 'Graceperiod';
$string['graceperiod_help'] = 'Erabiltzaileak saioan sartzeko behar duen denbora, berandu parte hartzea zenbatu aurretik';
$string['session'] = 'Saioa';
$string['participationtime'] = 'Parte hartu duen denbora';
$string['noattendanceregister'] = 'Ezin duzu saioko asistentzia erregistratu';
$string['attexempt'] = 'Kalifikaziorako salbuetsita';
$string['noatt'] = 'Ez da parte- hartzerik erregistratu';
$string['attendanceresume'] = 'Asistentziaren Laburpena';
$string['attendedsessions'] = 'Joan diren saioetara';
$string['validatedattendance'] = 'Baliozko parte-hartzeak';
$string['finalgrade'] = 'Azken kalifikazioa';
$string['late'] = 'Berandu iristea';
$string['earlyleave'] = 'Utzi goiztiarra';
$string['withatt'] = 'Laguntzarekin';
$string['withoutatt'] = 'Laguntzarik gabe';
$string['notexempt'] = 'Ez dago salbuetsita';
$string['nofilter'] = 'Iragazkirik ez';
$string['vc'] = 'Bideokonferentzia';

$string['watchrecording'] = 'Ikusi grabazioa';
$string['norecording'] = 'Ez dago grabaketarik';
$string['entersession'] = 'Saiora sar zaitezke zure asistentzia markatzeko';
$string['exitsession'] = 'Zure asistentzia erregistratu da, gogoratu saioaren amaieran zure asistentzia amaitzea';
$string['novc'] = 'Bideokonferentziarik erabili gabe saioa';
$string['viewstudentinfo'] = 'Ikasleentzako Laguntza';
$string['viewsessioninfo'] = 'Saioko laguntza';
$string['nologsfound'] = 'Ez da saioko erabiltzailearen erregistrorik aurkitu';
$string['takensessions'] = 'Hartutako saioak';
$string['selectedsessions'] = 'Hautatutako saioak';
$string['anygroup'] = 'Edozein talde';
$string['withoutgroup'] = 'Talderik gabe';
$string['unknown'] = 'Definitu gabea';
$string['noattendanceusers'] = 'Ezin da informaziorik esportatu ikastaroan ez dagoelako erabiltzailerik izena emanda.';
$string['downloadexcel'] = 'Deskargatu Excel formatuan';
$string['downloadoo'] = 'Deskargatu OpenOffice formatuan';
$string['downloadtext'] = 'Deskargatu testu formatuan';
$string['startofperiod'] = 'Aldiaren hasiera';
$string['endofperiod'] = 'Aldiaren amaiera';
$string['includeall'] = 'Hautatu saio guztiak';
$string['joinurl'] = 'Saio-hasiera URLa:';
$string['passstring'] = 'Pasahitza:';
$string['vcconfigremoved'] = 'Jardueraren bideokonferentziaren konfigurazioa administratzaile batek kendu du';
$string['hiderecords'] = 'Ezkutatu grabazioak';

$string['visiblerecords'] = 'Erakutsi grabaketak';
$string['error:deleteinprogress'] = 'Ezin duzu abian dagoen saiorik ezabatu';
$string['deletewithhybridmods'] = 'Konfigurazio hau irakaskuntza hibridoko modulu hauetan erabiltzen da: {$a}. Ziur ezabatu nahi duzula?';
$string['lostconfig'] = 'Konfigurazio hau administratzaile batek ezabatu du';
$string['noinitialstateconfig'] = 'Bideokonferentzia mota honek ez du hasierako egoera konfiguraziorik';
$string['cantfinishunstarted'] = 'Ezin duzu saio bat amaitu multzoa hasi baino lehen';
$string['error_unable_join'] = 'Ezin da konektatu. Bilera ezin izan da aurkitu edo ezabatu egin da. Jarri harremanetan zure irakasle edo administratzailearekin.';
$string['sessionscheduling_help'] = 'Ikastaroan taldeen erabilera behartuta badago, saioa programatu beharko da. Behartuta ez badago, programatu gabeko saioak erabiltzeak saioan taldeen erabilera desgaitzen du.';
$string['error:importsessiontimetype'] = 'Saioen iraupen mota baliogabea! {$a} lerroa saltatzen.';
$string['invalidduration'] = 'Iraupen baliogabea';
$string['chaturlmeeting'] = 'Bilkura-txata';
$string['notesurlmeeting'] = 'Bilkuraren oharrak';
$string['sessionendbeforestart'] = 'Saioa hasi baino lehen amaituko litzateke, iraupena edo hasiera data aldatuko litzateke';
$string['repeatsessionsamedate'] = 'Errepikatu saioaren amaiera data ezin da gaur izan';
$string['programsessionbeforenow'] = 'Errepikatu saioaren amaiera data ezin da gaur baino lehenagokoa izan';
$string['daynotselected'] = 'Hautatu saioak errepikatzeko egun bat';
$string['norecordingmoderation'] = 'Ez duzu moderazio-sarbiderik bideo-konferentzia mota hau grabatzeko baimena emateko.';
$string['chats'] = 'Txaketak';
$string['enabled '] = 'Gaituta';
$string['enabled_help'] = 'Gaituta badago, luzapen hau gaituta egongo da';
$string['disabled'] = 'Desgaituta';
$string['savechanges'] = 'Gorde aldaketak';
$string['cancel'] = 'Utzi';
$string['categoryselect'] = 'Hautaketa pertsonalizatua';
$string['defaultsettings'] = 'Irakaskuntza-ezarpen hibrido orokorrak';

$string['defaultsettings_help'] = 'Ezarpen hauek irakaskuntza jarduera hibridoen ezarpen orokorrak definitzen dituzte';
$string['sessionscheduling_desc'] = 'Aktibatuta dagoenean, saioak saioen programazioa erabiliz sortzera behartzen ditu';
$string['waitmoderator_desc'] = 'Erabiltzaileek moderatzaile bat bideokonferentzian sartu arte itxaron behar dute';
$string['useattendance_help'] = 'Saioei laguntzaren erabilera gehitu';
$string['usevideoconference_help'] = 'Gehitu bideo-deien erabilera saioetan';
$string['userecord_help'] = 'Gehitu saioak grabatzeko aukera';
$string['sessionssettings'] = 'Irakaskuntza-saioen konfigurazio-ezarpen hibridoak';
$string['sessionssettings_help'] = 'Ezarpen hauek irakaskuntza saio hibridoetarako aukera lehenetsiak definitzen dituzte';
$string['userslimit_desc'] = 'Bideokonferentzian sartu ahal izango diren moderatzaileak ez diren erabiltzaileen gehienezko kopurua';
$string['attendancesettings'] = 'Irakaskuntzarako asistentzia-ezarpen hibridoak';

$string['attendancesettings_help'] = 'Ezarpen hauek irakaskuntza-jarduera hibridoetarako asistentzia- ezarpenak definitzen dituzte';
$string['useqr_desc'] = 'Erabiltzaileei qr-rekin asistentzia erregistratzeko aukera gehitzen die';
$string['rotateqr_desc'] = 'Biratutako qr bat erabiltzera behartu asistentzia erregistratzeko';
$string['studentpassword_desc'] = 'Erabiltzaileek euren asistentzia erregistratzeko erabili behar duten pasahitza';
$string['hidechats'] = 'Ezkutatu berriketak';
$string['visiblechats'] = 'Erakutsi txatak';
$string['advanceentrycount'] = 'Sarrerako aldez aurretiko ordua';
$string['advanceentrycount_help'] = 'Saiorako sarrera-ordua aurreratu';
$string['advanceentryunit'] = 'Aurreratu sarrera unitatea';
$string['advanceentryunit_help'] = 'Sarrera aldez aurretiko denbora-unitatea';
$string['closedoorscount'] = 'Ateak ixteko ordua';
$string['closedoorscount_help'] = 'Erabiltzaileek behar duten denbora saioa hasi baino lehen';
$string['closedoorsunit'] = 'Ateak ixteko unitatea';
$string['closedoorsunit_help'] = 'Ateak ixteko denbora-unitatea';
$string['validateattendance_help'] = 'Erabiltzaileek saioan egon behar duten denbora euren asistentzia balioztatzeko';
$string['attendanceunit '] = 'Asistentzia Unitatea';
$string['attendanceunit_help'] = 'Asistentzia denbora-unitatea';
$string['graceperiod_help'] = 'Erabiltzaileek saioa hasi zenetik duten denbora, eta ondoren, sarrera ez da baliozkotuko';
$string['graceperiodunit'] = 'Grazio-aldiaren unitatea';
$string['graceperiodunit_help'] = 'Grazio-aldiaren denbora-unitatea';
$string['updatecalen'] = 'Eguneratu egutegiko gertaera';

$string['sessiontobecreated'] = 'Sortu beharreko saioa';
$string['recordingdisabled'] = 'Grabaketak ez daude gaituta. Deskarga ez da onartzen.';
$string['cannotmanageactivity'] = 'Ez duzu {$a} eguneratzeko baimenik';
$string['nouseconfig'] = 'Konfigurazio hau ez da {$a} bideo-konferentziari aplikatzen.';
$string['hybridteaching:createsessions'] = 'Baimendu saioak sortzea';
$string['bulkhide'] = 'Erakutsi/Ezkutatu saioak';
$string['bulkhididechats'] = 'Erakutsi/ezkutatu txatak';
$string['bulkhiderecordings'] = 'Erakutsi/Ezkutatu grabazioak';
$string['bulkhidetext'] = 'Ziur hurrengo saioak erakutsi/ezkutatu nahi dituzula?';
$string['bulkhidechatstext'] = 'Ziur txat hauek erakutsi/ezkutatu nahi dituzula?';
$string['bulkhiderecordingstext'] = 'Ziur grabaketa hauek erakutsi/ezkutatu nahi dituzula?';
$string['bulkhidesuccess '] = 'Arrakastaz ezkutatu diren saioak';
$string['bulkhididechatssuccess'] = 'Txaketak ongi ezkutatu dira';
$string['bulkhiderecordingssuccess'] = 'Grabaketak ongi ezkutatu dira';
$string['hiddenuserattendance'] = '(Saioa erabiltzaileari ezkutatuta)';
$string['cantcreatevc'] = 'Ezin zara bideo-konferentzian sartu: ez duzu baimen nahikorik edo moderatzailearen zain egon behar duzu.';
$string['sessionperformed'] = '{$a} saio egin dira jada (sarbidea Saioak fitxan)';
$string['qrupdatetime'] = 'QR/Pasahitza txandakatzeko epea';
$string['qrupdatetime_help'] = 'QR mantenduko den denbora-tartea, aldatu arte, pasahitzari ere aplikagarria.';

$string['bulkhideatt'] = 'Saio hauetarako asistentzia erakutsi/ezkutatu';
$string['bulkhideatttext'] = 'Ziur hurrengo saioetarako asistentzia erakutsi/ezkutatu nahi duzula?';
$string['bulkhidideattsuccess'] = 'Laguntzak ongi ezkutatu dira';
$string['hideatt'] = 'Saio honetarako asistentzia ezkutatu';
$string['visibleatt'] = 'Erakutsi saio honetarako asistentzia';
$string['updatefinished'] = 'Denbora dela eta amaitu diren saioak amaitu';

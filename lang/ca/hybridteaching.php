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

$string['pluginname'] = 'Docència Híbrida';
$string['modulename'] = 'Docència Híbrida';
$string['modulenameu'] = 'Docencia_hibrida';
$string['modulenameplural'] = 'Docència Híbrida';
$string['hybridteachingname'] = 'Nom';
$string['pluginadministration'] = 'Administració Docència híbrida';

$string['sectionsessions'] = 'Temporalització de sessions';
$string['sectionaudience'] = 'Accés i rol de participants';
$string['sectionsessionaccess'] = 'Accés a la sessió';
$string['sectioninitialstates'] = 'Estats inicials de la videoconferència';
$string['sectionrecording'] = 'Opcions d\'enregistrament';
$string['sectionattendance'] = 'Registre d\'assistència';

$string['sessionscheduling'] = 'Usar programació de sessions';
$string['undatedsession'] = 'Reutilitzar el recurs intern';
$string['starttime'] = 'Inici de sessió';
$string['duration'] = 'Durada';

$string['useattendance'] = 'Utilitzeu registre d\'assistència d\'estudiants';
$string['useattendance_help'] = 'Activar el registre d\'assistència d\'estudiants i, consegüentment, les qualificacions basades en l\'assistència';
$string['usevideoconference'] = 'Usar accés per videoconferència';
$string['usevideoconference_help'] = 'Activar videoconferència';
$string['typevc'] = 'Tipus de videoconferència';
$string['userecordvc'] = 'Permetre enregistraments de videoconferència';
$string['userecordvc_help'] = 'Permetre els enregistraments a la videoconferència';

$string['waitmoderator'] = 'Esperar el moderador';
$string['advanceentry'] = 'Antelació d\'entrada';
$string['advanceentry_help'] = 'Quant de temps abans de l\'inici de la reunió es mostra el botó Unir-se.';
$string['closedoors'] = 'Tancament de portes';
$string['closedoors_help'] = 'Passat aquest temps els estudiants no es poden unir.';
$string['userslimit'] = 'Límit d\'usuaris';
$string['userslimit_help'] = 'Només aplicable a observadors, no moderadors';
$string['wellcomemessage'] = 'Missatge de benvinguda';
$string['wellcomemessage_help'] = 'Missatge de benvinguda a mostrar en entrar a la videoconferència';

$string['disablewebcam'] = 'Desactivar les càmeres web';
$string['disablemicro'] = 'Desactivar els micròfons';
$string['disableprivatechat'] = 'Desactivar el xat privat';
$string['disablepublicchat'] = 'Desactivar el xat públic';
$string['disablesharednotes'] = 'Desactivar notes compartides';
$string['hideuserlist'] = 'Amagar la llista d\'usuaris';
$string['blockroomdesign'] = 'Bloquejar el disseny de la sala';
$string['ignorelocksettings'] = 'Ignorar els paràmetres de bloqueig';

$string['initialrecord'] = 'Gravar-ho tot des de l\'inici';
$string['hiderecordbutton'] = 'Amagar botó d\'enregistrament';
$string['showpreviewrecord'] = 'Mostra vista prèvia d\'enregistrament';
$string['downloadrecords'] = 'Estudiants poden descarregar enregistraments';

$string['validateattendance'] = 'Permanència per validar assistència';
$string['totalduration'] = '% durada total';
$string['attendance'] = 'Assistència';
$string['attendance_help'] = 'Quantitat de temps que l\'estudiant ha de passar a la videoconferència perquè la seva assistència sigui vàlida. <br>Es pot introduir en temps o % respecte a la durada total de la sessió';

$string['completionattendance'] = 'L\'usuari ha d\'assistir a sessions';
$string['completionattendancegroup'] = 'Requereix assistència';
$string['completiondetail:attendance'] = 'Assistència a sessions: {$a}';

$string['subplugintype_hybridteachvc'] = 'Tipus de videoconferència';
$string['subplugintype_hybridteachvc_plural'] = 'Tipus de videoconferència';
$string['hybridteachvc'] = 'Plugin Videoconferència';
$string['hybridteachvcpluginname'] = 'Plugin Videoconferència';
$string['headerconfig'] = 'Gestionar extensions de videoconferència';
$string['videoconferenceplugins'] = 'Gestionar extensions de videoconferència';

$string['subplugintype_hybridteachstore'] = 'Tipus d\'emmagatzematge';
$string['subplugintype_hybridteachstore_plural'] = 'Tipus d\'emmagatzematgeo';

$string['view_error_url_missing_parameters'] = 'Falten paràmetres en aquesta URL';

$string['programschedule'] = 'Programació';
$string['sessions'] = 'Sessions';
$string['import'] = 'Importar';
$string['export'] = 'Exportar';

$string['hybridteaching:addinstance'] = 'Afegeix una nova Docència híbrida';
$string['hybridteaching:manageactivity'] = 'Gestionar configuració docència híbrida';
$string['hybridteaching:view'] = 'Veure docència híbrida';
$string['hybridteaching:viewjoinurl'] = 'Veure url d\'inici';
$string['hybridteaching:programschedule'] = 'Programació de docència híbrida';
$string['hybridteaching:sessions'] = 'Veure sessions';
$string['hybridteaching:attendance'] = 'Veure assistència';
$string['hybridteaching:import'] = 'Importar';
$string['hybridteaching:export'] = 'Exportar';
$string['hybridteaching:bulksessions'] = 'Mostra el selector d\'accions múltiples de sessions';
$string['hybridteaching:sessionsactions'] = 'Veure accions a la llista de sessions';
$string['hybridteaching:sessionsfulltable'] = 'Mostra tots els camps de la llista de sessions';
$string['hybridteaching:attendancesactions'] = 'Accés a les accions a la vista d\'assistència';
$string['hybridteaching:attendanceregister'] = 'Permís per registrar assistència a la sessió';
$string['hybridteaching:record'] = 'Permetre enregistraments';
$string['hybridteaching:viewrecordings'] = 'Veure enregistraments';
$string['hybridteaching:viewchat'] = 'Veure xats';
$string['hybridteaching:downloadrecordings'] = 'Descarregar enregistraments';
$string['hybridteaching:viewhiddenitems'] = 'Veure elements ocults';
$string['hybridteaching:viewallsessions'] = 'Permet veure totes les sessions sense filtre de grup';

$string['type'] = 'Tipus';
$string['order'] = 'Ordenar';
$string['hideshow'] = 'Amagar/Mostrar';
$string['addsetting'] = 'Afegir configuració';
$string['editconfig'] = 'Edita configuració';
$string['saveconfig'] = 'Desar configuració';
$string['configgeneralsettings'] = 'Configuració general de docència híbrida';
$string['configname'] = 'Nom de configuració';
$string['configselect'] = 'Seleccionar una configuració';
$string['generalconfig'] = 'Configuració general';
$string['configsconfig'] = 'Administrar configuracions';
$string['configsvcconfig'] = 'Administrar configuracions de videoconferència';
$string['configsstoreconfig'] = 'Administrar configuracions d\'emmagatzematge';

$string['errorcreateconfig'] = 'Error en crear la configuració';
$string['errorupdateconfig'] = 'Error en actualitzar la configuració';
$string['errordeleteconfig'] = 'Error en eliminar la configuració';
$string['createdconfig'] = 'Configuració creada amb èxit';
$string['updatedconfig'] = 'Configuració actualitzada amb èxit';
$string['deletedconfig'] = 'Configuració eliminada amb èxit';
$string['deleteconfirm'] = 'Esteu segur que voleu eliminar la configuració: {$a}?';

$string['view_error_url_missing_parameters'] = 'Falten paràmetres en aquesta URL';

$string['recording'] = 'Enregistrament';
$string['materials'] = 'Materials';
$string['actions'] = 'Accions';
$string['start'] = 'Inici';

$string['sessionfor'] = 'Sessió per al grup';
$string['sessiondate'] = 'Data de la sessió';
$string['addsession'] = 'Afegir sessió';
$string['allgroups'] = 'Tots els grups';
$string['sessiontypehelp'] = 'Podeu afegir sessions per a tots els alumnes o per a un grup d\'alumnes.
La possibilitat d\'afegir diferents tipus depèn del mode de grup de l\'activitat.
  * Al mode de grup "Sense grups" només podeu afegir sessions per a tots els estudiants.
  * En el mode de grup "Grups separats" podeu afegir només sessions per a un grup d\'estudiants.
  * Al mode de grup "Grups visibles" podeu afegir tots dos tipus de sessions.
';
$string['nogroups'] = 'Aquesta activitat ha estat configurada per utilitzar grups, però no existeixen grups al curs.';
$string['addsession'] = 'Afegir sessió';
$string['presentationfile'] = 'Arxiu de presentació';
$string['replicatedoc'] = 'Replicar fitxer a totes les sessions';
$string['caleneventpersession'] = 'Crear un esdeveniment de calendari per sessió';
$string['addmultiplesessions'] = 'Múltiples sessions';
$string['repeatasfollows'] = 'Repetir la sessió anterior de la manera següent';
$string['createmultiplesessions'] = 'Crear múltiples sessions';
$string['createmultiplesessions_help'] = 'Aquesta funció us permet crear múltiples sessions en un simple pas.
Les sessions comencen a la data de la sessió base i continuen fins a la data de "repetició".

  * <strong>Repetir el</strong>: Seleccioneu els dies de la setmana en què es reunirà la vostra classe (per exemple, dilluns/dimecres/divendres).
  * <strong>Repetir cada</strong>: Això permet establir una freqüència. Si la vostra classe es reunirà cada setmana, seleccioneu 1; si es reunirà cada dues setmanes, seleccioneu 2; cada tres setmanes, seleccioneu 3, etc.
  * <strong>Repetir fins</strong>: Selecciona l\'últim dia de classe (l\'últim dia que vols passar a punt).
';

$string['repeaton'] = 'Repetir el';
$string['repeatevery'] = 'Repetir cada';
$string['repeatuntil'] = 'Repetir fins';
$string['otheroptions'] = 'Altres opcions';
$string['sessionname'] = 'Nom de la sessió';

$string['nosessions'] = 'No hi ha sessions disponibles';
$string['nogroup'] = 'La propera sessió no es realitza per al vostre grup';
$string['nosubplugin'] = 'El tipus de videoconferència és incorrecte. Contacteu amb el vostre administrador';
$string['noconfig'] = 'No hi ha la configuració de videoconferència seleccionada. Contacteu amb el vostre administrador';
$string['noconfig_viewer'] = 'No hi ha la configuració de videoconferència. Contacteu amb el vostre professor.';

$string['status_progress'] = 'Sessió en progrés';
$string['status_finished'] = 'Aquesta sessió ha finalitzat';
$string['status_start'] = 'La sessió començarà properament';
$string['status_ready'] = 'La sessió està llesta. Podeu entrar ara.';
$string['status_undated'] = 'Podeu crear una sessió recurrent';
$string['status_undated_wait'] = 'Heu d\'esperar a que comenci una nova sessió';

$string['closedoors_hours'] = ' {$a} hores després de l\'inici';
$string['closedoors_minutes'] = ' {$a} minuts després de l\'inici';
$string['closedoors_seconds'] = ' {$a} segons després de l\'inici';

$string['sessionstart'] = 'La següent sessió començarà el';
$string['estimatedduration'] = 'Durada estimada:';
$string['advanceentry'] = 'Antelació d\'entrada:';
$string['closedoors'] = 'Tancament de portes d\'accés:';
$string['status'] = 'Estat';
$string['started'] = 'Va iniciar el';
$string['inprogress'] = 'En progrés';
$string['closedoorsnext'] = 'Es tancaran les portes després';
$string['closedoorsnext2'] = 'de l\'inici';
$string['closedoorsprev'] = 'Aquesta sessió va tancar les portes als';
$string['closedoorsafter'] = 'de començar';
$string['finished'] = 'Aquesta sessió es va acabar el';

$string['mod_form_field_participant_list_action_add'] = 'Afegir';
$string['mod_form_field_participant_list'] = 'Llista de participants';
$string['mod_form_field_participant_list_type_all'] = 'Tots els usuaris inscrits';
$string['mod_form_field_participant_list_type_role'] = 'Rol';
$string['mod_form_field_participant_list_type_user'] = 'Usuari';
$string['mod_form_field_participant_list_type_owner'] = 'Propietari';
$string['mod_form_field_participant_list_text_as'] = 'entra a la sessió com';
$string['mod_form_field_participant_list_action_add'] = 'Afegir';
$string['mod_form_field_participant_list_action_remove'] = 'Eliminar';
$string['mod_form_field_participant_role_moderator'] = 'Moderador';
$string['mod_form_field_participant_role_viewer'] = 'Observador';

$string['equalto'] = 'Igual a';
$string['morethan'] = 'MaJor que';
$string['lessthan'] = 'Menor que';
$string['options'] = 'Opcions';
$string['sesperpage'] = 'Sessions per pàgina';

$string['updatesessions'] = 'Actualitzar sessions';
$string['deletesessions'] = 'Esborrar sessions';
$string['withselectedsessions'] = 'Amb les sessions seleccionades';
$string['go'] = 'Anar';
$string['options'] = 'Opcions';
$string['sessionsuc'] = 'Sessions';
$string['programscheduleuc'] = 'Programació de sessions';
$string['nosessionsselected'] = 'Sense sessions seleccionades';
$string['deletecheckfull'] = 'Esteu segur que voleu eliminar completament les sessions següents, incloses totes les dades de l\'usuari?';
$string['sessiondeleted'] = 'Sessió eliminada amb èxit';
$string['strftimedmyhm'] = '%d %b %Y %I.%M%p';
$string['extend'] = 'Estendre';
$string['reduce'] = 'Reduir';
$string['seton'] = 'Establir a';
$string['updatesesduration'] = 'Modificar durada de la sessió';
$string['updatesesstarttime'] = 'Modificar l\'inici de la sessió';
$string['updateduration'] = 'Modificar durada';
$string['updatestarttime'] = 'Modificar inici';
$string['advance'] = 'Avançar';
$string['delayin'] = 'Retardar a';
$string['editsession'] = 'Edita la sessió';

$string['headerconfigstore'] = 'Administrar extensions d\'emmagatzematge';
$string['storageplugins'] = 'Extensions d\'emmagatzematge';
$string['importsessions'] = 'Importar sessions';
$string['invalidimportfile'] = 'El format del fitxer no és correcte.';
$string['processingfile'] = 'Processant arxiu...';
$string['sessionsgenerated'] = '{$a} sessions generades amb èxit';

$string['error:importsessionname'] = '¡Nom de sessió invàlid! Saltant línia {$a}.';
$string['error:importsessionstarttime'] = '¡Hora d\'inici de sessió no vàlida! Saltant línia {$a}.';
$string['error:importsessionduration'] = '¡Durada de sessió invàlida! Saltant línia {$a}.';
$string['formaterror:importsessionstarttime'] = '¡Format no vàlid per a l\'hora d\'inici de sessió! Saltant línia {$a}.';
$string['formaterror:importsessionduration'] = '¡Format no vàlid per a la durada de la sessió! Saltant línia {$a}.';
$string['error:sessionunknowngroup'] = 'Nom de grup desconegut: {$a}.';
$string['examplecsv'] = 'Fitxer de text d\'exemple';
$string['examplecsv_help'] = 'Les sessions es poden importar mitjançant CSV, Excel o ODP. El format del fitxer ha de ser el següent:

  * Cada línia del fitxer conté un registre
  * Cada registre és una sèrie de dades separades pel separador seleccionat.
  * El primer registre conté una llista de noms de camp que defineixen el format de la resta del fitxer.
  * Els noms de camp obligatoris són el nom, l\'hora inicial i la durada.
  * Els noms de camp opcionals són grups i descripció';

$string['nostarttime'] = 'Sense data d\'inici';
$string['noduration'] = 'Sense durada';
$string['notypevc'] = 'Sense tipus de videoconferència';
$string['labeljoinvc'] = 'Accedir a videoconferència';
$string['joinvc'] = 'Unir-te a la reunió';
$string['createsession'] = 'Crear sessió';
$string['showqr'] = 'Mostra codi QR';
$string['canjoin'] = 'Podràs unir-te a la reunió quan el professor l\'hagi iniciat';
$string['canattendance'] = 'Podràs registrar la teva assistència quan el professor hagi iniciat la sessió';
$string['recurringses'] = 'Sessió recurrent';
$string['finishsession'] = 'Finalitzar sessió';
$string['sessionnoaccess'] = 'No tens accés a aquesta sessió';
$string['lessamin'] = 'Menys d\'1 min';

$string['qrcode'] = 'Codi QR';
$string['useqr'] = 'Incloure ús de QR';
$string['rotateqr'] = 'Rotar codi QR';
$string['studentpassword'] = 'Contrasenya d\'alumnes';
$string['passwordheader'] = 'Introduïu la contrasenya de sota per registrar la vostra assistència';
$string['qrcodeheader'] = 'Escanegeu el QR per registrar la vostra assistència';
$string['qrcodeandpasswordheader'] = 'Escanegeu el QR o introduïu la contrasenya de sota per registrar la vostra assistència';
$string['noqrpassworduse'] = 'L\'ús de QR o contrasenya estan deshabilitats';
$string['labelshowqrpassword'] = 'Mostra contrasenya/QR per assistència a l\'aula';
$string['showqrpassword'] = 'Mostra Contrasenya / QR';
$string['qrcodevalidbefore'] = 'Codi QR vàlid per:';
$string['qrcodevalidafter'] = 'segons.';
$string['labelattendwithpassword'] = 'Registrar assistència a Aula';
$string['attendwithpassword'] = 'Contrasenya d\'accés: ';
$string['markattendance'] = 'Registrar assistència';
$string['incorrect_password'] = 'Contrasenya incorrecta introduïda.';
$string['attendance_registered'] = 'Assistència registrada correctament';
$string['qr_expired'] = 'El codi QR ha expirat, assegureu-vos de llegir el codi correcte';
$string['grade'] = 'Qualificacions';
$string['commonattendance'] = 'Tots els grups';
$string['videoconference'] = 'Vconf';
$string['classroom'] = 'Aula';

$string['resultsperpage'] = 'Resultats per pàgina';
$string['sessresultsperpage_desc'] = 'Quantitat de sessions per pàgina';
$string['donotusepaging'] = 'No fer servir paginació';
$string['reusesession'] = 'Reutilitzar recursos externs de sessions';
$string['reusesession_desc'] = 'Si està marcat, es reutilitzaran els recursos de sessions recurrents';

$string['allsessions'] = 'Global - totes les sessions';
$string['entrytime'] = 'Entrada';
$string['leavetime'] = 'Sortida';
$string['permanence'] = 'Permanència';

$string['passwordgrp'] = 'Contrasenya d\'estudiant';
$string['passwordgrp_help'] = 'Si s\'estableix, els estudiants hauran d\'ingressar aquesta contrasenya per establir l\'assistència a la sessió.

  * Si està buit, no cal contrasenya.
  * Si marqueu l\'opció de rotar QR, la contrasenya serà variable i girarà al costat del QR.';

$string['maxgradeattendance'] = 'Assistència per a màxima puntuació';
$string['maxgradeattendance_help'] = 'Mode de càlcul

  * Nº d\'sessions donades per assistides
  * % nº d\'assistències sobre el total de sessions accessibles
  * % temps assistit sobre el total nominal de sessions accessibles

';
$string['numsess'] = 'Nº sesions';
$string['percennumatt'] = '% nº assistència';
$string['percentotaltime'] = '% temps total';
$string['percentage'] = 'Percentatge';

$string['eventsessionadded'] = 'Sessió afegida';
$string['eventsessionviewed'] = 'Sessió vista';
$string['eventsessionupdated'] = 'Sessió actualitzada';
$string['eventsessionrecordviewed'] = 'Registre de sessió vist';
$string['eventsessionrecorddownloaded'] = 'Registre de sessió descarregat';
$string['eventsessionmngviewed'] = 'Gestió de sessió vista';
$string['eventsessionjoined'] = 'Sessió unida';
$string['eventsessioninfoviewed'] = 'Informació de la sessió vista';
$string['eventsessionfinished'] = 'Sessió finalitzada';
$string['eventsessiondeleted'] = 'Sessió eliminada';
$string['eventattviewed'] = 'Assistència vista';
$string['eventattupdated'] = 'Assistència actualitzada';
$string['eventattmngviewed'] = 'Gestió d\'assistència vista';

$string['gradenoun'] = 'Qualificació';
$string['gradenoun_help'] = 'Qualificació de la sessió / Qualificació total de l\'activitat / Qualificació màxima de l\'activitat';
$string['finishattend'] = 'Acabar assistència';
$string['bad_neededtime'] = 'Temps per completar assistència menor que el de la sessió';
$string['attnotfound'] = 'Error en trobar l\'id per a la vostra assistència contacteu un administrador';
$string['entryregistered'] = 'La vostra entrada s\'ha registrat correctament';
$string['exitregistered'] = 'Se n\'ha registrat la sortida correctament';
$string['alreadyregistered'] = 'Ja heu registrat la vostra entrada, si no podeu entrar a la sessió, proveu de finalitzar la vostra assistència i proveu de tornar a unir-vos';
$string['exitingleavedsession'] = 'Ja ha registrat la sortida';
$string['entryneededtoexit'] = 'Intentant finalitzar assistència en sessió sense entrada, heu de registrar la vostra entrada a la sessió abans de sortir';
$string['marks'] = 'Marca';
$string['hour'] = 'Hora';
$string['firstentry'] = 'Marca l\'entrada a la sessió';
$string['sessionentry'] = 'Entra a la sessió';
$string['sessionexit'] = 'Surt de la sessió';
$string['lastexit'] = 'Marca la sortida de la sessió';
$string['sessionstarttime'] = 'Inici efectiu';
$string['sessionendtime'] = 'Finalització real';
$string['participant'] = 'Participant';
$string['userfor'] = 'Assistència per a estudiant:';
$string['combinedatt'] = 'Total registrat';
$string['withselectedattends'] = 'Amb les assistències seleccionades';
$string['prevattend'] = 'Assistència';
$string['setattendance'] = 'Canviar assistència';
$string['setexempt'] = 'Canviar exempt';
$string['setsessionexempt'] = 'Canviar ús de sessió en còmput nota';
$string['activeattendance'] = 'Donar per assistit';
$string['inactiveattendance'] = 'Donar per no assistit';
$string['updateattendance'] = 'Actualitzar assistència';
$string['attnotforgrade'] = '(Sessió no usada en còmput de notes)';
$string['exempt'] = 'Exempt';
$string['exemptattendance'] = 'Exemptar ús d\'assistència per a notes';
$string['notexemptattendance'] = 'Usar assistència per a notes';
$string['exemptsessionattendance'] = 'Exemptar ús de session en assistència';
$string['notexemptsessionattendance'] = 'Usar sessió en assistència';
$string['exemptuser'] = 'Usuari exempt a la sessió';
$string['sessionsattendance'] = 'Assistència sessions';
$string['studentsattendance'] = 'Assistència estudiants';

$string['graceperiod'] = 'Període de gràcia';
$string['graceperiod_help'] = 'Temps que l\'usuari té per unir-se a la sessió, abans que se li comptabilitzi l\'assistència amb retard';
$string['session'] = 'Sessió';
$string['participationtime'] = 'Temps participat';
$string['noattendanceregister'] = 'No pots registrar assistència a la sessió';
$string['attexempt'] = 'Exempta per a qualificació';
$string['noatt'] = 'Sense assistència registrada';
$string['attendanceresume'] = 'Resum d\'assistència';
$string['attendedsessions'] = 'Sessions ateses';
$string['validatedattendance'] = 'Assistències vàlides';
$string['finalgrade'] = 'Qualificació final';
$string['late'] = 'Arribada tardana';
$string['earlyleave'] = 'Abandó primerenc';
$string['withatt'] = 'Amb assistència';
$string['withoutatt'] = 'Sense assistència';
$string['notexempt'] = 'No exempt';
$string['nofilter'] = 'Sense filtre';
$string['vc'] = 'Videoconferència';

$string['watchrecording'] = 'Veure enregistrament';
$string['norecording'] = 'Sense enregistrament';

$string['entersession'] = 'Podeu entrar a la sessió per marcar la vostra assistència';
$string['exitsession'] = 'S\'ha registrat la vostra assistència recordeu acabar la vostra assistència en acabar la sessió';
$string['novc'] = 'Sessió sense ús de videoconferència';
$string['viewstudentinfo'] = 'Assistència estudiant';
$string['viewsessioninfo'] = 'Assistència sessió';
$string['nologsfound'] = 'No s\'han trobat registres per a l\'usuari a la sessió';
$string['takensessions'] = 'Sessions realitzades';
$string['selectedsessions'] = 'Sessions seleccionades';
$string['anygroup'] = 'Qualsevol grup';
$string['withoutgroup'] = 'Sense grup';
$string['unknown'] = 'Sense definir';
$string['noattendanceusers'] = 'No és possible exportar cap informació perquè no hi ha usuaris inscrits al curs.';
$string['downloadexcel'] = 'Descarregar en format Excel';
$string['downloadooo'] = 'Descarregar en format OpenOffice';
$string['downloadtext'] = 'Descarregar en format de text';
$string['startofperiod'] = 'Inici del període';
$string['endofperiod'] = 'Fi del període';
$string['includeall'] = 'Selecciona totes les sessions';
$string['joinurl'] = 'Url d\'accés: ';
$string['passstring'] = 'Contrasenya: ';
$string['vcconfigremoved'] = 'La configuració de videoconferència de l\'activitat va ser eliminada per un administrador';
$string['hiderecords'] = 'Amagar enregistraments';
$string['visiblerecords'] = 'Mostra enregistraments';

$string['error:deleteinprogress'] = 'No podeu esborrar una sessió en curs';
$string['deletewithhybridmods'] = 'Aquesta configuració es fa servir en els següents mòduls de hybrid teaching: {$a}. Esteu segur que voleu esborrar-la?';
$string['lostconfig'] = 'Aquesta configuració ha estat eliminada per un administrador';
$string['noinitialstateconfig'] = 'Aquest tipus de videoconferència no té cap configuració d\'estats inicials';
$string['cantfinishunstarted'] = 'No podeu finalitzar una sessió abans de l\'inici establert';

$string['error_unable_join'] = 'No s\'ha pogut connectar. La reunió no s\'ha pogut trobar o ha estat eliminada. Contacteu amb el vostre professor o administrador.';
$string['sessionscheduling_help'] = 'Si es força l\'ús de grups al curs, la sessió s\'haurà de programar obligatòriament.

Si no es força, en utilitzar sessions sense programació, es desactiva l\'ús de grups a la sessió.';

$string['error:importsessiontimetype'] = 'Tipus de durada de sessió invàlida! Saltant línia {$a}.';

$string['invalidduration'] = 'Durada no vàlida';
$string['chaturlmeeting'] = 'Xat de la reunió';
$string['notesurlmeeting'] = 'Notes de la reunió';

$string['sessionendbeforestart'] = 'La sessió acabaria abans de començar, canvieu la durada, o la data d\'inici';
$string['repeatsessionsamedate'] = 'La data de fi de repetir sessió no pot ser avui';
$string['programsessionbeforenow'] = 'La data de fi de repetir sessió no pot ser abans d\'avui';
$string['daynotselected'] = 'Seleccioneu un dia en què repetir sessions';

$string['norecordingmoderation'] = 'No teniu accés de moderació per permetre l\'enregistrament d\'aquest tipus de videoconferència.';
$string['chats'] = 'Xats';

$string['enabled'] = 'Activat';
$string['enabled_help'] = 'Si l\'opció està activada, aquesta extensió estarà habilitada';
$string['disabled'] = 'Deshabilitat';
$string['savechanges'] = 'Desar canvis';
$string['cancel'] = 'Cancel·la';
$string['categoryselect'] = 'Selecció personalitzada';

$string['defaultsettings'] = 'Ajustaments de configuració de docència híbrida generals';
$string['defaultsettings_help'] = 'Aquests paràmetres defineixen les configuracions generals de les activitats de docència híbrida';
$string['sessionscheduling_desc'] = 'En estar actiu, obliga que les sessions siguin creades usant programació de sessions';
$string['waitmoderator_desc'] = 'Els usuaris han d\'esperar que s\'uneixi un moderador per unir-se a la videoconferència';
$string['useattendance_help'] = 'Afegeix l\'ús d\'assistència a les sessions';
$string['usevideoconference_help'] = 'Afegeix l\'ús de videotrucades a les sessions';
$string['userecord_help'] = 'Afegeix la possibilitat de realitzar enregistraments a les sessions';
$string['sessionssettings'] = 'Ajustaments de configuració de les sessions de docència híbrida';
$string['sessionssettings_help'] = 'Aquests ajustaments defineixen les opcions per defecte de les sessions de docència híbrida';
$string['userslimit_desc'] = 'Quantitat màxima d\'usuaris no moderadors que poden entrar a la videoconferència';
$string['attendancesettings'] = 'Configuració de configuració d\'assistència de docència híbrida';
$string['attendancesettings_help'] = 'Aquests paràmetres defineixen les configuracions d\'assistència de les activitats de docència híbrida';
$string['useqr_desc'] = 'Afegeix la possibilitat de registrar assistència amb qr als usuaris';
$string['rotateqr_desc'] = 'Força l\'ús d\'un qr rotant per registrar assistència';
$string['studentpassword_desc'] = 'La contrasenya que els usuaris han d\'usar per registrar la vostra assistència';
$string['hidechats'] = 'Amagar xats';
$string['visiblechats'] = 'Mostra xats';

$string['advanceentrycount'] = 'Temps d\'antelació d\'entrada';
$string['advanceentrycount_help'] = 'Temps d\'antelació d\'entrada a la sessió';
$string['advanceentryunit'] = 'Unitat d\'antelació d\'entrada';
$string['advanceentryunit_help'] = 'Unitat de temps d\'antelació d\'entrada';
$string['closedoorscount'] = 'Temps de tancament de portes';
$string['closedoorscount_help'] = 'Temps que els usuaris tenen abans de no poder entrar a la sessió';
$string['closedoorsunit'] = 'Unitat de tancament de portes';
$string['closedoorsunit_help'] = 'Unitat de temps del tancament de portes';
$string['validateattendance_help'] = 'Temps que els usuaris han d\'estar a la sessió per validar la seva assistència';
$string['attendanceunit'] = 'Unitat dassistència';
$string['attendanceunit_help'] = 'Unitat de temps per a l\'assistència';
$string['graceperiod_help'] = 'Temps que tenen els usuaris des de l\'inici de la sessió a partir del qual no se\'ls validarà l\'entrada';
$string['graceperiodunit'] = 'Unitat de període de gràcia';
$string['graceperiodunit_help'] = 'Unitat de temps del període de gràcia';
$string['updatecalen'] = 'Actualitzar esdeveniment del calendari';

$string['sessiontobecreated'] = 'Sessió pendent de ser creada';
$string['recordingdisabled'] = 'Els enregistraments no estan activats. Descàrrega no permesa.';
$string['cannotmanageactivity'] = 'No teniu permisos per actualitzar {$a}';

$string['nouseconfig'] = 'Aquesta configuració no s\'aplica a les videoconferències de {$a}.';
$string['hybridteaching:createsessions'] = 'Permetre crear sessions';

$string['bulkhide'] = 'Mostrar/Amagar sessions';
$string['bulkhidechats'] = 'Mostrar/Amagar xats';
$string['bulkhiderecordings'] = 'Mostrar/Amagar enregistraments';

$string['bulkhidetext'] = 'Estàs segur que vols mostrar/amagar les sessions següents?';
$string['bulkhidechatstext'] = 'Estàs segur que vols mostrar/ocultar els xats següents?';
$string['bulkhiderecordingstext'] = 'Estàs segur que vols mostrar/ocultar els enregistraments següents?';

$string['bulkhidesuccess'] = 'Sessions ocultades amb èxit';
$string['bulkhidechatssuccess'] = 'Xats ocultats amb èxit';
$string['bulkhiderecordingssuccess'] = 'Enregistraments ocultats amb èxit';

$string['hiddenuserattendance'] = '(Sessió oculta per a l\'usuari)';
$string['cantcreatevc'] = 'No pots entrar a la videoconferència: no tens prou permisos o has d\'esperar el moderador.';
$string['sessionperformed'] = '{$a} sessions ja realitzades (accediu en pestanya Sessions)';

$string['qrupdatetime'] = 'Període de rotació del QR/Contrasenya';
$string['qrupdatetime_help'] = 'El període de temps que el QR es mantindrà, fins que canviï, també aplicable a la contrasenya.';
$string['rotateqr_help'] = 'Fuerza l\'ús d\'un codigo QR i una contrasenya que iran rotant cada temps definit

* Deshabilita el camp de la contrasenya si està actiu';

$string['bulkhideatt'] = 'Mostra/Oculta assistència a aquestes sessions';
$string['bulkhideatttext'] = 'Estàs segur que vols mostrar/ocultar l\'assistència a les següents sessions?';
$string['bulkhideattsuccess'] = 'Assistències ocultades amb èxit';
$string['hideatt'] = 'Amagar assistència a aquesta sessió';
$string['visibleatt'] = 'Mostra assistència a aquesta sessió';
$string['updatefinished'] = 'Finalitza sessions que hagin finalitzat per temps';

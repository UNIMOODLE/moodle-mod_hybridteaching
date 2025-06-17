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

$string['pluginname'] = 'Ensinanza Híbrida';
$string['modulename'] = 'Ensinanza Híbrida';
$string['modulenameu'] = 'Docencia_hibrida';
$string['modulenameplural'] = 'Ensinanza híbrida';
$string['hybridteachingname'] = 'Nombre';
$string['pluginadministration'] = 'Administración docente híbrida';

$string['sectionsessions'] = 'Temporalización da sesión';
$string['sectionaudience'] = 'Acceso e función dos participantes';
$string['sectionsessionaccess'] = 'Acceso á sesión';
$string['sectioninitialstates'] = 'Estados iniciais da videoconferencia';
$string['sectionrecording'] = 'Opcións de gravación';
$string['sectionattendance'] = 'Acta de asistencia';

$string['sessionscheduling'] = 'Use a programación de sesións';
$string['undatedsession'] = 'Reutilizar recurso interno';
$string['starttime'] = 'Inicio de sesión';
$string['duration'] = 'Duración';

$string['useattendance'] = 'Utilizar o rexistro de asistencia do alumnado';
$string['useattendance_help'] = 'Activar o rexistro de asistencia do alumnado e, en consecuencia, as cualificacións baseadas na asistencia';
$string['usevideoconference'] = 'Usa o acceso de videoconferencia';
$string['usevideoconference_help'] = 'Activar a videoconferencia';
$string['typevc'] = 'Tipo de videoconferencia';
$string['userecordvc'] = 'Permitir gravacións de videoconferencia';
$string['userecordvc_help'] = 'Permitir gravacións na videoconferencia';

$string['waitmoderator'] = 'Agarda polo moderador';
$string['advanceentry'] = 'Entrada anticipada';
$string['advanceentry_help'] = 'Canto tempo antes do comezo da reunión aparecerá o botón Unirse.';
$string['closedoors'] = 'Peche de portas';
$string['closedoors_help'] = 'Pasado este tempo os estudantes non poden unirse.';
$string['userslimit'] = 'Límite de usuarios';
$string['userslimit_help'] = 'Só aplicable a observadores, non moderadores';
$string['wellcomemessage'] = 'Mensaxe de benvida';
$string['wellcomemessage_help'] = 'Mensaxe de benvida para mostrar ao entrar na videoconferencia';

$string['disablewebcam'] = 'Desactivar cámaras web';
$string['disablemicro'] = 'Desactivar micrófonos';
$string['disableprivatechat'] = 'Desactiva o chat privado';
$string['disablepublicchat'] = 'Desactiva o chat público';
$string['disablesharednotes'] = 'Desactivar as notas compartidas';
$string['hideuserlist'] = 'Ocultar a lista de usuarios';
$string['blockroomdesign'] = 'Disposición da sala de peche';
$string['ignorelocksettings'] = 'Ignorar a configuración do bloqueo';

$string['initialrecord'] = 'Grava todo dende o principio';
$string['hiderecordbutton'] = 'Ocultar o botón de gravación';
$string['showpreviewrecord'] = 'Mostrar a vista previa da gravación';
$string['downloadrecords'] = 'Os estudantes poden descargar gravacións';

$string['validateattendance'] = 'Permanencia para validar asistencia';
$string['totalduration'] = '% lonxitude total';
$string['attendance'] = 'Asistencia';
$string['attendance_help'] = 'Tempo que o alumno debe pasar na videoconferencia para que a súa asistencia sexa válida. <br>Pódese introducir en tempo ou % con respecto á duración total da sesión';

$string['completionattendance'] = 'O usuario debe asistir ás sesións';
$string['completionattendancegroup'] = 'Require asistencia';
$string['completiondetail:attendance'] = 'Asistencia á sesión: {$a}';

$string['subplugintype_hybridteachvc'] = 'Tipo de videoconferencia';
$string['subplugintype_hybridteachvc_plural'] = 'Tipos de videoconferencia';
$string['hybridteachvc'] = 'Plugin Videoconferencia';
$string['hybridteachvcpluginname'] = 'Plugin Videoconferencia';
$string['headerconfig'] = 'Xestiona extensións de videoconferencia';
$string['videoconferenceplugins'] = 'Plugins de videoconferencia';

$string['subplugintype_hybridteachstore'] = 'Tipo de almacenamento';
$string['subplugintype_hybridteachstore_plural'] = 'Tipos de almacenamento';

$string['view_error_url_missing_parameters'] = 'Faltan parámetros neste URL';

$string['programschedule'] = 'Programación';
$string['sessions'] = 'Sesións';
$string['import'] = 'Importar';
$string['export'] = 'Exportar';

$string['hybridteaching:addinstance'] = 'Añade unha nova Docencia híbrida';
$string['hybridteaching:manageactivity'] = 'Xestionar a configuración do ensino híbrido';
$string['hybridteaching:view'] = 'Ver ensinanza híbrida';
$string['hybridteaching:viewjoinurl'] = 'Ver o URL da casa';
$string['hybridteaching:programschedule'] = 'Programación didáctica híbrida';
$string['hybridteaching:sessions'] = 'Ver sesións';
$string['hybridteaching:attendance'] = 'Ver asistencia';
$string['hybridteaching:import'] = 'Importar';
$string['hybridteaching:export'] = 'Exportar';
$string['hybridteaching:bulksessions'] = 'Mostrar o selector de accións de varias sesións';
$string['hybridteaching:sessionsactions'] = 'Ver accións na lista de sesións';
$string['hybridteaching:sessionsfulltable'] = 'Mostra todos os campos da lista de sesións';
$string['hybridteaching:attendancesactions'] = 'Accedendo ás accións na vista de asistencia';
$string['hybridteaching:attendanceregister'] = 'Permiso para rexistrar asistencia á sesión';
$string['hybridteaching:record'] = 'Permitir gravacións';
$string['hybridteaching:viewrecordings'] = 'Ver gravacións';
$string['hybridteaching:viewchat'] = 'Ver chats';
$string['hybridteaching:downloadrecordings'] = 'Descargar gravacións';
$string['hybridteaching:viewhiddenitems'] = 'Ver elementos ocultos';
$string['hybridteaching:viewallsessions'] = 'Permite ver todas as sesións sen filtro de grupo';

$string['type'] = 'Tipo';
$string['order'] = 'Orde';
$string['hideshow'] = 'Ocultar/Mostrar';
$string['addsetting'] = 'Engadir configuración';
$string['editconfig'] = 'Editar a configuración';
$string['saveconfig'] = 'Garda a configuración';
$string['configgeneralsettings'] = 'Configuración xeral do ensino híbrido';
$string['configname'] = 'Nome da configuración';
$string['configselect'] = 'Seleccione unha configuración';
$string['generalconfig'] = 'Configuración xeral';
$string['configsconfig'] = 'Xestionar a configuración';
$string['configsvcconfig'] = 'Xestiona a configuración das videoconferencias';
$string['configsstoreconfig'] = 'Xestionar configuracións de almacenamento';

$string['errorcreateconfig'] = 'Erro ao crear a configuración';
$string['errorupdateconfig'] = 'Erro ao actualizar a configuración.';
$string['errordeleteconfig'] = 'Erro ao eliminar a configuración';
$string['createdconfig'] = 'Configuración creada con éxito';
$string['updatedconfig'] = 'Configuración actualizada con éxito';
$string['deletedconfig'] = 'Configuración eliminada con éxito';
$string['deleteconfirm'] = 'Estás seguro de que queres eliminar a configuración: {$a}?';

$string['view_error_url_missing_parameters'] = 'Faltan parámetros neste URL';

$string['recording'] = 'Gravación';
$string['materials'] = 'Materiais';
$string['actions'] = 'Accións';
$string['start'] = 'Comeza';

$string['sessionfor'] = 'Sesión para o grupo';
$string['sessiondate'] = 'Data da sesión';
$string['addsession'] = 'Engadir sesión';
$string['allgroups'] = 'Todos os grupos';
$string['sessiontypehelp'] = 'Podes engadir sesións para todos os estudantes ou para un grupo de estudantes.
A posibilidade de engadir diferentes tipos depende da modalidade grupal da actividade.
  * No modo de grupo "Sen grupos" só podes engadir sesións para todos os estudantes.
  * No modo de grupo "Grupos separados" podes engadir só sesións para un grupo de estudantes.
  * No modo de grupo "Grupos visibles" pode engadir ambos tipos de sesións.
';
$string['nogroups'] = 'Esta actividade configurouse para utilizar grupos, pero non hai grupos no curso.';
$string['addsession'] = 'Engadir sesión';
$string['presentationfile'] = 'Ficheiro de presentación';
$string['replicatedoc'] = 'Replica o ficheiro en todas as sesións';
$string['caleneventpersession'] = 'Crea un evento de calendario por sesión';
$string['addmultiplesessions'] = 'Sesións múltiples';
$string['repeatasfollows'] = 'Repita a sesión anterior do seguinte xeito';
$string['createmultiplesessions'] = 'Crea varias sesións';
$string['createmultiplesessions_help'] = 'Esta función permítelle crear varias sesións nun só paso.
As sesións comezan na data da sesión base e continúan ata a data de "repetición"..

  * <strong>repetir o</strong>: Selecciona os días da semana na que se reunirá a túa clase (por exemplo, luns/mércores/venres).
  * <strong>Repetir cada</strong>: Isto permítelle establecer unha frecuencia. Se a túa clase reunirase semanalmente, selecciona 1; se te reunirás cada dúas semanas, selecciona 2; cada tres semanas, seleccione 3, etc.
  * <strong>Repetir ata</strong>: Selecciona o último día de clase (o último día no que queres atender a chamada nominal).
';

$string['repeaton'] = 'Repetir o';
$string['repeatevery'] = 'Repetir cada';
$string['repeatuntil'] = 'Repetir ata';
$string['otheroptions'] = 'Outras opcións';
$string['sessionname'] = 'Nome da sesión';

$string['nosessions'] = 'Non hai sesións dispoñibles';
$string['nogroup'] = 'A seguinte sesión non se celebra para o teu grupo';
$string['nosubplugin'] = 'O tipo de videoconferencia é incorrecto. Contacta co teu administrador';
$string['noconfig'] = 'A configuración de videoconferencia seleccionada non existe. Contacta co teu administrador';
$string['noconfig_viewer'] = 'Non hai ningunha configuración de videoconferencia. Contacta co teu profesor.';

$string['status_progress'] = 'Sesión en curso';
$string['status_finished'] = 'Esta sesión rematou';
$string['status_start'] = 'A sesión comezará en breve';
$string['status_ready'] = 'A sesión está lista. Podes entrar agora.';
$string['status_undated'] = 'Podes crear unha sesión recorrente';
$string['status_undated_wait'] = 'Debes esperar ata que comece a nova sesión';

$string['closedoors_hours'] = ' {$a} horas despois do inicio';
$string['closedoors_minutes'] = ' {$a} minutos despois do inicio';
$string['closedoors_seconds'] = ' {$a} 4 segundos despois do inicio';

$string['sessionstart'] = 'A próxima sesión comezará o';
$string['estimatedduration'] = 'Duración estimada:';
$string['advanceentry'] = 'Entrada anticipada:';
$string['closedoors'] = 'Peche de portas de acceso:';
$string['status'] = 'Estado';
$string['started'] = 'Comezou o';
$string['inprogress'] = 'En progreso';
$string['closedoorsnext'] = 'As súas portas pecharanse despois';
$string['closedoorsnext2'] = 'dende o principio';
$string['closedoorsprev'] = 'Esta sesión pechou as súas portas';
$string['closedoorsafter'] = 'de comenzar';
$string['finished'] = 'Esta sesión rematou o';

$string['mod_form_field_participant_list_action_add'] = 'Engadir';
$string['mod_form_field_participant_list'] = 'Listaxe de participantes';
$string['mod_form_field_participant_list_type_all'] = 'Todos os usuarios rexistrados';
$string['mod_form_field_participant_list_type_role'] = 'Rol';
$string['mod_form_field_participant_list_type_user'] = 'Usuario';
$string['mod_form_field_participant_list_type_owner'] = 'Propietario';
$string['mod_form_field_participant_list_text_as'] = 'entrar na sesión como';
$string['mod_form_field_participant_list_action_add'] = 'Engadir';
$string['mod_form_field_participant_list_action_remove'] = 'Eliminar';
$string['mod_form_field_participant_role_moderator'] = 'Moderador';
$string['mod_form_field_participant_role_viewer'] = 'Observador';

$string['equalto'] = 'Igual a';
$string['morethan'] = 'Máis grande cá';
$string['lessthan'] = 'Menor que';
$string['options'] = 'Opcións';
$string['sesperpage'] = 'Sesións por páxina';

$string['updatesessions'] = 'Sesións de actualización';
$string['deletesessions'] = 'Eliminar sesións';
$string['withselectedsessions'] = 'Coas sesións seleccionadas';
$string['go'] = 'Vaia';
$string['options'] = 'Opcións';
$string['sessionsuc'] = 'Sesións';
$string['programscheduleuc'] = 'Programación de sesións';
$string['nosessionsselected'] = 'Non hai sesións seleccionadas';
$string['deletecheckfull'] = 'Estás seguro de que queres eliminar completamente as seguintes sesións, incluídos todos os datos do usuario?';
$string['sessiondeleted'] = 'A sesión eliminouse correctamente';
$string['strftimedmyhm'] = '%d %b %Y %I.%M%p';
$string['extend'] = 'Estender';
$string['reduce'] = 'Diminuír';
$string['seton'] = 'Establecer en';
$string['updatesesduration'] = 'Modificar a duración da sesión';
$string['updatesesstarttime'] = 'Modificar o inicio da sesión';
$string['updateduration'] = 'Modificar duración';
$string['updatestarttime'] = 'Modificar inicio';
$string['advance'] = 'Pase';
$string['delayin'] = 'Atraso en';
$string['editsession'] = 'Editar sesión';

$string['headerconfigstore'] = 'Xestionar extensións de almacenamento';
$string['storageplugins'] = 'Extensións de almacenamento';
$string['importsessions'] = 'Importar sesións';
$string['invalidimportfile'] = 'O formato do ficheiro non é correcto.';
$string['processingfile'] = 'Procesando ficheiro...';
$string['sessionsgenerated'] = '{$a} sesións xeradas con éxito';

$string['error:importsessionname'] = 'O nome da sesión non é válido! Saltando fila {$a}.';
$string['error:importsessionstarttime'] = 'Hora de inicio de sesión non válida! Saltando fila {$a}.';
$string['error:importsessionduration'] = 'Duración da sesión non válida! Saltando fila {$a}.';
$string['formaterror:importsessionstarttime'] = 'Formato non válido para a hora de inicio de sesión! Saltando fila {$a}.';
$string['formaterror:importsessionduration'] = 'Formato non válido para a duración da sesión. Saltando fila {$a}.';
$string['error:sessionunknowngroup'] = 'Nome do grupo descoñecido: {$a}.';
$string['examplecsv'] = 'Ficheiro de texto de exemplo';
$string['examplecsv_help'] = 'As sesións pódense importar mediante CSV, Excel ou ODP. O formato do ficheiro debe ser o seguinte:

  * Cada liña do ficheiro contén un rexistro.
  * Cada rexistro é unha serie de datos separados polo separador seleccionado.
  * O primeiro rexistro contén unha lista de nomes de campo que definen o formato do resto do ficheiro.
  * Os nomes dos campos obrigatorios son o nome, a hora de inicio e a duración.
  * Os nomes de campo opcionais son grupos e descrición.';

$string['nostarttime'] = 'Sen data de inicio';
$string['noduration'] = 'Sen duración';
$string['notypevc'] = 'Sen tipo de videoconferencia';
$string['labeljoinvc'] = 'Acceso a videoconferencia';
$string['joinvc'] = 'Únete á reunión';
$string['createsession'] = 'Crear sesión';
$string['showqr'] = 'Mostrar código QR';
$string['canjoin'] = 'Poderás unirte á reunión cando o profesor a iniciase';
$string['canattendance'] = 'Poderás rexistrar a túa asistencia cando o profesor comece a sesión';
$string['recurringses'] = 'Sesión recorrente';
$string['finishsession'] = 'Finalizar sesión';
$string['sessionnoaccess'] = 'Non tes acceso a esta sesión';
$string['lessamin'] = 'Menos de 1 min';

$string['qrcode'] = 'Codigo QR';
$string['useqr'] = 'Inclúe o uso de QR';
$string['rotateqr'] = 'Xire o código QR';
$string['studentpassword'] = 'Contrasinal do estudante';
$string['passwordheader'] = 'Introduce o contrasinal a continuación para rexistrar a túa asistencia';
$string['qrcodeheader'] = 'Escanea o QR para rexistrar a túa asistencia';
$string['qrcodeandpasswordheader'] = 'Escanea o QR ou introduce o contrasinal a continuación para rexistrar a túa asistencia';
$string['noqrpassworduse'] = 'O uso de QR ou contrasinal está desactivado';
$string['labelshowqrpassword'] = 'Mostrar contrasinal/QR para a asistencia da aula';
$string['showqrpassword'] = 'Mostrar contrasinal/QR';
$string['qrcodevalidbefore'] = 'Código QR válido para:';
$string['qrcodevalidafter'] = 'segundos.';
$string['labelattendwithpassword'] = 'Rexistrar asistencia en Classroom';
$string['attendwithpassword'] = 'Contrasinal de acceso: ';
$string['markattendance'] = 'Rexistrar asistencia';
$string['incorrect_password'] = 'Introduciuse un contrasinal incorrecto.';
$string['attendance_registered'] = 'Asistencia correctamente rexistrada';
$string['qr_expired'] = 'O código QR caducou, asegúrate de ler o código correcto';
$string['grade'] = 'Valoracións';
$string['commonattendance'] = 'Todos os grupos';
$string['videoconference'] = 'Vconf';
$string['classroom'] = 'Aula';

$string['resultsperpage'] = 'Resultados por páxina';
$string['sessresultsperpage_desc'] = 'Número de sesións por páxina';
$string['donotusepaging'] = 'Non use paxinación';
$string['reusesession'] = 'Reutilizar recursos de sesión externos';
$string['reusesession_desc'] = 'Se se selecciona, reutilizaranse os recursos da sesión recorrente';

$string['allsessions'] = 'Global: todas as sesións';
$string['entrytime'] = 'Entrada';
$string['leavetime'] = 'Saír';
$string['permanence'] = 'Permanencia';

$string['passwordgrp'] = 'Contrasinal do estudante';
$string['passwordgrp_help'] = 'Se se estableceu, os estudantes deberán introducir este contrasinal para establecer a asistencia á sesión.

  * Se está baleiro, non se precisa contrasinal.
  * Se se marca a opción Xirar QR, o contrasinal será variable e xirará xunto ao QR.';

$string['maxgradeattendance'] = 'Asistencia á puntuación máxima';
$string['maxgradeattendance_help'] = 'Modo de cálculo

  * Número de sesións consideradas asistidas
  * % número de asistencias sobre o total de sesións accesibles
  * % tempo de asistencia sobre o total nominal de sesións accesibles

';
$string['numsess'] = 'Nº sesións';
$string['percennumatt'] = '% nº de asistencia';
$string['percentotaltime'] = '% tempo total';
$string['percentage'] = 'Porcentaxe';

$string['eventsessionadded'] = 'Sesión engadida';
$string['eventsessionviewed'] = 'Sesión vista';
$string['eventsessionupdated'] = 'Sesión actualizada';
$string['eventsessionrecordviewed'] = 'Rexistro de sesión visto';
$string['eventsessionrecorddownloaded'] = 'Rexistro de sesión descargado';
$string['eventsessionmngviewed'] = 'Ver xestión de sesións';
$string['eventsessionjoined'] = 'Sesión unida';
$string['eventsessioninfoviewed'] = 'Ver información da sesión';
$string['eventsessionfinished'] = 'Rematou a sesión';
$string['eventsessiondeleted'] = 'Eliminouse a sesión';
$string['eventattviewed'] = 'Asistencia vista';
$string['eventattupdated'] = 'Asistencia actualizada';
$string['eventattmngviewed'] = 'Ver xestión de asistencia';

$string['gradenoun'] = 'Cualificación';
$string['gradenoun_help'] = 'Valoración da sesión / Valoración total da actividade / Valoración máxima da actividade';
$string['finishattend'] = 'Terminar asistencia';
$string['bad_neededtime'] = 'Tempo para completar a asistencia menos que a sesión';
$string['attnotfound'] = 'Produciuse un erro ao atopar a identificación para obter axuda, póñase en contacto cun administrador';
$string['entryregistered'] = 'A túa entrada rexistrouse correctamente';
$string['exitregistered'] = 'A túa saída rexistrouse correctamente';
$string['alreadyregistered'] = 'Xa rexistraches a túa entrada, se non podes entrar na sesión, intenta finalizar a túa asistencia e intenta unirte de novo';
$string['exitingleavedsession'] = 'Xa fixeches a compra';
$string['entryneededtoexit'] = 'Tentando finalizar a asistencia á sesión sen iniciar sesión, debes rexistrar a túa entrada na sesión antes de saír';
$string['marks'] = 'Marca';
$string['hour'] = 'Hora';
$string['firstentry'] = 'Marca a entrada da sesión';
$string['sessionentry'] = 'Entra na sesión';
$string['sessionexit'] = 'Pechar sesión';
$string['lastexit'] = 'Saír da sesión';
$string['sessionstarttime'] = 'Inicio efectivo';
$string['sessionendtime'] = 'Finalización real';
$string['participant'] = 'Participante';
$string['userfor'] = 'Asistencia ao alumnado:';
$string['combinedatt'] = 'Total rexistrado';
$string['withselectedattends'] = 'Con asistencias seleccionadas';
$string['prevattend'] = 'Asistencia';
$string['setattendance'] = 'Cambiar asistencia';
$string['setexempt'] = 'Cambiar exento';
$string['setsessionexempt'] = 'Cambiar o uso da sesión na computación de notas';
$string['activeattendance'] = 'Dar por asistido';
$string['inactiveattendance'] = 'Dar por no asistido';
$string['updateattendance'] = 'Actualizar asistencia';
$string['attnotforgrade'] = '(Sesión non utilizada nas cualificacións de computación)';
$string['exempt'] = 'Exento';
$string['exemptattendance'] = 'Exentar uso do asistencia para notas';
$string['notexemptattendance'] = 'Use a asistencia de notas';
$string['exemptsessionattendance'] = 'Uso da sesión exenta na asistencia';
$string['notexemptsessionattendance'] = 'Usar sesion en asistencia';
$string['exemptuser'] = 'Usuario exento na sesión';
$string['sessionsattendance'] = 'Asistencia á sesión';
$string['studentsattendance'] = 'Asistencia ao alumnado';

$string['graceperiod'] = 'Periodo de gracia';
$string['graceperiod_help'] = 'Tempo que ten o usuario para unirse á sesión, antes de que se conte a asistencia atrasada';
$string['session'] = 'Sesión';
$string['participationtime'] = 'Tempo de participación';
$string['noattendanceregister'] = 'Non se pode rexistrar a asistencia á sesión';
$string['attexempt'] = 'Exento de cualificación';
$string['noatt'] = 'Non hai asistencia rexistrada';
$string['attendanceresume'] = 'Resumo de asistencia';
$string['attendedsessions'] = 'Asistiu ás sesións';
$string['validatedattendance'] = 'Asistencias válidas';
$string['finalgrade'] = 'Puntuación final';
$string['late'] = 'Chegada tardía';
$string['earlyleave'] = 'Saída cedo';
$string['withatt'] = 'Con asistencia';
$string['withoutatt'] = 'Sen asistencia';
$string['notexempt'] = 'Non exento';
$string['nofilter'] = 'Sen filtro';
$string['vc'] = 'Videoconferencia';

$string['watchrecording'] = 'Ver gravación';
$string['norecording'] = 'Sen gravación';

$string['entersession'] = 'Podes entrar na sesión para marcar a túa asistencia';
$string['exitsession'] = 'A súa asistencia foi gravada, recordade finalizar a súa asistencia ao final da sesión';
$string['novc'] = 'Sesión sen uso de videoconferencia';
$string['viewstudentinfo'] = 'Asistencia ao estudante';
$string['viewsessioninfo'] = 'Asistencia á sesión';
$string['nologsfound'] = 'Non se atoparon rexistros para o usuario na sesión';
$string['takensessions'] = 'Sesións celebradas';
$string['selectedsessions'] = 'Sesións seleccionadas';
$string['anygroup'] = 'Calquera grupo';
$string['withoutgroup'] = 'Sen grupo';
$string['unknown'] = 'Sen definir';
$string['noattendanceusers'] = 'Non é posible exportar ningunha información porque non hai usuarios inscritos no curso.';
$string['downloadexcel'] = 'Descarga en formato Excel';
$string['downloadooo'] = 'Descarga en formato OpenOffice';
$string['downloadtext'] = 'Descarga en formato de texto';
$string['startofperiod'] = 'Inicio do período';
$string['endofperiod'] = 'Fin do período';
$string['includeall'] = 'Selecciona todas as sesións';
$string['joinurl'] = 'Url de acceso: ';
$string['passstring'] = 'Contrasinal: ';
$string['vcconfigremoved'] = 'Un administrador eliminou a configuración de videoconferencia da actividade';
$string['hiderecords'] = 'Ocultar gravacións';
$string['visiblerecords'] = 'Mostrar gravacións';

$string['error:deleteinprogress'] = 'Non podes eliminar unha sesión en curso';
$string['deletewithhybridmods'] = 'Esta configuración utilízase nos seguintes módulos de ensinanza híbrida: {$a}. Estás seguro de que queres eliminalo?';
$string['lostconfig'] = 'Este axuste foi eliminado por un administrador';
$string['noinitialstateconfig'] = 'Este tipo de videoconferencia non ten configuración de estado inicial';
$string['cantfinishunstarted'] = 'Non pode finalizar unha sesión antes do inicio programado';

$string['error_unable_join'] = 'Non foi posible conectarse. Non se puido atopar a reunión ou eliminouse. Contacta co teu profesor ou administrador.';
$string['sessionscheduling_help'] = 'Se no curso se obriga o uso de grupos, haberá que programar a sesión.

Se non é forzado, o uso de sesións non programadas desactiva o uso de grupos na sesión.';

$string['error:importsessiontimetype'] = 'Tipo de duración da sesión non válido! Saltando fila {$a}.';

$string['invalidduration'] = 'Duración non válida';
$string['chaturlmeeting'] = 'Chat de reunión';
$string['notesurlmeeting'] = 'Notas da reunión';

$string['sessionendbeforestart'] = 'A sesión remataría antes de comezar, cambiaría a duración ou a data de inicio';
$string['repeatsessionsamedate'] = 'A data de finalización da sesión repetida non pode ser hoxe';
$string['programsessionbeforenow'] = 'A data de finalización da sesión repetida non pode ser anterior a hoxe';
$string['daynotselected'] = 'Seleccione un día para repetir sesións';

$string['norecordingmoderation'] = 'Non tes acceso de moderación para permitir a gravación deste tipo de videoconferencia.';
$string['chats'] = 'Chats';

$string['enabled'] = 'Activado';
$string['enabled_help'] = 'Se a opción está activada, esta extensión estará habilitada';
$string['disabled'] = 'Deshabilitado';
$string['savechanges'] = 'Gardar cambios';
$string['cancel'] = 'Cancelar';
$string['categoryselect'] = 'Selección personalizada';

$string['defaultsettings'] = 'Configuración xeral de ensinanza híbrida';
$string['defaultsettings_help'] = 'Estes axustes definen as configuracións xerais das actividades docentes híbridas';
$string['sessionscheduling_desc'] = 'Cando está activo, obriga a crear sesións mediante a programación de sesións';
$string['waitmoderator_desc'] = 'Os usuarios deben esperar a que un moderador se una á videoconferencia';
$string['useattendance_help'] = 'Engadir o uso da asistencia nas sesións';
$string['usevideoconference_help'] = 'Engade o uso de videochamadas nas sesións';
$string['userecord_help'] = 'Engade a posibilidade de realizar gravacións en sesións';
$string['sessionssettings'] = 'Configuración da sesión de ensinanza híbrida';
$string['sessionssettings_help'] = 'Esta configuración define as opcións predeterminadas para as sesións de ensino híbrido.';
$string['userslimit_desc'] = 'Número máximo de usuarios non moderadores que poden entrar na videoconferencia';
$string['attendancesettings'] = 'Configuración de asistencia para a ensinanza híbrida';
$string['attendancesettings_help'] = 'Estes axustes definen as configuracións de asistencia para as actividades de ensino híbrido.';
$string['useqr_desc'] = 'Engade a posibilidade de rexistrar asistencia con QR para os usuarios';
$string['rotateqr_desc'] = 'Forzar o uso dun QR rotativo para rexistrar a asistencia';
$string['studentpassword_desc'] = 'O contrasinal que deben utilizar os usuarios para rexistrar a súa asistencia';
$string['hidechats'] = 'Ocultar chats';
$string['visiblechats'] = 'Mostrar chats';

$string['advanceentrycount'] = 'Tempo de entrada anticipada';
$string['advanceentrycount_help'] = 'Tempo de entrada anticipada á sesión';
$string['advanceentryunit'] = 'Unidade de entrada anticipada';
$string['advanceentryunit_help'] = 'Unidade de prazo de entrada';
$string['closedoorscount'] = 'Tiempo de peche da porta';
$string['closedoorscount_help'] = 'Tempo que teñen os usuarios antes de non poder iniciar sesión';
$string['closedoorsunit'] = 'Unidade de peche de portas';
$string['closedoorsunit_help'] = 'Unidade de tempo de peche de portas';
$string['validateattendance_help'] = 'Tempo que teñen que estar os usuarios na sesión para validar a súa asistencia';
$string['attendanceunit'] = 'Unidade de asistencia';
$string['attendanceunit_help'] = 'Unidade de tempo de asistencia';
$string['graceperiod_help'] = 'Tempo que teñen os usuarios desde o inicio da sesión a partir do cal non se validará a súa entrada';
$string['graceperiodunit'] = 'Unidade de período de graza';
$string['graceperiodunit_help'] = 'Unidade de tempo de período de graza';
$string['updatecalen'] = 'Actualizar o evento do calendario';

$string['sessiontobecreated'] = 'Sesión pendente de creación';
$string['recordingdisabled'] = 'As gravacións non están activadas. Non se permite a descarga.';
$string['cannotmanageactivity'] = 'Non tes permisos para actualizar {$a}';

$string['nouseconfig'] = 'Esta configuración non se aplica ás videoconferencias de {$a}.';
$string['hybridteaching:createsessions'] = 'Permitir crear sesiones';

$string['bulkhide'] = 'Mostrar/Ocultar sesións';
$string['bulkhidechats'] = 'Mostrar/Ocultar chats';
$string['bulkhiderecordings'] = 'Mostrar/Ocultar gravacións';

$string['bulkhidetext'] = 'Estás seguro de que queres mostrar/ocultar as seguintes sesións?';
$string['bulkhidechatstext'] = 'Estás seguro de que queres mostrar/ocultar os seguintes chats?';
$string['bulkhiderecordingstext'] = 'Estás seguro de que queres mostrar/ocultar as seguintes gravacións?';

$string['bulkhidesuccess'] = 'Sesións ocultas correctamente';
$string['bulkhidechatssuccess'] = 'Chats ocultos correctamente';
$string['bulkhiderecordingssuccess'] = 'Gravacións ocultas correctamente';

$string['hiddenuserattendance'] = '(Sesión oculta para o usuario)';
$string['cantcreatevc'] = 'Non podes entrar na videoconferencia: non tes permisos suficientes ou debes esperar ao moderador.';
$string['sessionperformed'] = '{$a} sesións xa realizadas (acceso na pestana Sesións)';

$string['qrupdatetime'] = 'Período de rotación de QR/contrasinal';
$string['qrupdatetime_help'] = 'O período de tempo que permanecerá o QR, ata que cambie, tamén aplicable ao contrasinal.';
$string['rotateqr_help'] = 'Forza o uso dun código QR e un contrasinal que rotará cada vez que se defina

  * Desactiva o campo de contrasinal se está activo';

$string['bulkhideatt'] = 'Mostrar/Ocultar a asistencia a estas sesións';
$string['bulkhideatttext'] = 'Estás seguro de que queres mostrar/ocultar a asistencia ás seguintes sesións?';
$string['bulkhideattsuccess'] = 'Asistencias ocultas correctamente';
$string['hideatt'] = 'Ocultar asistencia a esta sesión';
$string['visibleatt'] = 'Mostrar asistencia a esta sesión';
$string['updatefinished'] = 'Remata as sesións que remataron por mor do tempo';
$string['cachedef_sessatt'] = 'Datos de asistencia da sesión';


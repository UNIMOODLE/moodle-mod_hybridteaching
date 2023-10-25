<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     mod_hybridteaching
 * @category    string
 * @copyright   2023 isyc <isyc@isyc.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Docencia Híbrida';
$string['modulename'] = 'Docencia híbrida';
$string['modulenameu'] = 'Docencia_hibrida';
$string['modulenameplural'] = 'Docencia híbrida';
$string['hybridteachingname'] = 'Nombre';
$string['pluginadministration'] = 'Administración Docencia híbrida';

$string['sectionsessions'] = 'Temporalización de sesiones';
$string['sectionaudience'] = 'Acceso y rol de participantes';
$string['sectionsessionaccess'] = 'Acceso a la sesión';
$string['sectioninitialstates'] = 'Estados iniciales de la videoconferencia';
$string['sectionrecording'] = 'Opciones de grabación';
$string['sectionattendance'] = 'Registro de asistencia';

$string['sessionscheduling'] = 'Usar programación de sesiones';
$string['undatedsession'] = 'Reutilizar el recurso interno';
$string['starttime'] = 'Inicio de sesión';
$string['duration'] = 'Duración';

$string['useattendance'] = 'Usar registro de asistencia de estudiantes';
$string['useattendance_help'] = 'Activar el registro de asistencia de estudiantes, y consecuentemente las calificaciones basadas en la asistencia';
$string['usevideoconference'] = 'Usar acceso por videoconferencia';
$string['usevideoconference_help'] = 'Activar videoconferencia';
$string['typevc'] = 'Tipo de videoconferencia';
$string['userecordvc'] = 'Permitir grabaciones de videoconferencia';
$string['userecordvc_help'] = 'Permitir las grabaciones en la videoconferencia';

$string['waitmoderator'] = 'Esperar al moderador';
$string['advanceentry'] = 'Antelación de entrada';
$string['advanceentry_help'] = 'Cuánto tiempo antes del inicio de la reunión se muestra el botón Unirse.';
$string['closedoors'] = 'Cierre de puertas';
$string['closedoors_help'] = 'Pasado este tiempo los estudiantes no pueden unirse.';
$string['userslimit'] = 'Límite de usuarios';
$string['userslimit_help'] = 'Solo aplicable a observadores, no moderadores';

$string['disablewebcam'] = 'Desactivar las cámaras web';
$string['disablemicro'] = 'Desactivar los micrófonos';
$string['disableprivatechat'] = 'Desactivar el chat privado';
$string['disablepublicchat'] = 'Desactivar el chat público';
$string['disablesharednotes'] = 'Desactivar notas compartidas';
$string['hideuserlist'] = 'Ocultar la lista de usuarios';
$string['blockroomdesign'] = 'Bloquear el diseño de la sala';
$string['ignorelocksettings'] = 'Ignorar los ajustes de bloqueo';

$string['initialrecord'] = 'Grabar todo desde el inicio';
$string['hiderecordbutton'] = 'Ocultar botón de grabación';
$string['showpreviewrecord'] = 'Mostrar vista previa de grabación';
$string['downloadrecords'] = 'Estudiantes pueden descargar grabaciones';

$string['validateattendance'] = 'Permanencia para validar asistencia';
$string['totalduration'] = '% duración total';
$string['attendance'] = 'Asistencia';
$string['attendance_help'] = 'Cantidad de tiempo que debe pasar el estudiante en la videoconferencia para que su asistencia sea válida. <br>Puede introducirse en tiempo o % con respecto a la duración total de la sesión';

$string['completionattendance'] = 'El usuario debe asistir a sesiones';
$string['completionattendancegroup'] = 'Requiere asistencia';
$string['completiondetail:attendance'] = 'Sessions attendance: {$a}';

$string['subplugintype_hybridteachvc'] = 'Tipo de videoconferencia';
$string['subplugintype_hybridteachvc_plural'] = 'Tipos de videoconferencia';
$string['hybridteachvc'] = 'Plugin Videoconferencia';
$string['hybridteachvcpluginname'] = 'Plugin Videoconferencia';
$string['headerconfig'] = 'Gestionar extensiones de videoconferencia';
$string['mediaplayers'] = 'Plugins de videoconferencia';

$string['view_error_url_missing_parameters'] = 'Faltan parámetros en esta URL';

$string['programschedule'] = 'Programación';
$string['sessions'] = 'Sesiones';
$string['import'] = 'Importar';
$string['export'] = 'Exportar';

$string['hybridteaching:addinstance'] = 'Añade una nueva docencia híbrida';
$string['hybridteaching:view'] = 'Ver docencia híbrida';
$string['hybridteaching:viewjoinurl'] = 'Ver url de inicio';
$string['hybridteaching:programschedule'] = 'Programación de docencia híbrida';
$string['hybridteaching:sessions'] = 'Ver sesiones';
$string['hybridteaching:attendance'] = 'Ver asistencia';
$string['hybridteaching:import'] = 'Importar';
$string['hybridteaching:export'] = 'Exportar';
$string['hybridteaching:downloadrecordings'] = 'Descargar grabaciones';
$string['hybridteaching:manage_recordings'] = 'Administrar grabaciones';
$string['hybridteaching:record'] = 'Permitir grabaciones';
$string['hybridteaching:viewallsessions'] = 'Permitir ver todas las sesiones sin filtro de grupo';
$string['hybridteaching:viewhiddenitems'] = 'Ver elementos ocultos';
$string['hybridteaching:viewrecordings'] = 'Ver grabaciones';

$string['type'] = 'Tipo';
$string['order'] = 'Ordenar';
$string['hideshow'] = 'Ocultar/Mostrar';
$string['addsetting'] = 'Añadir configuración';
$string['editconfig'] = 'Editar configuración';
$string['saveconfig'] = 'Guardar configuración';
$string['configgeneralsettings'] = 'Configuración general de docencia híbrida';
$string['configname'] = 'Nombre de configuración';
$string['configselect'] = 'Seleccionar una configuración';
$string['generalconfig'] = 'Configuración general';
$string['configsconfig'] = 'Administrar configuracións';
$string['configsvcconfig'] = 'Administrar configuraciones de videoconferencia';
$string['configsstoreconfig'] = 'Administrar configuraciones de almacenamiento';
$string['storageplugins'] = 'Extensiones de almacenamiento';

$string['errorcreateconfig'] = 'Error al crear la configuración';
$string['errorupdateconfig'] = 'Error al actualizar la configuración';
$string['errordeleteconfig'] = 'Error al eliminar la configuración';
$string['createdconfig'] = 'Configuración creada con éxito';
$string['updatedconfig'] = 'Configuración actualizada con éxito';
$string['deletedconfig'] = 'Configuración eliminada con éxito';
$string['deleteconfirm'] = '¿Está seguro de que desea eliminar la configuración: {$a}?';

$string['view_error_url_missing_parameters'] = 'Faltan parámetros en esta URL';

$string['recording'] = 'Grabación';
$string['materials'] = 'Materiales';
$string['actions'] = 'Acciones';
$string['start'] = 'Inicio';

$string['sessionfor'] = 'Sesión para el grupo';
$string['sessiondate'] = 'Fecha de la sesión';
$string['addsession'] = 'Añadir sesión';
$string['allgroups'] = 'Todos los grupos';
$string['sessiontypehelp'] = 'Puedes añadir sesiones para todos los alumnos o para un grupo de alumnos. 
La posibilidad de añadir diferentes tipos depende del modo de grupo de la actividad.
  * En el modo de grupo "Sin grupos" sólo puede añadir sesiones para todos los estudiantes.
  * En el modo de grupo "Grupos separados" puede añadir sólo sesiones para un grupo de estudiantes.
  * En el modo de grupo "Grupos visibles" puede añadir ambos tipos de sesiones.
';
$string['nogroups'] = 'Esta actividad ha sido configurada para usar grupos, pero no existen grupos en el curso.';
$string['addsession'] = 'Añadir sesión';
$string['addsession'] = 'Añadir sesión';
$string['presentationfile'] = 'Archivo de presentación';
$string['replicatedoc'] = 'Replicar archivo a todas las sesiones';
$string['caleneventpersession'] = 'Crear un evento de calendario por sesión';
$string['addmultiplesessions'] = 'Múltiples sesiones';
$string['repeatasfollows'] = 'Repetir la sesión anterior de la siguiente manera';
$string['createmultiplesessions'] = 'Crear múltiples sesiones';
$string['createmultiplesessions_help'] = 'Esta función le permite crear múltiples sesiones en un simple paso.
Las sesiones comienzan en la fecha de la sesión base y continúan hasta la fecha de "repetición".

  * <strong>Repetir el</strong>: Seleccione los días de la semana en los que se reunirá su clase (por ejemplo, lunes/miércoles/viernes).
  * <strong>Repetir cada</strong>: Esto permite establecer una frecuencia. Si su clase se reunirá todas las semanas, seleccione 1; si se reunirá cada dos semanas, seleccione 2; cada tres semanas, seleccione 3, etc.
  * <strong>Repetir hasta</strong>: Selecciona el último día de clase (el último día que quieres pasar lista).
';

$string['repeaton'] = 'Repetir el';
$string['repeatevery'] = 'Repetir cada';
$string['repeatuntil'] = 'Repetir hasta';
$string['otheroptions'] = 'Otras opciones';
$string['sessionname'] = 'Nombre de la sesión';

$string['nosessions'] = 'No hay sesiones disponibles';
$string['nogroup'] = 'La próxima sesión no se realiza para su grupo';
$string['nosubplugin'] = 'El tipo de videoconferencia es incorrecto. Contacte con su administrador';
$string['noconfig'] = 'No existe la configuración de videoconferencia seleccionada. Contacte con su adminstrador';

$string['status_progress'] = 'Sesión en progreso';
$string['status_finished'] ='Esta sesión ha finalizado';
$string['status_start'] = 'La sesión comenzará próximamente';
$string['status_ready'] = 'La sesión está lista. Puede entrar ahora.';
$string['status_undated'] = 'Puede crear una sesión recurrente';

$string['closedoors_hours'] = ' {$a} horas tras el inicio';
$string['closedoors_minutes'] = ' {$a} minutos tras el inicio';
$string['closedoors_seconds'] = ' {$a} segundos tras el inicio';

$string['sessionstart'] = 'La siguiente sesión comenzará el';
$string['estimatedduration'] = 'Duración estimada:';
$string['advanceentry'] = 'Antelación de entrada:';
$string['closedoors'] = 'Cierre de puertas de acceso:';
$string['status'] = 'Estado';
$string['started'] = 'Inició el';
$string['inprogress'] = 'En progreso';
$string['closedoorsnext'] = 'Se cerrarán sus puertas tras';
$string['closedoorsnext2'] = 'del inicio';
$string['closedoorsprev'] = 'Esta sesión cerró sus puertas a los';
$string['finished'] = 'Esta sesión se terminó el';

$string['mod_form_field_participant_list_action_add'] = 'Agregar';
$string['mod_form_field_participant_list'] = 'Lista de participantes';
$string['mod_form_field_participant_list_type_all'] = 'Todos los usuarios inscritos';
$string['mod_form_field_participant_list_type_role'] = 'Rol';
$string['mod_form_field_participant_list_type_user'] = 'Usuario';
$string['mod_form_field_participant_list_type_owner'] = 'Propietario';
$string['mod_form_field_participant_list_text_as'] = 'entra en la sesión como';
$string['mod_form_field_participant_list_action_add'] = 'Agregar';
$string['mod_form_field_participant_list_action_remove'] = 'Eliminar';
$string['mod_form_field_participant_role_moderator'] = 'Moderador';
$string['mod_form_field_participant_role_viewer'] = 'Observador';

$string['equalto'] = 'Igual a';
$string['morethan'] = 'Mayor que';
$string['lessthan'] = 'Menor que';
$string['options'] = 'Opciones';
$string['sesperpage'] = 'Sesiones por página';
$string['hybridteaching:bulksessions'] = 'Mostrar el selector de acciones múltiples de sesiones';
$string['updatesessions'] = 'Actualizar sesiones';
$string['deletesessions'] = 'Borrar sesiones';
$string['withselectedsessions'] = 'Con las sesiones seleccionadas';
$string['go'] = 'Ir';
$string['options'] = 'Opciones';
$string['sessionsuc'] = 'Sesiones';
$string['programscheduleuc'] = 'Programación de sesiones';
$string['nosessionsselected'] = 'Sin sesiones seleccionadas';
$string['deletecheckfull'] = '¿Está completamente seguro de que desea eliminar por completo {$a}, incluidos todos los datos del usuario?';
$string['sessiondeleted'] = 'Sesión eliminada con éxito';
$string['strftimedmyhm'] = '%d %b %Y %I.%M%p';
$string['extend'] = 'Extender';
$string['reduce'] = 'Reducir';
$string['seton'] = 'Establecer en';
$string['updatesesduration'] = 'Modificar duración de la sesión';
$string['updatesesstarttime'] = 'Modificar el inicio de la sesión';
$string['updateduration'] = 'Modificar duración';
$string['updatestarttime'] = 'Modificar inicio';
$string['advance'] = 'Adelantar';
$string['delayin'] = 'Retrasar en';
$string['hybridteaching:sessionsactions'] = 'Ver acciones en la lista de sesiones';
$string['hybridteaching:sessionsfulltable'] = 'Mostrar todos los campos de las lista de sesiones';
$string['editsession'] = 'Editar la sesión';

$string['error:importsessionname'] = '¡Nombre de sesión inválido! Saltando línea {$a}.';
$string['error:importsessionstarttime'] = '¡Hora de inicio de sesión no válida! Saltando línea {$a}.';
$string['error:importsessionduration'] = '¡Duración de sesión inválida! Saltando línea {$a}.';
$string['formaterror:importsessionstarttime'] = '¡Formato no válido para la hora de inicio de sesión! Saltando línea {$a}.';
$string['formaterror:importsessionduration'] = '¡Formato no válido para la duración de la sesión! Saltando línea {$a}.';
$string['error:sessionunknowngroup'] = 'Nombre de grupo desconocido: {$a}.';
$string['examplecsv'] = 'Archivo de texto de ejemplo';
$string['examplecsv_help'] = 'Las sesiones pueden importarse mediante CSV, Excel u ODP. El formato del archivo debe ser el siguiente:

  * Cada línea del archivo contiene un registro
  * Cada registro es una serie de datos separados por el separador seleccionado.
  * El primer registro contiene una lista de nombres de campo que definen el formato del resto del fichero.
  * Los nombres de campo obligatorios son el nombre, la hora de inicio y la duración.
  * Los nombres de campo opcionales son grupos y descripción';

$string['nostarttime'] = 'Sin fecha de inicio';
$string['noduration'] = 'Sin duración';
$string['notypevc'] = 'Sin tipo de videoconferencia';
$string['joinvc'] = 'Unirte a la reunión';
$string['createsession'] = 'Crear sesión';
$string['showqr'] = 'Mostrar código QR';
$string['canjoin'] = 'Podrás unirte a la reunión cuando el profesor la haya iniciado';
$string['canattendance'] = 'Podrás registrar tu asistencia cuando el profesor haya iniciado la sesión';
$string['recurringses'] = 'Sesión recurrente';
$string['finishsession'] = 'Finalizar sesión';
$string['sessionnoaccess'] = 'No tienes acceso a esta sesión';
$string['lessamin'] = 'Menos de 1 min';

$string['qrcode'] = 'Codigo QR';
$string['useqr'] = 'Incluir uso de QR';
$string['rotateqr'] = 'Rotar codigo QR';
$string['studentpassword'] = 'Contraseña de alumnos';
$string['passwordheader'] = 'Introduzca la contraseña de abajo para registrar su asistencia';
$string['qrcodeheader'] = 'Escanee el qr para registrar su asistencia';
$string['qrcodeandpasswordheader'] = 'Escanee el QR o introduzca la contraseña de abajo para registrar su asistencia';
$string['noqrpassworduse'] = 'El uso de QR o contraseña se encuentran deshabilitados';
$string['showqrpassword'] = 'Mostrar Contraseña / QR';
$string['qrcodevalidbefore'] = 'Codigo QR valido por:';
$string['qrcodevalidafter'] = 'segundos.';
$string['attendwithpassword'] = 'Contraseña de acceso: ';
$string['markattendance'] = 'Registrar asistencia';
$string['incorrect_password'] = 'Contraseña incorrecta introducida.';
$string['attendance_registered'] = 'Asistencia registrada correctamente';
$string['qr_expired'] = 'El codigo QR ha expirado, asegurese de leer el codigo correcto';
$string['grade'] = 'Calificaciones';
$string['commonattendance'] = 'Todos los grupos';
$string['videoconference'] = 'Vconf';
$string['classroom'] = 'Aula';

$string['importsessions'] = 'Importar sesiones';
$string['export'] = 'Exportar';
$string['invalidimportfile'] = 'El formato del archivo no es correcto.';
$string['processingfile'] = 'Procesando archivo...';
$string['sessionsgenerated'] = '{$a} sesiones generadas con éxito';
$string['resultsperpage'] = 'Resultados por página';
$string['sessresultsperpage_desc'] = 'Número de sesiones por página';
$string['donotusepaging'] = 'No usar paginación';
$string['reusesession'] = 'Reutilizar recursos externos de sesiones';
$string['reusesession_desc'] = 'Si está marcado, se reutilizarán los recursos de sesiones recurrentes';
$string['configsubcategories'] = 'Aplicar configuración para subcategorías';
$string['configsubcategories_desc'] = 'Si está marcado, la configuración de los subplugins estará disponible para la categoría seleccionada y sus subcategorías';

$string['allsessions'] = 'Global - todas las sesiones';
$string['entrytime'] = 'Entrada';
$string['leavetime'] = 'Salida';
$string['permanence'] = 'Permanencia';

$string['passwordgrp'] = 'Contraseña de estudiante';
$string['passwordgrp_help'] = 'Si se establece, los estudiantes deberán ingresar esta contraseña antes de poder establecer su propio estado de asistencia para la sesión. Si está vacío, no se requiere contraseña.';

$string['maxgradeattendance'] = 'Asistencia para máxima puntuación';
$string['maxgradeattendance_help'] = 'Modo de cálculo
  * Nº de sesiones dadas por asistidas
  * % nº de asistencias sobre el total de sesiones accesibles
  * % tiempo asistido sobre el total nominal de sesiones accesibles';

$string['numsess'] = 'Nº sesiones';
$string['percennumatt'] = '% nº asistencia';
$string['percentotaltime'] = '% tiempo total';
$string['percentage'] = 'Porcentaje';

$string['eventsessionadded'] = 'Sesión añadida';
$string['eventsessionviewed'] = 'Sesión vista';
$string['eventsessionupdated'] = 'Sesión actualizada';
$string['eventsessionrecordviewed'] = 'Registro de sesión visto';
$string['eventsessionrecorddownloaded'] = 'Registro de sesión descargado';
$string['eventsessionmngviewed'] = 'Gestión de sesión vista';
$string['eventsessionjoined'] = 'Sesión unida';
$string['eventsessioninfoviewed'] = 'Información de la sesión vista';
$string['eventsessionfinished'] = 'Sesión finalizada';
$string['eventsessiondeleted'] = 'Sesión eliminada';
$string['eventattviewed'] = 'Asistencia vista';
$string['eventattupdated'] = 'Asistencia actualizada';
$string['eventattmngviewed'] = 'Gestión de asistencia vista';

$string['gradenoun'] = 'Calificación';
$string['gradenoun_help'] = 'Calificación de la sesión / Calificación total de la actividad / Calificación máxima de la actividad';
$string['finishattend'] = 'Terminar asistencia';
$string['bad_neededtime'] = 'Tiempo para completar asistencia menor que el de la session';
$string['attnotfound'] = 'Error al encontrar el id para su asistencia contacte un administrador';
$string['entryregistered'] = 'Se ha registrado su entrada correctamente';
$string['exitregistered'] = 'Se ha registrado su salida correctamente';
$string['alreadyregistered'] = 'Ya ha registrado su entrada, si no puede entrar a la sesion, intente finalizar su asistencia y pruebe a unirse de nuevo';
$string['exitingleavedsession'] = 'Ya ha registrado su salida';
$string['entryneededtoexit'] = 'Intentando finalizar asistencia en sesion sin entrada, debe registrar su entrada a la sesión antes de salir';
$string['marks'] = 'Marca';
$string['hour'] = 'Hora';
$string['firstentry'] = 'Marca la entrada a la sesión';
$string['sessionentry'] = 'Entra a la sesión';
$string['sessionexit'] = 'Sale de la sesión';
$string['lastexit'] = 'Marca la salida de la sesión';
$string['sessionstarttime'] = 'Inicio efectivo';
$string['sessionendtime'] = 'Finalización real';
$string['participant'] = 'Participante';
$string['userfor'] = 'Asistencia para estudiante:';
$string['combinedatt'] = 'Total registrado';
$string['withselectedattends'] = 'Con las asistencias seleccionadas';
$string['prevattend'] = 'Asistencia';
$string['setattendance'] = 'Cambiar asistencia';
$string['setexempt'] = 'Cambiar exento';
$string['setsessionexempt'] = 'Cambiar uso de sesión en computo nota';
$string['activeattendance'] = 'Dar por asistido';
$string['inactiveattendance'] = 'Dar por no asistido';
$string['updateattendance'] = 'Actualizar asistencia';
$string['attnotforgrade'] = '(Sesión no usada en computo de notas)';
$string['exempt'] = 'Exento';
$string['exemptattendance'] = 'Exentar uso de asistencia para notas';
$string['notexemptattendance'] = 'Usar asistencia para notas';
$string['exemptsessionattendance'] = 'Exentar uso de session en asistencia';
$string['notexemptsessionattendance'] = 'Usar sesion en asistencia';
$string['exemptuser'] = 'Usuario exento en la sesión';
$string['sessionsattendance'] = 'Asistencia sesiones';
$string['studentsattendance'] = 'Asistencia studiantes';

$string['graceperiod'] = 'Periodo de gracia';
$string['graceperiod_help'] = 'Tiempo que el usuario tiene para unirse a la sesión, antes de que se le contabilice la asistencia con retraso';
$string['session'] = 'Sesión';
$string['participationtime'] = 'Tiempo participado';
$string['noattendanceregister'] = 'No puedes registrar asistencia en la sesión';
$string['attexempt'] = 'Exenta para calificación';
$string['noatt'] = 'Sin asistencia registrada';
$string['attendanceresumee'] = 'Resumen de asistencia';
$string['attendedsessions'] = 'Sesiones atendidas';
$string['validatedattendance'] = 'Asistencias validas';
$string['finalgrade'] = 'Nota final';
$string['late'] = 'Llegada tardía';
$string['earlyleave'] = 'Abandono temprano';
$string['withatt'] = 'Con asistencia';
$string['withoutatt'] = 'Sin asistencia';
$string['notexempt'] = 'No exento';
$string['nofilter'] = 'Sin filtro';
$string['vc'] = 'Videoconferencia';

$string['watchrecording'] = 'Ver grabación';
$string['norecording'] = 'Sin grabación';

$string['entersession'] = 'Puede entrar a la sesión para marcar su asistencia';
$string['exitsession'] = 'Se ha registrado su asistencia recuerde terminar su asistencia al acabar la sesión';
$string['hybridteaching:attendancesactions'] = 'Acceso a las acciones en la vista de asistencia';
$string['hybridteaching:attendanceregister'] = 'Permiso para registrar asistencia en la sesión';
$string['novc'] = 'Sesión sin uso de videoconferencia';
$string['viewstudentinfo'] = 'Asistencia estudiante';
$string['viewsessioninfo'] = 'Asistencia sesión';
$string['nologsfound'] = 'No se encontraron registros para el usuario en la sesión';
$string['takensessions'] = 'Sesiones realizadas';
$string['selectedsessions'] = 'Sessiones seleccionadas';
$string['anygroup'] = 'Cualquier grupo';
$string['withoutgroup'] = 'Sin grupo';

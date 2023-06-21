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
$string['attendance'] = 'Asistencia';
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


$string['type'] = 'Tipo';
$string['order'] = 'Ordenar';
$string['hideshow'] = 'Ocultar/Mostrar';
$string['addinstance'] = 'Agregar instancia';
$string['editinstance'] = 'Editar instancia';
$string['saveinstance'] = 'Guardar instancia';
$string['instancegeneralsettings'] = 'Configuración general de docencia híbrida';
$string['instancename'] = 'Nombre de instancia';
$string['instanceselect'] = 'Seleccionar una instancia';
$string['generalconfig'] = 'Configuración general';
$string['instancesconfig'] = 'Administrar instancias';

$string['errorcreateinstance'] = 'Error al crear la instancia';
$string['errorupdateinstance'] = 'Error al actualizar la instancia';
$string['errordeleteinstance'] = 'Error al eliminar la instancia';
$string['createdinstance'] = 'Instancia creada con éxito';
$string['updatedinstance'] = 'Instancia actualizada con éxito';
$string['deletedinstance'] = 'Instancia eliminada con éxito';
$string['deleteconfirm'] = '¿Está seguro de que desea eliminar la instancia: {$a}?';

$string['view_error_url_missing_parameters'] = 'Faltan parámetros en esta URL';

$string['recording'] = 'Grabación';
$string['materials'] = 'Materiales';
$string['actions'] = 'Acciones';
$string['start'] = 'Inicio';

$string['sessionfor'] = 'Sesión para el grupo';
$string['sessiondate'] = 'Fecha de la sesión';
$string['addsession'] = 'Añadir sesión';
$string['commonsession'] = 'Todos los grupos';
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

$string['status_progress'] = 'Videoconferencia en progreso';
$string['status_finished'] ='Esta sesión de videoconferencia ha finalizado';
$string['status_start'] = 'Comenzará próximamente';

$string['closedoors_hours'] = ' {$a} horas tras el inicio';
$string['closedoors_minutes'] = ' {$a} minutos tras el inicio';
$string['closedoors_seconds'] = ' {$a} segundos tras el inicio';

$string['sessionstart'] = 'La siguiente sesión comenzará el';
$string['estimatedduration'] = 'Duración estimada:';
$string['advanceentry'] = 'Antelación de entrada:';
$string['closedoors'] = 'Cierre de puestas de acceso:';
$string['status'] = 'Estado';
$string['started'] = 'Inició el';
$string['inprogress'] = 'En progreso';
$string['closedoorsnext'] = 'Se cerrarán sus puertas tras';
$string['closedoorsnext2'] = 'del inicio';
$string['closedoorsprev'] = 'Esta sesión cerró sus puertas a los';
$string['finished'] = 'Esta sesión se terminó el';


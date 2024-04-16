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
 * Display information about all the mod_hybridteaching modules in the requested course. *
 * @package    hybridteachstore_onedrive
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'OneDrive híbrido';
$string['pluginconfig'] = 'Configuración de OneDrive Store';
$string['pluginnewconfig'] = 'Nueva configuración de OneDrive Store';
$string['tenantid'] = 'Tenant ID';
$string['clientid'] = 'Cliente ID';
$string['clientsecret'] = 'Secreto del cliente';
$string['useremail'] = 'Email del usuario';
$string['onedrive'] = 'onedrive';
$string['alias'] = 'OneDrive';
$string['err_field_missing'] = '{$a} no encontrado';
$string['errorwebservice'] = 'Error de webservice de OneDrive: {$a}.';
$string['err_no_access_token'] = 'No se devolvió ningún token de acceso';
$string['licenses'] = 'Licencias';
$string['updatestores'] = 'Subir vídeos OneDrive';
$string['subdomain'] = 'Dominio de OneDrive';
$string['hybridteaching'] = 'hybridteaching';
$string['notuploading'] = 'No se pudo subir la grabación de la sesión \'{$a->name}\' en el cursoid {$a->course}. ';
$string['incorrectconfig'] = 'Configuración o acceso erróneos a OneDrive para la sesión  del curso {$a->course}. Contacte con el administrador para la configuración OneDrive.';
$string['correctupload'] = 'Vídeo subido correctamente: {$a->name}';

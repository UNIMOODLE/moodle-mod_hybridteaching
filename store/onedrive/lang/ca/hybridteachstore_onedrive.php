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

$string['pluginname'] = 'OneDrive híbrid';
$string['pluginconfig'] = 'Configuració de OneDrive Store';
$string['pluginnewconfig'] = 'Nova configuració de OneDrive Store';
$string['tenantid'] = 'ID de llogater';
$string['clientid'] = 'ID de client';
$string['clientsecret'] = 'Secret del client';
$string['useremail'] = 'Email de l\'usuari';
$string['onedrive'] = 'onedrive';
$string['alias'] = 'OneDrive';
$string['err_field_missing'] = '{$a} no trobat';
$string['errorwebservice'] = 'Error de webservice de OneDrive: {$a}.';
$string['err_no_access_token'] = 'No s\'ha tornat cap token d\'accés';
$string['licenses'] = 'Llicències';
$string['updatestores'] = 'Pujar vídeos OneDrive';
$string['subdomain'] = 'Domini d\'OneDrive';
$string['hybridteaching'] = 'hybridteaching';
$string['notuploading'] = 'No s\'ha pogut pujar l\'enregistrament de la sessió \'{$a->name}\' al curs {$a->course}. ';
$string['incorrectconfig'] = 'Configuració o accés erronis a OneDrive per a la sessió del curs {$a->course}. Contacteu amb l\'administrador per a la configuració OneDrive.';
$string['correctupload'] = 'Vídeo pujat correctament: {$a->name}';

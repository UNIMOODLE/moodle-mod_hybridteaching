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

namespace hybridteachstore_pumukit\task;

class updatestores extends \core\task\scheduled_task {

    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('updatestores', 'hybridteachstore_pumukit');
    }

    public function execute() {
        global $DB, $CFG;
        // require_once($CFG->dirroot . '/mod/hybridteaching/store/youtube/classes/youtube_handler.php');

        // Sessions to get recordings, with pumukit store method.
        $sql = "SELECT ht.id AS htid,ht.course, hs.id AS hsid, hs.name,
            hs.userecordvc, hs.processedrecording, hs.storagereference, hc.subpluginconfigid
            FROM {hybridteaching_session} hs
            INNER JOIN {hybridteaching} ht ON ht.id=hs.hybridteachingid
            INNER JOIN {hybridteaching_configs} hc ON hs.storagereference=hc.id
            WHERE hs.userecordvc=1 AND hc.type='pumukit' AND hs.processedrecording=0";

        $storespumukit = $DB->get_records_sql($sql);

        // 3.  subirlas a pumukit
        // 4. guardar el registro como ya subido a pumukit.
        // 5. eliminar el archivo (ya procesado) de moodledata.

        foreach ($storespumukit as $store) {

            // Aquí hay que leer la instancia de store que corresponda.
            // De momento leemos la guardada con storeinstance.

            $configyt = $DB->get_record('hybridteachstore_pumukit_con', ['id' => $store->subpluginconfigid]);

            // Aqui conectar con la config de pumukit, aún no hecho.
            // $pumukitclient = new \pumukig_handler($configyt);

            // $videoPath es el path que se guardará de moodledata en la subactividad de vc (zoom,bbb)
            // Hay que hacer una estructura donde poder descargar los vídeos antes de subirlos,

            // Comprobar si una sesión tuviera varios archivos de grabación, se añade entonces como -1.mp4, -2.mp4,.....
            // Con zoom se guardan en mp4. Comprobar la extensión de los otros.
            // Sistemas de videoconferencia por si hubiera que hacer comprobaciones.
            $path = $CFG->dataroot.'/repository/hybridteaching/'.$store->course.'/'.$store->htid.'-'.$store->hsid;
            $videopath = $path.'-1.mp4';
            $number = 1;

            /*repeat while exists file with records for the session, change number*/
            // while (file_exists($videopath)) {
                // Aquí subir a pumukit.
                // $pumukitcode=$pumukitclient->uploadfile($store,$videopath);

                // Adaptar esto a pumukit.
                /*
                if ($pumukitcode!=null){ //si ha habido una subida correcta y tenemos algún id:
                    $pumukit=new  \stdClass();
                    $pumukit->sessionid=$store->hsid;
                    $pumukit->code=$youtubecode;
                    $pumukit->visible=true;
                    $pumukit->timecreated=time();


                    $youtubeid = $DB->insert_record('hybridteachstore_pumikit',$pumukit);
                    // Actualizar _session , con el id de _youtube.
                    $storesession=$DB->get_record('hybridteaching_session',['id'=>$store->hsid]);
                    $storesession->processedrecording=$pumukitid;    //1; //no poner 1, podemos poner el id de subactividad youtube
                    $DB->update_record('hybridteaching_session', $storesession);

                    // Delete video from origin download vc moodledata.
                    unlink ($videopath);
                */
            // }

            // Change number for the record for session.
            $number++;
            $videopath = $path.'-'.$number.'.mp4';
        }
    }
}

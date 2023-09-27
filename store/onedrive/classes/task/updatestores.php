<?php 
namespace hybridteachstore_onedrive\task;


defined('MOODLE_INTERNAL') || die();

class updatestores extends \core\task\scheduled_task {

    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('updatestores', 'hybridteachstore_onedrive');
    }

    public function execute() {
        global $DB, $CFG;

        //sessions to get recordings, with onedrive store method 
        $sql="SELECT ht.id AS htid,ht.course, c.shortname, hs.id AS hsid, hs.name, hs.userecordvc, hs.processedrecording, hs.storagereference, hc.subpluginconfigid
        FROM {hybridteaching_session} hs
        INNER JOIN {hybridteaching} ht ON ht.id=hs.hybridteachingid
        INNER JOIN {hybridteaching_configs} hc ON hs.storagereference=hc.id          
        INNER JOIN {course} c ON c.id=ht.course
        WHERE hs.userecordvc=1 AND hc.type='onedrive' AND hs.processedrecording=0";

        $storeonedrive=$DB->get_records_sql($sql);

        var_dump($storeonedrive);
        
        //3.  subirlas a onedrive
        //4. guardar el registro como ya subido a onedrive
        //5. eliminar el archivo (ya procesado) de moodledata.

        foreach ($storeonedrive as $store){
          
            //aquí hay que leer la instancia de store que corresponda. 
            //De momento leemos la guardada con storeinstance

            $configonedrive=$DB->get_record('hybridteachstore_sharepo_con', array('id'=>$store->subpluginconfigid));

       
            //$videoPath es el path que se guardará de moodledata en la subactividad de vc (zoom,bbb, meet,...)
            //hay que hacer una estructura donde poder descargar los vídeos antes de subirlos,

            //comprobar si una sesión tuviera varios archivos de grabación, se añade entonces como -1.mp4, -2.mp4,.....
            //Con zoom se guardan en mp4. Comprobar la extensión de los otros sistemas de videoconferencia por si hubiera que hacer comprobaciones
            $path = $CFG->dataroot.'/repository/hybridteaching/'.$store->course.'/'.$store->htid.'-'.$store->hsid;
            $videopath=$path.'-1.mp4';
            $number=1;

            /*repeat while exists file with records for the session, change number*/
            while(file_exists($videopath)){          

                //aqui conectar con la config de onedrive        
                $onedriveclient = new \hybridteachstore_onedrive\onedrive_handler($configonedrive);

                $onedrivecode=$onedriveclient->uploadfile($store,$videopath);
                //adaptar esto a onedrive:
                /*                    
                if ($onedrivecode!=null){ //si ha habido una subida correcta y tenemos algún id:
                    
                        $onedrive=new  \stdClass();
                        $onedrive->sessionid=$store->hsid;
                        $onedrive->code=$youtubecode; 
                        $onedrive->visible=true;
                        $onedrive->timecreated=time();
                            

                        $onedriveid = $DB->insert_record('hybridteachstore_onedrive',$onedrive);
                        //actualizar _session , con el id de _youtube.
                        $storesession=$DB->get_record('hybridteaching_session',['id'=>$store->hsid]);
                        $storesession->processedrecording=$onedriveid;    //1; //no poner 1 como true, podemos poner el id de subactividad youtube
                        $DB->update_record('hybridteaching_session', $storesession);
                    
                    //delete video from origin download vc moodledata
                    unlink ($videopath);
                */                    
            }

            //change number for the record for session
            $number++;
            $videopath=$path.'-'.$number.'.mp4';

        }
    }
}
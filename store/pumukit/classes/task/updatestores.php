<?php 
namespace hybridteachstore_pumukit\task;


defined('MOODLE_INTERNAL') || die();



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
        //require_once($CFG->dirroot . '/mod/hybridteaching/store/youtube/classes/youtube_handler.php');

        //sessions to get recordings, with pumukit store method 
        $sql="SELECT ht.id AS htid,ht.course, hs.id AS hsid, hs.name, hs.userecordvc, hs.processedrecording, hs.storagereference, hc.subpluginconfigid
        FROM {hybridteaching_session} hs
        INNER JOIN {hybridteaching} ht ON ht.id=hs.hybridteachingid
        INNER JOIN {hybridteaching_configs} hc ON hs.storagereference=hc.id          
        WHERE hs.userecordvc=1 AND hc.type='pumukit' AND hs.processedrecording=0";

        $storespumukit=$DB->get_records_sql($sql);
var_dump($storespumukit);
        
        //3.  subirlas a pumukit
        //4. guardar el registro como ya subido a pumukit
        //5. eliminar el archivo (ya procesado) de moodledata.

        foreach ($storespumukit as $store){
          
            //aquí hay que leer la instancia de store que corresponda. 
            //De momento leemos la guardada con storeinstance

            $configyt=$DB->get_record('hybridteachstore_pumukit_con', array('id'=>$store->subpluginconfigid));
            
//aqui conectar con la config de pumukit, aún no hecho            
//$pumukitclient = new \pumukig_handler($configyt);

            //$videoPath es el path que se guardará de moodledata en la subactividad de vc (zoom,bbb)
            //hay que hacer una estructura donde poder descargar los vídeos antes de subirlos,

            //comprobar si una sesión tuviera varios archivos de grabación, se añade entonces como -1.mp4, -2.mp4,.....
            //Con zoom se guardan en mp4. Comprobar la extensión de los otros sistemas de videoconferencia por si hubiera que hacer comprobaciones
            $path = $CFG->dataroot.'/repository/hybridteaching/'.$store->course.'/'.$store->htid.'-'.$store->hsid;
            $videopath=$path.'-1.mp4';
            $number=1;

            /*repeat while exists file with records for the session, change number*/
            while(file_exists($videopath)){          
                //aquí subir a pumukit. Aún no hecho                
                //$pumukitcode=$pumukitclient->uploadfile($store,$videopath);

                //adaptar esto a pumukit:
                /*                    
                if ($pumukitcode!=null){ //si ha habido una subida correcta y tenemos algún id:
                    
                        $pumukit=new  \stdClass();
                        $pumukit->sessionid=$store->hsid;
                        $pumukit->code=$youtubecode; 
                        $pumukit->visible=true;
                        $pumukit->timecreated=time();
                            

                        $youtubeid = $DB->insert_record('hybridteachstore_pumikit',$pumukit);
                        //actualizar _session , con el id de _youtube.
                        $storesession=$DB->get_record('hybridteaching_session',['id'=>$store->hsid]);
                        $storesession->processedrecording=$pumukitid;    //1; //no poner 1, podemos poner el id de subactividad youtube
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
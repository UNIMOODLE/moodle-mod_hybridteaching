<?php 
namespace hybridteachstore_sharepoint\task;


defined('MOODLE_INTERNAL') || die();

class updatestores extends \core\task\scheduled_task {

    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('updatestores', 'hybridteachstore_sharepoint');
    }

    public function execute() {
        global $DB, $CFG;

        //sessions to get recordings, with sharepoint store method 
        $sql="SELECT ht.id AS htid,ht.course, hs.id AS hsid, hs.name, hs.userecordvc, hs.processedrecording, hs.storagereference, hc.subpluginconfigid
        FROM {hybridteaching_session} hs
        INNER JOIN {hybridteaching} ht ON ht.id=hs.hybridteachingid
        INNER JOIN {hybridteaching_configs} hc ON hs.storagereference=hc.id          
        WHERE hs.userecordvc=1 AND hc.type='sharepoint' AND hs.processedrecording=0";

        $storesharepoint=$DB->get_records_sql($sql);

        var_dump($storesharepoint);
        
        //3.  subirlas a sharepoint
        //4. guardar el registro como ya subido a sharepoint
        //5. eliminar el archivo (ya procesado) de moodledata.

        foreach ($storesharepoint as $store){
          
            //aquí hay que leer la instancia de store que corresponda. 
            //De momento leemos la guardada con storeinstance

            $configsp=$DB->get_record('hybridteachstore_sharepo_con', array('id'=>$store->subpluginconfigid));
        
//aqui conectar con la config de sharepoint, aún no hecho            
$sharepointclient = new \hybridteachstore_sharepoint\sharepoint_handler($configsp);
$sharepointclient->upload_recordings();

            //$videoPath es el path que se guardará de moodledata en la subactividad de vc (zoom,bbb, meet,...)
            //hay que hacer una estructura donde poder descargar los vídeos antes de subirlos,

            //comprobar si una sesión tuviera varios archivos de grabación, se añade entonces como -1.mp4, -2.mp4,.....
            //Con zoom se guardan en mp4. Comprobar la extensión de los otros sistemas de videoconferencia por si hubiera que hacer comprobaciones
            $path = $CFG->dataroot.'/repository/hybridteaching/'.$store->course.'/'.$store->htid.'-'.$store->hsid;
            $videopath=$path.'-1.mp4';
            $number=1;

            /*repeat while exists file with records for the session, change number*/
            while(file_exists($videopath)){          
                //aquí subir a sharepoint. Aún no hecho                
                //$sharepointcode=$sharepointclient->uploadfile($store,$videopath);

                //adaptar esto a sharepoint:
                /*                    
                if ($sharepointcode!=null){ //si ha habido una subida correcta y tenemos algún id:
                    
                        $sharepoint=new  \stdClass();
                        $sharepoint->sessionid=$store->hsid;
                        $sharepoint->code=$youtubecode; 
                        $sharepoint->visible=true;
                        $sharepoint->timecreated=time();
                            

                        $sharepointid = $DB->insert_record('hybridteachstore_sharepoint',$sharepoint);
                        //actualizar _session , con el id de _youtube.
                        $storesession=$DB->get_record('hybridteaching_session',['id'=>$store->hsid]);
                        $storesession->processedrecording=$sharepointid;    //1; //no poner 1, podemos poner el id de subactividad youtube
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
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

        //process:
        //1.  upload to  onedrive
        //2. save record as upload to onedrive 
        //3. delete file from moodledata

        foreach ($storeonedrive as $store){
          
            //aquí hay que leer la instancia de store que corresponda. 
            //De momento leemos la guardada con storeinstance

            $configonedrive=$DB->get_record('hybridteachstore_onedrive_co', array('id'=>$store->subpluginconfigid));
            
            //$videoPath es el path que se guardará de moodledata en la subactividad de vc (zoom,bbb, meet,...)
            //hay que hacer una estructura donde poder descargar los vídeos antes de subirlos,

            //comprobar si una sesión tuviera varios archivos de grabación, se añade entonces como -1.mp4, -2.mp4,.....
            //Con zoom se guardan en mp4. Comprobar la extensión de los otros sistemas de videoconferencia por si hubiera que hacer comprobaciones
            $path = $CFG->dataroot.'/repository/hybridteaching/'.$store->course.'/';

            $files=glob($path.'*');           

            //for each file, upload:
            foreach ($files as $videopath){
                //onedrive connect
                $onedriveclient = new \hybridteachstore_onedrive\onedrive_handler($configonedrive);

                //upload file
                $response=$onedriveclient->uploadfile($store,$videopath);
                
                //if there are correct response
                if ($response!=null){ 
                    $onedrive=new  \stdClass();
                    $onedrive->sessionid=$store->hsid;
                    $onedrive->weburl = $store->shortname.'/'.$store->name.'.mp4';
                    $onedrive->downloadurl=$response['downloadurl']; 
                    $onedrive->visible=true;
                    $onedrive->timecreated=time();
                        
                    $onedriveid = $DB->insert_record('hybridteachstore_onedrive',$onedrive);
                    //update _session with onedrive id 
                    $storesession=$DB->get_record('hybridteaching_session',['id'=>$store->hsid]);
                    $storesession->processedrecording=$onedriveid;    //1; //no poner 1 como true, podemos poner el id de subactividad onedrive
                    $DB->update_record('hybridteaching_session', $storesession);
                    
                    //delete video from origin download vc moodledata
                    unlink ($videopath);
                }  
            }
        }
    }
}
<?php 
namespace hybridteachstore_youtube\task;


defined('MOODLE_INTERNAL') || die();



class updatestores extends \core\task\scheduled_task {

    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('updatestores', 'hybridteachstore_youtube');
    }

    public function execute() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/mod/hybridteaching/store/youtube/classes/youtube_handler.php');
        //sessions to get
        //1.  obtener las sesiones de las que descargar grabaciones, que tengan el método de almacenamiento de youtube
        $sql=" SELECT ht.id AS htid,ht.course, hs.id AS hsid, hs.name, hs.typestorage, hs.userecordvc, hs.processedrecording, hs.storagereference
        FROM {hybridteaching_session} hs
        INNER JOIN {hybridteaching} ht ON ht.id=hs.hybridteachingid
        WHERE hs.userecordvc=1 AND hs.typestorage='youtube' AND hs.processedrecording=0";
        $storesyoutube=$DB->get_records_sql($sql);

        //2.  descargarlas utilizando el método adecuado de cada subplugin, descargarlas en moodledata
        //este paso 2 igual habría que hacerlo en otra task del subplugin de vc, que descargue automa´ticamentene moodledata, 
        //y luego esta task que suba de moodledata a youtube.
        //3.  subirlas a youtube
        //4. guardar el registro como ya subido a youtube
        //5. eliminar el archivo (ya procesado) de moodledata.

        foreach ($storesyoutube as $store){
          
            //aquí hay que leer la instancia de store que corresponda. 
            //De momento leemos la guardada con storeinstance
            $configyt=$DB->get_record('hybridteachstore_youtube_ins',array('id'=>$store->storagereference));

            $youtubeclient = new \youtube_handler($configyt);

            //$videoPath es el path que se guardará de moodledata en la subactividad de vc (zoom,bbb)
            //hay que hacer una estructura donde poder descargar los vídeos antes de subirlos,
            //no se pueden traspasar directamente porque no se permite por los vc (ojalá se pudiera...)

            //comprobar si una sesión tuviera varios archivos de grabación, se añade entonces como -1.mp4, -2.mp4,.....
            //Con zoom se guardan en mp4. Comprobar la extensión de los otros sistemas de videoconferencia por si hubiera que hacer comprobaciones
            $path = $CFG->dataroot.'/repository/hybridteaching/'.$store->course.'/'.$store->htid.'-'.$store->hsid;
            $videopath=$path.'-1.mp4';
            $number=1;

            /*repeat while exists file with records for the session, change number*/
            while(file_exists($videopath)){          
                $youtubecode=$youtubeclient->uploadfile($store,$videopath);

                if ($youtubecode!=null){ //si ha habido una subida correcta y tenemos el youtube code:
                    //guardar id y registro de youtube, tipo "jj_6Ic7N8Dg"
                    $youtube=new  \stdClass();
                    $youtube->sessionid=$store->hsid;
                    $youtube->code=$youtubecode; 
                    $youtube->visible=true;
                    $youtube->timecreated=time();
                        

                    $youtubeid = $DB->insert_record('hybridteachstore_youtube',$youtube);
                    //actualizar _session , con el id de _youtube.
                    $storesession=$DB->get_record('hybridteaching_session',['id'=>$store->hsid]);
                    $storesession->processedrecording=$youtubeid;    //1; //no poner 1, podemos poner el id de subactividad youtube
                    $DB->update_record('hybridteaching_session', $storesession);

                    /*delete video from origin download vc moodledata*/
                    unlink ($videopath);
                }

                //change number for the record for session
                $number++;
                $videopath=$path.'-'.$number.'.mp4';
            };

        }
    }
}
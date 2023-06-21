<?php 
namespace hybridteachvc_zoom\task;


defined('MOODLE_INTERNAL') || die();



class downloadrecords extends \core\task\scheduled_task {

    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('downloadrecords', 'hybridteachvc_zoom');
    }

    public function execute() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/mod/hybridteaching/vc/zoom/classes/webservice.php');
        require_once($CFG->dirroot . '/mod/hybridteaching/vc/zoom/classes/sessions.php');
        
        $sessioninstance=new \sessions();

        $sql='SELECT hs.id AS hsid, ht.id AS htid, ht.course, ht.instance, zoom.meetingid
            FROM {hybridteaching_session} hs
            INNER JOIN {hybridteachvc_zoom} zoom ON zoom.htsession=hs.id
            INNER JOIN {hybridteaching} ht ON ht.id=hs.hybridteachingid
            WHERE hs.typevc="zoom" AND hs.userecordvc=1 AND hs.processedrecording=-1';
        $download=$DB->get_records_sql($sql);
        
        foreach ($download as $session){
            $folder=$CFG->dataroot."/repository/hybridteaching/".$session->course;
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            $folder_file=$folder."/".$session->htid."-".$session->hsid;

            $zoominstance = $sessioninstance->load_zoom_instance($session->instance);
            $service = new \mod_hybrid_webservice($zoominstance); 
            $response = $service->get_meeting_recordings($session->meetingid);
            //$responsesettings=$service->get_meeting_recordingsettings($session->meetingid);
            //var_dump($response->download_access_token);
            /*echo "<br>settings:";
            var_dump($responsesettings);*/
            if ($response != false) {
                $count=1;
                foreach ($response->recording_files as $file){
                    if (($file->file_type=='MP4')){
                        $folder_file=$folder_file."-".$count.'.'.$file->file_type;
                        /*
                        //tests:
                        echo "tamaño:".$file->file_size;
                        echo "<br>destino:".$folder_file;
                        echo "<br>url:".$file->download_url;
                        echo "<br>";*/
                        
                        //get file size
                        $file_size = @filesize($folder_file);
                        
                        if ((!file_exists($folder_file)) || (file_exists($folder_file) && ($file->file_size!=$file_size))){
                            //$source = @file_get_contents($file->download_url.'?access_token='.$service->get_access_token());   //.'?access_token='.$service->get_access_token()
                                            //?download_token='.$service->get_access_token()
                          $response= $service->_make_call_download($file->download_url);
         
                          $file = fopen($folder_file, "w+");
                          fputs($file, $response);
                          fclose($file);
                  
                          //save processedrecording in hybridteaching_session=0: ready to upload to store
                          $session = $DB->get_record('hybridteaching_session', array('id' => $session->hsid));
                          $session->processedrecording=0;
                          $DB->update_record('hybridteaching_session',$session);


                        }                       
                    }
                }
            }
        }
    }
}
<?php 
namespace hybridteachvc_teams\task;


use hybridteachvc_teams\sessions;
use hybridteachvc_teams\teams_handler;


defined('MOODLE_INTERNAL') || die();

class downloadrecords extends \core\task\scheduled_task {

    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('downloadrecordsteams', 'hybridteachvc_teams');
    }

    public function execute() {
        
        global $DB, $CFG;
        
        $sessionconfig=new sessions();

        $sql='SELECT hs.id AS hsid, ht.id AS htid, ht.course, ht.config, teams.meetingid, teams.organizer
            FROM {hybridteaching_session} hs
            INNER JOIN {hybridteachvc_teams} teams ON teams.htsession=hs.id
            INNER JOIN {hybridteaching} ht ON ht.id=hs.hybridteachingid
            WHERE hs.typevc="teams" AND hs.userecordvc=1 AND hs.processedrecording=-1';
        $download=$DB->get_records_sql($sql);

        foreach ($download as $session){
            $folder=$CFG->dataroot."/repository/hybridteaching/".$session->course;
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            //$folder_file=$folder."/".$session->htid."-".$session->hsid.'.mp4';

            $teamsconfig = $sessionconfig->load_teams_config($session->config);

            $teamshandler=new teams_handler($teamsconfig);
            
            //llamada para descargar grabaciones
            $response = $teamshandler->get_meeting_recordings($session->meetingid, $session->organizer);
            



          
            /*if ($response['returncode'] == 'SUCCESS') {
                if ($response['recordingid']!=''){
                    $bbb = $DB->get_record('hybridteachvc_bbb', array('meetingid' => $session->meetingid) );
                    //$bbb->recordingurl = $response['recordingurl'];
                    $bbb->recordingid = $response['recordingid'];
                    $DB->update_record('hybridteachvc_bbb',$bbb);
                    //save processedrecording in hybridteaching_session=0: ready to upload to store
                    $session = $DB->get_record('hybridteaching_session', array('id' => $session->hsid));
                    $session->processedrecording=0;
                    $DB->update_record('hybridteaching_session',$session);
                }
                

            }*/
        }
    }
}
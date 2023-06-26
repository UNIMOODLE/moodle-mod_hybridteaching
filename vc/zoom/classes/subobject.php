<?php


class subobject {

    protected $zoom;

    public function __construct($sessionid) {
        global $DB;
        //aqui leer de la bbdd del subplugin, para leer el reg de zoom.


        $this->zoom = $DB->get_record('hybridteachvc_zoom',['htsession'=>$sessionid]);

        /*$sql="SELECT * 
                FROM {hybridteaching_session} AS hs
                INNER JOIN {hybridteachvc_zoom} AS zoom ON zoom.htsession=hs.id
                WHERE hs.hybridteachingid = :id AND (hs.starttime + hs.duration >= UNIX_TIMESTAMP() OR hs.starttime IS NULL) 
                ORDER BY hs.starttime LIMIT 1";
        $this->zoom = $DB->get_record_sql($sql, array('id' => $hybridteachingid));

        //if there not are next session, get the last session
        if (!$this->zoom){
            $sql="SELECT * 
                FROM {hybridteaching_session} AS hs
                INNER JOIN {hybridteachvc_zoom} AS zoom ON zoom.htsession=hs.id
                WHERE hs.hybridteachingid = :id
                ORDER BY hs.starttime DESC LIMIT 1";
            $this->zoom = $DB->get_record_sql($sql, array('id' => $hybridteachingid));
        }*/

    }

    public function get_sessions() {
        return $this->zoom;
    }

    function get_zone_access() {

        //comprobar si el rol es para iniciar reunión o para entrar a reunión
        //y mandamos la url de acceso (o bien starturl o bien joinurl)
        //starturl o join url, según sea hospedador o participante

        //aqui antes también comprobar que el plugin de tipo $type existe, está instalado y activo
        
        /*
        if ($vc){
            //si hospedador:
            $nexturl = new moodle_url($vc->starturl);
            //si participante
            $nexturl = new moodle_url($vc->joinurl);
        }
        */


        if ($this->zoom){
            $array=[
                'id'=>$this->zoom->id,
                'ishost'=>true,
                'isaccess'=>true,
                'url'=>base64_encode($this->zoom->starturl),
            ];
            return $array;
        }
        else {
            return null;
        }
    }
}


   


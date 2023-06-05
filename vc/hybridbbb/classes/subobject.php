<?php


class subobject{

   protected $bbb;

    public function __construct($id){
        //aqui leer de la bbdd del subplugin, para leer el reg de zoom.

        global $DB;

        $this->bbb=$DB->get_records('hybridteaching_bbb',array('hybridteachingid'=> $id));
        //$this->zoom->type="zoom";

    }

    public function get_sessions(){
        return $this->bbb;
    }

    function get_zone_access($hybridteachingid){
        global $DB;
        //aqu calcular los datos necesarios de la zona de acceso
        //comprobar si el rol es para iniciar reunión o para entrar a reunión
        //y mandamos la url de acceso (o bien starturl o bien joinurl)
        //starturl o join url, según sea hospedador o participante

        //obtener el registro más cercano en fecha
        //si hubiera fecha, si es recurrente, el zoom  que haya, 
        //por ejemplo esto, pero hay que pulirlo, puede ser que sea recurrente, o no haya starttime,....

        //aqui antes también comprobar que el plugin de tipo $type existe, está instalado y activo
        //comprobar también que el usuario tiene permisos para acceder, que pertenezca al grupo, que puede acceder, bla bla,....

        //solo obtener un registro
        $vc=$DB->get_record_sql("SELECT * FROM {hybridteaching_bbb} WHERE hybridteachingid=:id AND (starttime+duration>=UNIX_TIMESTAMP() OR starttime IS NULL) ORDER BY starttime DESC LIMIT 1", array('id'=>$hybridteachingid));

        /*
        if ($vc){
            //si hospedador:
            $nexturl = new moodle_url($vc->starturl);
            //si participante
            $nexturl = new moodle_url($vc->joinurl);
        }
        */

        if ($vc){
            //comprobar el estado de la reunión: 
            $estado="en progreso";
            $starturl="";
            if ($vc->starturl){
                $starturl=base64_encode($vc->starturl);
            }

            $array=[
                'id'=>$vc->id,
                'ishospedador'=>true,
                'isaccess'=>true,
                'url'=>$starturl,
                'starttime'=>$vc->starttime,
                'duration'=>$vc->duration,
                'estado'=> $estado
            ];
            return $array;
        }
    }
}

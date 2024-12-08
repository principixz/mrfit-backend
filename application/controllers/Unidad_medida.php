<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";

class Unidad_medida extends BaseController {

 	public function __construct(){
        parent::__construct();
  	}

	public function index(){
		$data = array();
		$data["titulo_descripcion"]="Lista de Unidad de Medida";
		$data["tabla"]=$this->Mantenimiento_m->consulta("SELECT * FROM unidad_medida where unidad_medida_estado=1");
		$this->vista("Unidad_medida/index",$data);
		}

	public function ver_datos(){
		$dat=$this->Mantenimiento_m->consulta("select * from unidad_medida where id_tipo_unidad_medida=".$_POST["id"]);
		echo "<option value=''>Seleccionar unidad</option>";
		foreach ($dat as $key => $value) {
			echo "<option value='".$value->id_unidad_medida."'>".$value->unidad_medida_descripcion."</option>";
		}
	}
	public function data(){
		$dato=$this->Mantenimiento_m->consulta3("select * from relacion_medida where id_unidad_medida1=".$_POST["unidad_medida"]." and id_unidad_medida2=".$_POST["unidad_medida1"]);
		if(count($dato)>0){
		echo  $dato[0]["relacion_medida_relacion"];
		}else{
		echo "";
		}
	}
	
	public function guardar(){
	    $this->db->query("delete from relacion_medida where id_unidad_medida1=".$_POST["unidad_medida"]." and id_unidad_medida2=".$_POST["unidad_medida1"]);
	    $data=array(
	     "id_unidad_medida1"=>$_POST["unidad_medida"],
	     "id_unidad_medida2"=>$_POST["unidad_medida1"],
	     "relacion_medida_relacion"=>$_POST["relacion"]
	    );
	    $this->Mantenimiento_m->insertar("relacion_medida",$data);
	}

 	public function guardar_unidad(){	       
	    $data=array(
	    	"unidad_medida_descripcion"=>$_POST["descripcion_unidad"],
	        "id_tipo_unidad_medida"=>$_POST["tipo_medida"],
	        "valor_unidad_medida"=>$_POST["numero"]
	    );
	    $this->Mantenimiento_m->insertar("unidad_medida",$data);
	}
	

}
<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once "BaseController.php";



class Ubicacionmesa extends BaseController {
 	public function __construct(){
        parent::__construct();  
    }



	public function index(){
	    $data=array();
	    $data["titulo_descripcion"]="Lista de Ubicación de Mesa";
	    $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from lugar_mesa where lugarmesa_estado =1 and id_sede=".$_COOKIE["id_sede"]);
		$this->vista("UbicacionMesa/index",$data);
	}



	public function nuevo(){
     	$data=array();
     	$data["titulo_descripcion"]="Nuevo Ubicación de Mesa";
	 	$this->vista("UbicacionMesa/nuevo",$data);
	}



	public function guardar(){
		$data=array(
			"lugarmesa_descripcion"=>$this->input->post("descripcion"),
			"id_sede" => $_COOKIE["id_sede"]
		);
		if($this->input->post("id")==""){
         	$estado=$this->db->insert('lugar_mesa', $data);
		}
		else{
            $this->db->where('lugarmesa_id',$this->input->post("id"));
	        $estado=$this->db->update('lugar_mesa', $data);
		}
     	header('Location: '.base_url()."Ubicacionmesa");
	}



	public function editar($id){
		$data=array();
	    $data["titulo_descripcion"]="Actualizar Ubicación de Mesa";
	    $data["id"]=$id;
		$this->vista("UbicacionMesa/nuevo",$data);
	}



	public function traer_uno(){
		$dat=$this->Mantenimiento_m->consulta3("select * from lugar_mesa where lugarmesa_estado =1 and lugarmesa_id=".$this->input->post("id"));
		echo json_encode($dat);
	}



	public function eliminar($id){
    	$this->Mantenimiento_m->consulta1("update lugar_mesa set lugarmesa_estado=0 where lugarmesa_id=".$id);
	} 

}


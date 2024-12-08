<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";

class Tipo_moneda extends BaseController {

 	public function __construct(){
        parent::__construct();  
    }

	public function index(){
	    $data=array();
	    $data["titulo_descripcion"]="Lista de Tipo de Monedas";
	    $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from monedas where moneda_estado =1");
		$this->vista("TipoMoneda/index",$data);
	}

	public function nuevo(){
     	$data=array();
     	$data["titulo_descripcion"]="Nuevo Tipo Moneda";
	 	$this->vista("TipoMoneda/nuevo",$data);
	}

	public function guardar(){
		$data=array("moneda_descripcion"=>$this->input->post("descripcion"));
		if($this->input->post("id")==""){
         	$estado=$this->db->insert('monedas', $data);
		}
		else{
            $this->db->where('moneda_id',$this->input->post("id"));
	        $estado=$this->db->update('monedas', $data);
		}
     	header('Location: '.base_url()."Tipo_moneda");
	}

	public function editar($id){
		$data=array();
	    $data["titulo_descripcion"]="Actualizar Tipo de Moneda";
	    $data["id"]=$id;
		$this->vista("TipoMoneda/nuevo",$data);
	}

	public function traer_uno(){
		$dat=$this->Mantenimiento_m->consulta3("select * from monedas where moneda_id=".$this->input->post("id"));
		echo json_encode($dat);
	}

	public function eliminar($id){
    	$this->Mantenimiento_m->consulta1("update monedas set moneda_estado=0 where moneda_id=".$id);
	} 
}

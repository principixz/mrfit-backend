<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";

class Mesas extends BaseController {

 public function __construct() {
        parent::__construct();      	
    }

	public function index(){
     	$data=array();
      	$data["titulo_descripcion"]="Lista de Mesas";
      	$data["tabla"]=$this->Mantenimiento_m->consulta3("SELECT * FROM mesa INNER JOIN lugar_mesa ON mesa.mesa_id_lugar = lugar_mesa.lugarmesa_id WHERE lugar_mesa.id_sede  = ".$_COOKIE["id_sede"]." and mesa.mesa_estado = 1 ");
	  	$this->vista("Mesas/index",$data);
	}


	public function nuevo(){
     	$data=array();
      	$data["titulo_descripcion"]="Creación de Mesas";

       	$data["select_lugar"]=$this->Mantenimiento_m->consulta3("SELECT * FROM lugar_mesa WHERE id_sede = ".$_COOKIE["id_sede"]);
	  	$this->vista("Mesas/nuevo",$data);
	}

	public function traer_mesa(){
		$data = $this->Mantenimiento_m->consulta2("SELECT * FROM mesa WHERE mesa_id =". $_POST["id"]);
		echo json_encode($data);
	}

	public function traersiguiente(){
		$valor = $_POST["valor"];
		$cantidad = $_POST["cantidad"];
		for ($i=0; $i < $cantidad ; $i++) { 
			$dat[$i] = $valor++;
		}
		echo json_encode($dat);
	}

	public function editar($id){
		$data=array();
      	$data["titulo_descripcion"]="Actualizar Perfil";
      	$data["select_lugar"]=$this->Mantenimiento_m->consulta3("SELECT * FROM lugar_mesa WHERE id_sede = ".$_COOKIE["id_sede"]);
      	//$data["tabla"]=$this->Mantenimiento_m->consulta3("select * from perfiles where estado=1");
      	$data["id"]=$id;
	  	$this->vista("Mesas/editar",$data);
	}
	public function procesamiento(){
		$post=file_get_contents("php://input");  
		$res=json_decode($post, true);
		$lista_item=array();
		parse_str($res["documento"], $lista_item); 
		foreach ($lista_item["nombre_mesa"] as $key => $value) {
			$data = array(
				"mesa_numero" => $value,
				"mesa_estado" => 1,
				"mesa_id_lugar" => $lista_item["ubicacionmesa"]

			); 
			$estado=$this->db->insert('mesa', $data);
		}
		echo 1;
	}
 
 	public function Editar_mesa(){
		$post=file_get_contents("php://input");  
		$res=json_decode($post, true);
		$lista_item=array();
		parse_str($res["documento"], $lista_item);  
			$data = array(
				"mesa_numero" => $lista_item["nombre_mesa"],
				"mesa_estado" => 1,
				"mesa_id_lugar" => $lista_item["id_ubicacion"]

			); 
		$this->db->where('mesa_id',$lista_item["id"]);
		$estado=$this->db->update('mesa', $data); 
		echo 1;
	}

	public function eliminar($id){
      	$this->Mantenimiento_m->consulta1("update mesa set mesa_estado=0 where mesa_id=".$id);
	} 
	
}

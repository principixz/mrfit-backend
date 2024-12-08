<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once "BaseController.php";

//require_once("src/autoload1.php");

class Clientes extends BaseController {



 	public function __construct(){

        parent::__construct();

    }

	public function index(){  

      	$data=array();

      	$data["titulo_descripcion"]="Lista de Clientes";

      	$data["tabla"]=$this->Mantenimiento_m->consulta3("select * from clientes where estado='1' ");

	  	$this->vista("Clientes/index",$data);

	}

	public function nuevo(){

	  $data=array();

      $data["titulo_descripcion"]="Nuevo Cliente";

	  $this->vista("Clientes/nuevo",$data);

	}

	public function guardar(){

		$data=array(

			"cliente_nombres"=>$this->input->post("nombres"),

			"cliente_direccion"=>$this->input->post("direccion"),

			"cliente_dni"=>$this->input->post("dni"),

			"cliente_ubicacion"=>$this->input->post("ciudad"),

			"cliente_telefono"=>$this->input->post("telefono"),

			"cliente_email"=>$this->input->post("correo"),

			"empresa_ruc"=>$_COOKIE["ruc_empresa"]

		);

		if($this->input->post("id")==""){

        	$estado=$this->db->insert('clientes', $data);

		}else{

        	$this->db->where('cliente_id',$this->input->post("id"));

	    	$estado=$this->db->update('clientes', $data);

		}

    	header('Location: '.base_url()."Clientes");

	}


public function guardar_ajax(){
		$data=array(
			"cliente_nombres"=>$this->input->post("nombres"),
			"cliente_direccion"=>$this->input->post("direccion"),
			"cliente_dni"=>$this->input->post("dni"),
			"cliente_ubicacion"=>$this->input->post("ciudad"),
			"cliente_telefono"=>$this->input->post("telefono"),
			"empresa_ruc"=>$_COOKIE["ruc_empresa"]
		);
		if($this->input->post("id")==""){
         $estado=$this->db->insert('clientes', $data);
		}
		else{
            $this->db->where('cliente_id',$this->input->post("id"));
	        $estado=$this->db->update('clientes', $data);
		}
		$datos=array("id"=>1);
 		echo json_encode($datos);
}


	public function editar($id){

	  	$data=array();

      	$data["titulo_descripcion"]="Editar Clientes";

      	$data["id"]=$id;

	  	$this->vista("Clientes/nuevo",$data);

	}



	public function traer_uno(){

  		$dat=$this->Mantenimiento_m->consulta3("select * from clientes where cliente_id=".$this->input->post("id"));

		echo json_encode($dat);

  	}

   

	public function ver_dni(){  

	  	$cliente = new \Reniec\Reniec();

	  	$data = $cliente->search($_POST["id"]);

	  	echo json_encode($data);

	}

 

	public function eliminar($id)

	{

      $this->Mantenimiento_m->consulta1("update clientes set estado=0 where cliente_id=".$id);

	} 

	



 

 

}

?>
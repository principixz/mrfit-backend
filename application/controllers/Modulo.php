<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";

class modulo extends BaseController {

 public function __construct() {
        parent::__construct();


    }

	public function index()
	{
	 // echo  $this->input->post("usuario")." ". $this->input->post("clave");

     $data=array();
      $data["titulo_descripcion"]="Lista de modulos";
      $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from modulos where estado=1 and modulo_id!=1");
	  $this->vista("Modulo/index",$data);
	}
	public function nuevo()
	{

     $data=array();
      $data["titulo_descripcion"]="Nuevo Modulo";
       $data["select_modulo_padre"]=$this->Mantenimiento_m->consulta3("select * from modulos where modulo_padre=1");
      //$data["tabla"]=$this->Mantenimiento_m->consulta3("select * from perfiles where estado=1");
	  $this->vista("Modulo/nuevo",$data);
	}
	public function guardar()
	{
		$data=array(
          "modulo_nombre"=>$this->input->post("descripcion"),
          "modulo_icono"=>$this->input->post("icono"),
          "modulo_url"=>$this->input->post("url"),
          "modulo_padre"=>$this->input->post("modulo")
		);
		//print_r($data);exit();
		if($this->input->post("id")=="")
		{
         $estado=$this->db->insert('modulos', $data);
		}
		else
		{
                   $this->db->where('modulo_id',$this->input->post("id"));
	            	$estado=$this->db->update('modulos', $data);
		}
     header('Location: '.base_url()."modulo");

	}
	public function editar($id)
	{
		 $data=array();
      $data["titulo_descripcion"]="Actualizar Modulo";
      //$data["tabla"]=$this->Mantenimiento_m->consulta3("select * from perfiles where estado=1");
      $data["id"]=$id;
       $data["select_modulo_padre"]=$this->Mantenimiento_m->consulta3("select * from modulos where modulo_padre=1");
	  $this->vista("Modulo/nuevo",$data);


	}
	public function traer_uno()
	{
		$dat=$this->Mantenimiento_m->consulta3("select * from modulos where modulo_id=".$this->input->post("id"));
		echo json_encode($dat);
	}

	public function eliminar($id){
      	$this->Mantenimiento_m->consulta1("update modulos set estado=0 where modulo_id=".$id);
	} 
	
}

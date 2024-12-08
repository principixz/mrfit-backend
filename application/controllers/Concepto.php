<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";

class concepto extends BaseController {

 public function __construct() {
        parent::__construct();
       	
      
    }
    public function index()
    {
    	    $data=array();
    	   $data["lista_concepto"]=$this->Mantenimiento_m->consulta3("select * from concepto,tipo_movimiento where concepto.id_tipo_movimiento=tipo_movimiento.id_tipo_movimiento and con_estado_visible=1 and con_estado=1 and (id_sede IS NULL  or id_sede=".$_COOKIE["id_sede"].")");
    	   $data["titulo_descripcion"]="Lista de Conceptos";
    	$this->vista("Concepto/index",$data);
    }
    public function nuevo()
    {
    	    	   $data["lista_tipoconcepto"]=$this->Mantenimiento_m->consulta3("select * from tipo_movimiento");
    	   $data["titulo_descripcion"]="Nuevo de Concepto";
    	$this->vista("Concepto/nuevo",$data);

    }
    public function guardar()
	{

		$data=array(
			"id_tipo_movimiento"=>$this->input->post("id_tipo_concepto"),
			"con_descripcion"=>$this->input->post("descripcion"),
			"id_sede"=>$_COOKIE["id_sede"]
			


		);
		if($this->input->post("id")=="")
		{
         $estado=$this->db->insert('concepto', $data);
		}
		else
		{
                   $this->db->where('con_id',$this->input->post("id"));
	            	$estado=$this->db->update('concepto', $data);
		}
     header('Location: '.base_url()."concepto");
	}

	public function editar($id)
	{
		  $data=array();
      $data["titulo_descripcion"]="Editar Concepto";
      $data["lista_tipoconcepto"]=$this->Mantenimiento_m->consulta3("select * from tipo_movimiento");
      $data["id"]=$id;
     // $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and empresa_ruc=".$_SESSION["ruc_empresa"]);
	  $this->vista("Concepto/nuevo",$data);

	}
	  public function traer_uno()
  {
  	$dat=$this->Mantenimiento_m->consulta3("select * from concepto where con_id=".$this->input->post("id"));
		echo json_encode($dat);
  }
  	public function eliminar($id)
	{
      $this->Mantenimiento_m->consulta1("update concepto set con_estado=0 where con_id=".$id);
	} 
	

}
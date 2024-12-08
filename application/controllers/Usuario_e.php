<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";
class Usuario_e extends BaseController {
  public function __construct() {
 	  parent::__construct();
  }
  public function index(){
    $data=array();
    $data["titulo_descripcion"]="Configuración de Usuario para Eliminar";
    $this->vista("Usuario_e/index",$data);
  }

  public  function traer_uno(){
  	$dat["usuario"]=$this->Mantenimiento_m->consulta3("SELECT * FROM usuario_eliminar LIMIT 1");
    echo json_encode($dat);
  }

 public function guardar(){ 
    $dato_usuario=array(
        "usuario_eli"=>$this->input->post("usuario"),
        "clave_eli"=>$this->input->post("clave"),
    );
    $estado=$this->db->update('usuario_eliminar', $dato_usuario);  
     header('Location: '.base_url()."Usuario_e");
  }

}
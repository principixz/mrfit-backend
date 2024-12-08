
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";

class Plato_desactivar extends BaseController {

 public function __construct() {
        parent::__construct();
       	
      
    }

	public function index()
	{
	 // echo  $this->input->post("usuario")." ". $this->input->post("clave");
  
     $data=array();
      $data["titulo_descripcion"]="Desactivar Productos";
         $sql="SELECT * 
          from producto
          where producto_estado=1
          and producto_id_tipoproducto=3
          and id_sede=1";

          $data["tabla"]=$this->db->query($sql)->result_array();
      /*$data["tabla"]=$this->Mantenimiento_m->consulta3("SELECT *
FROM
producto
INNER JOIN marca ON producto.marca_id = marca.marca_id
INNER JOIN clase ON producto.clase_id = clase.clase_id
 where producto_estado=1 and producto.producto_id_tipoproducto!=3 and id_sede=".$_COOKIE["id_sede"]);*/
	  $this->vista("Plato_desactivar/index",$data);
	}

  public function cargar_platos()
  {

    $sql="SELECT * 
          from producto
          where producto_estado=1
          and producto_id_tipoproducto=3
          and id_sede=1";

          $dato=$this->db->query($sql)->result_array();

          echo json_encode($dato);exit();
  }

  public function prender_producto()
  {
    $data=array(
      "producto_encendido"=>$_POST["estado"]
    );

       $this->db->where('producto_id',$this->input->post("id"));
                $estado=$this->db->update('producto', $data);
  }

}
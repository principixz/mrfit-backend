<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";

class Produccion extends BaseController {

 public function __construct() {
        parent::__construct();
       	
      
    }

	public function index()
	{
	 // echo  $this->input->post("usuario")." ". $this->input->post("clave");
  
     $data=array();
      $data["titulo_descripcion"]="Lista de Produccion";
      $data["tabla"]=$this->Mantenimiento_m->consulta3("SELECT *
FROM
empleados
INNER JOIN produccion ON produccion.empleado_id = empleados.empleado_id
where produccion.produccion_estado=1 and produccion.id_sede=".$_COOKIE["id_sede"]);

	  $this->vista("Produccion/index",$data);
	}

  public function nuevo()
  {
         $data=array();
      $data["titulo_descripcion"]="Nueva Produccion";
      //$data["tabla"]=$this->Mantenimiento_m->consulta3("select * from perfiles where estado=1");
      $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from almacenes where almacen_estado=1 and id_sede=".$_COOKIE["id_sede"]);
    $this->vista("Produccion/nuevo",$data);
  }

  public function buscar_producto()
  {
  $buscar="";
  if(isset($_GET["q"])){
    $buscar=$_GET["q"];
  }
    $json=array();
    $data=$this->Mantenimiento_m->consulta3("select * from producto where id_sede=".$_COOKIE["id_sede"]." and (producto_descripcion like '%".$buscar."%' or producto_codigobarra like '%".$buscar."%' ) and producto_estado=1 and producto.producto_id_tipoproducto=3  limit 50");
    foreach ($data as $key => $value) {
      $json["results"][$key]["id"]=$value["producto_id"];
      $json["results"][$key]["text"]=$value["producto_descripcion"];
      //$json["results"][$key]["full_name"]=$value["producto_descripcion"];

    }
    echo json_encode($json);

  }

  public function mostrar_stock()
   {
     
      $json=array();
      $producto_id=$_POST["producto_id"];
      $almacen_id=$_POST["almacen_id"];
      if($producto_id!=""){
      $unidad_medida=$this->Mantenimiento_m->consulta3("SELECT *
FROM
producto
INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida
where  producto_id=".$producto_id);

      $json["unidad_medida"]=$unidad_medida[0]["unidad_medida_descripcion"];

      $almacen=$this->Mantenimiento_m->consulta3("select * from detalle_producto_almacen where detalle_producto=".$producto_id." and detalle_almacen=".$almacen_id);
      $json["stock"]=$almacen[0]["detalle_stock"];
      echo json_encode($json);exit();
      }


   }

   public function mostrar_unidad_medida()
{
  if($_POST["id"]!=""){

          $data=$this->Mantenimiento_m->consulta3("SELECT *
        FROM
        detalle_unidad_producto
        INNER JOIN unidad_medida ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida
        where detalle_unidad_producto_estado=1
        and producto_id=".$_POST["id"]);
        }else
        {

            $data[0]["detalle_unidad_producto_factor"]=1;
            $data[0]["unidad_medida_descripcion"]="Seleccione un producto ";
            $data[0]["detalle_unidad_producto_id"]="";


        }
  echo json_encode($data);exit();
}



public function procesar_produccion()
{

 $post=file_get_contents("php://input");  
  $res=json_decode($post, true);
  $producto=array();
  $plato=array();
  $observacion=array();

  $id_caja=0;
  $monto=0;

  parse_str($res["producto"], $producto);
  parse_str($res["plato"], $plato);
  parse_str($res["observacion"], $observacion);

  //print_r($producto);
 // print_r($plato);
  //print_r($observacion);
 $this->db->trans_begin();
  $data=array(
      "empleado_id"=>$_COOKIE["usuario_id"],
      "produccion_descripcion"=>"Produccion de paletas",
      "produccion_fecha"=>date("Y-m-d H:i:s"),
      "produccion_observacion"=>$observacion["observacion"],
      "id_sede"=>$_COOKIE["id_sede"]
  );
$this->Mantenimiento_m->insertar("produccion",$data);
$produccion_id=$this->db->insert_id();
// ingresar platos ,suma el stock de producto
foreach ($plato["plato"] as $key => $value) {

  $data_plato=array(
    "producto_id"=>$value,
    "produccion_plato_cantidad"=>$plato["cantidad_plato"][$key],
    "produccion_id"=>$produccion_id,
  );

  $this->Mantenimiento_m->insertar("producto_plato",$data_plato);

  $this->actualizar_stock_plato($value,(float)$plato["cantidad_plato"][$key],1);
  
}
// extraer datos de almacen y restar del stock

if(isset($producto["producto_id_data"])){
foreach ($producto["producto_id_data"] as $key => $value) {
 $data_producto=array(
   "producto_id"=>$value,
    "produccion_producto_cantidad"=>$producto["cantidad_producto"][$key],
     "detalle_unidad_producto_id"=>$producto["unidad_medida_producto"][$key],
      "produccion_id"=>$produccion_id,
      "almacen_id"=>$producto["id_almacen_data"][$key]

 );
  $this->Mantenimiento_m->insertar("produccion_producto",$data_producto);
$detalle_unidad=explode("/",$producto["unidad_medida_producto"][$key]);
 $this->actualizar_stock($producto["id_almacen_data"][$key],$value,$detalle_unidad[0],$producto["cantidad_producto"][$key],0,2,"","","");
}

}




$json["estado"]=1;

echo json_encode($json);

 if ($this->db->trans_status() === FALSE) {
            //if something went wrong, rollback everything
            $this->db->trans_rollback();
        return FALSE;
        } else {
            //if everything went right, commit the data to the database
            $this->db->trans_commit();
            return TRUE;
        }

}


public function mostrar_produccion()
{ 


$json=array();
$json["plato"]=$this->Mantenimiento_m->consulta3("SELECT *
FROM
producto_plato
INNER JOIN producto ON producto_plato.producto_id = producto.producto_id
where producto_plato.produccion_id=".$_POST["id"]);

$json["producto"]=$this->Mantenimiento_m->consulta3("SELECT *
FROM
produccion_producto
INNER JOIN producto ON produccion_producto.producto_id = producto.producto_id
INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.producto_id = producto.producto_id AND produccion_producto.detalle_unidad_producto_id = detalle_unidad_producto.detalle_unidad_producto_id
INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida AND detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida
where produccion_producto.produccion_id=".$_POST["id"]);

echo json_encode($json);exit();

 

}





}

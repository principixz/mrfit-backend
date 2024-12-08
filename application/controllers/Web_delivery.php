<?php 
  header("Access-Control-Allow-Credentials: true");
  header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin, X-Requested-With, Content-Type, Accept, Authorization");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Content-Type: application/json; charset=UTF-8');

require_once "BaseController.php";
class Web_delivery extends BaseController {


  public function  __construct(){
    parent::__construct();
   }

   public function enviar_productos()
   {

   	  $response=array();
 	  $postdata = file_get_contents("php://input");
  	  $request = json_decode($postdata,true); 
       $respuesta=array();
  	  $sql="select * from producto inner join categoria_producto 
  	  on categoria_producto.categoria_producto_id=producto.categoria_producto_id
  	 where producto_estado=1 and producto.id_sede=1";
  	  $respuesta["productos"]=$this->db->query($sql)->result_array();

  	  $respuesta["categoria"]=$this->db->query("select * from categoria_producto where categoria_producto_estado=1")->result_array();
  	  echo json_encode($respuesta);exit();
   }


}
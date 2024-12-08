
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";
//require_once("src/autoload.php");
class Cocina extends BaseController {

 public function __construct() {
        parent::__construct();
       	
      
    }

	public function index()
	{
        $data=array();
      $data["titulo_descripcion"]="Cocina";

	  $this->vista("Cocina/index",$data);
	}

	public function generar_ventas()
{


$data="";

$cocina=array();

     $data=$this->Mantenimiento_m->consulta3(" SELECT if(lugarmesa_descripcion IS null,'',lugarmesa_descripcion) as lugar,if(datos_delivery.nombre IS null,'',datos_delivery.nombre) as 'nombre_delivery'
 ,mesa.*,venta.*,clientes.*,empleados.*,buscar_mesas_hijo(mesa.mesa_id) as 'mesas_agrupas'
FROM
mesa
left JOIN venta ON mesa.mesa_id = venta.venta_codigomesa
left JOIN clientes ON venta.venta_codigocliente = clientes.cliente_id
INNER JOIN empleados ON venta.venta_codigomozo = empleados.empleado_id 
LEFT JOIN datos_delivery ON datos_delivery.id_venta=venta.venta_idventas
LEFT JOIN lugar_mesa ON lugar_mesa.lugarmesa_id=mesa.mesa_id_lugar
where  venta.venta_idsede=".$_COOKIE["id_sede"]." and (venta.venta_estado!=0 and venta.venta_estadococina = 1) 
   
order by venta.venta_idventas asc");


  


$contador=0;
foreach ($data as $key => $value) {
   $cont=0;
    $mod = $this->db->query("SELECT *
                FROM
                producto
                INNER JOIN detalle_venta ON detalle_venta.cod_producto_venta = producto.producto_id

                
                where  
                detalle_venta.cod_producto_venta<>9999 and
               detalle_venta.detalle_estado_preparado=1 and
               detalle_venta.estado_pedido=1 and
                detalle_venta.id_venta=".$value["venta_idventas"])->result_array();
                                 $data[$key]["lista"] = $mod;
            
                foreach ($mod as $key1 => $detalle_datos) {
                  $cont=1;
                     
                }

                if( $cont==1)
                {
                  $cocina[$contador]=$value;
                    $cocina[$contador]["lista"] = $mod;
                   $contador++;
                }


}

								  /*  foreach ($data as $key => $value) {
								                $mod = $this->db->query("SELECT *
								FROM
								producto
								INNER JOIN detalle_venta ON detalle_venta.cod_producto_venta = producto.producto_id
								INNER JOIN tipo_entrega ON detalle_venta.id_tipoentrega = tipo_entrega.tipoentrega_idtipoentrega
								where detalle_venta.id_venta=".$value["venta_idventas"]." and detalle_venta.detalle_estado_preparado=1 and detalle_venta.estado_pedido<>0 and producto.producto_id_tipoproducto=1")->result_array();
								                 $data[$key]["lista"] = $mod;
    								}*/


    echo json_encode($cocina);
}


public function actividad_venta(){


    $sql="SELECT
venta.venta_codigomozo,

lugar_mesa.lugarmesa_descripcion,
UPPER(CONCAT('El pedido de la mesa ',
mesa_numero,buscar_mesas_hijo(mesa.mesa_id),' sector ',
CAST(lugar_mesa.lugarmesa_descripcion AS CHAR CHARACTER SET utf8)

 ,' esta listo para entregar al cliente')) as 'mesa',
'PEDIDO PREPARADO' as 'titulo'
from venta
inner join mesa on venta.venta_codigomesa=mesa.mesa_id
inner join lugar_mesa on lugar_mesa.lugarmesa_id=mesa.mesa_id_lugar

where venta.venta_idventas=".$_POST["id"];

$resp=$this->db->query($sql)->row_array();

$titulo=$resp["titulo"];
$mensaje=$resp["mesa"];
$cliente=$resp["venta_codigomozo"];
$data=array(
"titulo"=>$resp["titulo"],
"mensaje"=>$resp["mesa"]

);
$this->enviar_notificacion_cliente($titulo,$mensaje,$cliente,$data);


  $data=array(
        "venta_estadococina"=>2,
        "venta_fecha_preparacion"=>date("Y-m-d H:i:s")
    );
   
   $this->db->where('venta_idventas', $_POST["id"]);
      $this->db->update('venta', $data);


       $data=array(
       "detalle_estado_preparado"=>2,
    "fecha_preparar"=>date("Y-m-d H:i:s")
    );
   
   $this->db->where('id_venta', $_POST["id"]);
      $this->db->update('detalle_venta', $data);

   /* $data=$this->Mantenimiento_m->consulta3("SELECT producto.producto_id,detalle_venta.cantidad FROM  venta
  INNER JOIN detalle_venta ON detalle_venta.id_venta = venta.venta_idventas
  INNER JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id
  WHERE venta.venta_idsede=".$_COOKIE["id_sede"]." and producto.producto_id_tipoproducto = 1 and venta_idventas=".$_POST["id"]);
  foreach ($data as $key => $value) {
    $id_producto =$value["producto_id"] ;
    $cantidad = $value["cantidad"];
    $this->actualizar_stock_insumo_salida($id_producto,$cantidad);
  }
  echo 1 ;*/

  echo 1;
}


 public function deshacer_preparar()
     {
        
  
                 
            $data=array(
                "venta_estadococina"=>1,
                "venta_fecha_preparacion"=>""
            );
           
           $this->db->where('venta_idventas', $_POST["id"]);
              $this->db->update('venta', $data);


               $data=array(
               "detalle_estado_preparado"=>1,
            "fecha_preparar"=>""
            );
           
             $this->db->where('id_venta', $_POST["id"]);
                $this->db->update('detalle_venta', $data);

           echo 1;


     }


}
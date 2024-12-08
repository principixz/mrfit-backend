
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";
require_once("src/autoload.php");
require_once("convertirAletra.php");

class Reporteventa extends BaseController {

 public function __construct() {
        parent::__construct();
       	
      
    }

	public function index()
	{
	  $data["titulo_descripcion"]="Reporte de Ventas";
     $this->vista("Reporte/ventas",$data);
   }

public function cargar_reporte_venta()
  {
    //$data_token = json_decode($this->consultar_token(),true);
 // $response=array();
   // $postdata = file_get_contents("php://input");
    //$request = json_decode($postdata,true); 
    $response=array();

    $documento="";
    if((int)$_POST["tipo"]==2)
    {
           $documento=" and venta_estado_pagado = 1";
    }

     if((int)$_POST["tipo"]==3)
    {
           $documento=" and  venta_estado_pagado = 2";
    }
  
 $sql="SELECT
  venta.venta_num_serie AS 'serie',
  venta.venta_num_documento AS 'documento',
  DATE_FORMAT( venta.venta_fecha_pago, '%d/%m/%Y' ) AS 'fecha',
  TIME( venta.venta_fecha_pago ) AS 'hora',
IF
  ( venta_estado_pagado = 1, venta.venta_nombre_descripcion, 'Cliente anulado' ) AS 'cliente',
  IF ( venta_estado_pagado = 1, venta.venta_documento_descripcion, '00000000' ) AS 'nro_documento',
IF
  ( venta_estado_pagado = 1, ROUND( venta.venta_monto, 2 ), 0 ) AS 'monto',
IF
  ( ventas_idtipodocumento = 1, 'FACTURA ELECTRÓNICA', IF ( ventas_idtipodocumento = 2, 'BOLETA ELECTRÓNICA', 'TICKET' ) ) AS 'codigo_documento',
  venta.venta_idventas ,
 IF
  (  venta.venta_eliminacion_descripcion is null,'',venta.venta_eliminacion_descripcion) as 'motivo_eliminacion',
    formapago.for_descripcion as 'formapago',
  CONCAT(empleados.empleado_nombres,' ',empleados.empleado_apellidos) as 'nombre'
FROM
  venta
  LEFT JOIN clientes ON venta.venta_codigocliente = clientes.cliente_id
  INNER JOIN empleados ON empleados.empleado_id = venta.venta_id_cajero 
    LEFT JOIN movimiento on movimiento.mov_id=venta.venta_idmovimiento
  LEFT JOIN formapago on formapago.for_id=movimiento.mov_formapago
WHERE
  venta.venta_estado IN ( 2, 0 ) 
  AND date( venta.venta_fecha_pago ) BETWEEN '".$_POST["fecha_inicio"]."' and '".$_POST["fecha_fin"]."'
  AND venta.ventas_idtipodocumento != 0  
   ". $documento."
  order by venta_fecha_pago desc";
//echo $sql;
$response=$this->db->query($sql)->result_array();


    
    echo json_encode($response); 
  }




}
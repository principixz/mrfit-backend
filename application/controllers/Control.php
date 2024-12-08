<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";
class Control extends BaseController {
  public function __construct() {
 	  parent::__construct();
  }
  public function index(){
    $data=array();
    $data["titulo_descripcion"]="Panel de control";
    $this->vista("Control/index",$data);
  }
   	public function venta_anual(){
     $datos=array();
     if ($_COOKIE["id_sede"] == 8) {
     	$dat=$this->Mantenimiento_m->consulta3("select Sum(venta_monto) AS monto,
      	YEAR(venta_fecha_pago) AS nombre_dia,DAY(venta_fecha_pago) from venta where venta_estado=2 and venta_fecha_pago is not null GROUP BY YEAR(venta_fecha_pago)
      	ORDER BY YEAR(venta_fecha_pago)");
     }else{
		$dat=$this->Mantenimiento_m->consulta3("select Sum(venta_monto) AS monto,
      	YEAR(venta_fecha_pago) AS nombre_dia,DAY(venta_fecha_pago) from venta where venta_idsede=".$_COOKIE["id_sede"]." and venta_estado=2 and venta_fecha_pago is not null GROUP BY YEAR(venta_fecha_pago)
      	ORDER BY YEAR(venta_fecha_pago)");
     }

       foreach ($dat as $key => $value) {        
           $datos["extension"][$key]=($value["nombre_dia"]);
           $datos["monto"][$key]=(float)$value["monto"];
       }
       $datos["cronologia"]="";
       echo json_encode($datos);
   	}
   
   	public function top_5_producto(){
   	   	if ($_COOKIE["id_sede"] == 8) {
   	   		$sql=$this->Mantenimiento_m->consulta3("SELECT sum(detalle_venta.cantidad) as suma,producto.producto_descripcion,
          	producto.producto_id 
          	from producto,detalle_venta WHERE
          	producto.producto_id=detalle_venta.cod_producto_venta  and producto.producto_estado=1
          	GROUP BY producto.producto_id ORDER BY sum(detalle_venta.cantidad) desc
          limit 5");
   	   	}else{
   	   		$sql=$this->Mantenimiento_m->consulta3("SELECT sum(detalle_venta.cantidad) as suma,producto.producto_descripcion,
          producto.producto_id 
          from producto,detalle_venta WHERE
          producto.producto_id=detalle_venta.cod_producto_venta AND
          producto.id_sede=".$_COOKIE["id_sede"]." and producto.producto_estado=1
          GROUP BY producto.producto_id ORDER BY sum(detalle_venta.cantidad) desc
          limit 5");
   	   	}         
        $datos=array();
        foreach ($sql as $key => $value) {
             $datos[$key]["y"]=(float)$value["suma"];
             $datos[$key]["name"]=$value["producto_descripcion"];
        }

      echo json_encode($datos);
   }

   public function top_5_producto_dia(){ 
        if ($_COOKIE["id_sede"] == 8) {
          $sql=$this->Mantenimiento_m->consulta3("SELECT sum(detalle_venta.cantidad) as suma,producto.producto_descripcion,
            producto.producto_id 
            from producto,detalle_venta WHERE
            producto.producto_id=detalle_venta.cod_producto_venta  and producto.producto_estado=1
            WHERE DATE(detalle_venta.fecha_preparar) BETWEEN '".$_POST["fecha_inicio"]."' and '".$_POST["fecha_fin"]."'
            GROUP BY producto.producto_id ORDER BY sum(detalle_venta.cantidad) desc
          limit 5");
        }else{
          $sql=$this->Mantenimiento_m->consulta3("SELECT sum(detalle_venta.cantidad) as suma,producto.producto_descripcion,
          producto.producto_id 
          from producto,detalle_venta WHERE
          producto.producto_id=detalle_venta.cod_producto_venta AND
          producto.id_sede=".$_COOKIE["id_sede"]." and producto.producto_estado=1
          AND  DATE(detalle_venta.fecha_preparar) BETWEEN '".$_POST["fecha_inicio"]."' and '".$_POST["fecha_fin"]."'
          GROUP BY producto.producto_id ORDER BY sum(detalle_venta.cantidad) desc
          limit 5");
        }         
        $datos=array();
        foreach ($sql as $key => $value) {
             $datos[$key]["y"]=(float)$value["suma"];
             $datos[$key]["name"]=$value["producto_descripcion"];
        }

      echo json_encode($datos);
   }

      public function top_cliente(){
      	if ($_COOKIE["id_sede"] == 8) {
       $data=$this->Mantenimiento_m->consulta3("select sum(venta.venta_monto) as suma,clientes.cliente_nombres
 			from venta,clientes where  venta.venta_codigocliente=clientes.cliente_id
			GROUP BY clientes.cliente_id 
			ORDER BY sum(venta.venta_monto) desc");
      	}else{
        	$data=$this->Mantenimiento_m->consulta3("select sum(venta.venta_monto) as suma,clientes.cliente_nombres
 			from venta,clientes where   venta_idsede=".$_COOKIE["id_sede"]." and venta.venta_codigocliente=clientes.cliente_id
			GROUP BY clientes.cliente_id 
			ORDER BY sum(venta.venta_monto) desc");     		
      	}
    	echo json_encode($data);
	}
	   public function mov_sede(){
    $datos = array();
    $totalIngresoF=0;
    $totalIngresoV=0;
    $totalEgresoF=0;
    $totalEgresoV=0;
    $hingresosf=$this->db->query("SELECT SUM(movimiento.mov_monto) as monto from sede_caja,sesion_caja,movimiento,concepto 
      where sede_caja.id_sede_caja=sesion_caja.id_sede_caja and
      sesion_caja.id_sesion_caja=movimiento.id_sesion_caja and movimiento.mov_concepto=concepto.con_id AND
      concepto.id_tipo_movimiento=1 and movimiento.mov_estado = 1 and sede_caja.id_caja=1 and sede_caja.id_sede=".$_COOKIE["id_sede"])->result_array();
    if ($hingresosf[0]["monto"]=="") {
      $hingresosf=0.00;
    }else{
      $hingresosf = $hingresosf[0]["monto"];
    }

    $totalIngresoF+=$hingresosf;

    $hegresosf=$this->db->query("SELECT SUM(movimiento.mov_monto) as monto from sede_caja,sesion_caja,movimiento,concepto 
      where sede_caja.id_sede_caja=sesion_caja.id_sede_caja and
      sesion_caja.id_sesion_caja=movimiento.id_sesion_caja and movimiento.mov_concepto=concepto.con_id AND
      concepto.id_tipo_movimiento=2 and sede_caja.id_caja=1 and movimiento.mov_estado = 1 and sede_caja.id_sede=".$_COOKIE["id_sede"])->result_array();
    if ($hegresosf[0]["monto"]=="") {
      $hegresosf=0.00;
    }else{
      $hegresosf = $hegresosf[0]["monto"];
    }

    $totalEgresoF+=$hegresosf;

    $hingresosv=$this->db->query("SELECT SUM(movimiento.mov_monto) as monto from sede_caja,sesion_caja,movimiento,concepto 
      where sede_caja.id_sede_caja=sesion_caja.id_sede_caja and
      sesion_caja.id_sesion_caja=movimiento.id_sesion_caja and movimiento.mov_concepto=concepto.con_id AND
      concepto.id_tipo_movimiento=1 and sede_caja.id_caja=2 and movimiento.mov_estado = 1 and sede_caja.id_sede=".$_COOKIE["id_sede"])->result_array();
    if ($hingresosv[0]["monto"]=="") {
      $hingresosv=0.00;
    }else{
      $hingresosv = $hingresosv[0]["monto"];
    }

    $totalIngresoV+=$hingresosv;

    $hegresosv=$this->db->query("SELECT SUM(movimiento.mov_monto) as monto from sede_caja,sesion_caja,movimiento,concepto 
      where sede_caja.id_sede_caja=sesion_caja.id_sede_caja and
      sesion_caja.id_sesion_caja=movimiento.id_sesion_caja and movimiento.mov_concepto=concepto.con_id AND
      concepto.id_tipo_movimiento=2 and sede_caja.id_caja=2 and movimiento.mov_estado = 1 and sede_caja.id_sede=".$_COOKIE["id_sede"])->result_array();
    if ($hegresosv[0]["monto"]=="") {
      $hegresosv=0.00;
    }else{
      $hegresosv = $hegresosv[0]["monto"];
    }
    $total = round(($hingresosf+$hingresosv)-($hegresosf+$hegresosv),2);
    $totalEgresoV+=$hegresosv;
    $datos["hingresosf"]=(float) $hingresosf;
    $datos["hingresosv"]=(float) $hingresosv;
    $datos["hegresosf"]=(float) $hegresosf;
    $datos["hegresosv"]=(float) $hegresosv;
    $datos["total"] = "Total de Ganancia Neta S/.". $total;
    echo json_encode($datos);
  }
  public function top_10_insumo()   {
   $sql=$this->Mantenimiento_m->consulta3("SELECT sum(detalle_compras.dc_cantidad) as suma,insumo.insumo_descripcion,
    insumo.insumo_id
    from insumo,detalle_compras 
    WHERE detalle_compras.producto_id = insumo.insumo_id AND
    insumo.id_sede=".$_COOKIE["id_sede"]." and insumo.insumo_estado=1
    GROUP BY insumo.insumo_id ORDER BY sum(detalle_compras.dc_cantidad) desc
    limit 10");
   $datos=array();
   foreach ($sql as $key => $value) {
     $datos[$key]["y"]=(float)$value["suma"];
     $datos[$key]["name"]=$value["insumo_descripcion"];
     $datos[$key]["drilldown"]=$value["insumo_descripcion"];


   }

   echo json_encode($datos);
 }



}
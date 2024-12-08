<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";

class R_plato_vendido extends BaseController {

	public function __construct() {
		parent::__construct();            
	}

	public function index()
	{

				$data=array();
      $data["titulo_descripcion"]="Reporte de Productos y Platos";
    //$data["empleado"]=$this->Mantenimiento_m->consulta3("select * from empleados where estado=1 and empresa_sede=".$_COOKIE["id_sede"]);
      $data["platos"]=$this->Mantenimiento_m->consulta3("select * from producto where producto_estado=1  and id_sede=".$_COOKIE["id_sede"]);
	  $this->vista("R_plato_vendido/index",$data);
	}

	public function traer_reporte_platos_vendidos(){
		$id_platos="";
		foreach ($_REQUEST["platos"] as $key=>$value) {
			$id_platos=$id_platos.",".$value;
		}
		$id_platos=substr($id_platos, 1);

  	$data=$this->Mantenimiento_m->consulta3("SELECT producto.producto_descripcion as descripcion, venta.venta_fecha_pago,detalle_venta.precio,SUM(detalle_venta.cantidad) as total from producto INNER JOIN detalle_venta ON detalle_venta.cod_producto_venta = producto.producto_id	INNER JOIN venta ON detalle_venta.id_venta = venta.venta_idventas where DATE(venta.venta_fecha_pago) BETWEEN '".$_POST["inicio"]."' and '".$_POST["fin"]."'  and producto.producto_estado=1 and producto.producto_id in (".$id_platos.") GROUP BY producto.producto_descripcion");

  	foreach ($data as $key => $value) {
  		echo "<tr>";
  		echo "<td>".$value["descripcion"]."</td>";
  		echo "<td>".$value["venta_fecha_pago"]."</td>";
        echo "<td>".$value["total"]."</td>";
        echo "<td>".$value["precio"]."</td>";

  		echo "</tr>";

  	}
	}


}
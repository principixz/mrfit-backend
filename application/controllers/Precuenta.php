
 

<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Content-type: text/html; charset=utf8");
require_once "BaseController.php";

class Precuenta extends BaseController {

	public function __construct() {
		parent::__construct();


	}
public function mostrar_comprobante($id){
		$empresa=$this->Mantenimiento_m->consulta3("select * from empresa");
		$venta=$this->Mantenimiento_m->consulta3("SELECT * from mesa,venta where mesa.mesa_id=venta.venta_codigomesa and venta.venta_idventas=".$id);
		$detalle_venta=$this->Mantenimiento_m->consulta3("SELECT *,sum(cantidad) as cant_total  FROM detalle_venta
			INNER JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id
			where detalle_venta.estado_pedido=1 and  detalle_venta.id_venta=".$id."
            GROUP by cod_producto_venta
			");
		$total=0;
		foreach ($detalle_venta as $key => $val) {
			$total+=(float)$val["cant_total"]*$val["producto_precio"];
		}

		$dat=intval($total); 
		$fl=$total-$dat;
		$flotante=(string)(round($fl*100));

		$vendedor=$this->Mantenimiento_m->consulta3("select * from venta,empleados where venta.venta_credito_usuario=empleados.empleado_id and venta.venta_idventas=".$id);

		$cliente=$this->Mantenimiento_m->consulta3("select * from venta,clientes where venta.venta_codigocliente=clientes.cliente_id and venta.venta_idventas=".$id);
		$sede=$this->Mantenimiento_m->consulta3("select * from sede where id_sede=".$_COOKIE["id_sede"]);
		$estado_mesa="";


		$delivery=$this->db->query("select * from datos_delivery where id_venta=".$id)->result_array();

		$letras= NumeroALetras::convertir($dat);
		$this->load->view("Comprobante/prefactura",compact("sede","estado_mesa","id","empresa","venta","detalle_venta","letras","flotante","vendedor","cliente","delivery"));  

	}
}
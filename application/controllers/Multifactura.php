<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";

class Multifactura extends BaseController {

	public function __construct() {
		parent::__construct();
	}

	public function particionarventa($id_venta){  
     	//$data=array();
 
		$data["titulo_descripcion"]="Administración de Cobro"; 
 
			$data["venta"] = $this->db->query("SELECT venta.venta_idventas,venta.venta_fecha,venta.venta_monto,venta.venta_fechapedido,
				venta.venta_pedidofecha,'Mesa ' as silla_descripcion,mesa.mesa_numero,venta.venta_codigomozo,venta.venta_codigomesa as codigoubicacion
				FROM
				venta
				LEFT JOIN mesa ON venta.venta_codigomesa = mesa.mesa_id 
				where venta.venta_idventas =".$id_venta." and venta.venta_idsede=".$_COOKIE['id_sede']."")->row();	
		$data["detalleventa"] = $this->Mantenimiento_m->consulta3("SELECT
			venta.venta_idventas,
			detalle_venta.descripcion,
			detalle_venta.cantidad as 'real',
			detalle_venta.observaciones,
			detalle_venta.estado_pedido,
			detalle_venta.detalle_estado_preparado,
			detalle_venta.cod_producto_venta,detalle_venta.precio,producto.producto_descripcion,
			coalesce(sum(detalle_cuentas_temporal.cantidad),0) as cantidad_temporal,
			detalle_venta.cantidad-coalesce(sum(detalle_cuentas_temporal.cantidad),0) as cantidad,
			detalle_cuentas_temporal.estado_cuenta,
			detalle_venta.id_detalle_venta
			FROM
			venta
			INNER JOIN detalle_venta ON detalle_venta.id_venta = venta.venta_idventas
			INNER JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id
			LEFT JOIN cuentas_venta_temporal ON cuentas_venta_temporal.cuentas_idventas = venta.venta_idventas
			LEFT JOIN detalle_cuentas_temporal ON detalle_cuentas_temporal.cod_producto_venta = producto.producto_id 
			AND detalle_cuentas_temporal.cuentas_idtemporal = cuentas_venta_temporal.cuentas_idtemporal  
			AND detalle_cuentas_temporal.estado_cuenta <> 0 
AND detalle_cuentas_temporal.estado_pedido <> 0
			AND  detalle_venta.id_detalle_venta = detalle_cuentas_temporal.estado_descuento
			WHERE
			venta.venta_idventas =".$id_venta."  GROUP BY detalle_venta.cod_producto_venta,detalle_venta.estado_descuento ORDER BY coalesce(detalle_cuentas_temporal.cantidad,0) DESC"); 

		$data["formapago"]=$this->Mantenimiento_m->consulta3("select * from formapago");
		$data["tipopago"]=$this->Mantenimiento_m->consulta3("select * from tipo_pago");
		$data["tipo_documento"]=$this->Mantenimiento_m->consulta3("select * FROM
		tipo_documento
		INNER JOIN documento ON documento.id_tipodocumento = tipo_documento.tipodoc_id
		INNER JOIN detalle_doc_sede ON detalle_doc_sede.detalle_id_documento = documento.id_documento
		where detalle_id_sede=".$_COOKIE["id_sede"]." and tipo_documento.tipodoc_id IN(1,2,5) ");
		$data["id_venta"]=$id_venta;
		$data["empresa"]=$this->Mantenimiento_m->consulta3("select * FROM empresa where empresa_ruc=".$_COOKIE["ruc_empresa"]);

	$data["estado_impresion"]=1;
		$this->vista("Multifactura/index",$data);
	}

	public function temporal(){
		$datos["response"] = $this->Mantenimiento_m->consulta3("SELECT DISTINCT(nombre_cuenta)  as cuenta FROM cuentas_venta_temporal
			where venta_estado = 1 and cuentas_idventas = ".$_POST['id_venta']."
			ORDER BY cuenta asc");
		foreach ($datos["response"] as $key => $value) {
			$data_detalle = $this->Mantenimiento_m->consulta3("SELECT *
				FROM
				cuentas_venta_temporal
				INNER JOIN detalle_cuentas_temporal ON detalle_cuentas_temporal.cuentas_idtemporal = cuentas_venta_temporal.cuentas_idtemporal
				where venta_estado = 1 and cuentas_idventas = ".$_POST['id_venta']." and cuentas_venta_temporal.nombre_cuenta = '".$value['cuenta']."' and detalle_cuentas_temporal.estado_cuenta <>0 and detalle_cuentas_temporal.estado_pedido <>0");
			$datos["response"][$key]["detalle"] = $data_detalle;
		}
		echo json_encode($datos);
	}

	public function eliminar(){
		$detalle = array(
			"estado_pedido" => 0
		);
		$this->db->where('detalle_iddetalletemporal',$_POST["id_tem"]);
		$estado=$this->db->update('detalle_cuentas_temporal', $detalle);
	}

	public function procesar_venta(){

		//$this->db->trans_begin();
		$post=file_get_contents("php://input");
		$res=json_decode($post, true);
		$ventas_producto=array();
		$ventas_pago=array();
		$id_caja=0;
		$monto=0;
		$descripcion_comprobante="";
		$serie="";
		$correlativo="";
		$id_documento="";
		$nuevo_correlativo=0;

		parse_str($res["venta_pago"], $ventas_pago);
        $id_venta = $ventas_pago["id_modal_venta"];

		// print_r($ventas_pago);
		// print_r($res["venta_producto"]);
		// print_r($res["total_pagar"]);exit();
		$igv=0;
		$estado_igv="";
		$monto_igv=0;
		$monto_parcial=0;
		$cantidadproductos = count($res["venta_producto"]);
		$id_mozo = null;
		if($ventas_pago["forma_pago"]==1){
			$id_caja=1;
		}else{
			$id_caja=2;
		} 
 
			$nombre_variable = 'venta_codigomesa';
 
		$idubicacion =$ventas_pago["codigoubicacion"];
		$codigodelmozo = $ventas_pago["codigodelmozo"];
		$total=$res["total_pagar"];
		$id_tipo_comprobante=$ventas_pago["tipo_comprabante"];
		$monto=$total;
		$formapago=$ventas_pago["forma_pago"];
		$concepto=1;
		$descripcion="COBRO DE VENTA DE PRODUCTOS";
		$tipomovimiento=1;		
		$id_movimiento=0;
		if(isset($ventas_pago["igv"])){			
			$igv=(float)$ventas_pago["igv"];
			$estado_igv="";
			$monto_igv=0;
			$monto_parcial=0;
		}else{
			$igv=0;
			$estado_igv="";
			$monto_igv=0;
			$monto_parcial=0;		
		}
		if($igv==0){
			$estado_igv="0";
			$monto_igv=0;
			$monto_parcial=$monto;
		}else{
			$estado_igv="1";
			$sumaigv = (1+$igv);
			$monto_parcial=round(($monto/$sumaigv),2);
			$monto_igv=round(($monto-$monto_parcial),2);
		}
		$comprobante_detalle=0;
		if(isset($ventas_pago["comprobante_detalle"])){
        	$comprobante_detalle=1;
	    }
		$id_movimiento_transporte=0;
		if($ventas_pago["tipo_pago"]==1){
			$id_movimiento=$this->generar_movimiento($id_caja,$monto,$formapago,$concepto,$descripcion,$tipomovimiento,$id_tipo_comprobante,$descripcion_comprobante);
			if($id_movimiento==0){
				echo 2;exit();
			}else{
				$id_movimiento;
			}
		}
		if($id_tipo_comprobante!=0){
			$dat=$this->Mantenimiento_m->consulta3("SELECT * FROM detalle_doc_sede INNER JOIN documento ON detalle_doc_sede.detalle_id_documento = documento.id_documento
				INNER JOIN tipo_documento ON documento.id_tipodocumento = tipo_documento.tipodoc_id where detalle_doc_sede.detalle_id_sede=".$_COOKIE["id_sede"]." and documento.id_tipodocumento=".$ventas_pago["tipo_comprabante"]);
			$descripcion_comprobante=$dat[0]["doc_serie"]."-".$dat[0]["doc_correlativo"];
			$serie=$dat[0]["doc_serie"];
			$correlativo=$dat[0]["doc_correlativo"];			
			$id_documento=$dat[0]["id_documento"];
			$nuevo_correlativo=(int)$correlativo+1;
			$datos=array(
				"doc_correlativo"=>$nuevo_correlativo
			);
			$this->Mantenimiento_m->actualizar("documento",$datos,$id_documento,"id_documento");
			$data=array(
				"tipo_comprobante_descripcion"=>$descripcion_comprobante
			);
			$this->db->where('mov_id',$id_movimiento);
			$estado=$this->db->update('movimiento', $data);
		}
		$id_venta = $ventas_pago["id_modal_venta"];
		$id_cuenta = $res["venta_producto"][0]["id_temp"];
		// $id_venta = $res["venta_producto"][0]["id_temp"];
		$data = array(
			"venta_fechapedido" => time(),
			"venta_pedidofecha" => date("Y-m-d H:i:s"),
			"venta_codigocliente" => $ventas_pago["cliente"],
			"venta_tipopago" => $ventas_pago["tipo_pago"],
			"venta_formapago" => $ventas_pago["forma_pago"],
			"venta_credito_usuario" => $_COOKIE["usuario_id"],
			"venta_fecha_pago"=>date("Y-m-d H:i:s"),
			"venta_idsede" => $_COOKIE["id_sede"],
			"ventas_idtipodocumento"=>$ventas_pago["tipo_comprabante"], 
			"venta_idmoneda"=>1,
			"venta_num_serie" => $serie ,
			"venta_idmovimiento" => $id_movimiento,
			"venta_codigomozo" => $codigodelmozo,
			'venta_num_documento' => $correlativo, 
			'venta_estado' => 2,
			"venta_estado_pagado" => 1,
			$nombre_variable => $idubicacion,
			"venta_igv_estado"=>$estado_igv	,
			"venta_igv_monto"=>	$monto_igv	,
			"venta_estado_consumo"=> $comprobante_detalle,
			"venta_monto_sinigv"=>$monto_parcial,
			'venta_monto' => $ventas_pago["dinero_entregado"],
			"venta_codigomesa" => 0,
			"venta_id_padre" => $id_venta,
			"venta_estadococina"=>2
		);
		$this->db->insert('venta', $data);
		$id_ventaseparada = $this->db->insert_id();	

		for ($i=0; $i <$cantidadproductos ; $i++) { 
			$datos = array(
				"descripcion" => $res["venta_producto"][$i]["nombrecuentadetalle"],
				"cantidad" => $res["venta_producto"][$i]["cantidad"],
				"precio" => $res["venta_producto"][$i]["precio"],
				"id_venta" => $id_ventaseparada,
				"fecha_preparar" => date("Y/m/d"),
				"id_tipoentrega" => 1,
				"cod_producto_venta" => $res["venta_producto"][$i]["codigo_producto"]
			);
			$this->db->insert('detalle_venta', $datos);		
			$detalle = array(
				"estado_pedido" => 2
			);
			$this->db->where('cuentas_idtemporal',$res["venta_producto"][$i]["id_cuentatemp"]);
			$estado=$this->db->update('detalle_cuentas_temporal', $detalle);
		}	
		$cuentas = array(
			"cuenta_estadopago" => 2
		);
		$this->db->where('cuentas_idtemporal',$id_cuenta);
		$estado=$this->db->update('cuentas_venta_temporal', $cuentas);
		if($ventas_pago["tipo_pago"]==2){
			$dato=array(
				"venta_credito_estado"=>1,
				"venta_credito_cuotas"=> $ventas_pago["cuotas"]
			);
			$this->db->where('venta_idventas',$id_ventaseparada);
			$estado=$this->db->update('venta', $dato);
			foreach ($ventas_pago["nrocuotas"] as $key => $value) {
				$datos=array(
					"cuo_venta"=> $id_ventaseparada,
					"cuo_ventanrocuota"=>$value,
					"cuo_ventafechavence"=>$ventas_pago["fechavence"][$key] ,
					"cuo_ventamontocuota"=>$ventas_pago["montocuota"][$key]  ,
					"cuo_ventamontopagado"=>0,
				);
				$this->Mantenimiento_m->insertar("cuotaventa", $datos);
			}
		}

		$traercantidadesdetalle["detalleventa"] = $this->Mantenimiento_m->consulta3("SELECT
			venta.venta_idventas,
			detalle_venta.descripcion,
			detalle_venta.cantidad as 'real',
			detalle_venta.observaciones,
			detalle_venta.estado_pedido,
			detalle_venta.detalle_estado_preparado,
			detalle_venta.cod_producto_venta,detalle_venta.precio,producto.producto_descripcion,
			coalesce(sum(detalle_cuentas_temporal.cantidad),0) as cantidad_temporal,
			detalle_venta.cantidad-coalesce(sum(detalle_cuentas_temporal.cantidad),0) as cantidad,
			detalle_cuentas_temporal.estado_cuenta
			FROM
			venta
			INNER JOIN detalle_venta ON detalle_venta.id_venta = venta.venta_idventas
			INNER JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id
			LEFT JOIN cuentas_venta_temporal ON cuentas_venta_temporal.cuentas_idventas = venta.venta_idventas and cuentas_venta_temporal.cuenta_estadopago = 2
			LEFT JOIN detalle_cuentas_temporal ON detalle_cuentas_temporal.cod_producto_venta = producto.producto_id 
			AND detalle_cuentas_temporal.cuentas_idtemporal = cuentas_venta_temporal.cuentas_idtemporal 
			AND detalle_cuentas_temporal.estado_cuenta <> 0 
AND detalle_cuentas_temporal.estado_pedido <> 0
			AND detalle_venta.id_detalle_venta = detalle_cuentas_temporal.estado_descuento

			WHERE
			venta.venta_idventas =".$id_venta."   GROUP BY detalle_venta.cod_producto_venta,	detalle_venta.estado_descuento ORDER BY coalesce(detalle_cuentas_temporal.cantidad,0) DESC
			");

		$band = 1;
		foreach ($traercantidadesdetalle["detalleventa"] as $llave => $valores) {
			if ($valores["cantidad"] !=0) {
				$band = 1;
				break; 
			}else{
				$band = 0;
			}
		}
$detalle_venta=$this->Mantenimiento_m->consulta3("SELECT detalle_venta.id_detalle_venta, detalle_venta.cod_producto_venta,producto.producto_id_tipoproducto,
			detalle_venta.precio,detalle_venta.cantidad
			FROM detalle_venta
			INNER JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id  where id_venta=".$id_ventaseparada." and detalle_venta.estado_pedido<>0");

		foreach ($detalle_venta as $key => $value){		
			$tipo_producto=$this->db->query("SELECT producto.producto_id_tipoproducto, producto.producto_stock,producto.producto_stock_temporal FROM producto WHERE producto.producto_id=".$value["cod_producto_venta"].";")->row();
			if($tipo_producto->producto_id_tipoproducto==1){	
				$almacen_id = $this->Mantenimiento_m->consulta2("SELECT almacen_id FROM almacenes where almacenes.id_sede = ".$_COOKIE["id_sede"]." and almacen_uso = 1 ");
				$id_almacen=$this->Mantenimiento_m->consulta2("SELECT detalle_producto_almacen.detalle_almacen as id_almacen FROM detalle_producto_almacen WHERE detalle_producto_almacen.detalle_producto=".$value["cod_producto_venta"]." AND detalle_producto_almacen.detalle_almacen = ".$almacen_id->almacen_id.";");
 				$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida
 					INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida 
 					WHERE producto.producto_id = ".$value["cod_producto_venta"]);
				$this->movimiento_stock_temporal($id_almacen->id_almacen,$value["cod_producto_venta"],$id_unidad_medida->detalle_unidad_producto_id,$value["cantidad"],$value["precio"],2,3,$serie,$correlativo,$ventas_pago["tipo_comprabante"]);
			}else{ 
				$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida
 					INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida 
 					WHERE producto.producto_id = ".$value["cod_producto_venta"]);
				$this->movimiento_stock_tplatos($value["cod_producto_venta"],$id_unidad_medida->detalle_unidad_producto_id,$value["cantidad"],$value["precio"],2,3);
			}


			//$this->venta_actualizacion_stock($value["cod_producto_venta"],$value["cantidad"],$value["precio"],$serie ,$correlativo,$id_tipo_comprobante);
		}




		if($band == 0){
			$ventaupdate = array(
				"venta_estado" => 2
			);
			$this->db->where('venta_idventas',$id_venta);
			$this->db->update('venta', $ventaupdate);

				$mesa = array(
					'mesa_empleado' => $id_mozo,
					'mesa_disponible' => 0
				);
				$this->db->where('mesa_id',$idubicacion);
				$estado=$this->db->update('mesa', $mesa);
			
		}

 
		//$this->relacion_cuentas($id_ventaseparada,14);
		//$this->imprimir_comprobante($id_ventaseparada);

		//if ($this->db->trans_status() === FALSE){     
		//	$this->db->trans_rollback();      
		//	return false;    
		//}else{    
			//ob_start();
			//$this->generar_qr($id_ventaseparada);
			//$this->facturacion_electronica($id_venta);
			//ob_end_clean();
			$enviados["estado"] = 1;
			$enviados["idventa"] = $id_ventaseparada;
			echo json_encode($enviados);   
		//	$this->db->trans_commit();    
		//	return true;    
		//}  

	}

	public function verificarventa()
	{
		$id_venta=$_POST["id"];
		$verificar=$this->Mantenimiento_m->consulta3("select * from venta where venta.venta_idventas=".$id_venta." and venta.venta_estado=2");
		if(count($verificar)>0)
		{
			echo 1;
			
			
		}else{

			echo 2;
		}
	}

}?>
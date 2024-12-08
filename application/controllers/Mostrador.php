 <?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";

class Mostrador extends BaseController {

	public function __construct() {
		parent::__construct();
	}
	public function index(){
		$data=array();
		$data["titulo_descripcion"]="Nueva Venta";
		$data["categoriaproducto"] = $this->Mantenimiento_m->consulta3("SELECT categoria_producto.categoria_producto_id,categoria_producto.id_sede,categoria_producto.categoria_producto_descripcion,categoria_producto.categoria_imagen FROM categoria_producto where categoria_producto.categoria_producto_estado = 1 and categoria_producto.id_sede =".$_COOKIE["id_sede"]);
		$data["tipo_entrega"]=$this->Mantenimiento_m->consulta3("SELECT * FROM tipo_entrega where tipoentrega_estado = 1");
		$data["productos"]=$this->Mantenimiento_m->consulta3("SELECT producto.producto_id,
			producto.producto_descripcion,
			producto.producto_precio,
			producto.producto_stock,
			producto.producto_id_tipoproducto,
			if(detalle_producto_almacen.detalle_almacen is null,'null',detalle_producto_almacen.detalle_almacen) as detalle_almacen,	
				detalle_producto_almacen.detalle_stock,
			producto.producto_imagen from producto LEFT JOIN detalle_producto_almacen ON detalle_producto_almacen.detalle_producto = producto.producto_id where producto_estado=1 and id_sede=".$_COOKIE["id_sede"]." LIMIT 12");
		$data["formapago"]=$this->Mantenimiento_m->consulta3("select * from formapago");
		$data["tipopago"]=$this->Mantenimiento_m->consulta3("select * from tipo_pago");
		$data["almacen"]=$this->Mantenimiento_m->consulta3("select * from almacenes where almacen_estado=1 and id_sede=".$_COOKIE["id_sede"]);
		$data["tipo_documento"]=$this->Mantenimiento_m->consulta3("select * FROM
tipo_documento
INNER JOIN documento ON documento.id_tipodocumento = tipo_documento.tipodoc_id
INNER JOIN detalle_doc_sede ON detalle_doc_sede.detalle_id_documento = documento.id_documento
where detalle_id_sede=".$_COOKIE["id_sede"]." and tipo_documento.tipodoc_id<=6");
		$data["empresa"]=$this->Mantenimiento_m->consulta3("select * FROM empresa where empresa_ruc=".$_COOKIE["ruc_empresa"]);

		// $data["estado_impresion"]=$this->estado_configuracion(10);
		// $data["estadostock"] =$this->estado_configuracion(11); 
		$data["estado_impresion"] =1;
		$data["estadostock"] =1;
		$this->vista("Mostrador/nuevo",$data);
	}

	public function buscar_ubicacionllevar(){
		if ($this->input->get("id") == 2) {
			$nombre_mesa = 'Mesa Para Llevar';
		}else{
			$nombre_mesa = 'Mesa Para Delivery';
		}
		$estado = 0;
		$sql ="SELECT CONCAT('M',mesa.mesa_numero) AS id,'".$nombre_mesa."' AS nombre,IF(mesa.mesa_disponible = 0,'Desocupado',IF(mesa.mesa_disponible = 1,'Ocupado',IF(mesa.mesa_disponible = 2,'Reservado','ERROR'))) AS nombre_estado
		FROM mesa
		WHERE
		mesa.mesa_id = 0 AND  ('".$nombre_mesa."' LIKE '%".$this->input->get("q")."%' or (IF( mesa.mesa_numero = 1,'Ocupado','ERROR')) LIKE '%".$this->input->get("q")."%') 
		UNION ";
		if ($estado == 0) {
			$sql =$sql. " SELECT CONCAT('M',mesa.mesa_id) AS id, CONCAT('MESA',' ',mesa.mesa_numero) AS nombre,IF(mesa.mesa_disponible = 0,'Desocupado',IF(mesa.mesa_disponible = 1,'Ocupado',IF(mesa.mesa_disponible = 2,'Reservado','ERROR'))) AS nombre_estado
			FROM mesa
			INNER JOIN lugar_mesa ON mesa.mesa_id_lugar = lugar_mesa.lugarmesa_id
			WHERE  lugar_mesa.id_sede = ".$_COOKIE["id_sede"]." and  mesa.mesa_estado = 1 and mesa.mesa_disponible = 1 and (CONCAT('MESA',' ',mesa.mesa_numero) LIKE '%".$this->input->get("q")."%' or (IF( mesa.mesa_numero = 1,'Ocupado','ERROR')) LIKE '%".$this->input->get("q")."%')";
		}else{
			$sql = $sql." SELECT CONCAT('S',silla.silla_idsilla) AS id,
			CONCAT('Mesa',' ',mesa.mesa_numero,silla.silla_descripcion)  AS nombre,
			IF(silla.silla_estado = 0,'Desocupado',IF(silla.silla_estado = 1,'Ocupado',IF(silla.silla_estado = 2,'Reservado','ERROR'))) AS nombre_estado
			FROM silla 
			INNER JOIN mesa ON silla.silla_mesa_id = mesa.mesa_id
			INNER JOIN lugar_mesa ON mesa.mesa_id_lugar = lugar_mesa.lugarmesa_id
			WHERE lugar_mesa.id_sede = ".$_COOKIE["id_sede"]."  AND  mesa.mesa_estado = 1 and silla.silla_estado = 1 and (CONCAT('Mesa',' ',mesa.mesa_numero,silla.silla_descripcion) LIKE '%".$this->input->get("q")."%' or (IF(silla.silla_estado = 1,'Ocupado','ERROR')) LIKE '%".$this->input->get("q")."%')";
		}
		$data=$this->Mantenimiento_m->consulta3($sql);
		$data1=array();
		foreach ($data as $key => $value) {
			$data1["results"][$key]["id"]=$value["id"];
			$data1["results"][$key]["text"]=$value["nombre"];
			$data1["results"][$key]["documento"]=$value["nombre_estado"];
		}
		echo json_encode($data1);
	}

	public function buscar_ubicacioninsta(){
		// $estado = $this->estado_configuracion(3);
		$estado = 0;
		if ($estado == 0) {
			$sql ="SELECT CONCAT('M',mesa.mesa_id) AS id, CONCAT('MESA',' ',mesa.mesa_numero) AS nombre,IF(mesa.mesa_disponible = 0,'Desocupado',IF(mesa.mesa_disponible = 1,'Ocupado',IF(mesa.mesa_disponible = 2,'Reservado','ERROR'))) AS nombre_estado
			FROM mesa
			INNER JOIN lugar_mesa ON mesa.mesa_id_lugar = lugar_mesa.lugarmesa_id
			WHERE  lugar_mesa.id_sede = ".$_COOKIE["id_sede"]." and  mesa.mesa_estado = 1 and (CONCAT('MESA',' ',mesa.mesa_numero) LIKE '%".$this->input->get("q")."%' or (IF(mesa.mesa_numero = 0,'Desocupado',IF( mesa.mesa_numero = 1,'Ocupado',IF(mesa.mesa_numero = 2,'Reservado','ERROR')))) LIKE '%".$this->input->get("q")."%')";
		}else{
			$sql ="SELECT
			CONCAT('S',silla.silla_idsilla) AS id,
			CONCAT('Mesa',' ',mesa.mesa_numero,silla.silla_descripcion)  AS nombre,
			IF(silla.silla_estado = 0,'Desocupado',IF(silla.silla_estado = 1,'Ocupado',IF(silla.silla_estado = 2,'Reservado','ERROR'))) AS nombre_estado
			FROM silla
			INNER JOIN mesa ON silla.silla_mesa_id = mesa.mesa_id
			INNER JOIN lugar_mesa ON mesa.mesa_id_lugar = lugar_mesa.lugarmesa_id
			WHERE lugar_mesa.id_sede = ".$_COOKIE["id_sede"]." AND  mesa.mesa_estado = 1 and (CONCAT('Mesa',' ',mesa.mesa_numero,silla.silla_descripcion) LIKE '%".$this->input->get("q")."%' or (IF(silla.silla_estado = 0,'Desocupado',IF(silla.silla_estado = 1,'Ocupado',IF(silla.silla_estado = 2,'Reservado','ERROR')))) LIKE '%".$this->input->get("q")."%')  ";	
		}

		$data=$this->Mantenimiento_m->consulta3($sql);
		$data1=array();
		foreach ($data as $key => $value) {
			$data1["results"][$key]["id"]=$value["id"];
			$data1["results"][$key]["text"]=$value["nombre"];
			$data1["results"][$key]["documento"]=$value["nombre_estado"];
		}
		echo json_encode($data1);
	}

	public function traer_un_cliente(){
		//$_POST['ntelefono']
		$telefono=$_POST['ntelefono'];
		$data = $this->db->query("SELECT datos_delivery.idcliente,COUNT(datos_delivery.numero_celular) as cantidad FROM datos_delivery WHERE numero_celular = ".$telefono ." GROUP BY datos_delivery.idcliente,datos_delivery.numero_celular;")->result_array();
		$cantidad = count($data);
		if ($cantidad == 0) {
			$res["cant"] = null;
			$res["info"] = null;
		}else{
			if ($cantidad == 1) {
				$res["cant"]=1;
				$res["info"]=$this->db->query("SELECT clientes.cliente_nombres,clientes.cliente_dni,clientes.cliente_id
					FROM
					datos_delivery
					INNER JOIN clientes ON datos_delivery.idcliente = clientes.cliente_id
					where datos_delivery.numero_celular = ".$telefono." and clientes.empresa_ruc = ".$_COOKIE["ruc_empresa"].";")->row();

				$res["direcciones"] = $this->db->query("SELECT datos_delivery.direccion,clientes.cliente_dni,clientes.cliente_id
					FROM datos_delivery INNER JOIN clientes ON datos_delivery.idcliente = clientes.cliente_id
					WHERE numero_celular = ".$telefono." and clientes.cliente_dni = ".$res["info"]->cliente_dni." GROUP BY datos_delivery.direccion")->result_array();
				$res["cantidaddi"] = count($res["direcciones"]);
			}else{
				$res["cant"]= count($data);
				$res["info"] = $this->db->query("SELECT clientes.cliente_nombres,clientes.cliente_dni,clientes.cliente_id FROM clientes INNER JOIN datos_delivery ON datos_delivery.idcliente = clientes.cliente_id
					where datos_delivery.numero_celular = ".$telefono ." and clientes.empresa_ruc =  ".$_COOKIE["ruc_empresa"]." GROUP BY clientes.cliente_id;")->result_array();
			}
		}
		print_r (json_encode($res));
	}
	function traer_direccion(){
		$telefono = $_POST["telefono"];
		$dni = $_POST["dni"];
		$res["direcciones"] = $this->db->query("SELECT datos_delivery.direccion,clientes.cliente_dni
			FROM datos_delivery INNER JOIN clientes ON datos_delivery.idcliente = clientes.cliente_id
			WHERE numero_celular = ".$telefono." and clientes.cliente_dni = ".$dni." GROUP BY datos_delivery.direccion")->result_array();
		$res["cantidaddi"] = count($res["direcciones"]);
		print_r(json_encode($res));
	}


	function guardarpedido(){
		$post=file_get_contents("php://input");  
		$res=json_decode($post, true);
		$ventas_producto=array();
		parse_str($res["venta_producto"], $ventas_producto);
		$ventas_total_pagar = '';
		if (isset($ventas_producto["igv"])) {
			$igv = 1;
		}else{
			$igv = 0;
		}
		$cantidadproductos = count($ventas_producto["id_producto"]);
		switch ($ventas_producto["perfil"]) {
			case 1:
			$this->pedidoparallevar($ventas_producto,$ventas_total_pagar,$igv,$cantidadproductos,1);
			break;
			case 2:
			$this->pedidoparallevar($ventas_producto,$ventas_total_pagar,$igv,$cantidadproductos,2);
			break;
			case 3:
			$this->pedidodelivery($ventas_producto,$ventas_total_pagar,$igv,$cantidadproductos);
			break;
			default:
			exit();
			break;
		}
	}

	function pedidodelivery($ventas_producto,$ventas_total_pagar,$igv,$cantidadproductos){
		if (!isset($ventas_producto["idventa"])){
			$id_mozo = null;
			if (isset($ventas_producto["id_mozo"])) {
				$id_mozo = $ventas_producto["id_mozo"];
			}else{
				if ($_COOKIE["usuario_perfil"] == 2) {
					$id_mozo = $_COOKIE["usuario_id"];
				}else{
					$id_mozo = $_COOKIE["usuario_id"];
				}
			}
			// if ($this->estado_configuracion(5) == 1) {
				
			// }else{
			// 	$pantallaestado = 2 ;
			// }
			$pantallaestado = 1;
			$idmesa = 1;
			$data = array(
				"venta_fechapedido" => time(), 
				"venta_pedidofecha" => date("Y-m-d H:i:s"),
				"venta_codigomozo" => 1,
				"venta_idsede" => $_COOKIE["id_sede"],
				"venta_codigocliente" =>1,
				"venta_estadococina"=>$pantallaestado,
				"venta_igv_estado" => $igv,
				"venta_monto" => $ventas_producto["total_pagaroc"],
				"venta_id_delivery" => 1,
				"venta_codigomesa" => $idmesa,
				"venta_codigosilla" => 0
			);
			$this->db->insert('venta', $data);
			$idventa = $this->db->insert_id();		
			for ($i=0; $i <$cantidadproductos ; $i++) { 
				//milton
			$tipo_producto=$this->db->query("SELECT producto.producto_id_tipoproducto, producto.producto_stock,producto.producto_stock_temporal FROM producto WHERE producto.producto_id=".$ventas_producto["id_producto"][$i].";")->row();
			if($tipo_producto->producto_id_tipoproducto=='2'){
				$stock_detalle=$this->db->query("SELECT detalle_producto_almacen.detalle_stock, detalle_producto_almacen.detalle_stock_temporal FROM detalle_producto_almacen WHERE detalle_producto_almacen.detalle_producto=".$ventas_producto["id_producto"][$i].";")->row();
				$detalle_almacen= array(
					"detalle_stock" => ($stock_detalle->detalle_stock-$ventas_producto["cantidad"][$i]),
					"detalle_stock_temporal" => $stock_detalle->detalle_stock_temporal + $ventas_producto["cantidad"][$i]
				);
				$this->db->where('detalle_producto',$ventas_producto["id_producto"][$i]);
				$estado=$this->db->update('detalle_producto_almacen', $detalle_almacen);
			}else{ 
				// if ($this->estado_configuracion(11)) { 
					$producto=array(
						"producto_stock" => ($tipo_producto->producto_stock-$ventas_producto["cantidad"][$i]),
						"producto_stock_temporal" => $tipo_producto->producto_stock_temporal + $ventas_producto["cantidad"][$i]
					);
					$this->db->where('producto_id',$ventas_producto["id_producto"][$i]);
					$estado=$this->db->update('producto', $producto);
				// } 
				// $this->movimiento_stock_temporal($ventas_producto["id_producto"][$i],$ventas_producto["cantidad"][$i],1,$_COOKIE["id_sede"]);
			}
			//fin milton
				$datos = array(
					"descripcion" => $ventas_producto["comentarioventa"][$i],
					"cantidad" => $ventas_producto["cantidad"][$i],
					"precio" => $ventas_producto["precio"][$i],
					"id_venta" => $idventa,
					"fecha_preparar" => date("Y/m/d"),
					"id_tipoentrega" => 3,
					"cod_producto_venta" => $ventas_producto["id_producto"][$i]
				);
				$this->db->insert('detalle_venta', $datos);			
			}
			/*if ($ventas_producto["idcliente"] == '') {
				$cliente=$this->db->query("SELECT clientes.cliente_id FROM clientes where (clientes.cliente_dni = ".$ventas_producto["dnidelivery"]." or clientes.cliente_ruc = ".$ventas_producto["dnidelivery"]." ) and clientes.empresa_ruc = ".$_COOKIE["ruc_empresa"])->row();

				if (isset($cliente->cliente_id)) {
					$id_cliente = $cliente->cliente_id;
					$datosc = array(
						"cliente_nombres" => $ventas_producto["nombre"],
						"cliente_telefono" => $ventas_producto["ntelefono"],
						"cliente_direccion" => $ventas_producto["direccion"]
					);
					$this->db->where('cliente_id',$id_cliente);
					$this->db->update('clientes', $datosc);
				}else{
					$datosc = array(
						"cliente_nombres" => $ventas_producto["nombre"],
						"cliente_dni" => $ventas_producto["dnidelivery"],
						"cliente_telefono" => $ventas_producto["ntelefono"],
						"cliente_direccion" => $ventas_producto["direccion"],
						"empresa_ruc" => $_COOKIE["ruc_empresa"]
					);
					$this->db->insert('clientes', $datosc);
					$id_cliente = $this->db->insert_id();
				} 

			}else{
				$id_cliente =$ventas_producto["idcliente"];
			}*/


$ultimo_idcliente="";
				if($ventas_producto["idcliente"]=="")
						{

								$datos_cliente = array(
								"cliente_nombres" => $ventas_producto["nombre"],
								"cliente_direccion" => $ventas_producto["direccion"],
								"cliente_dni" => $ventas_producto["dnidelivery"],
								"cliente_celular" => $ventas_producto["ntelefono"],
								"tipo_cliente_id"=>1
								);	
								$this->db->insert('clientes', $datos_cliente);
								$ultimo_idcliente = $this->db->insert_id();
								if($ventas_producto["iddireccion"]=="")
								{
									$datos_direccion = array(
										"cliente_id" => $ultimo_idcliente,
										"direccion_descripcion" => $ventas_producto["direccion"]
										
										);	
									$this->db->insert('direccion', $datos_direccion);

								}

						}else{

							$ultimo_idcliente = $ventas_producto["idcliente"];
								if($ventas_producto["iddireccion"]=="")
								{
									$datos_direccion = array(
										"cliente_id" => $ultimo_idcliente,
										"direccion_descripcion" => $ventas_producto["direccion"]
										
										);	
									$this->db->insert('direccion', $datos_direccion);

								}



						$datos_cliente = array(
							"cliente_nombres" => $ventas_producto["nombre"],
							"cliente_direccion" => $ventas_producto["direccion"],
							"cliente_dni" => $ventas_producto["dnidelivery"],
							"cliente_celular" => $ventas_producto["ntelefono"],
							"tipo_cliente_id"=>1
							);	


					$this->db->where('cliente_id',$ultimo_idcliente);
					$estado=$this->db->update('clientes', $datos_cliente);


						}
			$datos_delivery = array(
				"idcliente" => $ultimo_idcliente,
				"numero_celular" => $ventas_producto["ntelefono"],
				"direccion" => $ventas_producto["direccion"],
				"id_venta" => $idventa,
				"nombre"=>$ventas_producto["nombre"]
			);	
			$this->db->insert('datos_delivery', $datos_delivery);		
			//$data = array(
			//	"venta_idventas" => $idventa,
			//	"sesion_impresora_id" => 16
			//);
			//$this->db->insert('impresion',$data);
			// if ($this->estado_configuracion(4) == 1) {
			// 	// $this->imprimir_comanda($id_venta);
			// }
			//if ($this->db->trans_status() === FALSE){      
			//	$this->db->trans_rollback();      
			//	return false;    
			//}else{      
			//	$this->db->trans_commit();    
			//	return true;    
			//}  
		}
	}

	public function pedidoparallevar($ventas_producto,$ventas_total_pagar,$igv,$cantidadproductos,$id_tipoentrega){
		$contador_tipoproducto=2;
		foreach ($ventas_producto["id_producto"] as $k => $v) {
			$prod_tipo=$this->Mantenimiento_m->consulta3("SELECT producto.producto_id_tipoproducto FROM producto where producto.producto_id_tipoproducto=1 and producto.producto_id=".$v);
			if(count($prod_tipo)>0){
				$contador_tipoproducto=1;
			}
		}
		if (!isset($ventas_producto["idventa"])){
			//$this->db->trans_begin();
			$id_mozo = null;
			// if ($this->estado_configuracion(5) == 1) {
			// 	$pantallaestado = 1 ;
			// }else{
			// 	$pantallaestado = 2 ;
			// }
			$pantallaestado = 2 ;
			if (isset($ventas_producto["id_mozo"])) {
				$id_mozo = $ventas_producto["id_mozo"];
			}else{
				if ($_COOKIE["usuario_perfil"] == 2) {
					$id_mozo = $_COOKIE["usuario_id"];
				}else{
					$id_mozo = $_COOKIE["usuario_id"];
				}
			}
			if (isset($ventas_producto["mesa"])) {
				$identificador = substr($ventas_producto["mesa"],0,1);
				$id = substr($ventas_producto["mesa"],1,strlen($ventas_producto["mesa"]));
			}else{
				$identificador = substr($ventas_producto["mesallevar"],0,1);
				$id = substr($ventas_producto["mesallevar"],1,strlen($ventas_producto["mesallevar"]));
			}
			
			if ($identificador == 'S') {
				$estado_ubicacion=$this->db->query("SELECT silla.silla_estado FROM silla where silla.silla_idsilla =".$id.";")->row();
				$nombre_variable = 'venta_codigosilla';
				$estado_ubicacion = $estado_ubicacion->silla_estado ;
				if ($estado_ubicacion == 1) {
					$consulta_venta = $this->db->query("SELECT venta.venta_idventas,venta.venta_codigomozo,venta.venta_monto FROM venta where venta.venta_codigosilla =".$id." and venta.venta_estado != 2 and venta.venta_estado != 0;")->row();

					$id_venta = $consulta_venta->venta_idventas;
					$id_mozo =$consulta_venta->venta_codigomozo;
					$venta_monto = number_format($ventas_producto["total_pagaroc"],2);
				}else{
					$id_mozo = $_COOKIE["usuario_id"];
					$venta_monto = $ventas_producto["total_pagaroc"];
				}
			}else{
				$estado_ubicacion=$this->db->query("SELECT mesa.mesa_disponible FROM mesa where mesa.mesa_id =".$id.";")->row();
				$nombre_variable = 'venta_codigomesa';
				$estado_ubicacion = $estado_ubicacion->mesa_disponible;
				if ($estado_ubicacion == 1) {
					$consulta_venta = $this->db->query("SELECT venta.venta_idventas,venta.venta_codigomozo,venta.venta_monto FROM venta where venta.venta_codigomesa =".$id." and venta.venta_estado != 2 and venta.venta_estado != 0;")->row();
					$id_venta = $consulta_venta->venta_idventas;
					$id_mozo =$consulta_venta->venta_codigomozo;
					$venta_monto = number_format($ventas_producto["total_pagaroc"],2);

				}else{
					$id_mozo = $_COOKIE["usuario_id"];
					$venta_monto = $ventas_producto["total_pagaroc"];
				}
			}

			if ($estado_ubicacion == 1){
				$ventaupdate = array(
					"venta_codigomozo" => $id_mozo,
					"venta_idsede" => $_COOKIE["id_sede"],
					"venta_codigocliente" =>0,
					"venta_estadococina"=>$pantallaestado,
					"venta_igv_estado" => $igv,
					"venta_monto" => $venta_monto,
					$nombre_variable => $id
				);	
				$this->db->where('venta_idventas',$id_venta);
				$estado=$this->db->update('venta', $ventaupdate);
			}else{
				$data = array(
					"venta_fechapedido" => time(), 
					"venta_pedidofecha" => date("Y-m-d H:i:s"),
					"venta_codigomozo" => $id_mozo,
					"venta_idsede" => $_COOKIE["id_sede"],
					"venta_estadococina"=>$pantallaestado,
					"venta_codigocliente" =>0,
					"venta_igv_estado" => $igv,
					"venta_monto" => $venta_monto,
					$nombre_variable => $id,
					"venta_estadococina"=>$contador_tipoproducto
				);	
				$this->db->insert('venta', $data);
				$id_venta = $this->db->insert_id();	
				if ($identificador == 'S') {	
					$silla = array(
						'silla_id_empleado' => $id_mozo,
						'silla_estado' => 1
					);
					$this->db->where('silla_idsilla',$id);
					$estado=$this->db->update('silla', $silla);	
				}else{
					$mesa = array(
						'mesa_empleado' => $id_mozo,
						'mesa_disponible' => 1
					);
					$this->db->where('mesa_id',$id);
					$estado=$this->db->update('mesa', $mesa);	
				}
				
			}

			$tipo_producto = array();
		$stock_detalle = array();
			for ($i=0; $i <$cantidadproductos ; $i++) { 
				//milton
			$tipo_producto=$this->db->query("SELECT producto.producto_id_tipoproducto, producto.producto_stock,producto.producto_stock_temporal FROM producto WHERE producto.producto_id=".$ventas_producto["id_producto"][$i].";")->row();
			if($tipo_producto->producto_id_tipoproducto=='2'){
				$stock_detalle=$this->db->query("SELECT detalle_producto_almacen.detalle_stock, detalle_producto_almacen.detalle_stock_temporal FROM detalle_producto_almacen WHERE detalle_producto_almacen.detalle_producto=".$ventas_producto["id_producto"][$i].";")->row();
				$detalle_almacen= array(
					"detalle_stock" => ($stock_detalle->detalle_stock-$ventas_producto["cantidad"][$i]),
					"detalle_stock_temporal" => $stock_detalle->detalle_stock_temporal + $ventas_producto["cantidad"][$i]
				);
				$this->db->where('detalle_producto',$ventas_producto["id_producto"][$i]);
				$estado=$this->db->update('detalle_producto_almacen', $detalle_almacen);
			}else{ 
				// if ($this->estado_configuracion(11)) { 
					$producto=array(
						"producto_stock" => ($tipo_producto->producto_stock-$ventas_producto["cantidad"][$i]),
						"producto_stock_temporal" => $tipo_producto->producto_stock_temporal + $ventas_producto["cantidad"][$i]
					);
					$this->db->where('producto_id',$ventas_producto["id_producto"][$i]);
					$estado=$this->db->update('producto', $producto);
				// } 
				// $this->movimiento_stock_temporal($ventas_producto["id_producto"][$i],$ventas_producto["cantidad"][$i],1,$_COOKIE["id_sede"]);
			}
			//fin milton

				$datos = array(
					"descripcion" => $ventas_producto["comentarioventa"][$i],
					"cantidad" => $ventas_producto["cantidad"][$i],
					"precio" => $ventas_producto["precio"][$i],
					"id_venta" => $id_venta,
					"fecha_preparar" => date("Y/m/d"),
					"id_tipoentrega" => $id_tipoentrega,
					"cod_producto_venta" => $ventas_producto["id_producto"][$i]
				);
				$this->db->insert('detalle_venta', $datos);			
			}
			// $this->imprimir_comanda($id_venta);
			//$data = array(
			//	"venta_idventas" => $id_venta,
			//	"sesion_impresora_id" => 16
			//);
			//$this->db->insert('impresion',$data);	
			// if ($this->db->trans_status() === FALSE){       
			// 	$this->db->trans_rollback();      
			// 	return false;    
			// }else{      
			// 	$this->db->trans_commit();    
			// 	return true;    
			// }
		}
	}

	public function procesar_venta(){
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
		parse_str($res["venta_producto"], $ventas_producto);
		parse_str($res["venta_pago"], $ventas_pago);
		parse_str($res["total_pagar"], $total_pagar);
		$dinero_entrego=$ventas_pago["dinero_entregado"];
		$igv=0;
		$estado_igv="";
		$monto_igv=0;
		$monto_parcial=0;
		$id_mozo = null;
		//$this->db->trans_begin();
		// $estado_configuracion = $this->estado_configuracion(3);
		$estado_configuracion = 0;
		if ($ventas_producto["productos_por_vender"]>0) {
			$cantidadproductos = count($ventas_producto["id_producto"]);
		}else{
			$cantidadproductos = 0;
		}	
		if (isset($ventas_producto["id_mozo"])) {
			$id_mozo = $ventas_producto["id_mozo"];
		}else{
			if ($_COOKIE["usuario_perfil"] == 2) {
				$id_mozo = $_COOKIE["usuario_id"];
			}else{
				$id_mozo = $_COOKIE["usuario_id"];
			}
		}
		// if ($this->estado_configuracion(5) == 1) {
		// 	$pantallaestado = 1 ;
		// }else{
		// 	$pantallaestado = 2 ;
		// }
		$pantallaestado = 1 ;
		if (isset($ventas_producto["mesa"])) {
			$identificador = substr($ventas_producto["mesa"],0,1);
			$id = substr($ventas_producto["mesa"],1,strlen($ventas_producto["mesa"]));
		}else{
			if (isset($ventas_producto["mesallevar"])) {
				$identificador = substr($ventas_producto["mesallevar"],0,1);
				$id = substr($ventas_producto["mesallevar"],1,strlen($ventas_producto["mesallevar"]));
			}else{ 
				$id = 0;
				// if ($estado_configuracion == 1) {
				// 	$nombre_variable = 'venta_codigosilla';
				// }else{
				// 	$nombre_variable = 'venta_codigomesa';
				// }
				$nombre_variable = 'venta_codigomesa';
			}
			
		}
		if (!isset($ventas_producto["mesa"])  || !isset($ventas_producto["mesallevar"])) {
			$id_mozo = $_COOKIE["usuario_id"];
			$venta_monto = $ventas_producto["total_pagaroc"];
				if ($estado_configuracion == 1) {
					$nombre_variable = 'venta_codigosilla';
				}else{
					$nombre_variable = 'venta_codigomesa';
				}
		}else{		
			if ($estado_configuracion == 1) {
				$estado_ubicacion=$this->db->query("SELECT silla.silla_estado FROM silla where silla.silla_idsilla =".$id.";")->row();
				$nombre_variable = 'venta_codigosilla';
				$estado_ubicacion = $estado_ubicacion->silla_estado ;
				if ($estado_ubicacion == 1) {
					$consulta_venta = $this->db->query("SELECT venta.venta_idventas,venta.venta_codigomozo,venta.venta_monto,venta.venta_estadococina FROM venta where venta.venta_codigosilla =".$id." and venta.venta_estado != 2 and venta.venta_estado != 0;")->row();
					$id_venta = $consulta_venta->venta_idventas;
					$cocina_estado = $consulta_venta->venta_estadococina;
					$id_mozo =$consulta_venta->venta_codigomozo;
					$venta_monto = number_format($ventas_producto["total_pagaroc"],2);
				}else{
					$id_mozo = $_COOKIE["usuario_id"];
					$venta_monto = $ventas_producto["total_pagaroc"];
				}
			}else{
				$estado_ubicacion=$this->db->query("SELECT mesa.mesa_disponible FROM mesa where mesa.mesa_id =".$id.";")->row();
				$nombre_variable = 'venta_codigomesa';
				$estado_ubicacion = $estado_ubicacion->mesa_disponible;
				if ($estado_ubicacion == 1) {
					$consulta_venta = $this->db->query("SELECT venta.venta_idventas,venta.venta_codigomozo,venta.venta_monto FROM venta where venta.venta_codigomesa =".$id." and venta.venta_estado != 2 and venta.venta_estado != 0;")->row();
					$id_venta = $consulta_venta->venta_idventas;
					$id_mozo =$consulta_venta->venta_codigomozo;
					$venta_monto = number_format($ventas_producto["total_pagaroc"],2);
				}else{
					$id_mozo = $_COOKIE["usuario_id"];
					$venta_monto = $ventas_producto["total_pagaroc"];
				}
			}
		}

		if($ventas_pago["forma_pago"]==1){
			$id_caja=1;
		}else{
			$id_caja=2;
		}
		$total=0;
		$id_tipo_comprobante=$ventas_pago["tipo_comprabante"];
		$deliverymonto = $ventas_pago["monto_delivery"];
		$monto=$total + $deliverymonto;
		$formapago=$ventas_pago["forma_pago"];
		$concepto=1;
		$descripcion="COBRO DE VENTA DE PRODUCTOS";
		$tipomovimiento=1;		
		$id_movimiento=null;
		$id_movimiento_transporte=0;
		$monto = $venta_monto + $deliverymonto;
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
		$comprobante_detalle=0;
		if(isset($ventas_pago["comprobante_detalle"])){
        	$comprobante_detalle=1;
	    }
		$estado_igv="";
		$monto_igv=0;
		$monto_parcial=0;
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

$array_cliente = explode("/", $ventas_pago["cliente"]);
		if (isset($id_venta)){
			$ventaupdate = array(
				"venta_codigomozo" => $id_mozo,
				"venta_idsede" => $_COOKIE["id_sede"],
				"venta_monto" => $monto,
				"ventas_idtipodocumento"=>$ventas_pago["tipo_comprabante"],
				"venta_tipopago" => $ventas_pago["tipo_pago"],
				"venta_formapago" => $ventas_pago["forma_pago"],
				'venta_num_serie' =>$serie, 
				'venta_num_documento' => $correlativo, 
				"venta_idmoneda"=>1,
				"venta_id_cajero" => $_COOKIE["usuario_id"],
				"venta_monto_entregado"=>$dinero_entrego,
				"venta_codigocliente" => $ventas_pago["cliente"],
				"venta_idmovimiento" => $id_movimiento,
				"venta_credito_usuario" => $_COOKIE["usuario_id"],
				"venta_estadococina"=>$pantallaestado,
				"venta_estado_pagado" => 1,
				"venta_fecha_pago"=>date("Y-m-d H:i:s"),
				"venta_igv_estado"=>$estado_igv	,
				"venta_estado_consumo"=> $comprobante_detalle,
				"venta_igv_monto"=>	$monto_igv	,
				"venta_monto_sinigv"=>$monto_parcial,
				"venta_estado" => 2,
				$nombre_variable => 1,
				"venta_nombre_descripcion"=>$array_cliente[2],
				"venta_documento_descripcion"=>$array_cliente[1],
				"venta_direccion_descripcion"=>$_POST["direccion_descripcio"],
				"ventaid_tipo_venta"=>8
			); 
			$this->db->where('venta_idventas',$id_venta);
			$this->db->update('venta', $ventaupdate);			
		}else{
			$ventainsert = array(
				"venta_fechapedido" => time(), 
				"venta_pedidofecha" => date("Y-m-d H:i:s"),
				"venta_codigomozo" => $id_mozo,
				"venta_idsede" => $_COOKIE["id_sede"],
				"venta_monto" => $monto,
				"venta_id_cajero" => $_COOKIE["usuario_id"],
				"ventas_idtipodocumento"=>$ventas_pago["tipo_comprabante"],
				"venta_tipopago" => $ventas_pago["tipo_pago"],
				"venta_formapago" => $ventas_pago["forma_pago"], 
				'venta_num_serie' =>$serie, 
				'venta_num_documento' => $correlativo, 
				"venta_idmoneda"=>1,
				"venta_estado_pagado" => 1,
				"venta_estado_consumo"=> $comprobante_detalle,
				"venta_monto_entregado"=>$dinero_entrego,
				"venta_estadococina"=>$pantallaestado,
				"venta_codigocliente" => $ventas_pago["cliente"],
				"venta_idmovimiento" => $id_movimiento,
				"venta_credito_usuario" => $_COOKIE["usuario_id"],
				"venta_fecha_pago"=>date("Y-m-d H:i:s"),
				"venta_igv_estado"=>$estado_igv	,
				"venta_igv_monto"=>	$monto_igv	,
				"venta_monto_sinigv"=>$monto_parcial,
				"venta_estado" => 2,
				$nombre_variable => 1,
				"venta_nombre_descripcion"=>$array_cliente[2],
				"venta_documento_descripcion"=>$array_cliente[1],
				"venta_direccion_descripcion"=>$_POST["direccion_descripcio"],
				"ventaid_tipo_venta"=>8
			);
			$this->db->insert('venta', $ventainsert);
			$id_venta = $this->db->insert_id();	
		}

		if ($estado_configuracion == 1) {
			$silla = array(
				'silla_id_empleado' => null,
				'silla_estado' => 0
			);
			$this->db->where('silla_idsilla',$id);
			$estado=$this->db->update('silla', $silla);
		}else{
			$mesa = array(
				'mesa_empleado' => $id_mozo,
				'mesa_disponible' => 0
			);
			$this->db->where('mesa_id',$id);
			$estado=$this->db->update('mesa', $mesa);
		}

		for ($i=0; $i <$cantidadproductos ; $i++) { 
			$tipo_producto=$this->db->query("SELECT producto.producto_id_tipoproducto, producto.producto_stock,producto.producto_stock_temporal FROM producto WHERE producto.producto_id=".$ventas_producto["id_producto"][$i].";")->row();
			if($tipo_producto->producto_id_tipoproducto=='1'){
				$stock_detalle=$this->db->query("SELECT detalle_producto_almacen.detalle_stock, detalle_producto_almacen.detalle_stock_temporal FROM detalle_producto_almacen WHERE detalle_producto_almacen.detalle_producto=".$ventas_producto["id_producto"][$i].";")->row();
				$detalle_almacen= array(
					"detalle_stock" => ($stock_detalle->detalle_stock-$ventas_producto["cantidad"][$i]),
					"detalle_stock_temporal" => $stock_detalle->detalle_stock_temporal + $ventas_producto["cantidad"][$i]
				);
				$this->db->where('detalle_producto',$ventas_producto["id_producto"][$i]);
				$estado=$this->db->update('detalle_producto_almacen', $detalle_almacen);
			}else{ 
				// if ($this->estado_configuracion(11)) {
					$producto=array(
						"producto_stock" => ($tipo_producto->producto_stock-$ventas_producto["cantidad"][$i]),
						"producto_stock_temporal" => $tipo_producto->producto_stock_temporal + $ventas_producto["cantidad"][$i]
					);
					$this->db->where('producto_id',$ventas_producto["id_producto"][$i]);
					$estado=$this->db->update('producto', $producto); 
				// }

				// $this->movimiento_stock_temporal($ventas_producto["id_producto"][$i],$ventas_producto["cantidad"][$i],1,$_COOKIE["id_sede"]);

			}
			$datos = array(
				"descripcion" => $ventas_producto["comentarioventa"][$i],
				"cantidad" => $ventas_producto["cantidad"][$i],
				"precio" => $ventas_producto["precio"][$i],
				"id_venta" => $id_venta,
				"fecha_preparar" => date("Y/m/d"),
				"id_tipoentrega" => $ventas_producto["perfil"],
				"cod_producto_venta" => $ventas_producto["id_producto"][$i]
			);
			$this->db->insert('detalle_venta', $datos);			
		}


		if($ventas_pago["monto_delivery"] > 0){
			$datos = array(
				"descripcion" => "COSTO DE DELIVERY",
				"cantidad" => 1,
				"precio" => $ventas_pago["monto_delivery"],
				"id_venta" => $id_venta,
				"fecha_preparar" => date("Y/m/d"),
				"id_tipoentrega" =>  $ventas_producto["perfil"] ,
				"cod_producto_venta" => 9999
			);
			$this->db->insert('detalle_venta', $datos);
		}


		if($ventas_pago["tipo_pago"]==2){
			$dato=array(
				"venta_credito_estado"=>1,
				"venta_credito_cuotas"=> $ventas_pago["cuotas"]
			);
			$this->db->where('venta_idventas',$id_venta);
			$estado=$this->db->update('venta', $dato);
			foreach ($ventas_pago["nrocuotas"] as $key => $value) {
				$datos=array(
					"cuo_venta"=> $id_venta,
					"cuo_ventanrocuota"=>$value,
					"cuo_ventafechavence"=>$ventas_pago["fechavence"][$key] ,
					"cuo_ventamontocuota"=>$ventas_pago["montocuota"][$key]  ,
					"cuo_ventamontopagado"=>0,
				);
				$this->Mantenimiento_m->insertar("cuotaventa", $datos);
			}
		}
        $ultimo_idcliente="";
		if($ventas_producto["idcliente"]=="")
		{

				$datos_cliente = array(
				"cliente_nombres" => $ventas_producto["nombre"],
				"cliente_direccion" => $ventas_producto["direccion"],
				"cliente_dni" => $ventas_producto["dnidelivery"],
				"cliente_celular" => $ventas_producto["ntelefono"],
				"tipo_cliente_id"=>1
				);	
				$this->db->insert('clientes', $datos_cliente);
				$ultimo_idcliente = $this->db->insert_id();
				if($ventas_producto["iddireccion"]=="")
				{
					$datos_direccion = array(
						"cliente_id" => $ultimo_idcliente,
						"direccion_descripcion" => $ventas_producto["direccion"]
						
						);	
					$this->db->insert('direccion', $datos_direccion);

				}

		}else{

			$ultimo_idcliente = $ventas_producto["idcliente"];
				if($ventas_producto["iddireccion"]=="")
				{
					$datos_direccion = array(
						"cliente_id" => $ultimo_idcliente,
						"direccion_descripcion" => $ventas_producto["direccion"]
						
						);	
					$this->db->insert('direccion', $datos_direccion);

				}

					$datos_cliente = array(
				"cliente_nombres" => $ventas_producto["nombre"],
				"cliente_direccion" => $ventas_producto["direccion"],
				"cliente_dni" => $ventas_producto["dnidelivery"],
				"cliente_celular" => $ventas_producto["ntelefono"],
				"tipo_cliente_id"=>1
				);	


					$this->db->where('cliente_id',$ultimo_idcliente);
					$estado=$this->db->update('clientes', $datos_cliente);


		}

		$datos_delivery = array(
				"idcliente"=>$ultimo_idcliente,
				"numero_celular" => $ventas_producto["ntelefono"],
				"direccion" => $ventas_producto["direccion"],
				"id_venta" => $id_venta,
				"nombre"=>$ventas_producto["nombre"]
			);	
			$this->db->insert('datos_delivery', $datos_delivery);	



 


 
			ob_start();
			// $this->generar_qr($id_venta);
			// if ($this->estado_configuracion(4) == 1) {
			// 	$this->imprimir_comanda($id_venta);
			// }
			
			// $this->imprimir_comprobante($id_venta);
			//$this->facturacion_electronica($id_venta);
			ob_end_clean();

			 if((int)$id_tipo_comprobante!=6){

		 $this->facturacion_electronica($id_venta);

          }
			$enviados["estado"] = 1;

			$enviados["idventa"] = $id_venta;

			//$this->facturacion_electronica($id_venta);
			echo json_encode($enviados);   
			// $this->db->trans_commit();    
			// return true;    
		// }  
	}

	public function detalleventa(){
		$data = array();
		// $estado = $this->estado_configuracion(3);
		$estado = 0;
		$id = $_POST["id"];
		$data["estadoventa"] = 0;
		if ($estado == 1) { // sillas
			$estado_ubicacion=$this->db->query("SELECT silla.silla_estado FROM silla where silla.silla_idsilla =".$id.";")->row();
			$nombre_variable = 'venta_codigosilla';
			$estado_ubicacion = $estado_ubicacion->silla_estado ;
			if ($estado_ubicacion == 1) {
				$consulta_venta = $this->db->query("SELECT venta.venta_idventas,venta.venta_codigomozo,venta.venta_monto FROM venta where venta.venta_codigosilla =".$id." and venta.venta_estado != 2 and venta.venta_estado != 0;")->row();

				$id_venta = $consulta_venta->venta_idventas;
				$id_mozo =$consulta_venta->venta_codigomozo;
				$venta_monto = number_format($consulta_venta->venta_monto,2);
				$data["estadoventa"] = 1;
			}
		}else{ //mesas
			$estado_ubicacion=$this->db->query("SELECT mesa.mesa_disponible FROM mesa where mesa.mesa_id =".$id.";")->row();
			$nombre_variable = 'venta_codigomesa';
			$estado_ubicacion = $estado_ubicacion->mesa_disponible;
			if ($estado_ubicacion == 1) {
				$consulta_venta = $this->db->query("SELECT venta.venta_idventas,venta.venta_codigomozo,venta.venta_monto FROM venta where venta.venta_codigomesa =".$id." and venta.venta_estado != 2 and venta.venta_estado != 0;")->row();
				$id_venta = $consulta_venta->venta_idventas;
				$id_mozo =$consulta_venta->venta_codigomozo;
				$venta_monto = number_format($consulta_venta->venta_monto,2) ;
				$data["estadoventa"] = 1;
			}
		}
		if ($data["estadoventa"] == 1) {
			$data["venta"] = $this->Mantenimiento_m->consulta3("SELECT * FROM venta INNER JOIN empleados ON venta.venta_codigomozo = empleados.empleado_id where venta.venta_idventas =".$id_venta);
			// echo "SELECT * FROM venta INNER JOIN empleados ON venta.venta_codigomozo = empleados.empleado_id where venta.venta_idventas =".$id_venta;exit();
			$data["detalleventa"] = $this->Mantenimiento_m->consulta3("SELECT venta.venta_idventas,detalle_venta.descripcion,detalle_venta.cantidad,detalle_venta.observaciones,detalle_venta.estado_pedido,detalle_venta.detalle_estado_preparado,SUM(detalle_venta.cantidad) as cantidad,
				detalle_venta.cod_producto_venta,detalle_venta.precio,producto.producto_descripcion
				FROM venta
				INNER JOIN detalle_venta ON detalle_venta.id_venta = venta.venta_idventas
				INNER JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id
				WHERE
				venta.venta_idventas =".$id_venta." GROUP BY venta.venta_idventas,detalle_venta.descripcion,detalle_venta.cod_producto_venta ");
			$data["estadoventa"] = 1;
		}
		echo json_encode($data); 
	}


	public function traer_ventas(){
		$cliente=$this->db->query("SELECT clientes.cliente_id FROM clientes where (clientes.cliente_dni = ".$_POST["dni"]." or clientes.cliente_ruc = ".$_POST["dni"]." ) and clientes.empresa_ruc = ".$_COOKIE["ruc_empresa"])->row();
		if (!isset($cliente->cliente_id)){
			$res["estado"] = 0;
		}else{
			$cantidadventascliente = $this->db->query("SELECT COUNT(datos_delivery.id_venta) as cantidadventas from datos_delivery where datos_delivery.idcliente =".$cliente->cliente_id." " )->row();
			
			if($cantidadventascliente->cantidadventas == 0){
				$res["estado"] = 2;
			}else{
				if($this->estado_configuracion(3)==1){
					$res["ventas"]=$this->Mantenimiento_m->consulta3("SELECT  *  FROM
						venta
						INNER JOIN silla ON venta.venta_codigosilla = silla.silla_idsilla
						INNER JOIN empleados ON venta.venta_codigomozo = empleados.empleado_id
						INNER JOIN mesa ON silla.silla_mesa_id = mesa.mesa_id
						INNER JOIN clientes ON clientes.cliente_id = venta.venta_codigocliente
						INNER JOIN datos_delivery ON datos_delivery.id_venta = venta.venta_idventas 
						where venta.venta_idsede=".$_COOKIE["id_sede"]." and (venta.venta_estado=2) and datos_delivery.idcliente = ".$cliente->cliente_id." order by venta.venta_idventas asc LIMIT 5"); 
				}
				else{
					$res["ventas"] =$this->Mantenimiento_m->consulta3(" SELECT *
						FROM
						mesa
						INNER JOIN venta ON mesa.mesa_id = venta.venta_codigomesa
						INNER JOIN clientes ON venta.venta_codigocliente = clientes.cliente_id
						INNER JOIN empleados ON venta.venta_codigomozo = empleados.empleado_id 
						INNER JOIN datos_delivery ON datos_delivery.id_venta = venta.venta_idventas 
						where  venta.venta_idsede=".$_COOKIE["id_sede"]." and (venta.venta_estado!=2 ) and datos_delivery.idcliente = ".$cliente->cliente_id." order by venta.venta_idventas asc LIMIT 5");


				}



				foreach ($res["ventas"] as $key => $value) {
					$mod = $this->db->query("SELECT *
						FROM
						producto
						INNER JOIN detalle_venta ON detalle_venta.cod_producto_venta = producto.producto_id
						INNER JOIN tipo_entrega ON detalle_venta.id_tipoentrega = tipo_entrega.tipoentrega_idtipoentrega
						where detalle_venta.id_venta=".$value["venta_idventas"]." and  detalle_venta.estado_pedido<>0")->result_array();
					$res["ventas"][$key]["lista"] = $mod;
				}
				$res["estado"] = 1;
			}
		}
		echo (json_encode($res));
	}















	   public function facturacion($id)

    {

     //  $data_token = json_decode($this->consultar_token(),true);

 // $response=array();

  $postdata = file_get_contents("php://input");

  $request = json_decode($postdata,true); 

  $response=array();

$ruta = 'http://facturacion.selvafood.com/api/documents';
//produccion
$token = '8lqV6Y8G0eSwG5G0hgthgB8CD1g4N2W5xdVYUoWodrqreh82z9';
//prueba
//$token='8lqV6Y8G0eSwG5G0hgthgB8CD1g4N2W5xdVYUoWodrqreh82z9';


   $sql="SELECT
venta.venta_idventas as 'id',
venta.venta_num_serie  as 'serie',
venta.venta_num_documento as 'documento',
DATE_FORMAT(venta.venta_fecha_pago,'%Y-%m-%d') as 'fecha',
TIME(venta.venta_fecha_pago) as 'hora',
if(ventas_idtipodocumento=1,'01','03') as 'codigo_documento',
venta.venta_documento_descripcion as 'documento_dni',
if(LENGTH(venta.venta_documento_descripcion)=8,'1','6') as 'tam_dni',
venta.venta_direccion_descripcion as 'direccion',
clientes.cliente_telefono as 'telefono',
venta.venta_nombre_descripcion as 'cliente',
clientes.cliente_email as 'correo',
if(venta.venta_cdr_facturacion IS NULL ,'',venta.venta_cdr_facturacion) as 'cdr',
if(venta.venta_xml_facturacion IS NULL ,'',venta.venta_xml_facturacion) as  'xml',
if(venta.venta_pdf_facturacion IS NULL ,'',venta.venta_pdf_facturacion) as 'pdf',
venta.*,
empleados.*
FROM
venta
LEFT JOIN clientes ON venta.venta_codigocliente = clientes.cliente_id
INNER JOIN empleados ON empleados.empleado_id=venta.venta_id_cajero
where venta.venta_idventas=".$id;



        $venta=$this->db->query($sql)->row_array();

















        $detalle=array();

        $sql="select 
producto.producto_descripcion as 'descrip',
detalle_venta.*


from detalle_venta 
INNER JOIN producto ON detalle_venta.cod_producto_venta=producto.producto_id
where detalle_venta.estado_pedido=1 and id_venta=".$id;

        $rec=$this->db->query($sql)->result_array();

        foreach ($rec as $key => $value) {

           $detalle[$key]=array(

              "codigo_interno"=>  "P".$value["id_detalle_venta"],

              "descripcion"=> $value["descrip"],

              "codigo_producto_sunat"=>  "51121703",

              "unidad_de_medida"=>  "NIU",

              "cantidad"=>  $value["cantidad"],

              "valor_unitario"=>  $value["precio"],

              "codigo_tipo_precio"=>  "01",

              "precio_unitario"=>  $value["precio"],

              "codigo_tipo_afectacion_igv"=> "20",

              "total_base_igv"=>  (double)$value["cantidad"]*(double)$value["precio"],

              "porcentaje_igv"=>  18,

              "total_igv"=>  0,

              "total_impuestos"=>  0,

              "total_valor_item"=>  $value["cantidad"]*$value["precio"],

              "total_item"=>  $value["cantidad"]*$value["precio"],

           );

        }



      $empresa=$this->db->query("select * from empresa")->row_array();

$data = array(

   "serie_documento"=> $venta["serie"],

  "numero_documento"=>$venta["documento"],

  "fecha_de_emision"=>  $venta["fecha"],

  "hora_de_emision"=>  $venta["hora"],

  "codigo_tipo_operacion"=>  "0101",

  "codigo_tipo_documento"=>$venta["codigo_documento"],

  "codigo_tipo_moneda"=>  "PEN",

  "fecha_de_vencimiento"=>  $venta["fecha"],

  "numero_orden_de_compra"=>  $venta["id"],

  "datos_del_emisor"=>  array(

    "codigo_pais"=>  "PE",

    "ubigeo"=>  "220901",

    "direccion"=>  $empresa["empresa_direccion"],

    "correo_electronico"=>  $empresa["empresa_correo"],

    "telefono"=> $empresa["empresa_telefono"],

    "codigo_del_domicilio_fiscal"=>  "0000"

  ),  

  "datos_del_cliente_o_receptor"=>array(

    "codigo_tipo_documento_identidad"=> $venta["tam_dni"],

    "numero_documento"=> $venta["documento_dni"],

    "apellidos_y_nombres_o_razon_social"=>$venta["cliente"],

    "codigo_pais"=> "PE",

    "ubigeo"=>"220901",

    "direccion"=> $venta["direccion"],

    "correo_electronico"=>$venta["correo"],

    "telefono"=>"933122626"

  ), "totales"=>array(

    "total_exportacion"=> 0.00,

    "total_operaciones_gravadas"=> 0.00,

    "total_operaciones_inafectas"=> 0.00,

    "total_operaciones_exoneradas"=>$venta["venta_monto"],

    "total_operaciones_gratuitas"=> 0.00,

    "total_igv"=>0.00,

    "total_impuestos"=> 0.00,

    "total_valor"=> $venta["venta_monto"],

    "total_venta"=> $venta["venta_monto"]

  )

,



   "items"=>$detalle,

   "informacion_adicional"=> "OBSERVACIÓN: ".$venta["venta_observaciones"]."|EMITIDO POR:|".$venta["empleado_nombres"]."|".$venta["empleado_usuario"]

);





//print_r($data);exit();

$data_json = json_encode($data);



//echo $data_json;exit();

$curl = curl_init();



curl_setopt_array($curl, array(

  CURLOPT_URL => $ruta ,

  CURLOPT_RETURNTRANSFER => true,

  CURLOPT_ENCODING => "",

  CURLOPT_MAXREDIRS => 10,

  CURLOPT_TIMEOUT => 30,

  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

  CURLOPT_CUSTOMREQUEST => "POST",

  CURLOPT_POSTFIELDS => $data_json,

  CURLOPT_HTTPHEADER => array(

    "authorization: Bearer ".$token,

    "cache-control: no-cache",

    "content-type: application/json",

   

  ),

));



$response = curl_exec($curl);

$err = curl_error($curl);



curl_close($curl);



if ($err) {

  echo "cURL Error #:" . $err;

} else {

   $json=json_decode($response,true);

  //print_r($json);exit();





     $filename = $json["links"]["pdf"]; 

   // $origen   = fopen($filename, 'r');

    //$nombre_pdf=$this->generar_token_seguro(30);

   // $destino1 = fopen('public/factura/'.$nombre_pdf.'.pdf', 'w');



     //stream_copy_to_stream($origen, $destino1); 





   $data=array(

       "venta_pdf_facturacion"=>$json["links"]["pdf"],

       "venta_xml_facturacion"=>$json["links"]["xml"],

       "venta_cdr_facturacion"=>$json["links"]["cdr"],

      // "venta_ticket_facturacion"=>$nombre_pdf,

       "venta_ticket_external_id"=>$json["data"]["external_id"],
       "venta_qr"=>$json["data"]["qr"],

       "venta_hash"=>$json["data"]["hash"],
       "venta_number_to_letter"=>$json["data"]["number_to_letter"]


   );



     $this->db->where("venta_idventas",$id);

     $r = $this->db->update("venta",$data);
//print_r($json);exit();



     return $json["links"]["pdf"];


  // echo json_encode($json);exit();

}







    }

}
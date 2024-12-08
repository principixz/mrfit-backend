<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";
require_once("src/autoload.php");
require_once("convertirAletra.php");

class Pedido extends BaseController {

	public function __construct() {
		parent::__construct();
	}
	public function index(){
		$data["titulo_descripcion"]="Lista de Pedidos";
		$data["estado_de venta"]=0;
		$data["formapago"]=$this->Mantenimiento_m->consulta3("SELECT * from formapago");
		$data["tipopago"]=$this->Mantenimiento_m->consulta3("SELECT * from tipo_pago");
		$data["tipo_documento"]=$this->Mantenimiento_m->consulta3("SELECT * FROM
			tipo_documento INNER JOIN documento ON documento.id_tipodocumento = tipo_documento.tipodoc_id
			INNER JOIN detalle_doc_sede ON detalle_doc_sede.detalle_id_documento = documento.id_documento
			where detalle_id_sede=".$_COOKIE["id_sede"]." and tipo_documento.tipodoc_id IN (1,2,6) ");
		$data["empresa"]=$this->Mantenimiento_m->consulta3("SELECT * FROM empresa LIMIT 1 ");
		$data["delivery"]=$this->Mantenimiento_m->consulta3("SELECT * from empleados where empresa_sede=".$_COOKIE["id_sede"]." and perfil_id=13 and estado=1");

		$data["tipo_documento_cliente"]=$this->db->query("select * from tipo_cliente where tipo_cliente_estado=1")->result_array();
		$data["estado_impresion"]=1;
		$this->vista("Pedido/index",$data);
	}

	public function generar_ventas(){	
		$data=$this->Mantenimiento_m->consulta3("SELECT *,
		buscar_mesas_hijo(mesa.mesa_id)	as 'mesas_agrupas'
			FROM
			mesa
			INNER JOIN venta ON mesa.mesa_id = venta.venta_codigomesa
			LEFT JOIN clientes ON venta.venta_codigocliente = clientes.cliente_id
			INNER JOIN empleados ON venta.venta_codigomozo = empleados.empleado_id 
			where mesa.mesa_numero like '%".$_POST["buscar"]."%' and  venta.venta_idsede=".$_COOKIE["id_sede"]." and venta.venta_estado=1  and venta.venta_fecha_pago  is  null order by venta.venta_idventas asc");  	
		
		foreach ($data as $key => $value) {
			$mod = $this->db->query("SELECT *
				FROM
				producto
				INNER JOIN detalle_venta ON detalle_venta.cod_producto_venta = producto.producto_id
				where detalle_venta.id_venta=".$value["venta_idventas"]." and detalle_venta.estado_pedido=1")->result_array();
			$data[$key]["lista"] = $mod;

			$datos=$this->Mantenimiento_m->consulta3("select * from cuentas_venta_temporal where cuentas_idventas=".$value["venta_idventas"]);
			if(count($datos)>0){
				$data[$key]["pago_multiple"]=1;
			}else{
				$data[$key]["pago_multiple"]=0;
			}			
		}
		echo json_encode($data);
	}

	public function generar_boleta(){
		$dat=$this->Mantenimiento_m->consulta3("SELECT * FROM detalle_doc_sede
			INNER JOIN documento ON detalle_doc_sede.detalle_id_documento = documento.id_documento
			INNER JOIN tipo_documento ON documento.id_tipodocumento = tipo_documento.tipodoc_id
			where detalle_doc_sede.detalle_id_sede=".$_COOKIE["id_sede"]." and documento.id_tipodocumento=".$_POST["tipo_documento"]);
		if (count($dat)>0){
			$dat["estado"] = 1;
			$dat["correlativo"] = $dat[0]["doc_serie"]."-".$dat[0]["doc_correlativo"];
		}else{
			$dat["estado"] = 0;
		}

		echo json_encode($dat);
	}
	public function buscar_cliente1(){	
		$id=$this->input->get("id");
		$total = $this->input->get("totalpago");
		$tipo_pago=$this->input->get("tipo_pago");
		$sql_insertar="";
		if($tipo_pago==2){
			$sql_insertar=" and cliente_id<>1";
		}
		 if ($id == "1" || $id == "4" ) {
			 $sql="SELECT cliente_id,cliente_nombres, (IF(cliente_dni is null,'No cuenta con D.N.I',cliente_dni)) as cliente_dni  from clientes where LENGTH(cliente_dni) =11 and (estado=1   and (cliente_nombres LIKE '%".$this->input->get("q")."%' or cliente_dni LIKE '%".$this->input->get("q")."%' or cliente_celular LIKE '%".$this->input->get("q")."%'  ) ".$sql_insertar.") limit 10";
		}else{
			if($id != "6"){
				if ($id == "2" and $total > 700) {
				 	 $sql="SELECT  cliente_id,cliente_nombres, (IF(cliente_dni is null,'No cuenta con D.N.I',cliente_dni)) as cliente_dni from clientes where (estado=1 and clientes.cliente_id!=1 and (LENGTH(cliente_dni) = 8 or LENGTH(cliente_dni) = 11  ) and  (cliente_nombres LIKE '%".$this->input->get("q")."%' or cliente_dni LIKE '%".$this->input->get("q")."%' or cliente_celular LIKE '%".$this->input->get("q")."%' ) ".$sql_insertar.") limit 10";
				}else{
					 $sql="SELECT  cliente_id,cliente_nombres, (IF(cliente_dni is null,'No cuenta con D.N.I',cliente_dni)) as cliente_dni from clientes where ((estado=1  and (LENGTH(cliente_dni) = 8 or LENGTH(cliente_dni) = 11) and (cliente_nombres LIKE '%".$this->input->get("q")."%' or cliente_dni LIKE '%".$this->input->get("q")."%' or cliente_celular LIKE '%".$this->input->get("q")."%' ) ".$sql_insertar.") ) limit 10";
				}
			}else{
				$sql="SELECT cliente_id,cliente_nombres, (IF(cliente_dni is null,'No cuenta con D.N.I',cliente_dni)) as cliente_dni  from clientes where (estado=1 and (cliente_nombres LIKE '%".$this->input->get("q")."%' or cliente_dni LIKE '%".$this->input->get("q")."%' or cliente_celular LIKE '%".$this->input->get("q")."%' ) ".$sql_insertar.") limit 10";
			}
		}
		

				//$sql="SELECT cliente_id,cliente_nombres, (IF(cliente_dni is null,'No cuenta con D.N.I',cliente_dni)) as cliente_dni  from clientes where (estado=1 and (cliente_nombres LIKE '%".$this->input->get("q")."%' or cliente_dni LIKE '%".$this->input->get("q")."%' or cliente_celular LIKE '%".$this->input->get("q")."%' ) ".$sql_insertar.") limit 10";
		$data=$this->Mantenimiento_m->consulta3($sql);
		$data1=array();
		foreach ($data as $key => $value) {
			$sql="select * from direccion where cliente_id=".$value["cliente_id"]." and direccion_estado=1 order by direccion_id desc";
			$direcciones=$this->db->query($sql)->row_array();
		//	$direccion=$this->db->query($sql);
			$data1["results"][$key]["id"]=$value["cliente_id"]."/".$value["cliente_dni"]."/".$value["cliente_nombres"]."/".$direcciones["direccion_id"]."/".$direcciones["direccion_descripcion"];
			$data1["results"][$key]["text"]=$value["cliente_nombres"];
			$data1["results"][$key]["documento"]=$value["cliente_dni"];
		}
		echo json_encode($data1);

	}


/*public function mostrar_direccion_cliente()
	{
		// Get search term
		$term = $_GET['term'];
	   // $cliente=$_GET['cliente'];
	    $sql="SELECT 
direccion.direccion_id as 'id',
direccion.direccion_descripcion as 'label',
direccion.direccion_descripcion as 'value'
from direccion
where 
direccion.cliente_id=".$cliente." and
direccion.direccion_estado=1 and
direccion.direccion_descripcion like '%".$term."%' order by direccion_id desc ";
	    $datos=$this->db->query($sql)->result_array();
      // echo $term;
	    echo json_encode($datos);exit();
	}*/
		public function buscar_cliente2(){	
		$term = $_GET['term'];
			$sql="SELECT cliente_celular,cliente_id,cliente_nombres, (IF(cliente_dni is null,'No cuenta con D.N.I',cliente_dni)) as cliente_dni  from clientes where cliente_id<>1 and (estado=1   and (cliente_nombres LIKE '%".$term."%' or cliente_dni LIKE '%".$term."%' or cliente_celular LIKE '%".$term."%'  ) ) limit 10";
		

		$data=$this->Mantenimiento_m->consulta3($sql);
		$data1=array();
		foreach ($data as $key => $value) {
			$sql="select * from direccion where cliente_id=".$value["cliente_id"]." and direccion_estado=1 order by direccion_id desc";
			$direcciones=$this->db->query($sql)->row_array();
		

			$data1[$key]["id"]=$value["cliente_id"]."/".$value["cliente_dni"]."/".$value["cliente_nombres"]."/".$direcciones["direccion_id"]."/".$direcciones["direccion_descripcion"];
			$data1[$key]["label"]=$value["cliente_celular"]." | ".$value["cliente_nombres"];
			$data1[$key]["value"]=$value["cliente_celular"];

		}
		echo json_encode($data1);

	}
	public function eliminar_pedido(){
		if(!empty($_POST["eliminar_usuario_total"])){
			$administrador = array();
			$administrador = $datos_venta = $this->Mantenimiento_m->consulta3("SELECT id_usuarioe as empleado_id FROM usuario_eliminar WHERE usuario_eli = '".$_POST['eliminar_usuario_total']."'  and clave_eli = '".$_POST['eliminar_contrasena_total']."';");
			if(count($administrador)==0){
				echo 0;
				exit();
			}
		}
		$id_venta=$_POST["id_detalle_venta"];
		$motivo=$_POST["motivo_modal"];
		$data=array(
			"venta_estado"=>"0",
			"venta_fecha_eliminacion"=>date("Y-m-d H:i:s"),
			"venta_empleado_eliminacion"=>$_COOKIE["usuario_id"],
			"venta_eliminacion_descripcion"=>$motivo
		);

		$this->db->where('venta_idventas', $id_venta);
		$this->db->update('venta', $data);
		$datos=$this->Mantenimiento_m->consulta3("select * from detalle_venta where id_venta=".$id_venta);
		$tipo_producto = array();
		$stock_detalle = array();
		$idproducto = array();
		foreach ($datos as $key => $value) {

			$data=array(
				"observacion_eliminado"=>$motivo,
				"estado_pedido"=>0,
				"detalle_usuario_eliminado"=>$_COOKIE["usuario_id"],
				"detalle_venta_fecha_eliminar"=>date("Y-m-d H:i:s")
			);
			$this->db->where('id_detalle_venta',$value["id_detalle_venta"]);
			$this->db->update('detalle_venta', $data);
			$idproducto = $this->db->query("SELECT detalle_venta.cod_producto_venta, detalle_venta.cantidad,detalle_venta.precio FROM detalle_venta WHERE detalle_venta.id_detalle_venta=".$value["id_detalle_venta"].";")->row();
			$tipo_producto=$this->db->query("SELECT producto.producto_id_tipoproducto, producto.producto_stock,producto.producto_stock_temporal FROM producto WHERE producto.producto_id=".$idproducto->cod_producto_venta.";")->row();
			if($tipo_producto->producto_id_tipoproducto==1){
				$almacen_id = $this->Mantenimiento_m->consulta2("SELECT almacen_id FROM almacenes where almacenes.id_sede = ".$_COOKIE["id_sede"]." and almacen_uso = 1 ");
				$id_almacen=$this->Mantenimiento_m->consulta2("SELECT detalle_producto_almacen.detalle_almacen as id_almacen FROM detalle_producto_almacen WHERE detalle_producto_almacen.detalle_producto=".$idproducto->cod_producto_venta." AND detalle_producto_almacen.detalle_almacen = ".$almacen_id->almacen_id.";");
 				$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida
 					INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida 
 					WHERE producto.producto_id = ".$idproducto->cod_producto_venta);
				$this->movimiento_stock_temporal($id_almacen->id_almacen,$idproducto->cod_producto_venta,$id_unidad_medida->detalle_unidad_producto_id,$idproducto->cantidad,$idproducto->precio,1,2);

			}else{
 				//$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida WHERE producto.producto_id = ".$idproducto->cod_producto_venta);
				$this->movimiento_stock_tplatos($idproducto->cod_producto_venta,null,$idproducto->cantidad,$idproducto->precio,1,2);
								
			}


		}
		$dat=$this->Mantenimiento_m->consulta3("select * from venta where venta_idventas=".$id_venta);
			$id_mesa=$dat[0]["venta_codigomesa"];
			$data=array(
				"mesa_disponible"=>"0"
			);
			$this->Mantenimiento_m->actualizar("mesa",$data,  $id_mesa,"mesa_id");
		
		echo 1;
	}
	public function preguntar_configuracion(){
		echo 1;
	}
	public function eliminar_pedido_detalle(){
		if(!empty($_POST["eliminar_usuario"])){
			$administrador = array();
			$administrador= $datos_venta=$this->Mantenimiento_m->consulta3("SELECT empleados.empleado_id FROM empleados WHERE empleados.perfil_id=12 AND empleados.empleado_usuario='".$_POST['eliminar_usuario']."' AND empleados.empleado_clave='".$_POST['eliminar_contrasena']."' AND empleados.empresa_sede=".$_COOKIE["id_sede"]);
			if(count($administrador)==0){
				return 1;
			}
		}
		$motivo=$_POST["motivo"];
		$id_detalle_venta=$_POST["id_detalle_venta_modal"];
		$sede=$this->Mantenimiento_m->consulta3("select * from sede where id_sede=".$_COOKIE["id_sede"]);
		$ruc=$this->Mantenimiento_m->consulta2("SELECT empresa_ruc FROM empresa LIMIT 1");
		$ruc = $ruc->empresa_ruc;
		$existes = 1;
		$tipo_producto = array();
		$stock_detalle = array();
		$idproducto = array();
		if(($existes)==1){
			$data=array(
				"observacion_eliminado"=>$motivo,
				"estado_pedido"=>0,
				"detalle_usuario_eliminado"=>$_COOKIE["usuario_id"],
				"detalle_venta_fecha_eliminar"=>date("Y-m-d H:i:s")
			);
			$this->db->where('id_detalle_venta',$id_detalle_venta);
			$this->db->update('detalle_venta', $data);
			$idproducto = $this->db->query("SELECT detalle_venta.cod_producto_venta, detalle_venta.cantidad,detalle_venta.precio FROM detalle_venta WHERE detalle_venta.id_detalle_venta=".$_POST["id_detalle_venta_modal"].";")->row();
			$tipo_producto=$this->db->query("SELECT producto.producto_id_tipoproducto, producto.producto_stock,producto.producto_stock_temporal FROM producto WHERE producto.producto_id=".$idproducto->cod_producto_venta.";")->row();
			if($tipo_producto->producto_id_tipoproducto==1){
				$almacen_id = $this->Mantenimiento_m->consulta2("SELECT almacen_id FROM almacenes where almacenes.id_sede = ".$_COOKIE["id_sede"]." and almacen_uso = 1 ");
				$id_almacen=$this->Mantenimiento_m->consulta2("SELECT detalle_producto_almacen.detalle_almacen as id_almacen FROM detalle_producto_almacen WHERE detalle_producto_almacen.detalle_producto=".$idproducto->cod_producto_venta." AND detalle_producto_almacen.detalle_almacen = ".$almacen_id->almacen_id.";");
 				$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida
 					INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida 
 					WHERE producto.producto_id = ".$idproducto->cod_producto_venta);
				$this->movimiento_stock_temporal($id_almacen->id_almacen,$idproducto->cod_producto_venta,$id_unidad_medida->detalle_unidad_producto_id,$idproducto->cantidad,$idproducto->precio,1,2);
			}else{
 				//$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida WHERE producto.producto_id = ".$idproducto->cod_producto_venta);
				$this->movimiento_stock_tplatos($idproducto->cod_producto_venta,null,$idproducto->cantidad,$idproducto->precio,1,2);		
			}
			$detalle=$this->Mantenimiento_m->consulta3("select * from detalle_venta where id_detalle_venta=".$id_detalle_venta);

			$activo_detalle=$this->Mantenimiento_m->consulta3("select * from detalle_venta where estado_pedido=1 and id_venta=".$detalle[0]["id_venta"]);
			$total=0;
			foreach ($activo_detalle as $key => $value) {
				$total+=(float)$value["cantidad"]*(float)$value["precio"];
			}
			$venta=$this->Mantenimiento_m->consulta3("select * from venta where venta_idventas=".$detalle[0]["id_venta"]);
			$total=$total+(float)$venta[0]["venta_monto_delivery"];
			$data=array(
				"venta_monto"=> $total
			);
			$this->db->where('venta_idventas',$detalle[0]["id_venta"]);
			$this->db->update('venta', $data);
			$venta_total=$this->Mantenimiento_m->consulta3("select count(id_detalle_venta) as numero from detalle_venta where id_venta=".$detalle[0]["id_venta"]);
			$eliminar_total=$this->Mantenimiento_m->consulta3("select count(id_detalle_venta) as numero from detalle_venta where estado_pedido=0 and id_venta=".$detalle[0]["id_venta"]);
			$cocinado=$this->Mantenimiento_m->consulta3("select count(id_detalle_venta) as numero from detalle_venta where estado_pedido=0 and id_venta=".$detalle[0]["id_venta"]);
			if($venta_total[0]["numero"]==$eliminar_total[0]["numero"]){
				$data=array(
					"venta_estado"=>"0",
					"venta_fecha_eliminacion"=>date("Y-m-d H:i:s"),
					"venta_empleado_eliminacion"=>$_COOKIE["usuario_id"]
				);
				$this->db->where('venta_idventas',$detalle[0]["id_venta"]);
				$this->db->update('venta', $data);
				$dat=$this->Mantenimiento_m->consulta3("select * from venta where venta_idventas=".$detalle[0]["id_venta"]);
					$id_mesa=$dat[0]["venta_codigomesa"];
					$data=array(
						"mesa_disponible"=>"0"
					);
					$this->Mantenimiento_m->actualizar("mesa",$data,  $id_mesa,"mesa_id");
			}
			else{  
				$sql="select count(id_detalle_venta) as numero from detalle_venta where detalle_estado_preparado=1 and estado_pedido=1 and id_venta=".$detalle[0]["id_venta"];
				$cantidad_detalle_activo=$this->Mantenimiento_m->consulta3($sql);
				if($cantidad_detalle_activo[0]["numero"]==0){
					$data=array(
						"venta_estadococina"=>2,
						"venta_fecha_preparacion"=>date("Y-m-d H:i:s")
					);
					$this->db->where('venta_idventas',$detalle[0]["id_venta"]);
					$this->db->update('venta', $data);
				}
			}
			echo 1;
		}
		else{
			echo 2;
		}
	}
	public function procesar_venta(){
		$monto=$_POST["monto_venta"];
		if(isset($_POST["forma_pago"])){
			$formapago=$_POST["forma_pago"];
		}else{
			$formapago=1;
		}
 
		$concepto=1;
		$descripcion="COBRO DE VENTA DE PRODUCTOS";
		$tipomovimiento=1;
		$id_tipo_comprobante=$_POST["tipo_comprabante"];
		$descripcion_comprobante="";
		$serie="";
		$correlativo="";
		$id_documento="";
		$nuevo_correlativo=0;
		$respuesta=array();
		$id_movimiento=0;
		$id_movimiento_transporte=0;
		$dinero_entrego=$_POST["dinero_entregado"];
		$id_venta=$_POST["id_modal_venta"];

		$verificar=$this->Mantenimiento_m->consulta3("select * from venta where venta.venta_idventas=".$id_venta." and venta.venta_estado=2");
 		$deliverymonto = $_POST["monto_delivery"];
		if(count($verificar)>0)
		{
			$respuesta["estado"]=2;
			$respuesta["texto"]="LO SENTIMOS ESTA VENTA YA FUE PROCESADA :(";
			echo json_encode($respuesta);
			exit();

		}
		$verificar=$this->Mantenimiento_m->consulta3("select * from venta where venta.venta_idventas=".$id_venta." and venta.venta_estado=0");
		if(count($verificar)>0)
		{
			$respuesta["estado"]=2;
			$respuesta["texto"]="LO SENTIMOS ESTA VENTA YA FUE ELIMINADA :(";
			echo json_encode($respuesta);
			exit();

		}

		$id_cliente=$_POST["cliente"];
		$array_cliente = explode("/", $id_cliente);

		$Nota_pago=$_POST["note"];
 $comprobante_detalle=0;
		if(isset($_POST["comprobante_detalle"]))
	     {
                $comprobante_detalle=1;
	     }



		if($formapago==1)
		{
			$id_caja=1;
		}else{
			$id_caja=2;
		}

		$descripcion_comprobante="";

		if($_POST["tipo_pago"]==1)
		{

			$id_movimiento=$this->generar_movimiento($id_caja,$monto,$formapago,$concepto,$descripcion,$tipomovimiento,$id_tipo_comprobante,
				$descripcion_comprobante);
			if($id_movimiento==0){
				$respuesta["estado"]=2;
				$respuesta["texto"]="No se pudo generar el movimiento porque usted no abrio caja";
				echo json_encode($respuesta);
				exit();
			}
			else{
				$id_movimiento;
			}

		}
		else{

			$dato=array(
				"venta_credito_estado"=>1,
				"venta_credito_cuotas"=>$_POST["cuotas"]

			);

			$this->db->where('venta_idventas',$id_venta);
			$estado=$this->db->update('venta', $dato);


			foreach ($_POST["nrocuotas"] as $key => $value) {
				$datos=array(
					"cuo_venta"=> $id_venta,
					"cuo_ventanrocuota"=>$value,
					"cuo_ventafechavence"=>$_POST["fechavence"][$key] ,
					"cuo_ventamontocuota"=>$_POST["montocuota"][$key]  ,
					"cuo_ventamontopagado"=>0,

				);
				$this->Mantenimiento_m->insertar("cuotaventa", $datos);
			}
			$id_movimiento=0;




		}
		if($id_tipo_comprobante!=0){
			$dat=$this->Mantenimiento_m->consulta3("SELECT * 
				FROM
				detalle_doc_sede
				INNER JOIN documento ON detalle_doc_sede.detalle_id_documento = documento.id_documento
				INNER JOIN tipo_documento ON documento.id_tipodocumento = tipo_documento.tipodoc_id
				where detalle_doc_sede.detalle_id_sede=".$_COOKIE["id_sede"]." and documento.id_tipodocumento=".$id_tipo_comprobante);
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
		if(isset($_POST["igv"])){			
			$igv=(float)$_POST["igv"];
			$estado_igv="";
			$monto_igv=0;
			$monto_parcial=0;
		}else{
			$igv=0;
			$estado_igv="";
			$monto_igv=0;
			$monto_parcial=0;		
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


		if($_POST["tipo_pago"]==1)
		{
 
			$data=array(
				'venta_num_serie' =>$serie , 
				'venta_num_documento' => $correlativo, 
				'venta_estado' => 2,
				"venta_monto" => $monto,
				"ventas_idtipodocumento"=>$id_tipo_comprobante,
				"venta_tipopago"=>$_POST["tipo_pago"],
				"venta_credito_usuario"=>$_COOKIE["usuario_id"],
				"venta_fecha_pago"=>date("Y-m-d H:i:s"),
				"venta_monto_entregado"=>$dinero_entrego,
				"venta_id_cajero" => $_COOKIE["usuario_id"],
				"venta_idmoneda"=>1,
				"venta_estado_pagado" => 1,
				"venta_formapago" => $_POST["forma_pago"],
				"venta_igv_estado"=>$estado_igv	,
				"venta_idmovimiento" => $id_movimiento ,
				"venta_igv_monto"=>	$monto_igv	,
				"venta_monto_sinigv"=>$monto_parcial,
				"venta_codigocliente"=>$id_cliente,
				// "venta_empleado_deliverista"=>$_POST["id_delivery"],
				"venta_estado_pagado" => 1,
				"venta_monto_delivery"=>0,
				"venta_estado_consumo"=> $comprobante_detalle,
				"venta_nombre_descripcion"=>$array_cliente[2],
				"venta_documento_descripcion"=>$array_cliente[1],
				"venta_direccion_descripcion"=>$_POST["direccion_descripcion"]


			);
		}else{

            $formapago=null;
			if (isset($_POST["forma_pago"])) {
				$formapago=$_POST["forma_pago"];
			}
			
			$data=array(
				'venta_num_serie' =>$serie , 
				'venta_num_documento' => $correlativo, 
				'venta_estado' => 2,
				"venta_tipopago"=>$_POST["tipo_pago"],
				"ventas_idtipodocumento"=>$id_tipo_comprobante,
				"venta_credito_usuario"=>$_COOKIE["usuario_id"],
				"venta_id_cajero" => $_COOKIE["usuario_id"],
				"venta_fecha_pago"=>date("Y-m-d H:i:s"),
				"venta_monto_entregado"=>$dinero_entrego,
				"venta_idmoneda"=>1,
				"venta_monto" => $monto,
				"venta_formapago" =>$formapago,
				"venta_estado_pagado" => 1,
				"venta_igv_estado"=>$estado_igv	,
				"venta_estado_pagado" => 1,
				"venta_igv_monto"=>	$monto_igv	,
				"venta_monto_sinigv"=>$monto_parcial,
				"venta_codigocliente"=>$id_cliente,
				"venta_estado_consumo"=> $comprobante_detalle,
			"venta_nombre_descripcion"=>$array_cliente[2],
				"venta_documento_descripcion"=>$array_cliente[1],
				"venta_direccion_descripcion"=>$_POST["direccion_descripcion"]
			);




		}

		$this->Mantenimiento_m->actualizar("venta",$data,  $id_venta,"venta_idventas");


		$dat=$this->Mantenimiento_m->consulta3("select * from venta where venta_idventas=".$id_venta);



			$id_mesa=$dat[0]["venta_codigomesa"];

			$data=array(
				"mesa_disponible"=>"0"


			);
			$this->Mantenimiento_m->actualizar("mesa",$data,  $id_mesa,"mesa_id");

		


   // actualizar stock y kardex

		$detalle_venta=$this->Mantenimiento_m->consulta3("SELECT detalle_venta.id_detalle_venta, detalle_venta.cod_producto_venta,producto.producto_id_tipoproducto,
			detalle_venta.precio,detalle_venta.cantidad
			FROM detalle_venta
			INNER JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id  where id_venta=".$id_venta." and detalle_venta.estado_pedido<>0");

		foreach ($detalle_venta as $key => $value){		
			$tipo_producto=$this->db->query("SELECT producto.producto_id_tipoproducto, producto.producto_stock,producto.producto_stock_temporal FROM producto WHERE producto.producto_id=".$value["cod_producto_venta"].";")->row();
			if($tipo_producto->producto_id_tipoproducto==1){	
				$almacen_id = $this->Mantenimiento_m->consulta2("SELECT almacen_id FROM almacenes where almacenes.id_sede = ".$_COOKIE["id_sede"]." and almacen_uso = 1 ");
				$id_almacen=$this->Mantenimiento_m->consulta2("SELECT detalle_producto_almacen.detalle_almacen as id_almacen FROM detalle_producto_almacen WHERE detalle_producto_almacen.detalle_producto=".$value["cod_producto_venta"]." AND detalle_producto_almacen.detalle_almacen = ".$almacen_id->almacen_id.";");
 				$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida
 					INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida 
 					WHERE producto.producto_id = ".$value["cod_producto_venta"]);
				$this->movimiento_stock_temporal($id_almacen->id_almacen,$value["cod_producto_venta"],$id_unidad_medida->detalle_unidad_producto_id,$value["cantidad"],$value["cantidad"],2,3,$serie,$correlativo,$id_tipo_comprobante);
			}else{ 
				//$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida WHERE producto.producto_id = ".$value["cod_producto_venta"]);
				$this->movimiento_stock_tplatos($value["cod_producto_venta"],null,$value["cantidad"],$value["precio"],2,3);
			}


			//$this->venta_actualizacion_stock($value["cod_producto_venta"],$value["cantidad"],$value["precio"],$serie ,$correlativo,$id_tipo_comprobante);
		}


        if($_POST["monto_delivery"] > 0){
			$datos = array(
				"descripcion" => "COSTO DE DELIVERY",
				"cantidad" => 1,
				"precio" => $_POST["monto_delivery"],
				"id_venta" => $id_venta,
				"fecha_preparar" => date("Y/m/d"),
				"id_tipoentrega" =>  3,
				"cod_producto_venta" => 9999
			);
			$this->db->insert('detalle_venta', $datos);
		}
		//$this->generar_qr($id_venta);
		//$this->facturacion_electronica($id_venta);





		//$this->imprimir_comanda($id_venta);

		//if ($this->db->trans_status() === FALSE){     
		//	$this->db->trans_rollback();      
		//	return false;    
		//}else{    
			ob_start();
			//$this->generar_qr($id_venta);
			//$this->imprimir_comprobante($id_venta);
			//$this->facturacion_electronica($id_venta);
			//ob_end_clean();
            $this->anular_agrupacion_mesas($id_mesa);
			 if((int)$id_tipo_comprobante!=6){

		 $this->facturacion_electronica($id_venta);

          }
		//	 $this->facturacion_electronica($id_venta);
			$respuesta["estado"]=1;
			$respuesta["texto"]="El cobro de venta se registro correctamente";
			echo json_encode($respuesta);   
			$this->db->trans_commit();    
			return true;    
		//}  



	}
	public function guardar_cliente(){
          $nombre="";
          $direccion="";
          $celular="";
		if(isset($_POST["nombre"]))
	     {
           $nombre=$_POST["nombre"];
	     }else{
	     	$nombre=$_POST["nombre_nuevo"];
	     }
		

		if(isset($_POST["direccion"]))
	     {
           $direccion=$_POST["direccion"];
	     }else{
	     	$direccion=$_POST["direccion_nuevo"];
	     }
		


		if(isset($_POST["celular"]))
	     {
           $celular=$_POST["celular"];
	     }else{
	     	$celular=$_POST["celular_nuevo"];
	     }
		
		$data=array(
			"cliente_nombres"=>$nombre,
			"cliente_direccion"=>$direccion,
			"cliente_dni"=>$_POST["ruc"],
			"cliente_celular"=>$celular,
			"empresa_ruc"=>$_COOKIE["ruc_empresa"],
			"tipo_cliente_id"=>$_POST["tipo_documento_id"],
			"cliente_email"=>$_POST["correo"]

		);

		$this->Mantenimiento_m->insertar("clientes",$data);
		$ultimoId = $this->db->insert_id();
		$data=array(
			"cliente_id"=>$ultimoId,
			"direccion_descripcion"=>$_POST["direccion"],

		);

		$this->Mantenimiento_m->insertar("direccion",$data);
		echo 1;
	}
	public function estock_actualizar(){
		$tipo_producto = array();
		$idproducto = array();
		$stock_total=0;
		$idproducto = $this->db->query("SELECT detalle_venta.cod_producto_venta, detalle_venta.cantidad FROM detalle_venta WHERE detalle_venta.id_detalle_venta=".$_POST["id"].";")->row();
		$tipo_producto=$this->db->query("SELECT producto.producto_id_tipoproducto, producto.producto_stock,producto.producto_stock_temporal FROM producto WHERE producto.producto_id=".$idproducto->cod_producto_venta.";")->row();
		if($tipo_producto->producto_id_tipoproducto==1){
			$stock=$this->db->query("SELECT detalle_producto_almacen.detalle_stock as stock FROM detalle_producto_almacen WHERE detalle_producto_almacen.detalle_producto=".$idproducto->cod_producto_venta.";")->row();
			$stock_total=$stock->stock;
		}else{
			$stock_total=$this->db->query("SELECT producto.producto_stock as stock FROM producto WHERE producto.producto_id=".$idproducto->cod_producto_venta.";")->row();
			$stock_total=$stock_total->stock;
			//$stock_total=$this->extraer_stock_plato($idproducto->cod_producto_venta,$_COOKIE["id_sede"]);


		} 
		echo $stock_total;
	}

	public function editar_datos(){  
		$administrador = $datos_venta = $this->Mantenimiento_m->consulta3("SELECT id_usuarioe as empleado_id FROM usuario_eliminar WHERE usuario_eli = '".$_POST['usuario']."'  and clave_eli = '".$_POST['contrasena']."';");
		if(count($administrador)==0){
			echo 0;
			exit();
		} 
		$detalle_venta=$this->Mantenimiento_m->consulta3("SELECT detalle_venta.id_detalle_venta, detalle_venta.cod_producto_venta,producto.producto_id_tipoproducto,
			detalle_venta.precio,detalle_venta.cantidad
			FROM detalle_venta
			INNER JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id  where id_detalle_venta=".$_POST["id_detalle_venta"]." and detalle_venta.estado_pedido<>0");
 
		foreach ($detalle_venta as $key => $value){	

			$cantidad_nueva=(float)$_POST["cantidad"]; 
			$tipo_producto=$this->db->query("SELECT producto.producto_id_tipoproducto, producto.producto_stock,producto.producto_stock_temporal FROM producto WHERE producto.producto_id=".$value["cod_producto_venta"].";")->row();
			if($tipo_producto->producto_id_tipoproducto==1){	
				$almacen_id = $this->Mantenimiento_m->consulta2("SELECT almacen_id FROM almacenes where almacenes.id_sede = ".$_COOKIE["id_sede"]." and almacen_uso = 1 ");
				$id_almacen=$this->Mantenimiento_m->consulta2("SELECT detalle_producto_almacen.detalle_almacen as id_almacen FROM detalle_producto_almacen WHERE detalle_producto_almacen.detalle_producto=".$value["cod_producto_venta"]." AND detalle_producto_almacen.detalle_almacen = ".$almacen_id->almacen_id.";");
 				$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida
 					INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida 
 					WHERE producto.producto_id = ".$value["cod_producto_venta"]);
 				$this->movimiento_stock_temporal($id_almacen->id_almacen,$value["cod_producto_venta"],$id_unidad_medida->detalle_unidad_producto_id,$value["cantidad"],$value["precio"],1,2,null,null,null);
				$this->movimiento_stock_temporal($id_almacen->id_almacen,$value["cod_producto_venta"],$id_unidad_medida->detalle_unidad_producto_id,$cantidad_nueva,$value["precio"],2,2,null,null,null); 
			}else{ 
				//$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida WHERE producto.producto_id = ".$value["cod_producto_venta"]);
				$this->movimiento_stock_tplatos($value["cod_producto_venta"],null,$value["cantidad"],$value["precio"],1,2);
				$this->movimiento_stock_tplatos($value["cod_producto_venta"],null,$cantidad_nueva,$value["precio"],2,2);
			}
			$detalle_venta=array(
					"cantidad"=>$_POST["cantidad"],
			);
			$this->db->where('id_detalle_venta',$_POST["id_detalle_venta"]);
			$this->db->update('detalle_venta', $detalle_venta);
		}
$cod_venta=$this->db->query("SELECT detalle_venta.id_venta FROM detalle_venta WHERE detalle_venta.id_detalle_venta=".$_POST["id_detalle_venta"].";")->row();
			$datos_venta=$this->Mantenimiento_m->consulta3("SELECT detalle_venta.cantidad, detalle_venta.precio FROM detalle_venta WHERE detalle_venta.estado_pedido <> 0 and detalle_venta.id_venta=".$cod_venta->id_venta);
		//multiplicar y sumar
			$total=0;
			foreach ($datos_venta as $key => $value) {
				$total=$total+((float)$value["cantidad"]*(float)$value["precio"]);
			}
		//traer monto delivery
			$monto_delivery=$this->db->query("SELECT venta.venta_monto_delivery FROM venta WHERE venta.venta_idventas=".$cod_venta->id_venta.";")->row();
			$total= $total+(float)$monto_delivery->venta_monto_delivery;
		//actualizar total venta
			$datos_venta_actualizar = array(
				"venta_monto"=>$total
			);
			$this->db->where('venta_idventas',$cod_venta->id_venta);
			$this->db->update('venta', $datos_venta_actualizar);
		
		echo 1;
	}
	public function editar_canje_parte(){
		$administrador = $datos_venta = $this->Mantenimiento_m->consulta3("SELECT id_usuarioe as empleado_id FROM usuario_eliminar WHERE usuario_eli = '".$_POST['usuariocanje']."'  and clave_eli = '".$_POST['contrasenacanje']."';");
		if(count($administrador)==0){
			echo 0;
			exit();
		}
			//editar detalle
		//DC : Descuento Canje.
		//DS : Descuento Soles.
		//DP : Descuento Porcentaje
		//print_r($_POST);exit();
			if ($_POST["descuento"] == 1) {
				$estadodescuento = 'DC';
				$descripcion = 'Canje';
			}else{
				$estadodescuento = 'DS';
				$descripcion = 'Descuento';
			}

			if ($_POST["estockcanje_producto"] == 0) {
				$estadoplato = 0;
			}else{
				$estadoplato = 1;
			}
			$detalle_venta=array(
				"cantidad"=>$_POST["estockcanje_producto"],
				"estado_pedido" => $estadoplato
			);
			$this->db->where('id_detalle_venta',$_POST["id_detalle_canje"]);
			$this->db->update('detalle_venta', $detalle_venta);
		//traer datos de la venta
			$cod_venta=$this->db->query("SELECT detalle_venta.id_venta,detalle_venta.cod_producto_venta FROM detalle_venta WHERE detalle_venta.id_detalle_venta=".$_POST["id_detalle_canje"].";")->row();
			$detalle_deventa = array(
				"descripcion" => $descripcion,
				"cantidad" => $_POST["cantidadcanje"],
				"precio" => $_POST["precio_canjeproducto"],
				"id_venta" => $cod_venta->id_venta,
				"fecha_preparar" => date("Y/m/d"),
				"id_tipoentrega" => 1,
				"estado_descuento" => $estadodescuento,
				"cod_producto_venta" => $cod_venta->cod_producto_venta
			);
			$this->db->insert('detalle_venta', $detalle_deventa);	
			$datos_venta=$this->Mantenimiento_m->consulta3("SELECT detalle_venta.cantidad, detalle_venta.precio FROM detalle_venta WHERE detalle_venta.estado_pedido <> 0 and detalle_venta.id_venta=".$cod_venta->id_venta);
		//multiplicar y sumar
			$total=0;
			foreach ($datos_venta as $key => $value) {
				$total=$total+((float)$value["cantidad"]*(float)$value["precio"]);
			}
		//traer monto delivery
			$monto_delivery=$this->db->query("SELECT venta.venta_monto_delivery FROM venta WHERE venta.venta_idventas=".$cod_venta->id_venta.";")->row();
			$total= $total+(float)$monto_delivery->venta_monto_delivery;
		//actualizar total venta
			$datos_venta_actualizar = array(
				"venta_monto"=>$total
			);
			$this->db->where('venta_idventas',$cod_venta->id_venta);
			$this->db->update('venta', $datos_venta_actualizar);
			echo 1;

//		}
	}


	public function mostrar_direccion_cliente()
	{
		// Get search term
		$term = $_GET['term'];
	    $cliente=$_GET['cliente'];
	    $datos=array();
	    if($cliente!="")
	    {

	    	    $sql="SELECT 
direccion.direccion_id as 'id',
direccion.direccion_descripcion as 'label',
direccion.direccion_descripcion as 'value'
from direccion
where 
direccion.cliente_id=".$cliente." and
direccion.direccion_estado=1 and
direccion.direccion_descripcion like '%".$term."%' order by direccion_id desc ";
	    $datos=$this->db->query($sql)->result_array();
	    }
	
      // echo $term;
	    echo json_encode($datos);exit();
	}

	public function guardar_cliente_direccion()
	{

		$cliente_id=$_POST["id_cliente_direccion"];
		$fdireccion_descripcion=$_POST["fdireccion_descripcion"];
		$data=array(
           "cliente_id"=>$cliente_id,
           "direccion_descripcion"=>$fdireccion_descripcion 
		);
      	$this->Mantenimiento_m->insertar("direccion",$data);
		$ultimoId = $this->db->insert_id();
		$response=array();

		$response["estado"]=true;
		$response["direccion_id"]=$ultimoId;
		$response["direccion_descripcion"]=$fdireccion_descripcion;
		echo json_encode($response);exit();

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
